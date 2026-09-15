<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;
use Modules\IMS\Models\StockLotModel;
use Modules\IMS\Models\StocklotAttachmentModel;
use Modules\IMS\Models\StorageLocationModel;
use Modules\IMS\Models\TransactionModel;

class LotController extends BaseImsController
{
    public function index(): string
    {
        $state  = $this->resolvePaginationState();
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $db     = Database::connect();

        $applySearch = static function ($builder) use ($search): void {
            if ($search !== '') {
                $builder->join('ims_item_master iim_s', 'iim_s.id = isl.item_id', 'left')
                    ->groupStart()
                    ->like('iim_s.item_name', $search)
                    ->orLike('iim_s.item_code', $search)
                    ->orLike('isl.internal_lot_no', $search)
                    ->orLike('isl.lot_no', $search)
                    ->orLike('isl.supplier_lot_no', $search)
                    ->groupEnd();
            }
        };

        $countBuilder = $db->table('ims_stock_lots isl');
        $applySearch($countBuilder);
        $total = (int) $countBuilder->countAllResults();
        $pager = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $rowsBuilder = $db->table('ims_stock_lots isl')
            ->select('isl.*, iim.item_code, iim.item_name, iu.unit_name, init_unit.unit_name as initial_unit_name, supplier.supplier_name, brand.brand_name, loc.name as storage_location_name')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->join('ims_units iu', 'iu.id = isl.current_unit_id', 'left')
            ->join('ims_units init_unit', 'init_unit.id = isl.initial_unit_id', 'left')
            ->join('ims_suppliers supplier', 'supplier.id = isl.supplier_id', 'left')
            ->join('ims_brands brand', 'brand.id = isl.brand_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = isl.storage_location_id', 'left');

        if ($search !== '') {
            $rowsBuilder->groupStart()
                ->like('iim.item_name', $search)
                ->orLike('iim.item_code', $search)
                ->orLike('isl.internal_lot_no', $search)
                ->orLike('isl.lot_no', $search)
                ->orLike('isl.supplier_lot_no', $search)
                ->groupEnd();
        }

        $rows = $rowsBuilder
            ->orderBy('isl.id', 'desc')
            ->limit($pager['perPage'], $pager['offset'])
            ->get()->getResultArray();

        return $this->render('Modules\IMS\Views\lots\lots', [
            'pageTitle'    => lang('IMS.lots.indexTitle'),
            'pageSubtitle' => lang('IMS.lots.indexSubtitle'),
            'rows'         => $rows,
            'lotPager'     => $pager,
            'lotQuery'     => ['page' => $pager['page'], 'per_page' => $pager['perPage'], 'q' => $search],
        ]);
    }

    public function detail(int $id): string
    {
        $db  = Database::connect();
        $lot = $this->findLotForDisplay($id);

        if ($lot === null) {
            return redirect()->to(site_url('ims/lots'))
                ->with('message', lang('IMS.lots.messages.notFound'))
                ->with('message_type', 'danger');
        }

        $transactions = $db->table('ims_transactions it')
            ->select('it.*, iu.unit_name, from_loc.name as from_location_name, to_loc.name as to_location_name, u.username as performed_by_name')
            ->join('ims_units iu', 'iu.id = it.unit_id', 'left')
            ->join('ims_storage_locations from_loc', 'from_loc.id = it.from_location_id', 'left')
            ->join('ims_storage_locations to_loc', 'to_loc.id = it.to_location_id', 'left')
            ->join('users u', 'u.id = it.performed_by', 'left')
            ->where('it.stock_lot_id', $id)
            ->orderBy('it.transaction_date', 'desc')
            ->get()->getResultArray();

        try {
            $attachments = model(StocklotAttachmentModel::class)
                ->where('ims_stock_lot_id', $id)
                ->orderBy('uploaded_at', 'desc')
                ->findAll();
        } catch (Throwable) {
            $attachments = [];
        }

        $lotLabel = $lot['internal_lot_no'] ?: ($lot['lot_no'] ?: '#' . $id);

        return $this->render('Modules\IMS\Views\lots\lot_detail', [
            'pageTitle'    => $lotLabel . ' - ' . ($lot['item_name'] ?? lang('IMS.lots.stockLot')),
            'pageSubtitle' => lang('IMS.lots.detailSubtitle'),
            'lot'          => $lot,
            'transactions' => $transactions,
            'attachments'  => $attachments,
            'locations'    => model(StorageLocationModel::class)->orderBy('name', 'asc')->findAll(),
            'validation'   => session('errors') ?? [],
            'modalState'   => session('ims_lot_detail_modal'),
        ]);
    }

