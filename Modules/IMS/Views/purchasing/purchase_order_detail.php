<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation  = $validation  ?? []; ?>
<?php $po          = $po          ?? []; ?>
<?php $items       = $items       ?? []; ?>
<?php $attachments = $attachments ?? []; ?>
<?php $eligibleItems = $eligibleItems ?? []; ?>
<?php $allItems    = $allItems    ?? []; ?>
<?php $allUnits    = $allUnits    ?? []; ?>
<?php $allBrands   = $allBrands   ?? []; ?>
<?php $suppliers   = $suppliers   ?? []; ?>
<?php $modalState  = is_array($modalState ?? null) ? $modalState : []; ?>
<?php
$poLang = static function (string $key, array $args = [], ?string $fallback = null): string {
    $line = 'IMS.purchaseOrders.' . $key;
    $text = lang($line, $args);

    return $text === $line ? ($fallback ?? $key) : $text;
};
$statusCfg = [
    'draft'              => ['label' => $poLang('status.draft'),             'class' => ''],
    'approved'           => ['label' => $poLang('status.approved'),          'class' => 'neutral'],
    'ordered'            => ['label' => $poLang('status.ordered'),           'class' => 'info'],
    'partially_received' => ['label' => $poLang('status.partiallyReceived'), 'class' => 'warning'],
    'received'           => ['label' => $poLang('status.received'),          'class' => 'success'],
    'cancelled'          => ['label' => $poLang('status.cancelled'),         'class' => 'danger'],
];
$poId       = (int) ($po['id'] ?? 0);
$status     = $po['status'] ?? 'draft';
$sc         = $statusCfg[$status] ?? $statusCfg['draft'];
$isDraft    = $status === 'draft';
$isApproved = $status === 'approved';
$isOrdered  = $status === 'ordered';
$cancellable = in_array($status, ['draft', 'approved', 'ordered'], true);
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar" style="flex-wrap:wrap;gap:12px;">
        <div class="admin-card-head" style="padding-left:0;flex:1;min-width:0;">
            <h3 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tool-id-badge"><?= esc($po['po_number'] ?? '') ?></span>
                <span><?= esc($po['supplier_name'] ?? '—') ?></span>
                <span class="admin-badge <?= esc($sc['class']) ?>"><?= esc($sc['label']) ?></span>
            </h3>
            <span>
                <strong><?= number_format((float) ($po['grand_total'] ?? 0), 2) ?></strong>
                <?php if ((float) ($po['tax_amount'] ?? 0) > 0 || (float) ($po['shipping_cost'] ?? 0) > 0) : ?>
                    <span class="admin-muted" style="font-size:.85em;">
                        <?php if ((float) ($po['tax_amount'] ?? 0) > 0) : ?>
                            &nbsp;· Tax: <?= number_format((float) $po['tax_amount'], 2) ?>
                        <?php endif ?>
                        <?php if ((float) ($po['shipping_cost'] ?? 0) > 0) : ?>
                            &nbsp;· Shipping: <?= number_format((float) $po['shipping_cost'], 2) ?>
                        <?php endif ?>
                    </span>
                <?php endif ?>
            </span>
        </div>
        <div class="ims-toolbar-actions">
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/purchase-orders/' . $poId . '/pdf')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-file-pdf"></i> <?= esc($poLang('actions.printPdf')) ?>
            </a>
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/purchase-orders')) ?>">
                <i class="fa-solid fa-arrow-left"></i> <?= esc($poLang('actions.backToPurchaseOrders')) ?>
            </a>
            <?php if ($isDraft && lab_core_can('ims.stock.receive')) : ?>
                <button type="button" class="admin-btn secondary" data-bs-toggle="modal" data-bs-target="#poEditModal">
                    <i class="fa-solid fa-pen-to-square"></i> <?= esc($poLang('actions.edit')) ?>
                </button>
            <?php endif ?>
            <?php if ($isDraft && lab_core_can('ims.stock.adjust')) : ?>
                <button type="button" class="admin-btn success" data-bs-toggle="modal" data-bs-target="#approvePoModal">
                    <i class="fa-solid fa-circle-check"></i> <?= esc($poLang('actions.approve')) ?>
                </button>
            <?php endif ?>
            <?php if ($isApproved && lab_core_can('ims.stock.adjust')) : ?>
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#orderPoModal">
                    <i class="fa-solid fa-paper-plane"></i> <?= esc($poLang('actions.markOrdered')) ?>
                </button>
            <?php endif ?>
            <?php if ($cancellable && lab_core_can('ims.stock.adjust')) : ?>
                <button type="button" class="admin-btn danger" data-bs-toggle="modal" data-bs-target="#cancelPoModal">
                    <i class="fa-solid fa-ban"></i> <?= esc($poLang('common.cancel')) ?>
                </button>
            <?php endif ?>
            <?php if ($isDraft && lab_core_can('ims.stock.adjust')) : ?>
                <button type="button" class="admin-btn danger" data-bs-toggle="modal" data-bs-target="#deletePoModal">
                    <i class="fa-solid fa-trash-can"></i> <?= esc($poLang('common.delete')) ?>
                </button>
            <?php endif ?>
        </div>
    </div>

    <!-- Meta info bar -->
    <div style="display:flex;flex-wrap:wrap;gap:20px;padding:10px 20px 14px 20px;border-bottom:1px solid var(--border-color);font-size:.85em;">
        <?php if ($po['order_date'] ?? '') : ?>
            <div>
                <span class="admin-muted"><?= esc($poLang('columns.orderDate')) ?></span><br>
                <strong><?= esc($po['order_date']) ?></strong>
            </div>
        <?php endif ?>
        <?php if ($po['expected_delivery_date'] ?? '') : ?>
            <div>
                <span class="admin-muted"><?= esc($poLang('columns.expectedDelivery')) ?></span><br>
                <strong><?= esc($po['expected_delivery_date']) ?></strong>
            </div>
        <?php endif ?>
        <?php if ($po['payment_terms'] ?? '') : ?>
            <div>
                <span class="admin-muted"><?= esc($poLang('columns.paymentTerms')) ?></span><br>
                <strong><?= esc($po['payment_terms']) ?></strong>
            </div>
        <?php endif ?>
        <?php if ($po['delivery_address'] ?? '') : ?>
            <div>
                <span class="admin-muted"><?= esc($poLang('columns.deliveryAddress')) ?></span><br>
                <strong><?= esc($po['delivery_address']) ?></strong>
            </div>
        <?php endif ?>
        <?php if ($po['created_by_name'] ?? '') : ?>
            <div>
                <span class="admin-muted"><?= esc($poLang('columns.createdBy')) ?></span><br>
                <strong><?= esc($po['created_by_name']) ?></strong>
            </div>
        <?php endif ?>
        <?php if ($po['approved_by_name'] ?? '') : ?>
            <div>
                <span class="admin-muted"><?= esc($poLang('columns.approvedBy')) ?></span><br>
                <strong><?= esc($po['approved_by_name']) ?></strong>
                <?php if ($po['approved_date'] ?? '') : ?>
                    <span class="admin-muted"> <?= esc($poLang('columns.on')) ?> <?= esc($po['approved_date']) ?></span>
                <?php endif ?>
            </div>
        <?php endif ?>
        <?php if ($po['notes'] ?? '') : ?>
            <div style="flex:1;min-width:200px;">
                <span class="admin-muted"><?= esc($poLang('columns.notes')) ?></span><br>
                <span><?= esc($po['notes']) ?></span>
            </div>
        <?php endif ?>
    </div>

    <!-- Nav tabs -->
    <div style="padding: 0 20px 0 20px; border-bottom: 1px solid var(--border-color);">
        <ul class="nav nav-tabs border-0" id="poDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-items-btn" data-bs-toggle="tab" data-bs-target="#tabItems" type="button" role="tab">
                    <i class="fa-solid fa-list-ul"></i> <?= esc($poLang('tabs.items')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($items) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-attachments-btn" data-bs-toggle="tab" data-bs-target="#tabAttachments" type="button" role="tab">
                    <i class="fa-solid fa-paperclip"></i> <?= esc($poLang('tabs.attachments')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($attachments) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- ── Items Tab ── -->
        <div class="tab-pane fade show active" id="tabItems" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= count($items) ?> <?= esc($poLang('detail.itemCountSuffix')) ?></span>
                <?php if ($isDraft && lab_core_can('ims.stock.receive')) : ?>
                    <button type="button" class="admin-btn primary"
                        data-bs-toggle="modal" data-bs-target="#poItemFormModal"
                        data-mode="create">
                        <i class="fa-solid fa-plus"></i> <?= esc($poLang('actions.addItem')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($poLang('columns.itemRequest')) ?></th>
                            <th><?= esc($poLang('columns.casGrade')) ?></th>
                            <th><?= esc($poLang('columns.masterItem')) ?></th>
                            <th><?= esc($poLang('columns.qtyOrdered')) ?></th>
                            <th><?= esc($poLang('columns.unitPrice')) ?></th>
                            <th><?= esc($poLang('columns.discountPercent')) ?></th>
                            <th><?= esc($poLang('columns.taxPercent')) ?></th>
                            <th><?= esc($poLang('columns.lineTotal')) ?></th>
                            <th><?= esc($poLang('columns.qtyReceived')) ?></th>
                            <th><?= esc($poLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items === []) : ?>
                            <tr class="main-row">
                                <td colspan="10" class="admin-muted"><?= esc($poLang('detail.noItems')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($items as $item) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($poLang('columns.itemRequest')) ?>">
                                        <strong><?= esc($item['item_name'] ?? '') ?></strong>
                                        <?php if ($item['request_name'] ?? '') : ?>
                                            <div class="admin-muted">
                                                <i class="fa-solid fa-arrow-right-long" style="font-size:.75em;"></i>
                                                <?= esc($item['request_name']) ?>
                                            </div>
                                        <?php endif ?>
                                        <?php if ($item['pack_size'] ?? '') : ?>
                                            <div class="admin-muted"><?= esc($item['pack_size']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.casGrade')) ?>">
                                        <?= ($item['cas_no'] ?? '') ? esc($item['cas_no']) : '—' ?>
                                        <?php if ($item['grade'] ?? '') : ?>
                                            <div class="admin-muted"><?= esc($item['grade']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.masterItem')) ?>">
                                        <?php if ($item['item_id'] ?? '') : ?>
                                            <span class="admin-badge success" title="<?= esc($item['item_code'] ?? '') ?>">
                                                <i class="fa-solid fa-link"></i> <?= esc($item['item_code'] ?? 'Mapped') ?>
                                            </span>
                                        <?php else : ?>
                                            <span class="admin-badge danger" title="<?= esc($poLang('messages.noMasterItemMapped')) ?>">
                                                <i class="fa-solid fa-link-slash"></i> <?= esc($poLang('messages.notMapped')) ?>
                                            </span>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.qtyOrdered')) ?>">
                                        <strong><?= esc((string) ($item['qty_ordered'] ?? '0')) ?></strong>
                                        <span class="admin-muted"><?= esc($item['unit_name'] ?? '') ?></span>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.unitPrice')) ?>">
                                        <?= number_format((float) ($item['unit_price'] ?? 0), 2) ?>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.discountPercent')) ?>">
                                        <?= esc((string) ($item['discount_percent'] ?? '0')) ?>%
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.taxPercent')) ?>">
                                        <?= esc((string) ($item['tax_rate'] ?? '0')) ?>%
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.lineTotal')) ?>">
                                        <strong><?= number_format((float) ($item['line_total'] ?? 0), 2) ?></strong>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.qtyReceived')) ?>">
                                        <?= esc((string) ($item['qty_received'] ?? '0')) ?>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.action')) ?>">
                                        <div class="ims-action-group">
                                            <?php if ($isDraft && lab_core_can('ims.stock.receive')) : ?>
                                                <?php
                                                $itemJson = esc(json_encode([
                                                    'id'               => $item['id'],
                                                    'item_name'        => $item['item_name'] ?? '',
                                                    'cas_no'           => $item['cas_no'] ?? '',
                                                    'catalog_no'       => $item['catalog_no'] ?? '',
                                                    'grade'            => $item['grade'] ?? '',
                                                    'pack_size'        => $item['pack_size'] ?? '',
                                                    'brand_id'         => $item['brand_id'] ?? '',
                                                    'brand_name'       => $item['brand_name'] ?? '',
                                                    'item_id'          => $item['item_id'] ?? '',
                                                    'item_code'        => $item['item_code'] ?? '',
                                                    'qty_ordered'      => $item['qty_ordered'] ?? '',
                                                    'unit_id'          => $item['unit_id'] ?? '',
                                                    'unit_name'        => $item['unit_name'] ?? '',
                                                    'unit_price'       => $item['unit_price'] ?? '',
                                                    'discount_percent' => $item['discount_percent'] ?? '0',
                                                    'tax_rate'         => $item['tax_rate'] ?? '0',
                                                    'qty_received'     => $item['qty_received'] ?? '0',
                                                    'notes'            => $item['notes'] ?? '',
                                                    'request_name'     => $item['request_name'] ?? '',
                                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES));
                                                ?>
                                                <button type="button" class="ims-action-btn" title="Edit"
                                                    onclick='editPoItem(<?= $itemJson ?>)'>
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                            <?php endif ?>
                                            <?php if ($isDraft && lab_core_can('ims.stock.adjust')) : ?>
                                                <button type="button" class="ims-action-btn danger" title="Remove"
                                                    onclick="deletePoItem(<?= (int) $item['id'] ?>)">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                    <?php if ($items !== []) : ?>
                        <tfoot>
                            <tr style="background:var(--surface-raised,#f9fafb);font-weight:600;">
                                <td colspan="7" style="text-align:right;padding:8px 12px;"><?= esc($poLang('columns.subtotal')) ?></td>
                                <td style="padding:8px 12px;"><?= number_format((float) ($po['subtotal'] ?? 0), 2) ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr style="background:var(--surface-raised,#f9fafb);">
                                <td colspan="7" style="text-align:right;padding:4px 12px;" class="admin-muted"><?= esc($poLang('columns.tax')) ?></td>
                                <td style="padding:4px 12px;" class="admin-muted"><?= number_format((float) ($po['tax_amount'] ?? 0), 2) ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr style="background:var(--surface-raised,#f9fafb);">
                                <td colspan="7" style="text-align:right;padding:4px 12px;" class="admin-muted"><?= esc($poLang('columns.shipping')) ?></td>
                                <td style="padding:4px 12px;" class="admin-muted"><?= number_format((float) ($po['shipping_cost'] ?? 0), 2) ?></td>
                                <td colspan="2"></td>
                            </tr>
                            <tr style="background:var(--surface-raised,#f9fafb);font-weight:700;border-top:2px solid var(--border-color,#e5e7eb);">
                                <td colspan="7" style="text-align:right;padding:8px 12px;"><?= esc($poLang('columns.grandTotal')) ?></td>
                                <td style="padding:8px 12px;"><?= number_format((float) ($po['grand_total'] ?? 0), 2) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    <?php endif ?>
                </table>
            </div>
        </div>

        <!-- ── Attachments Tab ── -->
        <div class="tab-pane fade" id="tabAttachments" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= count($attachments) ?> <?= esc($poLang('detail.attachmentCountSuffix')) ?></span>
                <?php if (lab_core_can('ims.stock.receive')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#poAttachModal">
                        <i class="fa-solid fa-upload"></i> <?= esc($poLang('actions.uploadDocument')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($poLang('columns.type')) ?></th>
                            <th><?= esc($poLang('columns.file')) ?></th>
                            <th><?= esc($poLang('columns.remarks')) ?></th>
                            <th><?= esc($poLang('columns.uploaded')) ?></th>
                            <th><?= esc($poLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($attachments === []) : ?>
                            <tr class="main-row">
                                <td colspan="5" class="admin-muted"><?= esc($poLang('detail.noAttachments')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($attachments as $att) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($poLang('columns.type')) ?>">
                                        <span class="admin-badge neutral"><?= esc(strtoupper($att['attachment_type'] ?? 'document')) ?></span>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.file')) ?>">
                                        <strong><?= esc($att['file_name'] ?? '') ?></strong>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.remarks')) ?>" class="admin-muted"><?= esc($att['remarks'] ?? '—') ?></td>
                                    <td data-label="<?= esc($poLang('columns.uploaded')) ?>" class="admin-muted">
                                        <?= esc($att['uploaded_at'] ?? '—') ?>
                                        <?php if ($att['uploaded_by'] ?? '') : ?>
                                            <div><?= esc($att['uploaded_by']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($poLang('columns.action')) ?>">
                                        <a class="ims-action-btn" title="<?= esc($poLang('actions.viewFile')) ?>"
                                            href="<?= esc(site_url('ims/purchase-orders/' . $poId . '/attachments/' . $att['id'])) ?>"
                                            target="_blank" rel="noopener">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ── Edit PO Modal ───────────────────────────────────────────────────────── -->
<div class="modal fade app-crud-modal" id="poEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/purchase-orders/' . $poId)) ?>" id="poEditForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('actions.editPurchaseOrder')) ?> - <?= esc($po['po_number'] ?? '') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (($modalState['modal'] ?? '') === 'po-edit-modal' && $validation !== []) : ?>
                        <div class="admin-alert danger"><?= esc(implode(' ', $validation)) ?></div>
                    <?php endif ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="edit-supplier"><?= esc($poLang('columns.supplier')) ?> <span class="admin-error">*</span></label>
                            <select id="edit-supplier" name="supplier_id" class="filter-select" required>
                                <option value=""><?= esc($poLang('placeholders.selectSupplier')) ?></option>
                                <?php foreach ($suppliers as $s) : ?>
                                    <option value="<?= (int) $s['id'] ?>"
                                        <?= (string) ($po['supplier_id'] ?? '') === (string) $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['supplier_name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['supplier_id'])) : ?><small class="admin-error"><?= esc($validation['supplier_id']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-order-date"><?= esc($poLang('columns.orderDate')) ?></label>
                            <input id="edit-order-date" type="date" name="order_date"
                                value="<?= esc($po['order_date'] ?? '') ?>">
                            <?php if (isset($validation['order_date'])) : ?><small class="admin-error"><?= esc($validation['order_date']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-delivery-date"><?= esc($poLang('columns.expectedDelivery')) ?></label>
                            <input id="edit-delivery-date" type="date" name="expected_delivery_date"
                                value="<?= esc($po['expected_delivery_date'] ?? '') ?>">
                            <?php if (isset($validation['expected_delivery_date'])) : ?><small class="admin-error"><?= esc($validation['expected_delivery_date']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-tax"><?= esc($poLang('columns.taxAmount')) ?></label>
                            <input id="edit-tax" type="number" name="tax_amount" step="any" min="0"
                                value="<?= esc((string) ($po['tax_amount'] ?? '0')) ?>">
                            <?php if (isset($validation['tax_amount'])) : ?><small class="admin-error"><?= esc($validation['tax_amount']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="edit-shipping"><?= esc($poLang('columns.shippingCost')) ?></label>
                            <input id="edit-shipping" type="number" name="shipping_cost" step="any" min="0"
                                value="<?= esc((string) ($po['shipping_cost'] ?? '0')) ?>">
                            <?php if (isset($validation['shipping_cost'])) : ?><small class="admin-error"><?= esc($validation['shipping_cost']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="edit-payment-terms"><?= esc($poLang('columns.paymentTerms')) ?></label>
                            <input id="edit-payment-terms" type="text" name="payment_terms"
                                placeholder="<?= esc($poLang('placeholders.paymentTerms')) ?>"
                                value="<?= esc($po['payment_terms'] ?? '') ?>">
                            <?php if (isset($validation['payment_terms'])) : ?><small class="admin-error"><?= esc($validation['payment_terms']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="edit-delivery-address"><?= esc($poLang('columns.deliveryAddress')) ?></label>
                            <textarea id="edit-delivery-address" name="delivery_address" rows="2"><?= esc($po['delivery_address'] ?? '') ?></textarea>
                            <?php if (isset($validation['delivery_address'])) : ?><small class="admin-error"><?= esc($validation['delivery_address']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="edit-notes"><?= esc($poLang('columns.notes')) ?></label>
                            <textarea id="edit-notes" name="notes" rows="3"><?= esc($po['notes'] ?? '') ?></textarea>
                            <?php if (isset($validation['notes'])) : ?><small class="admin-error"><?= esc($validation['notes']) ?></small><?php endif ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($poLang('actions.saveChanges')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Add / Edit Item Modal ──────────────────────────────────────────────── -->
<div class="modal fade app-crud-modal" id="poItemFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="" id="poItemFormEl">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="poItemFormTitle"><?= esc($poLang('actions.addItem')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (($modalState['modal'] ?? '') === 'po-item-modal' && $validation !== []) : ?>
                        <div class="admin-alert danger"><?= esc(implode(' ', $validation)) ?></div>
                    <?php endif ?>

                    <!-- Request item picker (add mode only) -->
                    <div id="poItemPickerRow" style="margin-bottom:16px;">
                        <div class="admin-form-group">
                            <label for="poi-request-item"><?= esc($poLang('columns.requestItem')) ?></label>
                            <select id="poi-request-item" name="request_item_id" class="filter-select"
                                onchange="onRequestItemSelect(this.value)">
                                <option value=""><?= esc($poLang('placeholders.selectApprovedRequest')) ?></option>
                                <?php foreach ($eligibleItems as $ei) : ?>
                                    <option value="<?= (int) $ei['id'] ?>"
                                        data-item-name="<?= esc($ei['item_name'] ?? '') ?>"
                                        data-cas="<?= esc($ei['cas_no'] ?? '') ?>"
                                        data-ec="<?= esc($ei['ec_no'] ?? '') ?>"
                                        data-catalog="<?= esc($ei['catalog_no'] ?? '') ?>"
                                        data-grade="<?= esc($ei['grade'] ?? '') ?>"
                                        data-pack-size="<?= esc($ei['pack_size'] ?? '') ?>"
                                        data-unit-id="<?= (int) ($ei['default_unit_id'] ?? 0) ?>"
                                        data-brand-id="<?= (int) ($ei['suggested_brand_id'] ?? 0) ?>"
                                        data-qty="<?= esc((string) ($ei['qty'] ?? '')) ?>">
                                        [<?= esc($ei['request_name'] ?? '') ?>] <?= esc($ei['item_name'] ?? '') ?>
                                        <?= ($ei['cas_no'] ?? '') ? '— ' . esc($ei['cas_no']) : '' ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <?php if ($eligibleItems === []) : ?>
                                <small class="admin-muted"><?= esc($poLang('help.noEligibleRequestItems')) ?></small>
                            <?php endif ?>
                        </div>
                    </div>

                    <!-- Item detail fields -->
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="poi-item-name"><?= esc($poLang('columns.itemName')) ?> <span class="admin-error">*</span></label>
                            <input id="poi-item-name" type="text" name="item_name" required>
                            <?php if (isset($validation['item_name'])) : ?><small class="admin-error"><?= esc($validation['item_name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-cas"><?= esc($poLang('columns.casNo')) ?></label>
                            <input id="poi-cas" type="text" name="cas_no">
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-catalog"><?= esc($poLang('columns.catalogNo')) ?></label>
                            <input id="poi-catalog" type="text" name="catalog_no">
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-grade"><?= esc($poLang('columns.gradePurity')) ?></label>
                            <input id="poi-grade" type="text" name="grade">
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-pack-size"><?= esc($poLang('columns.packSize')) ?></label>
                            <input id="poi-pack-size" type="text" name="pack_size">
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-brand"><?= esc($poLang('columns.brand')) ?></label>
                            <select id="poi-brand" name="brand_id" class="filter-select">
                                <option value=""><?= esc($poLang('placeholders.none')) ?></option>
                                <?php foreach ($allBrands as $b) : ?>
                                    <option value="<?= (int) $b['id'] ?>"><?= esc($b['brand_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="poi-item-id"><?= esc($poLang('columns.masterItemIms')) ?></label>
                            <select id="poi-item-id" name="item_id" class="filter-select">
                                <option value=""><?= esc($poLang('placeholders.notMapped')) ?></option>
                                <?php foreach ($allItems as $mi) : ?>
                                    <option value="<?= (int) $mi['id'] ?>"><?= esc($mi['item_code'] . ' — ' . $mi['item_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <small class="admin-muted"><?= esc($poLang('help.requiredGoodsReceipt')) ?></small>
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-qty"><?= esc($poLang('columns.qtyOrdered')) ?> <span class="admin-error">*</span></label>
                            <input id="poi-qty" type="number" name="qty_ordered" step="any" min="0" required>
                            <?php if (isset($validation['qty_ordered'])) : ?><small class="admin-error"><?= esc($validation['qty_ordered']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-unit"><?= esc($poLang('columns.unit')) ?></label>
                            <select id="poi-unit" name="unit_id" class="filter-select">
                                <option value=""><?= esc($poLang('placeholders.none')) ?></option>
                                <?php foreach ($allUnits as $u) : ?>
                                    <option value="<?= (int) $u['id'] ?>"><?= esc($u['unit_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-price"><?= esc($poLang('columns.unitPrice')) ?> <span class="admin-error">*</span></label>
                            <input id="poi-price" type="number" name="unit_price" step="any" min="0" required>
                            <?php if (isset($validation['unit_price'])) : ?><small class="admin-error"><?= esc($validation['unit_price']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-discount"><?= esc($poLang('columns.discountPercent')) ?></label>
                            <input id="poi-discount" type="number" name="discount_percent" step="any" min="0" max="100" value="0">
                        </div>
                        <div class="admin-form-group">
                            <label for="poi-tax-rate"><?= esc($poLang('columns.taxPercent')) ?></label>
                            <input id="poi-tax-rate" type="number" name="tax_rate" step="any" min="0" value="0">
                        </div>
                        <div class="admin-form-group" id="poi-qty-received-row" style="display:none;">
                            <label for="poi-qty-received"><?= esc($poLang('columns.qtyReceived')) ?></label>
                            <input id="poi-qty-received" type="number" name="qty_received" step="any" min="0" value="0">
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label for="poi-notes"><?= esc($poLang('columns.notes')) ?></label>
                            <textarea id="poi-notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary" id="poItemFormSubmitBtn"><?= esc($poLang('actions.addItem')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Upload Attachment Modal ────────────────────────────────────────────── -->
<div class="modal fade app-crud-modal" id="poAttachModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/purchase-orders/' . $poId . '/attachments')) ?>"
                id="poAttachForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('actions.uploadDocument')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (($modalState['modal'] ?? '') === 'po-attachment-modal' && $validation !== []) : ?>
                        <div class="admin-alert danger"><?= esc(implode(' ', $validation)) ?></div>
                    <?php endif ?>
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label for="att-type"><?= esc($poLang('columns.attachmentType')) ?></label>
                            <select id="att-type" name="attachment_type" class="filter-select">
                                <?php foreach (['quotation', 'invoice', 'contract', 'delivery_note', 'document', 'other'] as $val) : ?>
                                    <?php $lbl = $poLang('attachmentTypes.' . $val); ?>
                                    <option value="<?= esc($val) ?>" <?= old('attachment_type', 'document') === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="att-file"><?= esc($poLang('columns.file')) ?> <span class="admin-error">*</span></label>
                            <input id="att-file" class="app-file-input" name="attachment_file" type="file" required>
                            <?php if (isset($validation['attachment_file'])) : ?><small class="admin-error"><?= esc($validation['attachment_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($poLang('help.maxFileSize')) ?></small>
                        </div>
                        <div class="admin-form-group">
                            <label for="att-remarks"><?= esc($poLang('columns.remarks')) ?></label>
                            <textarea id="att-remarks" name="remarks" rows="3"><?= esc((string) old('remarks')) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($poLang('actions.uploadFile')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Approve PO Modal ───────────────────────────────────────────────────── -->
<div class="modal fade" id="approvePoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/purchase-orders/' . $poId . '/approve')) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.approveTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= esc($poLang('modal.approveMessage', [$po['po_number'] ?? ''])) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn success"><?= esc($poLang('actions.approve')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Mark as Ordered Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="orderPoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/purchase-orders/' . $poId . '/order')) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.orderTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= esc($poLang('modal.orderMessage', [$po['po_number'] ?? ''])) ?></p>
                    <div class="admin-form-group">
                        <label for="order-po-date"><?= esc($poLang('columns.orderDate')) ?></label>
                        <input id="order-po-date" type="date" name="order_date"
                            value="<?= esc($po['order_date'] ?? date('Y-m-d')) ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($poLang('actions.confirmOrdered')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Cancel PO Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="cancelPoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/purchase-orders/' . $poId . '/cancel')) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.cancelTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= esc($poLang('modal.cancelMessage', [$po['po_number'] ?? ''])) ?> <?= esc($poLang('modal.cannotUndo')) ?></p>
                    <div class="admin-form-group">
                        <label for="cancel-reason"><?= esc($poLang('fields.reason')) ?></label>
                        <textarea id="cancel-reason" name="cancel_reason" rows="3"
                            placeholder="<?= esc($poLang('placeholders.cancelReason')) ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn danger"><?= esc($poLang('actions.cancelOrder')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete PO Modal ────────────────────────────────────────────────────── -->
<div class="modal fade" id="deletePoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/purchase-orders/' . $poId . '/delete')) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.deleteTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?= esc($poLang('delete.detailMessage', [$po['po_number'] ?? ''])) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn danger"><?= esc($poLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden delete-item form (used by JS) -->
<form id="poItemDeleteForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
</form>

<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
const poBaseUrl       = '<?= esc(rtrim(site_url('ims/purchase-orders'), '/')) ?>';
const currentPoId     = <?= $poId ?>;
const currentPoStatus = '<?= esc($status) ?>';
const canReceive      = <?= lab_core_can('ims.stock.receive') ? 'true' : 'false' ?>;
const canAdjust       = <?= lab_core_can('ims.stock.adjust') ? 'true' : 'false' ?>;
const poText = <?= json_encode([
    'addItem' => $poLang('actions.addItem'),
    'editItem' => $poLang('actions.editItem'),
    'saveChanges' => $poLang('actions.saveChanges'),
    'mapped' => $poLang('messages.mapped'),
    'notMapped' => $poLang('messages.notMapped'),
    'noMasterItemMapped' => $poLang('messages.noMasterItemMapped'),
    'removeItemConfirm' => $poLang('delete.itemMessage'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

function h(s) {
    if (s == null) return '';
    return String(s)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function renderMasterBadge(item) {
    if (item.item_id) {
        return '<span class="admin-badge success" title="' + h(item.item_code || '') + '"><i class="fa-solid fa-link"></i> ' + h(item.item_code || poText.mapped) + '</span>';
    }
    return '<span class="admin-badge danger" title="' + h(poText.noMasterItemMapped) + '"><i class="fa-solid fa-link-slash"></i> ' + h(poText.notMapped) + '</span>';
}

function editPoItem(item) {
    const modal     = document.getElementById('poItemFormModal');
    const form      = document.getElementById('poItemFormEl');
    const title     = document.getElementById('poItemFormTitle');
    const submitBtn = document.getElementById('poItemFormSubmitBtn');

    title.textContent     = poText.editItem;
    submitBtn.textContent = poText.saveChanges;
    form.action           = poBaseUrl + '/' + currentPoId + '/items/' + item.id;

    document.getElementById('poItemPickerRow').style.display       = 'none';
    document.getElementById('poi-qty-received-row').style.display  = 'block';

    document.getElementById('poi-item-name').value    = item.item_name        || '';
    document.getElementById('poi-cas').value          = item.cas_no           || '';
    document.getElementById('poi-catalog').value      = item.catalog_no       || '';
    document.getElementById('poi-grade').value        = item.grade            || '';
    document.getElementById('poi-pack-size').value    = item.pack_size        || '';
    document.getElementById('poi-brand').value        = item.brand_id         || '';
    document.getElementById('poi-item-id').value      = item.item_id          || '';
    document.getElementById('poi-qty').value          = item.qty_ordered      || '';
    document.getElementById('poi-unit').value         = item.unit_id          || '';
    document.getElementById('poi-price').value        = item.unit_price       || '';
    document.getElementById('poi-discount').value     = item.discount_percent || '0';
    document.getElementById('poi-tax-rate').value     = item.tax_rate         || '0';
    document.getElementById('poi-qty-received').value = item.qty_received     || '0';
    document.getElementById('poi-notes').value        = item.notes            || '';

    new bootstrap.Modal(modal).show();
}

function deletePoItem(itemId) {
    if (!confirm(poText.removeItemConfirm)) return;
    const form = document.getElementById('poItemDeleteForm');
    form.action = poBaseUrl + '/' + currentPoId + '/items/' + itemId + '/delete';
    form.submit();
}

function onRequestItemSelect(val) {
    if (!val) return;
    const opt = document.querySelector('#poi-request-item option[value="' + val + '"]');
    if (!opt) return;
    document.getElementById('poi-item-name').value = opt.dataset.itemName || '';
    document.getElementById('poi-cas').value       = opt.dataset.cas      || '';
    document.getElementById('poi-catalog').value   = opt.dataset.catalog  || '';
    document.getElementById('poi-grade').value     = opt.dataset.grade    || '';
    document.getElementById('poi-pack-size').value = opt.dataset.packSize || '';
    if (opt.dataset.unitId  && opt.dataset.unitId  !== '0') document.getElementById('poi-unit').value  = opt.dataset.unitId;
    if (opt.dataset.brandId && opt.dataset.brandId !== '0') document.getElementById('poi-brand').value = opt.dataset.brandId;
    if (opt.dataset.qty) document.getElementById('poi-qty').value = opt.dataset.qty;
}

// ── Item form modal show listener ─────────────────────────────────────────
document.getElementById('poItemFormModal').addEventListener('show.bs.modal', function (event) {
    const trigger = event.relatedTarget;
    const mode    = trigger ? (trigger.dataset.mode || 'create') : 'create';

    if (mode === 'create') {
        const form      = document.getElementById('poItemFormEl');
        const title     = document.getElementById('poItemFormTitle');
        const submitBtn = document.getElementById('poItemFormSubmitBtn');

        title.textContent     = poText.addItem;
        submitBtn.textContent = poText.addItem;
        form.action           = poBaseUrl + '/' + currentPoId + '/items';

        document.getElementById('poItemPickerRow').style.display      = 'block';
        document.getElementById('poi-qty-received-row').style.display = 'none';

        // Clear fields
        ['poi-item-name','poi-cas','poi-catalog','poi-grade','poi-pack-size','poi-notes'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        document.getElementById('poi-qty').value          = '';
        document.getElementById('poi-price').value        = '';
        document.getElementById('poi-discount').value     = '0';
        document.getElementById('poi-tax-rate').value     = '0';
        document.getElementById('poi-qty-received').value = '0';
        ['poi-brand','poi-item-id','poi-unit','poi-request-item'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }
    // edit mode fields are filled by editPoItem() before the modal is shown
});

// ── Modal restore ─────────────────────────────────────────────────────────
(() => {
    <?php if (($modalState['modal'] ?? null) === 'po-item-modal') : ?>
        {
            const form = document.getElementById('poItemFormEl');
            form.action = poBaseUrl + '/<?= $poId ?>/items<?= ! empty($modalState['po_item_id']) ? '/' . (int) $modalState['po_item_id'] : '' ?>';
            document.getElementById('poItemPickerRow').style.display      = <?= ! empty($modalState['po_item_id']) ? "'none'" : "'block'" ?>;
            document.getElementById('poi-qty-received-row').style.display = <?= ! empty($modalState['po_item_id']) ? "'block'" : "'none'" ?>;
            document.getElementById('poItemFormTitle').textContent        = <?= ! empty($modalState['po_item_id']) ? "poText.editItem" : "poText.addItem" ?>;
            document.getElementById('poItemFormSubmitBtn').textContent    = <?= ! empty($modalState['po_item_id']) ? "poText.saveChanges" : "poText.addItem" ?>;
            new bootstrap.Modal(document.getElementById('poItemFormModal')).show();
        }
    <?php elseif (($modalState['modal'] ?? null) === 'po-attachment-modal') : ?>
        new bootstrap.Modal(document.getElementById('poAttachModal')).show();
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-attachments-btn')).show();
    <?php elseif (($modalState['modal'] ?? null) === 'po-edit-modal') : ?>
        new bootstrap.Modal(document.getElementById('poEditModal')).show();
    <?php endif ?>
})();
</script>
<?= $this->endSection() ?>
