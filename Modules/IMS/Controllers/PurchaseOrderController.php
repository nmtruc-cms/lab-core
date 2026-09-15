<?php

declare(strict_types=1);

namespace Modules\IMS\Controllers;

use Config\Database;
use CodeIgniter\HTTP\Files\UploadedFile;
use Dompdf\Dompdf;
use Dompdf\Options;
use Throwable;
use Modules\IMS\Models\BrandModel;
use Modules\IMS\Models\ItemMasterModel;
use Modules\IMS\Models\PurchaseOrderModel;
use Modules\IMS\Models\PurchaseOrderItemModel;
use Modules\IMS\Models\PurchaseOrderAttachmentModel;
use Modules\IMS\Models\SupplierModel;
use Modules\IMS\Models\UnitModel;

class PurchaseOrderController extends BaseImsController
{
    public function index(): string
    {
        $state  = $this->resolvePaginationState();
        $search = trim((string) ($this->request->getGet('q') ?? ''));
        $db     = Database::connect();

        $countBuilder = $db->table('ims_purchase_orders ipo')
            ->join('ims_suppliers isp', 'isp.id = ipo.supplier_id', 'left');
        if ($search !== '') {
            $countBuilder->groupStart()
                ->like('ipo.po_number', $search)
                ->orLike('isp.supplier_name', $search)
                ->orLike('ipo.status', $search)
                ->groupEnd();
        }
        $total  = (int) $countBuilder->countAllResults();
        $pager  = $this->buildPaginationData($total, $state['page'], $state['perPage']);

        $rowsBuilder = $db->table('ims_purchase_orders ipo')
            ->select('ipo.*, isp.supplier_name, cb.username as created_by_name, ab.username as approved_by_name')
            ->join('ims_suppliers isp', 'isp.id = ipo.supplier_id', 'left')
            ->join('users cb', 'cb.id = ipo.created_by', 'left')
            ->join('users ab', 'ab.id = ipo.approved_by', 'left');
        if ($search !== '') {
            $rowsBuilder->groupStart()
                ->like('ipo.po_number', $search)
                ->orLike('isp.supplier_name', $search)
                ->orLike('ipo.status', $search)
                ->groupEnd();
        }
        $rows = $rowsBuilder
            ->orderBy('ipo.id', 'desc')
            ->limit($pager['perPage'], $pager['offset'])
            ->get()->getResultArray();

        return $this->render('Modules\IMS\Views\purchasing\purchase_orders', [
            'pageTitle'     => lang('IMS.purchaseOrders.indexTitle'),
            'pageSubtitle'  => lang('IMS.purchaseOrders.indexSubtitle'),
            'rows'          => $rows,
            'suppliers'     => model(SupplierModel::class)->orderBy('supplier_name', 'asc')->findAll(),
            'pager'         => $pager,
            'poQuery'       => ['page' => $pager['page'], 'per_page' => $pager['perPage'], 'q' => $search],
            'validation'    => session('errors') ?? [],
            'modalState'    => session('ims_po_modal'),
            'currentUserId' => lab_core_current_user()?->id,
        ]);
    }

