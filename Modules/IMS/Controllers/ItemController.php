<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use Throwable;
use Modules\IMS\Models\BrandModel;
use Modules\IMS\Models\CategoryModel;
use Modules\IMS\Models\ItemAttachmentModel;
use Modules\IMS\Models\ItemMasterModel;
use Modules\IMS\Models\StockLotModel;
use Modules\IMS\Models\StorageLocationModel;
use Modules\IMS\Models\SupplierModel;
use Modules\IMS\Models\UnitModel;

class ItemController extends BaseImsController
{
    public function index(): string
    {
        $state  = $this->resolvePaginationState();
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $db     = Database::connect();

        $countBuilder = $db->table('ims_item_master iim');
        if ($search !== '') {
            $countBuilder->groupStart()
                ->like('iim.item_name', $search)
                ->orLike('iim.item_code', $search)
                ->orLike('iim.alternate_name', $search)
                ->orLike('iim.cas_no', $search)
                ->groupEnd();
        }
        $total = (int) $countBuilder->countAllResults();
        $pager = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $rowsBuilder = $db->table('ims_item_master iim')
            ->select('iim.*, ic.category_name, iu.unit_name')
            ->join('ims_categories ic', 'ic.id = iim.category_id', 'left')
            ->join('ims_units iu', 'iu.id = iim.default_unit_id', 'left');

        if ($search !== '') {
            $rowsBuilder->groupStart()
                ->like('iim.item_name', $search)
                ->orLike('iim.item_code', $search)
                ->orLike('iim.alternate_name', $search)
                ->orLike('iim.cas_no', $search)
                ->groupEnd();
        }

        $rows = $rowsBuilder
            ->orderBy('iim.item_name', 'asc')
            ->limit($pager['perPage'], $pager['offset'])
            ->get()->getResultArray();

        return $this->render('Modules\IMS\Views\item\items', [
            'pageTitle'    => lang('IMS.items.indexTitle'),
            'pageSubtitle' => lang('IMS.items.indexSubtitle'),
            'rows'         => $rows,
            'itemPager'    => $pager,
            'itemQuery'    => ['page' => $pager['page'], 'per_page' => $pager['perPage'], 'q' => $search],
        ]);
    }

    public function form(int $id = 0)
    {
        $item = [];

        if ($id > 0) {
            $item = model(ItemMasterModel::class)->find($id);

            if ($item === null) {
                return redirect()->to(site_url('ims/items'))
                    ->with('message', 'Item not found.')
                    ->with('message_type', 'danger');
            }
        }

        return $this->render('Modules\IMS\Views\item\item_form', [
            'pageTitle'    => $id > 0 ? lang('IMS.items.form.editTitle') : lang('IMS.items.form.addTitle'),
            'pageSubtitle' => $id > 0 ? lang('IMS.items.form.editSubtitle') : lang('IMS.items.form.addSubtitle'),
            'item'         => $item,
            'categories'   => model(CategoryModel::class)->orderBy('category_name', 'asc')->findAll(),
            'units'        => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'validation'   => session('errors') ?? [],
        ]);
    }

    public function create()
    {
        if (($v = $this->validateItemForm()) !== true) {
            return $v;
        }

        model(ItemMasterModel::class)->insert($this->itemPayload(true));

        return $this->redirectIndex('Item created successfully.');
    }

    public function update(int $id)
    {
        $model = model(ItemMasterModel::class);
        $item  = $model->find($id);

        if ($item === null) {
            return $this->redirectIndex('Item record was not found.', 'danger');
        }

        if (($v = $this->validateItemForm($id)) !== true) {
            return $v;
        }

        $model->update($id, $this->itemPayload(false));

        return $this->redirectIndex('Item updated successfully.');
    }