    public function label(int $id)
    {
        $lot = $this->findLotForDisplay($id);

        if ($lot === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('IMS.lots.messages.notFound'));
        }

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('Modules\IMS\Views\lots\lot-label-pdf', ['lot' => $lot]), 'UTF-8');
        $dompdf->setPaper([0, 0, 141.73, 85.04]);
        $dompdf->render();

        $lotLabel = $lot['internal_lot_no'] ?: ($lot['lot_no'] ?: 'stock-lot-' . $id);
        $fileName = preg_replace('/[^A-Za-z0-9_\-]+/', '-', (string) $lotLabel);
        $fileName = trim((string) $fileName, '-') ?: 'stock-lot-label';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '-label.pdf"')
            ->setBody($dompdf->output());
    }

    public function createTransaction(int $lotId)
    {
        $lot = model(StockLotModel::class)->find($lotId);

        if ($lot === null) {
            return $this->redirectIndex(lang('IMS.lots.messages.notFound'), 'danger');
        }

        if (($v = $this->validateLotTransactionForm($lotId)) !== true) {
            return $v;
        }

        model(TransactionModel::class)->insert($this->transactionPayload(true));

        return $this->redirectDetail($lotId, lang('IMS.lots.messages.transactionRecorded'));
    }

    public function uploadAttachment(int $id)
    {
        $lot = model(StockLotModel::class)->find($id);

        if ($lot === null) {
            return $this->redirectIndex(lang('IMS.lots.messages.notFound'), 'danger');
        }

        $file = $this->request->getFile('attachment_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectDetail($id, null, 'lot-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.lots.validation.chooseFile'),
            ]);
        }

        if (! $file->isValid()) {
            return $this->redirectDetail($id, null, 'lot-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.lots.validation.uploadFailed'),
            ]);
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->redirectDetail($id, null, 'lot-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.lots.validation.maxFileSize'),
            ]);
        }

        $attachmentType = trim((string) $this->request->getPost('attachment_type')) ?: 'document';
        $remarks        = trim((string) $this->request->getPost('remarks')) ?: null;
        $destination    = WRITEPATH . 'uploads/ims/stock-lots/' . $id;

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $originalName = $file->getClientName();
        $newName      = $file->getRandomName();
        $file->move($destination, $newName, true);

        model(StocklotAttachmentModel::class)->insert([
            'ims_stock_lot_id' => $id,
            'attachment_type'  => $attachmentType,
            'file_name'        => $originalName,
            'file_path'        => 'uploads/ims/stock-lots/' . $id . '/' . $newName,
            'remarks'          => $remarks,
            'uploaded_by'      => lab_core_current_user()?->id,
            'uploaded_at'      => date('Y-m-d H:i:s'),
        ]);

        return $this->redirectDetail($id, lang('IMS.lots.messages.attachmentUploaded'));
    }

    public function serveAttachment(int $id, int $attachmentId)
    {
        $att = model(StocklotAttachmentModel::class)->find($attachmentId);

        if ($att === null || (int) $att['ims_stock_lot_id'] !== $id) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $fullPath = WRITEPATH . ltrim((string) $att['file_path'], '/\\');

        if (! is_file($fullPath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $mime = mime_content_type($fullPath) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . rawurlencode((string) $att['file_name']) . '"')
            ->setHeader('Content-Length', (string) filesize($fullPath))
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody((string) file_get_contents($fullPath));
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function resolvePaginationState(): array
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? $this->request->getPost('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? $this->request->getPost('per_page') ?? 10);

        return ['page' => $page, 'perPage' => in_array($perPage, [10, 25, 50], true) ? $perPage : 10];
    }

    private function redirectIndex(?string $message = null, string $type = 'success')
    {
        $state    = $this->resolvePaginationState();
        $redirect = redirect()->to(site_url('ims/lots?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])));

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        return $redirect;
    }

    private function redirectDetail(int $lotId, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [])
    {
        $redirect = redirect()->to(site_url('ims/lots/' . $lotId))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_lot_detail_modal', ['modal' => $modal, 'lot_id' => $lotId]);
        }

        return $redirect;
    }

    private function validateLotTransactionForm(int $lotId)
    {
        $rules = [
            'transaction_type' => 'required|in_list[receipt,issue,transfer,adjustment,return,disposal]',
            'transaction_date' => 'required',
            'item_id'          => 'required|integer|is_not_unique[ims_item_master.id]',
            'stock_lot_id'     => 'permit_empty|integer|is_not_unique[ims_stock_lots.id]',
            'qty'              => 'required|decimal',
            'unit_id'          => 'required|integer|is_not_unique[ims_units.id]',
            'from_location_id' => 'permit_empty|integer|is_not_unique[ims_storage_locations.id]',
            'to_location_id'   => 'permit_empty|integer|is_not_unique[ims_storage_locations.id]',
            'reason'           => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectDetail($lotId, null, 'lot-tx-modal', 'danger');
        }

        $rawDate   = trim((string) $this->request->getPost('transaction_date'));
        $converted = str_replace('T', ' ', $rawDate);

        if (strlen($converted) === 16) {
            $converted .= ':00';
        }

        if (\DateTime::createFromFormat('Y-m-d H:i:s', $converted) === false) {
            return $this->redirectDetail($lotId, null, 'lot-tx-modal', 'danger', [
                'transaction_date' => lang('IMS.lots.validation.validDateTime'),
            ]);
        }

        if ((float) $this->request->getPost('qty') <= 0) {
            return $this->redirectDetail($lotId, null, 'lot-tx-modal', 'danger', [
                'qty' => lang('IMS.lots.validation.qtyGreaterThanZero'),
            ]);
        }

        return true;
    }

    /**
     * @return array<string, int|string|float|null>
     */
    private function transactionPayload(bool $isCreate): array
    {
        $userId          = lab_core_current_user()?->id;
        $rawDate         = trim((string) $this->request->getPost('transaction_date'));
        $transactionDate = date('Y-m-d H:i:s');

        if ($rawDate !== '') {
            $converted       = str_replace('T', ' ', $rawDate);
            $transactionDate = strlen($converted) === 16 ? $converted . ':00' : $converted;
        }

        $payload = [
            'transaction_type' => trim((string) $this->request->getPost('transaction_type')),
            'transaction_date' => $transactionDate,
            'item_id'          => (int) $this->request->getPost('item_id'),
            'stock_lot_id'     => $this->nullableInt('stock_lot_id'),
            'qty'              => (float) $this->request->getPost('qty'),
            'unit_id'          => (int) $this->request->getPost('unit_id'),
            'from_location_id' => $this->nullableInt('from_location_id'),
            'to_location_id'   => $this->nullableInt('to_location_id'),
            'reason'           => trim((string) $this->request->getPost('reason')) ?: null,
            'performed_by'     => $userId,
        ];

        if ($isCreate) {
            $payload['transaction_no'] = $this->generateUniqueTransactionNo();
        }

        return $payload;
    }

    private function generateUniqueTransactionNo(): string
    {
        $maxNumber = 0;
        $rows      = model(TransactionModel::class)
            ->select('transaction_no')
            ->like('transaction_no', 'TXN-', 'after')
            ->findAll();

        foreach ($rows as $row) {
            if (preg_match('/^TXN-(\d+)$/', (string) ($row['transaction_no'] ?? ''), $matches) === 1) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        do {
            $maxNumber++;
            $code = 'TXN-' . str_pad((string) $maxNumber, 9, '0', STR_PAD_LEFT);
        } while (model(TransactionModel::class)->where('transaction_no', $code)->first() !== null);

        return $code;
    }

    private function findLotForDisplay(int $id): ?array
    {
        return Database::connect()->table('ims_stock_lots isl')
            ->select('isl.*, iim.item_name, iim.item_code, iu.unit_name, init_unit.unit_name as initial_unit_name, supplier.supplier_name, brand.brand_name, loc.name as storage_location_name')
            ->join('ims_item_master iim', 'iim.id = isl.item_id', 'left')
            ->join('ims_units iu', 'iu.id = isl.current_unit_id', 'left')
            ->join('ims_units init_unit', 'init_unit.id = isl.initial_unit_id', 'left')
            ->join('ims_suppliers supplier', 'supplier.id = isl.supplier_id', 'left')
            ->join('ims_brands brand', 'brand.id = isl.brand_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = isl.storage_location_id', 'left')
            ->where('isl.id', $id)
            ->get()->getRowArray();
    }

    private function nullableInt(string $field): ?int
    {
        $value = $this->request->getPost($field);

        return ($value === null || $value === '') ? null : (int) $value;
    }

    private function nullableDecimal(string $field): ?float
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : (float) $value;
    }

    private function nullableDate(string $field): ?string
    {
        $value = trim((string) $this->request->getPost($field));

        return $value === '' ? null : $value;
    }

    private function buildPaginationData(int $total, int $page, int $perPage): array
    {
        $perPage     = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $totalPages  = max(1, (int) ceil($total / $perPage));
        $page        = min(max(1, $page), $totalPages);
        $offset      = ($page - 1) * $perPage;
        $from        = $total === 0 ? 0 : $offset + 1;
        $to          = $total === 0 ? 0 : min($offset + $perPage, $total);
        $windowStart = max(1, $page - 2);
        $windowEnd   = min($totalPages, $page + 2);

        return [
            'page'       => $page,
            'perPage'    => $perPage,
            'total'      => $total,
            'totalPages' => $totalPages,
            'offset'     => $offset,
            'from'       => $from,
            'to'         => $to,
            'pages'      => range($windowStart, $windowEnd),
        ];
    }
}