    public function detail(int $id): string
    {
        $db = Database::connect();
        $po = $this->findPurchaseOrderForDisplay($id);

        if ($po === null) {
            return redirect()->to(site_url('ims/purchase-orders'))
                ->with('message', lang('IMS.purchaseOrders.messages.notFound'))
                ->with('message_type', 'danger');
        }

        $items = $db->table('ims_purchase_order_items poi')
            ->select('poi.*, iri.item_name as req_item_name, iirl.request_name, iim.item_code, iu.unit_name, ib.brand_name')
            ->join('ims_request_items iri',      'iri.id = poi.request_item_id', 'left')
            ->join('ims_item_request_list iirl',  'iirl.id = iri.ims_item_request_list_id', 'left')
            ->join('ims_item_master iim',          'iim.id = poi.item_id', 'left')
            ->join('ims_units iu',                 'iu.id = poi.unit_id', 'left')
            ->join('ims_brands ib',                'ib.id = poi.brand_id', 'left')
            ->where('poi.purchase_order_id', $id)
            ->orderBy('poi.id', 'asc')
            ->get()->getResultArray();

        $attachments = model(PurchaseOrderAttachmentModel::class)
            ->where('purchase_order_id', $id)
            ->orderBy('uploaded_at', 'desc')
            ->findAll();

        $eligibleItems = $db->table('ims_request_items iri')
            ->select('iri.*, iirl.request_name, ic.category_name, ib2.brand_name, iu2.unit_name')
            ->join('ims_item_request_list iirl', 'iirl.id = iri.ims_item_request_list_id AND iirl.status = \'approved\'', 'inner')
            ->join('ims_categories ic',  'ic.id = iri.category_id', 'left')
            ->join('ims_brands ib2',     'ib2.id = iri.suggested_brand_id', 'left')
            ->join('ims_units iu2',      'iu2.id = iri.default_unit_id', 'left')
            ->join('ims_purchase_order_items poi2', 'poi2.request_item_id = iri.id', 'left')
            ->where('poi2.id IS NULL')
            ->orderBy('iirl.request_name', 'asc')
            ->orderBy('iri.item_name', 'asc')
            ->get()->getResultArray();

        return $this->render('Modules\IMS\Views\purchasing\purchase_order_detail', [
            'pageTitle'      => $po['po_number'],
            'pageSubtitle'   => lang('IMS.purchaseOrders.detailSubtitle'),
            'po'             => $po,
            'items'          => $items,
            'attachments'    => $attachments,
            'eligibleItems'  => $eligibleItems,
            'allItems'       => model(ItemMasterModel::class)->orderBy('item_name', 'asc')->findAll(),
            'allUnits'       => model(UnitModel::class)->orderBy('unit_name', 'asc')->findAll(),
            'allBrands'      => model(BrandModel::class)->orderBy('brand_name', 'asc')->findAll(),
            'suppliers'      => model(SupplierModel::class)->orderBy('supplier_name', 'asc')->findAll(),
            'validation'     => session('errors') ?? [],
            'modalState'     => session('ims_po_detail_modal'),
        ]);
    }

    public function pdf(int $id)
    {
        $po = $this->findPurchaseOrderForDisplay($id);

        if ($po === null) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(lang('IMS.purchaseOrders.messages.notFound'));
        }