    public function delete(int $id)
    {
        try {
            model(ItemMasterModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectIndex('Item cannot be deleted because it is already referenced by IMS records.', 'danger');
        }

        return $this->redirectIndex('Item deleted successfully.');
    }

    public function detail(int $id): string
    {
        $db   = Database::connect();
        $item = $db->table('ims_item_master iim')
            ->select('iim.*, ic.category_name, iu.unit_name')
            ->join('ims_categories ic', 'ic.id = iim.category_id', 'left')
            ->join('ims_units iu', 'iu.id = iim.default_unit_id', 'left')
            ->where('iim.id', $id)
            ->get()->getRowArray();

        if ($item === null) {
            return redirect()->to(site_url('ims/items'))
                ->with('message', 'Item not found.')
                ->with('message_type', 'danger');
        }

        $stockLots = $db->table('ims_stock_lots isl')
            ->select('isl.*, iu.unit_name, init_unit.unit_name as initial_unit_name, supplier.supplier_name, brand.brand_name, loc.name as storage_location_name')
            ->join('ims_units iu', 'iu.id = isl.current_unit_id', 'left')
            ->join('ims_units init_unit', 'init_unit.id = isl.initial_unit_id', 'left')
            ->join('ims_suppliers supplier', 'supplier.id = isl.supplier_id', 'left')
            ->join('ims_brands brand', 'brand.id = isl.brand_id', 'left')
            ->join('ims_storage_locations loc', 'loc.id = isl.storage_location_id', 'left')
            ->where('isl.item_id', $id)
            ->orderBy('isl.id', 'desc')
            ->get()->getResultArray();

        try {
            $attachments = model(ItemAttachmentModel::class)
                ->where('item_id', $id)
                ->orderBy('uploaded_at', 'desc')
                ->findAll();
        } catch (Throwable) {
            $attachments = [];
        }

        return $this->render('Modules\IMS\Views\item\item_detail', [
            'pageTitle'    => ($item['item_name'] ?? ''),
            'pageSubtitle' => lang('IMS.items.detail.subtitle'),
            'item'         => $item,
            'stockLots'    => $stockLots,
            'attachments'  => $attachments,
            'validation'   => session('errors') ?? [],
            'modalState'   => session('ims_item_detail_modal'),
        ]);
    }

    public function lotForm(int $itemId, int $lotId = 0)
    {
        $item = model(ItemMasterModel::class)->find($itemId);

        if ($item === null) {
            return redirect()->to(site_url('ims/items'))
                ->with('message', 'Item not found.')
                ->with('message_type', 'danger');
        }

        $lot = [];

        if ($lotId > 0) {
            $lot = model(StockLotModel::class)->where('item_id', $itemId)->find($lotId);

            if ($lot === null) {
                return redirect()->to(site_url('ims/items/' . $itemId))
                    ->with('message', 'Stock lot not found.')
                    ->with('message_type', 'danger');
            }
        }

        return $this->render('Modules\IMS\Views\item\item_stock_lot_form', [
            'pageTitle'    => $lotId > 0 ? lang('IMS.items.stockLotForm.editTitle') : lang('IMS.items.stockLotForm.addTitle'),
            'pageSubtitle' => ($item['item_code'] ?? '') . ' - ' . ($item['item_name'] ?? ''),
            'item'         => $item,
            'lot'          => $lot,
            'suppliers'    => model(SupplierModel::class)->orderBy('supplier_name', 'asc')->findAll(),
            'brands'       => model(BrandModel::class)->orderBy('brand_name', 'asc')->findAll(),
            'units'        => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'locations'    => model(StorageLocationModel::class)->orderBy('name', 'asc')->findAll(),
            'validation'   => session('errors') ?? [],
        ]);
    }

    public function uploadAttachment(int $id)
    {
        $item = model(ItemMasterModel::class)->find($id);

        if ($item === null) {
            return $this->redirectIndex('Item record was not found.', 'danger');
        }

        $file = $this->request->getFile('attachment_file');

        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectDetail($id, null, 'item-attachment-modal', 'danger', [
                'attachment_file' => 'Please choose a file to upload.',
            ]);
        }

        if (! $file->isValid()) {
            return $this->redirectDetail($id, null, 'item-attachment-modal', 'danger', [
                'attachment_file' => 'File upload failed. Please try again.',
            ]);
        }

        if ($file->getSizeByUnit('mb') > 10) {
            return $this->redirectDetail($id, null, 'item-attachment-modal', 'danger', [
                'attachment_file' => 'Attachment size must not exceed 10MB.',
            ]);
        }

        $attachmentType = trim((string) $this->request->getPost('attachment_type')) ?: 'document';
        $remarks        = trim((string) $this->request->getPost('remarks')) ?: null;
        $destination    = WRITEPATH . 'uploads/ims/item/' . $id;

        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $originalName = $file->getClientName();
        $newName      = $file->getRandomName();
        $file->move($destination, $newName, true);

        model(ItemAttachmentModel::class)->insert([
            'item_id'         => $id,
            'attachment_type' => $attachmentType,
            'file_name'       => $originalName,
            'file_path'       => 'uploads/ims/item/' . $id . '/' . $newName,
            'remarks'         => $remarks,
            'uploaded_by'     => lab_core_current_user()?->id,
            'uploaded_at'     => date('Y-m-d H:i:s'),
        ]);

        return $this->redirectDetail($id, 'Attachment uploaded successfully.');
    }

