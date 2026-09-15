<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use Throwable;
use Modules\IMS\Models\ItemMasterModel;
use Modules\IMS\Models\StockLotModel;
use Modules\IMS\Models\StorageLocationModel;
use Modules\IMS\Models\TransactionModel;
use Modules\IMS\Models\UnitModel;

class TransactionController extends BaseImsController
{
    public function index(): string
    {
        $state = $this->resolvePaginationState();
        $db    = Database::connect();
        $total = (int) $db->table('ims_transactions')->countAllResults();
        $pager = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $rows = $db
            ->table('ims_transactions it')
            ->select('it.*, iim.item_name, iim.item_code, isl.internal_lot_no, iu.unit_name, from_loc.name as from_location_name, to_loc.name as to_location_name, u.username as performed_by_name')
            ->join('ims_item_master iim', 'iim.id = it.item_id', 'left')
            ->join('ims_stock_lots isl', 'isl.id = it.stock_lot_id', 'left')
            ->join('ims_units iu', 'iu.id = it.unit_id', 'left')
            ->join('ims_storage_locations from_loc', 'from_loc.id = it.from_location_id', 'left')
            ->join('ims_storage_locations to_loc', 'to_loc.id = it.to_location_id', 'left')
            ->join('users u', 'u.id = it.performed_by', 'left')
            ->orderBy('it.transaction_date', 'desc')
            ->limit($pager['perPage'], $pager['offset'])
            ->get()
            ->getResultArray();

        return $this->render('Modules\IMS\Views\transaction\transactions', [
            'pageTitle'    => lang('IMS.transactions.indexTitle'),
            'pageSubtitle' => lang('IMS.transactions.indexSubtitle'),
            'rows'         => $rows,
            'items'        => model(ItemMasterModel::class)->orderBy('item_name', 'asc')->findAll(),
            'units'        => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'locations'    => model(StorageLocationModel::class)->orderBy('name', 'asc')->findAll(),
            'lots'         => $db
                ->table('ims_stock_lots')
                ->select('id, item_id, internal_lot_no, lot_no')
                ->orderBy('id', 'desc')
                ->get()
                ->getResultArray(),
            'txPager'    => $pager,
            'txQuery'    => ['page' => $pager['page'], 'per_page' => $pager['perPage']],
            'validation' => session('errors') ?? [],
            'modalState' => session('ims_transactions_modal'),
        ]);
    }

    public function create()
    {
        if (($v = $this->validateForm()) !== true) {
            return $v;
        }

        model(TransactionModel::class)->insert($this->transactionPayload(true));

        return $this->redirectIndex(lang('IMS.transactions.messages.recorded'));
    }

    public function update(int $id)
    {
        $model = model(TransactionModel::class);

        if ($model->find($id) === null) {
            return $this->redirectIndex(lang('IMS.transactions.messages.notFound'), null, 'danger');
        }

        if (($v = $this->validateForm($id)) !== true) {
            return $v;
        }

        $model->update($id, $this->transactionPayload(false));

        return $this->redirectIndex(lang('IMS.transactions.messages.updated'));
    }

    public function delete(int $id)
    {
        if (model(TransactionModel::class)->find($id) === null) {
            return $this->redirectIndex(lang('IMS.transactions.messages.notFound'), null, 'danger');
        }

        try {
            model(TransactionModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectIndex(lang('IMS.transactions.messages.deleteReferenced'), null, 'danger');
        }

        return $this->redirectIndex(lang('IMS.transactions.messages.deleted'));
    }

    // ── Private helpers ──────────────────────────────────────────────────

    private function resolvePaginationState(): array
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? $this->request->getPost('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? $this->request->getPost('per_page') ?? 10);

        return ['page' => $page, 'perPage' => in_array($perPage, [10, 25, 50], true) ? $perPage : 10];
    }

    /**
     * @param array<string, string> $errors
     */
    private function redirectIndex(?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], ?int $txId = null)
    {
        $state    = $this->resolvePaginationState();
        $redirect = redirect()->to(site_url('ims/transactions?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirectErrors = $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []);
            $redirect = $redirect
                ->with('errors', $redirectErrors)
                ->with('ims_transactions_modal', [
                    'modal' => $modal,
                    'tx_id' => $txId,
                ]);
        }

        return $redirect;
    }

    private function validateForm(?int $ignoreId = null)
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
            return $this->redirectIndex(null, 'transaction-form-modal', 'danger', [], $ignoreId);
        }

        $rawDate   = trim((string) $this->request->getPost('transaction_date'));
        $converted = str_replace('T', ' ', $rawDate);

        if (strlen($converted) === 16) {
            $converted .= ':00';
        }

        if (\DateTime::createFromFormat('Y-m-d H:i:s', $converted) === false) {
            return $this->redirectIndex(null, 'transaction-form-modal', 'danger', [
                'transaction_date' => lang('IMS.transactions.validation.validDateTime'),
            ], $ignoreId);
        }

        if ((float) $this->request->getPost('qty') <= 0) {
            return $this->redirectIndex(null, 'transaction-form-modal', 'danger', [
                'qty' => lang('IMS.transactions.validation.qtyGreaterThanZero'),
            ], $ignoreId);
        }

        return true;
    }

    /**
     * @return array<string, int|string|float|null>
     */
    private function transactionPayload(bool $isCreate): array
    {
        $userId  = lab_core_current_user()?->id;
        $rawDate = trim((string) $this->request->getPost('transaction_date'));
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
        $rows = model(TransactionModel::class)
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

    private function nullableInt(string $field): ?int
    {
        $value = $this->request->getPost($field);

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return array{
     *   page:int,
     *   perPage:int,
     *   total:int,
     *   totalPages:int,
     *   offset:int,
     *   from:int,
     *   to:int,
     *   pages: array<int, int>
     * }
     */
    private function buildPaginationData(int $total, int $page, int $perPage): array
    {
        $perPage    = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = min(max(1, $page), $totalPages);
        $offset     = ($page - 1) * $perPage;
        $from       = $total === 0 ? 0 : $offset + 1;
        $to         = $total === 0 ? 0 : min($offset + $perPage, $total);
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