        $items = $this->purchaseOrderItems($id);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml(view('Modules\IMS\Views\purchasing\po-pdf', [
            'po' => $po,
            'items' => $items,
        ]), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $fileName = preg_replace('/[^A-Za-z0-9_\-]+/', '-', (string) ($po['po_number'] ?? 'purchase-order'));
        $fileName = trim((string) $fileName, '-') ?: 'purchase-order';

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '.pdf"')
            ->setBody($dompdf->output());
    }

    public function create()
    {
        $supplierId = $this->nullableInt('supplier_id');
        if ($supplierId === null) {
            return $this->redirectPo(lang('IMS.purchaseOrders.validation.supplierRequired'), 'po-form-modal', 'danger');
        }

        model(PurchaseOrderModel::class)->insert([
            'po_number'               => $this->generatePoNumber(),
            'supplier_id'             => $supplierId,
            'status'                  => 'draft',
            'order_date'              => $this->nullableDate('order_date'),
            'expected_delivery_date'  => $this->nullableDate('expected_delivery_date'),
            'tax_amount'              => (float) ($this->request->getPost('tax_amount') ?? 0),
            'shipping_cost'           => (float) ($this->request->getPost('shipping_cost') ?? 0),
            'payment_terms'           => trim((string) $this->request->getPost('payment_terms')) ?: null,
            'delivery_address'        => trim((string) $this->request->getPost('delivery_address')) ?: null,
            'notes'                   => trim((string) $this->request->getPost('notes')) ?: null,
            'created_by'              => lab_core_current_user()?->id,
        ]);

        return $this->redirectPo(lang('IMS.purchaseOrders.messages.created'));
    }

    public function update(int $id)
    {
        $po = model(PurchaseOrderModel::class)->find($id);
        if ($po === null) {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }

        $supplierId = $this->nullableInt('supplier_id');
        if ($supplierId === null) {
            return $this->redirectDetail($id, null, 'po-edit-modal', 'danger');
        }

        $taxAmount    = (float) ($this->request->getPost('tax_amount') ?? 0);
        $shippingCost = (float) ($this->request->getPost('shipping_cost') ?? 0);
        $grandTotal   = (float) $po['subtotal'] + $taxAmount + $shippingCost;

        model(PurchaseOrderModel::class)->update($id, [
            'supplier_id'            => $supplierId,
            'order_date'             => $this->nullableDate('order_date'),
            'expected_delivery_date' => $this->nullableDate('expected_delivery_date'),
            'tax_amount'             => $taxAmount,
            'shipping_cost'          => $shippingCost,
            'grand_total'            => $grandTotal,
            'payment_terms'          => trim((string) $this->request->getPost('payment_terms')) ?: null,
            'delivery_address'       => trim((string) $this->request->getPost('delivery_address')) ?: null,
            'notes'                  => trim((string) $this->request->getPost('notes')) ?: null,
        ]);

        return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.updated'));
    }

    public function delete(int $id)
    {
        $po = model(PurchaseOrderModel::class)->find($id);
        if ($po === null) {
            return $this->redirectPo(lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if ($po['status'] !== 'draft') {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.draftDeleteOnly'), null, 'danger');
        }

        try {
            model(PurchaseOrderModel::class)->delete($id);
        } catch (Throwable) {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.deleteFailed'), null, 'danger');
        }

        return $this->redirectPo(lang('IMS.purchaseOrders.messages.deleted'));
    }

    public function approve(int $id)
    {
        $po = model(PurchaseOrderModel::class)->find($id);
        if ($po === null) {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if ($po['status'] !== 'draft') {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.draftApproveOnly'), null, 'danger');
        }

        model(PurchaseOrderModel::class)->update($id, [
            'status'        => 'approved',
            'approved_by'   => lab_core_current_user()?->id,
            'approved_date' => date('Y-m-d H:i:s'),
        ]);

        return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.approved'));
    }

    public function markOrdered(int $id)
    {
        $po = model(PurchaseOrderModel::class)->find($id);
        if ($po === null) {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if ($po['status'] !== 'approved') {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.approvedOrderOnly'), null, 'danger');
        }

        model(PurchaseOrderModel::class)->update($id, [
            'status'     => 'ordered',
            'order_date' => $this->nullableDate('order_date') ?? date('Y-m-d'),
        ]);

        return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.markedOrdered'));
    }

    public function cancel(int $id)
    {
        $po = model(PurchaseOrderModel::class)->find($id);
        if ($po === null) {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if (! in_array($po['status'], ['draft', 'approved', 'ordered'], true)) {
            return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.cancelFailed'), null, 'danger');
        }

        model(PurchaseOrderModel::class)->update($id, [
            'status' => 'cancelled',
            'notes'  => trim((string) $this->request->getPost('cancel_reason')) ?: $po['notes'],
        ]);

        return $this->redirectDetail($id, lang('IMS.purchaseOrders.messages.cancelled'));
    }

    public function addItem(int $poId)
    {
        $po = model(PurchaseOrderModel::class)->find($poId);
        if ($po === null) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if ($po['status'] !== 'draft') {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.draftAddItemOnly'), null, 'danger');
        }

        $requestItemId = $this->nullableInt('request_item_id');
        if ($requestItemId === null) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.validation.requestItemRequired'), 'po-item-modal', 'danger');
        }

        $db  = Database::connect();
        $iri = $db->table('ims_request_items iri')
            ->select('iri.*, iirl.status as request_status')
            ->join('ims_item_request_list iirl', 'iirl.id = iri.ims_item_request_list_id', 'left')
            ->where('iri.id', $requestItemId)
            ->get()->getRowArray();

        if ($iri === null) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.requestItemNotFound'), 'po-item-modal', 'danger');
        }
        if ($iri['request_status'] !== 'approved') {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.requestItemMustBeApproved'), 'po-item-modal', 'danger');
        }
        $alreadyLinked = $db->table('ims_purchase_order_items')
            ->where('request_item_id', $requestItemId)
            ->countAllResults();
        if ($alreadyLinked > 0) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.requestItemAlreadyLinked'), 'po-item-modal', 'danger');
        }

        $qtyOrdered      = (float) ($this->request->getPost('qty_ordered') ?? 0);
        $unitPrice       = (float) ($this->request->getPost('unit_price') ?? 0);
        $discountPercent = (float) ($this->request->getPost('discount_percent') ?? 0);
        $taxRate         = (float) ($this->request->getPost('tax_rate') ?? 0);
        $lineTotal       = $this->calculatePoItemLineTotal($qtyOrdered, $unitPrice, $discountPercent, $taxRate);

        model(PurchaseOrderItemModel::class)->insert([
            'purchase_order_id' => $poId,
            'request_item_id'   => $requestItemId,
            'item_id'           => $this->nullableInt('item_id'),
            'item_name'         => trim((string) $this->request->getPost('item_name')) ?: $iri['item_name'],
            'catalog_no'        => trim((string) $this->request->getPost('catalog_no')) ?: ($iri['catalog_no'] ?? null),
            'cas_no'            => trim((string) $this->request->getPost('cas_no'))     ?: ($iri['cas_no'] ?? null),
            'grade'             => trim((string) $this->request->getPost('grade'))      ?: ($iri['grade'] ?? null),
            'pack_size'         => trim((string) $this->request->getPost('pack_size'))  ?: ($iri['pack_size'] ?? null),
            'brand_id'          => $this->nullableInt('brand_id'),
            'qty_ordered'       => $qtyOrdered,
            'unit_id'           => $this->nullableInt('unit_id'),
            'unit_price'        => $unitPrice,
            'discount_percent'  => $discountPercent,
            'tax_rate'          => $taxRate,
            'line_total'        => $lineTotal,
            'notes'             => trim((string) $this->request->getPost('notes')) ?: null,
        ]);

        $this->recalculatePoTotals($poId);

        return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.itemAdded'));
    }

    public function updateItem(int $poId, int $itemId)
    {
        $po = model(PurchaseOrderModel::class)->find($poId);
        if ($po === null) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if ($po['status'] !== 'draft') {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.draftEditItemOnly'), null, 'danger');
        }

        $item = model(PurchaseOrderItemModel::class)->find($itemId);
        if ($item === null || (int) $item['purchase_order_id'] !== $poId) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.itemNotFound'), null, 'danger');
        }

        $qtyOrdered      = (float) ($this->request->getPost('qty_ordered') ?? 0);
        $unitPrice       = (float) ($this->request->getPost('unit_price') ?? 0);
        $discountPercent = (float) ($this->request->getPost('discount_percent') ?? 0);
        $taxRate         = (float) ($this->request->getPost('tax_rate') ?? 0);
        $lineTotal       = $this->calculatePoItemLineTotal($qtyOrdered, $unitPrice, $discountPercent, $taxRate);

        model(PurchaseOrderItemModel::class)->update($itemId, [
            'item_id'          => $this->nullableInt('item_id'),
            'item_name'        => trim((string) $this->request->getPost('item_name')) ?: $item['item_name'],
            'catalog_no'       => trim((string) $this->request->getPost('catalog_no')) ?: null,
            'cas_no'           => trim((string) $this->request->getPost('cas_no'))     ?: null,
            'grade'            => trim((string) $this->request->getPost('grade'))      ?: null,
            'pack_size'        => trim((string) $this->request->getPost('pack_size'))  ?: null,
            'brand_id'         => $this->nullableInt('brand_id'),
            'qty_ordered'      => $qtyOrdered,
            'unit_id'          => $this->nullableInt('unit_id'),
            'unit_price'       => $unitPrice,
            'discount_percent' => $discountPercent,
            'tax_rate'         => $taxRate,
            'line_total'       => $lineTotal,
            'qty_received'     => (float) ($this->request->getPost('qty_received') ?? $item['qty_received']),
            'notes'            => trim((string) $this->request->getPost('notes')) ?: null,
        ]);

        $this->recalculatePoTotals($poId);

        return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.itemUpdated'));
    }

    public function deleteItem(int $poId, int $itemId)
    {
        $po = model(PurchaseOrderModel::class)->find($poId);
        if ($po === null) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }
        if ($po['status'] !== 'draft') {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.draftRemoveItemOnly'), null, 'danger');
        }

        $item = model(PurchaseOrderItemModel::class)->find($itemId);
        if ($item === null || (int) $item['purchase_order_id'] !== $poId) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.itemNotFound'), null, 'danger');
        }

        try {
            model(PurchaseOrderItemModel::class)->delete($itemId);
        } catch (Throwable) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.itemDeleteReferenced'), null, 'danger');
        }

        $this->recalculatePoTotals($poId);

        return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.itemRemoved'));
    }

    public function uploadAttachment(int $poId)
    {
        if (model(PurchaseOrderModel::class)->find($poId) === null) {
            return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.notFound'), null, 'danger');
        }

        $file = $this->request->getFile('attachment_file');
        if ($file === null || $file->getError() === UPLOAD_ERR_NO_FILE) {
            return $this->redirectDetail($poId, null, 'po-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.purchaseOrders.validation.chooseFile'),
            ]);
        }
        if (! $file->isValid()) {
            return $this->redirectDetail($poId, null, 'po-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.purchaseOrders.validation.uploadFailed'),
            ]);
        }
        if ($file->getSizeByUnit('mb') > 10) {
            return $this->redirectDetail($poId, null, 'po-attachment-modal', 'danger', [
                'attachment_file' => lang('IMS.purchaseOrders.validation.maxFileSize'),
            ]);
        }

        $destination = WRITEPATH . 'uploads/ims/purchase-orders/' . $poId;
        if (! is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $originalName = $file->getClientName();
        $newName      = $file->getRandomName();
        $file->move($destination, $newName, true);

        model(PurchaseOrderAttachmentModel::class)->insert([
            'purchase_order_id' => $poId,
            'attachment_type'   => trim((string) $this->request->getPost('attachment_type')) ?: 'document',
            'file_name'         => $originalName,
            'file_path'         => 'uploads/ims/purchase-orders/' . $poId . '/' . $newName,
            'remarks'           => trim((string) $this->request->getPost('remarks')) ?: null,
            'uploaded_by'       => lab_core_current_user()?->id,
            'uploaded_at'       => date('Y-m-d H:i:s'),
        ]);

        return $this->redirectDetail($poId, lang('IMS.purchaseOrders.messages.attachmentUploaded'));
    }

    public function serveAttachment(int $poId, int $attachmentId)
    {
        $att = model(PurchaseOrderAttachmentModel::class)->find($attachmentId);

        if ($att === null || (int) $att['purchase_order_id'] !== $poId) {
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

    private function recalculatePoTotals(int $poId): void
    {
        $db       = Database::connect();
        $subtotal = (float) ($db->table('ims_purchase_order_items')
            ->selectSum('line_total', 'total')
            ->where('purchase_order_id', $poId)
            ->get()->getRowArray()['total'] ?? 0);

        $po         = model(PurchaseOrderModel::class)->find($poId);
        $grandTotal = $subtotal + (float) ($po['tax_amount'] ?? 0) + (float) ($po['shipping_cost'] ?? 0);

        model(PurchaseOrderModel::class)->update($poId, [
            'subtotal'    => $subtotal,
            'grand_total' => $grandTotal,
        ]);
    }

    private function calculatePoItemLineTotal(float $qty, float $unitPrice, float $discountPercent, float $taxRate): float
    {
        $discountPercent = max(0, min(100, $discountPercent));
        $taxRate = max(0, $taxRate);
        $discounted = $qty * $unitPrice * (1 - $discountPercent / 100);

        return $discounted * (1 + $taxRate / 100);
    }

    private function findPurchaseOrderForDisplay(int $id): ?array
    {
        return Database::connect()->table('ims_purchase_orders ipo')
            ->select('ipo.*, isp.supplier_name, isp.supplier_code, isp.contact_name, isp.email, isp.phone, isp.address, cb.username as created_by_name, ab.username as approved_by_name')
            ->join('ims_suppliers isp', 'isp.id = ipo.supplier_id', 'left')
            ->join('users cb', 'cb.id = ipo.created_by', 'left')
            ->join('users ab', 'ab.id = ipo.approved_by', 'left')
            ->where('ipo.id', $id)
            ->get()->getRowArray();
    }

    private function purchaseOrderItems(int $id): array
    {
        return Database::connect()->table('ims_purchase_order_items poi')
            ->select('poi.*, iri.item_name as req_item_name, iirl.request_name, iim.item_code, iu.unit_name, ib.brand_name')
            ->join('ims_request_items iri',      'iri.id = poi.request_item_id', 'left')
            ->join('ims_item_request_list iirl',  'iirl.id = iri.ims_item_request_list_id', 'left')
            ->join('ims_item_master iim',          'iim.id = poi.item_id', 'left')
            ->join('ims_units iu',                 'iu.id = poi.unit_id', 'left')
            ->join('ims_brands ib',                'ib.id = poi.brand_id', 'left')
            ->where('poi.purchase_order_id', $id)
            ->orderBy('poi.id', 'asc')
            ->get()->getResultArray();
    }

    private function generatePoNumber(): string
    {
        $db     = Database::connect();
        $prefix = 'PO-' . date('Ymd') . '-';
        $rows   = $db->table('ims_purchase_orders')
            ->select('po_number')
            ->like('po_number', $prefix, 'after')
            ->get()->getResultArray();

        $max = 0;
        foreach ($rows as $row) {
            if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', (string) $row['po_number'], $m) === 1) {
                $max = max($max, (int) $m[1]);
            }
        }

        do {
            $max++;
            $candidate = $prefix . str_pad((string) $max, 2, '0', STR_PAD_LEFT);
        } while ($db->table('ims_purchase_orders')->where('po_number', $candidate)->countAllResults() > 0);

        return $candidate;
    }

    private function resolvePaginationState(): array
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? $this->request->getPost('page') ?? 1));
        $perPage = (int) ($this->request->getGet('per_page') ?? $this->request->getPost('per_page') ?? 10);
        $perPage = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;

        return ['page' => $page, 'perPage' => $perPage];
    }

    private function redirectPo(?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [], ?int $poId = null)
    {
        $state    = $this->resolvePaginationState();
        $redirect = redirect()->to(site_url('ims/purchase-orders?' . http_build_query([
            'page'     => $state['page'],
            'per_page' => $state['perPage'],
        ])))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }
        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_po_modal', ['modal' => $modal, 'po_id' => $poId]);
        }

        return $redirect;
    }

    private function redirectDetail(int $poId, ?string $message = null, ?string $modal = null, string $type = 'success', array $errors = [])
    {
        $redirect = redirect()->to(site_url('ims/purchase-orders/' . $poId))->withInput();

        if ($message !== null) {
            $redirect = $redirect->with('message', $message)->with('message_type', $type);
        }
        if ($modal !== null) {
            $redirect = $redirect
                ->with('errors', $errors !== [] ? $errors : ($this->validator?->getErrors() ?? []))
                ->with('ims_po_detail_modal', ['modal' => $modal, 'po_id' => $poId]);
        }

        return $redirect;
    }

    private function nullableInt(string $field): ?int
    {
        $value = $this->request->getPost($field);
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    private function nullableDate(string $field): ?string
    {
        $value = trim((string) ($this->request->getPost($field) ?? ''));

        return $value !== '' ? $value : null;
    }

    private function buildPaginationData(int $total, int $page, int $perPage): array
    {
        $perPage    = in_array($perPage, [10, 25, 50], true) ? $perPage : 10;
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page       = max(1, min($page, $totalPages));
        $offset     = ($page - 1) * $perPage;
        $from       = $total === 0 ? 0 : $offset + 1;
        $to         = min($offset + $perPage, $total);

        $window = 2;
        $start  = max(1, $page - $window);
        $end    = min($totalPages, $page + $window);
        $pages  = range($start, $end);

        return compact('page', 'perPage', 'total', 'totalPages', 'offset', 'from', 'to', 'pages');
    }
}