    public function serveAttachment(int $id, int $attachmentId)
    {
        $att = model(ItemAttachmentModel::class)->find($attachmentId);

        if ($att === null || (int) $att['item_id'] !== $id) {
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

    public function createLot(int $itemId)
    {
        if (model(ItemMasterModel::class)->find($itemId) === null) {
            return $this->redirectIndex('Item record was not found.', 'danger');
        }

        if (($v = $this->validateItemLotForm(null, $itemId)) !== true) {
            return $v;
        }

        $payload            = $this->lotPayload(true);
        $payload['item_id'] = $itemId;
        model(StockLotModel::class)->insert($payload);

        return $this->redirectDetail($itemId, 'Stock lot created successfully.');
    }

    public function updateLot(int $itemId, int $lotId)
    {
        $model = model(StockLotModel::class);
        $lot   = $model->where('item_id', $itemId)->find($lotId);

        if ($lot === null) {
            return $this->redirectDetail($itemId, 'Stock lot was not found for this item.', null, 'danger');
        }

        if (($v = $this->validateItemLotForm($lotId, $itemId)) !== true) {
            return $v;
        }

        $payload            = $this->lotPayload(false);
        $payload['item_id'] = $itemId;
        $model->update($lotId, $payload);

        return $this->redirectDetail($itemId, 'Stock lot updated successfully.');
    }

    public function deleteLot(int $itemId, int $lotId)
    {
        $model = model(StockLotModel::class);
        $lot   = $model->where('item_id', $itemId)->find($lotId);

        if ($lot === null) {
            return $this->redirectDetail($itemId, 'Stock lot was not found for this item.', null, 'danger');
        }

        try {
            $model->delete($lotId);
        } catch (Throwable) {
            return $this->redirectDetail($itemId, 'Stock lot cannot be deleted because it is already referenced by IMS records.', null, 'danger');
        }

        return $this->redirectDetail($itemId, 'Stock lot deleted successfully.');
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
        $redirect = redirect()->to(site_url('ims/items?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])));

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        return $redirect;
    }

    private function redirectForm(int $id = 0, array $errors = [])
    {
        $url           = $id > 0 ? site_url("ims/items/{$id}/edit") : site_url('ims/items/create');
        $flashedErrors = $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []);

        return redirect()->to($url)->withInput()->with('errors', $flashedErrors);
    }

    private function redirectLotForm(int $itemId, int $lotId = 0, array $errors = [])
    {
        $url           = $lotId > 0
            ? site_url("ims/items/{$itemId}/lots/{$lotId}/edit")
            : site_url("ims/items/{$itemId}/lots/create");
        $flashedErrors = $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []);

        return redirect()->to($url)->withInput()->with('errors', $flashedErrors);
    }

    private function redirectDetail(int $itemId, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], ?int $lotId = null)
    {
        $redirect = redirect()->to(site_url('ims/items/' . $itemId))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }

        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_item_detail_modal', ['modal' => $modal, 'item_id' => $itemId, 'lot_id' => $lotId]);
        }

        return $redirect;
    }

    private function validateItemForm(?int $ignoreId = null)
    {
        $rules = [
            'item_name'       => 'required|min_length[2]|max_length[255]',
            'alternate_name'  => 'permit_empty|max_length[255]',
            'category_id'     => 'required|integer|is_not_unique[ims_categories.id]',
            'description'     => 'permit_empty',
            'cas_no'          => 'permit_empty|max_length[100]',
            'ec_no'           => 'permit_empty|max_length[100]',
            'default_unit_id' => 'required|integer|is_not_unique[ims_units.id]',
            'min_stock_level' => 'permit_empty|decimal',
            'max_stock_level' => 'permit_empty|decimal',
            'reorder_level'   => 'permit_empty|decimal',
            'status'          => 'required|in_list[active,inactive,blocked,obsolete]',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectForm($ignoreId ?? 0);
        }

        $minStock = $this->nullableDecimal('min_stock_level');
        $maxStock = $this->nullableDecimal('max_stock_level');

        if ($minStock !== null && $maxStock !== null && $minStock > $maxStock) {
            return $this->redirectForm($ignoreId ?? 0, [
                'max_stock_level' => 'Max stock level must be greater than or equal to min stock level.',
            ]);
        }

        return true;
    }

    private function validateItemLotForm(?int $ignoreId, int $itemId)
    {
        $rules = [
            'item_id'               => 'required|integer|is_not_unique[ims_item_master.id]',
            'lot_no'                => 'permit_empty|max_length[100]',
            'supplier_lot_no'       => 'permit_empty|max_length[100]',
            'serial_no'             => 'permit_empty|max_length[100]',
            'type'                  => 'permit_empty|max_length[100]',
            'received_date'         => 'permit_empty|valid_date[Y-m-d]',
            'manufacture_date'      => 'permit_empty|valid_date[Y-m-d]',
            'expiry_date'           => 'permit_empty|valid_date[Y-m-d]',
            'opened_date'           => 'permit_empty|valid_date[Y-m-d]',
            'retest_date'           => 'permit_empty|valid_date[Y-m-d]',
            'supplier_id'           => 'permit_empty|integer|is_not_unique[ims_suppliers.id]',
            'catalog_no'            => 'permit_empty|max_length[100]',
            'grade'                 => 'permit_empty|max_length[100]',
            'brand_id'              => 'permit_empty|integer|is_not_unique[ims_brands.id]',
            'pack_size'             => 'permit_empty|max_length[100]',
            'initial_qty'           => 'required|decimal',
            'initial_unit_id'       => 'required|integer|is_not_unique[ims_units.id]',
            'current_qty'           => 'required|decimal',
            'current_unit_id'       => 'required|integer|is_not_unique[ims_units.id]',
            'concentration_value'   => 'permit_empty|decimal',
            'concentration_unit_id' => 'permit_empty|integer|is_not_unique[ims_units.id]',
            'purity_value'          => 'permit_empty|decimal',
            'storage_location_id'   => 'permit_empty|integer|is_not_unique[ims_storage_locations.id]',
            'ownership_status'      => 'required|in_list[owned,consigned,borrowed]',
            'remarks'               => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return $this->redirectLotForm($itemId, $ignoreId ?? 0);
        }

        $receivedDate    = $this->nullableDate('received_date');
        $expiryDate      = $this->nullableDate('expiry_date');
        $manufactureDate = $this->nullableDate('manufacture_date');

        if ($manufactureDate !== null && $expiryDate !== null && $manufactureDate > $expiryDate) {
            return $this->redirectLotForm($itemId, $ignoreId ?? 0, [
                'expiry_date' => 'Expiry date must be after manufacture date.',
            ]);
        }

        if ($receivedDate !== null && $expiryDate !== null && $receivedDate > $expiryDate) {
            return $this->redirectLotForm($itemId, $ignoreId ?? 0, [
                'expiry_date' => 'Expiry date must be after received date.',
            ]);
        }

        if ($this->nullableDecimal('initial_qty') !== null && $this->nullableDecimal('initial_qty') < 0) {
            return $this->redirectLotForm($itemId, $ignoreId ?? 0, [
                'initial_qty' => 'Initial quantity cannot be negative.',
            ]);
        }

        if ($this->nullableDecimal('current_qty') !== null && $this->nullableDecimal('current_qty') < 0) {
            return $this->redirectLotForm($itemId, $ignoreId ?? 0, [
                'current_qty' => 'Current quantity cannot be negative.',
            ]);
        }

        return true;
    }

    /**
     * @return array<string, int|string|float|null>
     */
    private function itemPayload(bool $isCreate): array
    {
        $userId  = lab_core_current_user()?->id;
        $payload = [
            'item_name'                => trim((string) $this->request->getPost('item_name')),
            'alternate_name'           => trim((string) $this->request->getPost('alternate_name')) ?: null,
            'category_id'              => (int) $this->request->getPost('category_id'),
            'description'              => trim((string) $this->request->getPost('description')) ?: null,
            'cas_no'                   => trim((string) $this->request->getPost('cas_no')) ?: null,
            'ec_no'                    => trim((string) $this->request->getPost('ec_no')) ?: null,
            'default_unit_id'          => (int) $this->request->getPost('default_unit_id'),
            'min_stock_level'          => $this->nullableDecimal('min_stock_level'),
            'max_stock_level'          => $this->nullableDecimal('max_stock_level'),
            'reorder_level'            => $this->nullableDecimal('reorder_level'),
            'requires_expiry_tracking' => $this->request->getPost('requires_expiry_tracking') ? 1 : 0,
            'requires_lot_tracking'    => $this->request->getPost('requires_lot_tracking') ? 1 : 0,
            'requires_coa'             => $this->request->getPost('requires_coa') ? 1 : 0,
            'requires_sds'             => $this->request->getPost('requires_sds') ? 1 : 0,
            'requires_special_storage' => $this->request->getPost('requires_special_storage') ? 1 : 0,
            'is_controlled_substance'  => $this->request->getPost('is_controlled_substance') ? 1 : 0,
            'is_flammable'             => $this->request->getPost('is_flammable') ? 1 : 0,
            'is_corrosive'             => $this->request->getPost('is_corrosive') ? 1 : 0,
            'is_toxic'                 => $this->request->getPost('is_toxic') ? 1 : 0,
            'is_cmr'                   => $this->request->getPost('is_cmr') ? 1 : 0,
            'status'                   => trim((string) $this->request->getPost('status')) ?: 'active',
            'is_active'                => $this->request->getPost('is_active') ? 1 : 0,
            'updated_by'               => $userId,
        ];

        if ($isCreate) {
            $payload['item_code']  = $this->generateUniqueItemCode();
            $payload['created_by'] = $userId;
        }

        return $payload;
    }

    private function generateUniqueItemCode(): string
    {
        $maxNumber = 0;
        $rows      = model(ItemMasterModel::class)
            ->select('item_code')
            ->like('item_code', 'INV_', 'after')
            ->findAll();

        foreach ($rows as $row) {
            if (preg_match('/^INV_(\d+)$/', (string) ($row['item_code'] ?? ''), $matches) === 1) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        do {
            $maxNumber++;
            $code = 'INV_' . str_pad((string) $maxNumber, 9, '0', STR_PAD_LEFT);
        } while (model(ItemMasterModel::class)->where('item_code', $code)->first() !== null);

        return $code;
    }

    /**
     * @return array<string, int|string|float|null>
     */
    private function lotPayload(bool $isCreate): array
    {
        $userId  = lab_core_current_user()?->id;
        $payload = [
            'item_id'               => (int) $this->request->getPost('item_id'),
            'lot_no'                => trim((string) $this->request->getPost('lot_no')) ?: null,
            'supplier_lot_no'       => trim((string) $this->request->getPost('supplier_lot_no')) ?: null,
            'serial_no'             => trim((string) $this->request->getPost('serial_no')) ?: null,
            'type'                  => trim((string) $this->request->getPost('type')) ?: null,
            'received_date'         => $this->nullableDate('received_date'),
            'manufacture_date'      => $this->nullableDate('manufacture_date'),
            'expiry_date'           => $this->nullableDate('expiry_date'),
            'opened_date'           => $this->nullableDate('opened_date'),
            'opened_by'             => $this->nullableDate('opened_date') !== null ? $userId : null,
            'retest_date'           => $this->nullableDate('retest_date'),
            'supplier_id'           => $this->nullableInt('supplier_id'),
            'catalog_no'            => trim((string) $this->request->getPost('catalog_no')) ?: null,
            'grade'                 => trim((string) $this->request->getPost('grade')) ?: null,
            'brand_id'              => $this->nullableInt('brand_id'),
            'pack_size'             => trim((string) $this->request->getPost('pack_size')) ?: null,
            'initial_qty'           => $this->nullableDecimal('initial_qty') ?? 0,
            'initial_unit_id'       => (int) $this->request->getPost('initial_unit_id'),
            'current_qty'           => $this->nullableDecimal('current_qty') ?? 0,
            'current_unit_id'       => (int) $this->request->getPost('current_unit_id'),
            'concentration_value'   => $this->nullableDecimal('concentration_value'),
            'concentration_unit_id' => $this->nullableInt('concentration_unit_id'),
            'purity_value'          => $this->nullableDecimal('purity_value'),
            'storage_location_id'   => $this->nullableInt('storage_location_id'),
            'ownership_status'      => trim((string) $this->request->getPost('ownership_status')) ?: 'owned',
            'remarks'               => trim((string) $this->request->getPost('remarks')) ?: null,
            'updated_by'            => $userId,
        ];

        if ($isCreate) {
            $payload['internal_lot_no'] = $this->generateUniqueInternalLotNo();
            $payload['created_by']      = $userId;
        }

        return $payload;
    }

    private function generateUniqueInternalLotNo(): string
    {
        $maxNumber = 0;
        $rows      = model(StockLotModel::class)
            ->select('internal_lot_no')
            ->like('internal_lot_no', 'L-', 'after')
            ->findAll();

        foreach ($rows as $row) {
            if (preg_match('/^L-(\d+)$/', (string) ($row['internal_lot_no'] ?? ''), $matches) === 1) {
                $maxNumber = max($maxNumber, (int) $matches[1]);
            }
        }

        do {
            $maxNumber++;
            $code = 'L-' . str_pad((string) $maxNumber, 8, '0', STR_PAD_LEFT);
        } while (model(StockLotModel::class)->where('internal_lot_no', $code)->first() !== null);

        return $code;
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
