<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php
$supplier       = $supplier ?? [];
$brandRows      = $brandRows ?? [];
$brands         = $brands ?? [];
$mappedBrandIds = $mappedBrandIds ?? [];
$purchaseOrders = $purchaseOrders ?? [];
$stockLots      = $stockLots ?? [];
$documentRows   = $documentRows ?? [];
$checklists     = $checklists ?? [];
$evaluationRows = $evaluationRows ?? [];
$validation     = $validation ?? [];
$modalState     = $modalState ?? null;
$activeDetailTab = $activeDetailTab ?? 'brands';
$supplierLang = static fn (string $key, array $args = []): string => lang('IMS.suppliers.' . $key, $args);
$attachmentTypeLabels = [
    'qualification' => $supplierLang('documents.types.qualification'),
    'contract' => $supplierLang('documents.types.contract'),
    'audit' => $supplierLang('documents.types.audit'),
    'certificate' => $supplierLang('documents.types.certificate'),
    'document' => $supplierLang('documents.types.document'),
    'other' => $supplierLang('documents.types.other'),
];

$statusBadgeClass = static function (?string $status): string {
    return match (strtolower((string) $status)) {
        'approved preferred', 'approved', 'received', 'confirmed' => 'success',
        'disqualified', 'cancelled' => 'danger',
        'conditional approval', 'improvement required', 'ordered', 'partially_received' => 'warning',
        default => 'neutral',
    };
};
$ownershipClass = static function (?string $status): string {
    return match (strtolower((string) $status)) {
        'consigned' => 'warning',
        'borrowed' => 'neutral',
        default => 'success',
    };
};
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar" style="flex-wrap:wrap;gap:12px;">
        <div class="admin-card-head" style="padding-left:0;flex:1;min-width:0;">
            <h3 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tool-id-badge"><?= esc($supplier['supplier_code'] ?? '') ?></span>
                <?= esc($supplier['supplier_name'] ?? $supplierLang('forms.supplier')) ?>
            </h3>
            <span>
                <?= esc($supplier['contact_name'] ?? $supplierLang('detail.noContact')) ?>
                <?php if ($supplier['phone'] ?? '') : ?> &middot; <?= esc($supplier['phone']) ?><?php endif ?>
                <?php if ($supplier['email'] ?? '') : ?> &middot; <?= esc($supplier['email']) ?><?php endif ?>
                <?php $supplierApprovalName = (string) ($supplier['approved_status_name'] ?? '-'); ?>
                <span class="admin-badge <?= esc($statusBadgeClass($supplierApprovalName)) ?>" style="margin-left:6px;">
                    <?= esc($supplierApprovalName !== '' ? $supplierApprovalName : '-') ?>
                </span>
                <span class="admin-badge <?= (int) ($supplier['is_active'] ?? 0) === 1 ? 'success' : 'neutral' ?>" style="margin-left:4px;">
                    <?= esc((int) ($supplier['is_active'] ?? 0) === 1 ? $supplierLang('status.active') : $supplierLang('status.inactive')) ?>
                </span>
            </span>
            <?php if (! empty($supplier['address']) || ! empty($supplier['vat_code'])) : ?>
                <span>
                    <?php if (! empty($supplier['address'])) : ?><?= esc($supplier['address']) ?><?php endif ?>
                    <?php if (! empty($supplier['vat_code'])) : ?> &middot; <?= esc($supplierLang('labels.vat')) ?> <?= esc($supplier['vat_code']) ?><?php endif ?>
                </span>
            <?php endif ?>
        </div>
        <div class="ims-toolbar-actions">
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/suppliers')) ?>">
                <i class="fa-solid fa-arrow-left"></i> <?= esc($supplierLang('common.backToSuppliers')) ?>
            </a>
        </div>
    </div>

    <div style="padding: 0 20px 0 20px; border-bottom: 1px solid var(--border-color);">
        <ul class="nav nav-tabs border-0" id="supplierDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-supplier-brands-btn" data-bs-toggle="tab" data-bs-target="#tabSupplierBrands" type="button" role="tab">
                    <i class="fa-solid fa-link"></i> <?= esc($supplierLang('tabs.brandMapping')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($brandRows) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-supplier-pos-btn" data-bs-toggle="tab" data-bs-target="#tabSupplierPurchaseOrders" type="button" role="tab">
                    <i class="fa-solid fa-file-invoice"></i> <?= esc($supplierLang('tabs.purchaseOrders')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($purchaseOrders) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-supplier-stock-btn" data-bs-toggle="tab" data-bs-target="#tabSupplierStockItems" type="button" role="tab">
                    <i class="fa-solid fa-boxes-stacked"></i> <?= esc($supplierLang('tabs.stockItem')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($stockLots) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-supplier-evaluations-btn" data-bs-toggle="tab" data-bs-target="#tabSupplierEvaluations" type="button" role="tab">
                    <i class="fa-solid fa-clipboard-check"></i> <?= esc($supplierLang('tabs.evaluations')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($evaluationRows) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-supplier-documents-btn" data-bs-toggle="tab" data-bs-target="#tabSupplierDocuments" type="button" role="tab">
                    <i class="fa-solid fa-folder-open"></i> <?= esc($supplierLang('tabs.documents')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($documentRows) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabSupplierBrands" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($supplierLang('detail.brandCount', [count($brandRows)])) ?></span>
                <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#supplierBrandMappingModal">
                        <i class="fa-solid fa-link"></i> <?= esc($supplierLang('tabs.brandMapping')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($supplierLang('columns.logo')) ?></th>
                            <th><?= esc($supplierLang('columns.brand')) ?></th>
                            <th><?= esc($supplierLang('columns.country')) ?></th>
                            <th><?= esc($supplierLang('columns.website')) ?></th>
                            <th><?= esc($supplierLang('columns.status')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($brandRows === []) : ?>
                            <tr class="main-row">
                                <td colspan="5" class="admin-muted"><?= esc($supplierLang('detail.noBrandMappings')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($brandRows as $brand) : ?>
                                <?php
                                $logo = (string) ($brand['logo'] ?? '');
                                $logoUrl = $logo !== '' && preg_match('~^(?:https?:)?//|^data:~', $logo)
                                    ? $logo
                                    : ($logo !== '' ? base_url(trim($logo, '/')) : null);
                                $brandName = (string) ($brand['brand_name'] ?? '-');
                                ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($supplierLang('columns.logo')) ?>">
                                        <?php if ($logoUrl) : ?>
                                            <img class="ims-brand-logo" src="<?= esc($logoUrl) ?>" alt="<?= esc($brandName) ?>">
                                        <?php else : ?>
                                            <div class="ims-brand-logo placeholder"><?= esc(strtoupper(substr($brandName, 0, 2))) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.brand')) ?>"><strong><?= esc($brandName) ?></strong></td>
                                    <td data-label="<?= esc($supplierLang('columns.country')) ?>"><?= esc($brand['country'] ?? '-') ?></td>
                                    <td data-label="<?= esc($supplierLang('columns.website')) ?>">
                                        <?php if (! empty($brand['website'])) : ?>
                                            <a class="detail-link" href="<?= esc($brand['website']) ?>" target="_blank" rel="noreferrer"><?= esc($brand['website']) ?></a>
                                        <?php else : ?>
                                            <span class="admin-muted">-</span>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.status')) ?>">
                                        <span class="admin-badge <?= (int) ($brand['is_active'] ?? 0) === 1 ? 'success' : 'neutral' ?>">
                                            <?= esc((int) ($brand['is_active'] ?? 0) === 1 ? $supplierLang('status.active') : $supplierLang('status.inactive')) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tabSupplierPurchaseOrders" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($supplierLang('detail.purchaseOrderCount', [count($purchaseOrders)])) ?></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($supplierLang('columns.poNumber')) ?></th>
                            <th><?= esc($supplierLang('columns.status')) ?></th>
                            <th><?= esc($supplierLang('columns.dates')) ?></th>
                            <th><?= esc($supplierLang('columns.grandTotal')) ?></th>
                            <th><?= esc($supplierLang('columns.createdApproved')) ?></th>
                            <th><?= esc($supplierLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($purchaseOrders === []) : ?>
                            <tr class="main-row">
                                <td colspan="6" class="admin-muted"><?= esc($supplierLang('detail.noPurchaseOrders')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($purchaseOrders as $po) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($supplierLang('columns.poNumber')) ?>"><strong><?= esc($po['po_number']) ?></strong></td>
                                    <td data-label="<?= esc($supplierLang('columns.status')) ?>">
                                        <span class="admin-badge <?= esc($statusBadgeClass($po['status'] ?? 'draft')) ?>">
                                            <?= esc(ucwords(str_replace('_', ' ', (string) ($po['status'] ?? 'draft')))) ?>
                                        </span>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.dates')) ?>">
                                        <?php if ($po['order_date'] ?? '') : ?><div><?= esc($supplierLang('labels.order')) ?>: <?= esc($po['order_date']) ?></div><?php endif ?>
                                        <?php if ($po['expected_delivery_date'] ?? '') : ?><div class="admin-muted"><?= esc($supplierLang('labels.expected')) ?>: <?= esc($po['expected_delivery_date']) ?></div><?php endif ?>
                                        <?php if ($po['approved_date'] ?? '') : ?><div class="admin-muted"><?= esc($supplierLang('labels.approved')) ?>: <?= esc($po['approved_date']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.grandTotal')) ?>"><strong><?= esc(number_format((float) ($po['grand_total'] ?? 0), 4)) ?></strong></td>
                                    <td data-label="<?= esc($supplierLang('columns.createdApproved')) ?>">
                                        <?php if ($po['created_by_name'] ?? '') : ?><div><?= esc($supplierLang('labels.created')) ?>: <?= esc($po['created_by_name']) ?></div><?php endif ?>
                                        <?php if ($po['approved_by_name'] ?? '') : ?><div class="admin-muted"><?= esc($supplierLang('labels.approved')) ?>: <?= esc($po['approved_by_name']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.action')) ?>">
                                        <a class="ims-action-btn" title="<?= esc($supplierLang('actions.viewPurchaseOrder')) ?>" href="<?= esc(site_url('ims/purchase-orders/' . $po['id'])) ?>">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tabSupplierStockItems" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($supplierLang('detail.stockLotCount', [count($stockLots)])) ?></span>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($supplierLang('columns.item')) ?></th>
                            <th><?= esc($supplierLang('columns.lot')) ?></th>
                            <th><?= esc($supplierLang('columns.dates')) ?></th>
                            <th><?= esc($supplierLang('columns.qty')) ?></th>
                            <th><?= esc($supplierLang('columns.location')) ?></th>
                            <th><?= esc($supplierLang('columns.spec')) ?></th>
                            <th><?= esc($supplierLang('columns.ownership')) ?></th>
                            <th><?= esc($supplierLang('columns.status')) ?></th>
                            <th><?= esc($supplierLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stockLots === []) : ?>
                            <tr class="main-row">
                                <td colspan="9" class="admin-muted"><?= esc($supplierLang('detail.noStockLots')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($stockLots as $row) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($supplierLang('columns.item')) ?>">
                                        <strong><?= esc($row['item_name'] ?? '-') ?></strong>
                                        <div class="admin-muted"><?= esc($row['item_code'] ?? '-') ?></div>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.lot')) ?>">
                                        <strong><?= esc($row['internal_lot_no'] ?: ($row['lot_no'] ?: '-')) ?></strong>
                                        <div class="admin-muted"><?= esc($row['supplier_lot_no'] ?: ($row['serial_no'] ?: '-')) ?></div>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.dates')) ?>">
                                        <strong><?= esc($supplierLang('labels.exp')) ?>: <?= esc($row['expiry_date'] ?? '-') ?></strong>
                                        <div class="admin-muted"><?= esc($supplierLang('labels.received')) ?>: <?= esc($row['received_date'] ?? '-') ?></div>
                                        <div class="admin-muted"><?= esc($supplierLang('labels.opened')) ?>: <?= esc($row['opened_date'] ?? '-') ?></div>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.qty')) ?>">
                                        <strong><?= esc((string) ($row['current_qty'] ?? '0')) ?> <?= esc($row['unit_name'] ?? '') ?></strong>
                                        <div class="admin-muted"><?= esc($supplierLang('labels.initial')) ?>: <?= esc((string) ($row['initial_qty'] ?? '0')) ?> <?= esc($row['initial_unit_name'] ?? '') ?></div>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.location')) ?>"><?= esc($row['storage_location_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($supplierLang('columns.spec')) ?>">
                                        <strong><?= esc($row['brand_name'] ?? '-') ?></strong>
                                        <div class="admin-muted"><?= esc($row['catalog_no'] ?: ($row['grade'] ?: '-')) ?></div>
                                        <div class="admin-muted"><?= esc($row['pack_size'] ?: '-') ?></div>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.ownership')) ?>">
                                        <span class="admin-badge <?= esc($ownershipClass($row['ownership_status'] ?? 'owned')) ?>"><?= esc(ucfirst((string) ($row['ownership_status'] ?? 'owned'))) ?></span>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.status')) ?>">
                                        <?php
                                        $expiryDate = $row['expiry_date'] ?? '';
                                        $isExpired = $expiryDate !== '' && $expiryDate !== null && strtotime($expiryDate) < strtotime('today');
                                        $initialQty = (float) ($row['initial_qty'] ?? 0);
                                        $currentQty = (float) ($row['current_qty'] ?? 0);
                                        $isLowStock = $initialQty > 0 && $currentQty <= 0.03 * $initialQty;
                                        ?>
                                        <?php if ($isExpired) : ?>
                                            <span class="admin-badge danger"><?= esc($supplierLang('status.expired')) ?></span>
                                        <?php endif ?>
                                        <?php if ($isLowStock) : ?>
                                            <span class="admin-badge warning"><?= esc($supplierLang('status.lowStock')) ?></span>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.action')) ?>">
                                        <div class="ims-action-group">
                                            <a class="ims-action-btn" title="<?= esc($supplierLang('actions.viewDetail')) ?>" href="<?= esc(site_url('ims/lots/' . $row['id'])) ?>">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tabSupplierEvaluations" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($supplierLang('detail.evaluationCount', [count($evaluationRows)])) ?></span>
                <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#supplierEvaluationModal">
                        <i class="fa-solid fa-plus"></i> <?= esc($supplierLang('actions.startEvaluation')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($supplierLang('columns.evaluation')) ?></th>
                            <th><?= esc($supplierLang('columns.checklist')) ?></th>
                            <th><?= esc($supplierLang('columns.totalRate')) ?></th>
                            <th><?= esc($supplierLang('columns.comment')) ?></th>
                            <th><?= esc($supplierLang('columns.details')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($evaluationRows === []) : ?>
                            <tr class="main-row">
                                <td colspan="5" class="admin-muted"><?= esc($supplierLang('detail.noEvaluations')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($evaluationRows as $evaluation) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($supplierLang('columns.evaluation')) ?>">
                                        <strong><?= esc($evaluation['evaluation_date'] ?? '-') ?></strong>
                                        <?php if ($evaluation['evaluated_by_name'] ?? '') : ?><div class="admin-muted"><?= esc($evaluation['evaluated_by_name']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.checklist')) ?>"><?= esc($evaluation['checklist_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($supplierLang('columns.totalRate')) ?>"><strong><?= esc(number_format((float) ($evaluation['total_rate'] ?? 0), 2)) ?>%</strong></td>
                                    <td data-label="<?= esc($supplierLang('columns.comment')) ?>"><?= esc($evaluation['comment'] ?? '-') ?></td>
                                    <td data-label="<?= esc($supplierLang('columns.details')) ?>">
                                        <div class="ims-action-group">
                                            <button type="button" class="ims-action-btn js-view-evaluation-detail" title="<?= esc($supplierLang('actions.viewDetail')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#supplierEvaluationDetailModal"
                                                data-confirm-action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/evaluations/' . $evaluation['id'] . '/confirm')) ?>"
                                                data-evaluation-label="<?= esc(($evaluation['evaluation_date'] ?? '-') . ' - ' . ($evaluation['checklist_name'] ?? '-')) ?>"
                                                data-locked="<?= esc((string) ($evaluation['locked'] ?? 0)) ?>"
                                                data-details="<?= esc(json_encode($evaluation['details'] ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?: '[]') ?>">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </button>
                                            <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                                                <?php if ((int) ($evaluation['locked'] ?? 0) === 1) : ?>
                                                    <button type="button" class="ims-action-btn danger" title="<?= esc($supplierLang('actions.deleteEvaluation')) ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#lockedEvaluationModal"
                                                        data-evaluation-label="<?= esc(($evaluation['evaluation_date'] ?? '-') . ' - ' . ($evaluation['checklist_name'] ?? '-')) ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                <?php else : ?>
                                                    <button type="button" class="ims-action-btn danger" title="<?= esc($supplierLang('actions.deleteEvaluation')) ?>"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteEvaluationModal"
                                                        data-delete-action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/evaluations/' . $evaluation['id'] . '/delete')) ?>"
                                                        data-delete-label="<?= esc(($evaluation['evaluation_date'] ?? '-') . ' - ' . ($evaluation['checklist_name'] ?? '-')) ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                <?php endif ?>
                                            <?php endif ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tabSupplierDocuments" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($supplierLang('detail.documentCount', [count($documentRows)])) ?></span>
                <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#supplierDocumentUploadModal">
                        <i class="fa-solid fa-upload"></i> <?= esc($supplierLang('actions.uploadDocument')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($supplierLang('columns.file')) ?></th>
                            <th><?= esc($supplierLang('columns.type')) ?></th>
                            <th><?= esc($supplierLang('columns.uploaded')) ?></th>
                            <th><?= esc($supplierLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($documentRows === []) : ?>
                            <tr class="main-row">
                                <td colspan="4" class="admin-muted"><?= esc($supplierLang('detail.noDocuments')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($documentRows as $document) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($supplierLang('columns.file')) ?>"><strong><?= esc($document['file_name']) ?></strong></td>
                                    <td data-label="<?= esc($supplierLang('columns.type')) ?>"><span class="admin-badge neutral"><?= esc($attachmentTypeLabels[$document['attachment_type'] ?? 'document'] ?? strtoupper($document['attachment_type'] ?? 'document')) ?></span></td>
                                    <td data-label="<?= esc($supplierLang('columns.uploaded')) ?>" class="admin-muted">
                                        <?= esc($document['created_at'] ?? '-') ?>
                                        <?php if ($document['uploaded_by_name'] ?? '') : ?><div><?= esc($document['uploaded_by_name']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($supplierLang('columns.action')) ?>">
                                        <div class="ims-action-group">
                                            <a class="ims-action-btn" title="<?= esc($supplierLang('actions.viewFile')) ?>" href="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/documents/' . $document['id'])) ?>" target="_blank" rel="noopener">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                                                <button type="button" class="ims-action-btn" title="<?= esc($supplierLang('actions.editDocument')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#supplierDocumentEditModal"
                                                    data-action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/documents/' . $document['id'])) ?>"
                                                    data-attachment-type="<?= esc($document['attachment_type'] ?? 'document') ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button type="button" class="ims-action-btn danger" title="<?= esc($supplierLang('actions.deleteDocument')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteSupplierDocumentModal"
                                                    data-delete-action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/documents/' . $document['id'] . '/delete')) ?>"
                                                    data-delete-label="<?= esc($document['file_name']) ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif ?>
                                        </div>
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

<div class="modal fade" id="deleteEvaluationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteEvaluationForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('delete.evaluationTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteEvaluationMessage"><?= esc($supplierLang('delete.evaluationDefaultMessage')) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="btn btn-danger"><?= esc($supplierLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="lockedEvaluationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc($supplierLang('delete.cannotDeleteEvaluation')) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
            </div>
            <div class="modal-body">
                <p id="lockedEvaluationMessage"><?= esc($supplierLang('delete.evaluationLockedDefault')) ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.close')) ?></button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierEvaluationDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="supplierEvaluationDetailForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('evaluation.detailTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p class="admin-muted" id="supplierEvaluationDetailCaption"></p>
                    <div style="overflow-x:auto;">
                        <table class="results-table app-responsive-table">
                            <thead>
                                <tr>
                                    <th><?= esc($supplierLang('columns.checkItem')) ?></th>
                                    <th><?= esc($supplierLang('columns.weight')) ?></th>
                                    <th><?= esc($supplierLang('columns.actualRate')) ?></th>
                                    <th><?= esc($supplierLang('columns.result')) ?></th>
                                    <th><?= esc($supplierLang('columns.evaluatedBy')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="supplierEvaluationDetailRows">
                                <tr class="main-row">
                                    <td colspan="5" class="admin-muted"><?= esc($supplierLang('evaluation.noDetailRows')) ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.close')) ?></button>
                    <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                        <button type="submit" class="admin-btn primary" id="supplierEvaluationConfirmButton"><?= esc($supplierLang('common.confirm')) ?></button>
                    <?php endif ?>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierEvaluationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/evaluations')) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('actions.startEvaluation')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="evaluation-date"><?= esc($supplierLang('forms.evaluationDate')) ?></label>
                            <input id="evaluation-date" name="evaluation_date" type="date" value="<?= old('evaluation_date', date('Y-m-d')) ?>">
                            <?php if (isset($validation['evaluation_date'])) : ?><small class="admin-error"><?= esc($validation['evaluation_date']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="evaluation-checklist"><?= esc($supplierLang('columns.checklist')) ?></label>
                            <select id="evaluation-checklist" name="ims_checklist_id" class="filter-select">
                                <option value=""><?= esc($supplierLang('forms.selectChecklist')) ?></option>
                                <?php foreach ($checklists as $checklist) : ?>
                                    <option value="<?= esc((string) $checklist['id']) ?>" <?= old('ims_checklist_id') == $checklist['id'] ? 'selected' : '' ?>>
                                        <?= esc($checklist['checklist_name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['ims_checklist_id'])) : ?><small class="admin-error"><?= esc($validation['ims_checklist_id']) ?></small><?php endif ?>
                            <?php if ($checklists === []) : ?><small class="admin-error"><?= esc($supplierLang('forms.noChecklistAvailable')) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group full">
                            <label for="evaluation-comment"><?= esc($supplierLang('columns.comment')) ?></label>
                            <textarea id="evaluation-comment" name="comment" rows="3"><?= old('comment') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary" <?= $checklists === [] ? 'disabled' : '' ?>><?= esc($supplierLang('forms.createEvaluation')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierDocumentUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/documents')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('actions.uploadDocument')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label for="supplier-doc-type"><?= esc($supplierLang('forms.attachmentType')) ?></label>
                            <select id="supplier-doc-type" name="attachment_type" class="filter-select">
                                <?php foreach ($attachmentTypeLabels as $value => $label) : ?>
                                    <option value="<?= esc($value) ?>" <?= old('attachment_type', 'document') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="supplier-doc-file"><?= esc($supplierLang('columns.file')) ?></label>
                            <input id="supplier-doc-file" class="app-file-input" name="attachment_file" type="file">
                            <?php if (isset($validation['attachment_file'])) : ?><small class="admin-error"><?= esc($validation['attachment_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($supplierLang('documents.maxSize')) ?></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($supplierLang('forms.uploadFile')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierDocumentEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="supplierDocumentEditForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('actions.editDocument')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label for="supplier-doc-edit-type"><?= esc($supplierLang('forms.attachmentType')) ?></label>
                            <select id="supplier-doc-edit-type" name="attachment_type" class="filter-select">
                                <?php foreach ($attachmentTypeLabels as $value => $label) : ?>
                                    <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($supplierLang('forms.saveDocument')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSupplierDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteSupplierDocumentForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('delete.documentTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteSupplierDocumentMessage"><?= esc($supplierLang('delete.documentDefaultMessage')) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="btn btn-danger"><?= esc($supplierLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierBrandMappingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/suppliers/' . ($supplier['id'] ?? 0) . '/brand-mapping')) ?>">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('tabs.brandMapping')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p class="admin-muted"><?= esc($supplierLang('brandMapping.help', [$supplier['supplier_name'] ?? ''])) ?></p>
                    <?php if ($brands === []) : ?>
                        <div class="admin-alert danger"><?= esc($supplierLang('brandMapping.noBrands')) ?></div>
                    <?php else : ?>
                        <div class="ims-brand-check-grid">
                            <?php foreach ($brands as $brand) : ?>
                                <label class="ims-brand-check-item">
                                    <input type="checkbox" name="brand_ids[]" value="<?= esc((string) $brand['id']) ?>" <?= in_array((int) $brand['id'], $mappedBrandIds, true) ? 'checked' : '' ?>>
                                    <span><?= esc($brand['brand_name']) ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                    <?php endif ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary" <?= $brands === [] ? 'disabled' : '' ?>><?= esc($supplierLang('brandMapping.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    (() => {
        const escapeHtml = (value) => String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const formatNumber = (value, decimals) => {
            const number = Number(value ?? 0);
            return Number.isFinite(number) ? number.toFixed(decimals) : Number(0).toFixed(decimals);
        };
        const actualRateOptions = (selectedValue) => {
            const selected = Math.min(10, Math.max(1, Math.round(Number(selectedValue || 1))));
            let options = '';

            for (let value = 1; value <= 10; value++) {
                options += `<option value="${value}"${value === selected ? ' selected' : ''}>${value}</option>`;
            }

            return options;
        };
        const updateEvaluationDetailScore = () => {
            const body = document.getElementById('supplierEvaluationDetailRows');
            const rows = body.querySelectorAll('tr[data-evaluation-detail-row]');
            let total = 0;

            rows.forEach((row) => {
                const weight = Number(row.dataset.weight || 0);
                const actualRate = Number(row.querySelector('.js-evaluation-actual-rate')?.value || 0);
                const result = weight * actualRate;
                total += result / 10;

                const resultCell = row.querySelector('[data-result-cell]');
                if (resultCell) {
                    resultCell.textContent = formatNumber(result, 4);
                }
            });

            const scoreCell = document.getElementById('supplierEvaluationDetailScore');
            if (scoreCell) {
                scoreCell.textContent = formatNumber(total, 2) + '%';
            }
        };

        const evaluationDetailModal = document.getElementById('supplierEvaluationDetailModal');
        evaluationDetailModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('supplierEvaluationDetailForm');
            const confirmButton = document.getElementById('supplierEvaluationConfirmButton');
            const caption = document.getElementById('supplierEvaluationDetailCaption');
            const body = document.getElementById('supplierEvaluationDetailRows');
            const isLocked = trigger?.dataset.locked === '1';
            let details = [];

            try {
                details = JSON.parse(trigger?.dataset.details || '[]');
            } catch {
                details = [];
            }

            form.action = trigger?.dataset.confirmAction || '';
            if (confirmButton) {
                confirmButton.hidden = isLocked;
                confirmButton.disabled = isLocked;
            }
            caption.textContent = trigger?.dataset.evaluationLabel || '';

            if (!Array.isArray(details) || details.length === 0) {
                body.innerHTML = <?= json_encode('<tr class="main-row"><td colspan="5" class="admin-muted">' . $supplierLang('evaluation.noDetailRows') . '</td></tr>') ?>;
                if (confirmButton) {
                    confirmButton.disabled = true;
                }
                return;
            }

            body.innerHTML = details.map((detail) => `
                <tr class="main-row" data-evaluation-detail-row data-weight="${escapeHtml(detail.weight || 0)}">
                    <td data-label="<?= esc($supplierLang('columns.checkItem')) ?>"><strong>${escapeHtml(detail.check_item || '-')}</strong></td>
                    <td data-label="<?= esc($supplierLang('columns.weight')) ?>">${escapeHtml(formatNumber(detail.weight, 4))}</td>
                    <td data-label="<?= esc($supplierLang('columns.actualRate')) ?>">
                        <select class="filter-select js-evaluation-actual-rate" name="actual_rate[${escapeHtml(detail.id)}]"${isLocked ? ' disabled' : ''}>
                            ${actualRateOptions(detail.actual_rate)}
                        </select>
                    </td>
                    <td data-label="<?= esc($supplierLang('columns.result')) ?>" data-result-cell>${escapeHtml(formatNumber(Number(detail.weight || 0) * Math.min(10, Math.max(1, Number(detail.actual_rate || 1))), 4))}</td>
                    <td data-label="<?= esc($supplierLang('columns.evaluatedBy')) ?>">${escapeHtml(detail.evaluated_by_name || '-')}</td>
                </tr>
            `).join('') + `
                <tr class="main-row">
                    <td colspan="3"><strong><?= esc($supplierLang('evaluation.scoreAchieved')) ?></strong></td>
                    <td data-label="<?= esc($supplierLang('evaluation.score')) ?>"><strong id="supplierEvaluationDetailScore">0.00%</strong></td>
                    <td class="admin-muted"><?= esc($supplierLang('evaluation.scoreFormula')) ?></td>
                </tr>
            `;

            body.querySelectorAll('.js-evaluation-actual-rate').forEach((select) => {
                select.addEventListener('change', updateEvaluationDetailScore);
            });
            updateEvaluationDetailScore();
        });

        const deleteEvaluationModal = document.getElementById('deleteEvaluationModal');
        deleteEvaluationModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            document.getElementById('deleteEvaluationForm').action = trigger.dataset.deleteAction || '';
            document.getElementById('deleteEvaluationMessage').textContent = <?= json_encode($supplierLang('delete.evaluationMessagePrefix')) ?> + (trigger.dataset.deleteLabel || '') + <?= json_encode($supplierLang('delete.messageSuffix')) ?>;
        });

        const lockedEvaluationModal = document.getElementById('lockedEvaluationModal');
        lockedEvaluationModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const label = trigger?.dataset.evaluationLabel || '';
            document.getElementById('lockedEvaluationMessage').textContent = label
                ? <?= json_encode($supplierLang('delete.evaluationLockedPrefix')) ?> + label + <?= json_encode($supplierLang('delete.evaluationLockedSuffix')) ?>
                : <?= json_encode($supplierLang('delete.evaluationLockedDefault')) ?>;
        });

        const supplierDocumentEditModal = document.getElementById('supplierDocumentEditModal');
        supplierDocumentEditModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const form = document.getElementById('supplierDocumentEditForm');
            form.action = trigger.dataset.action || '';
            form.querySelector('[name="attachment_type"]').value = trigger.dataset.attachmentType || 'document';
        });

        const deleteSupplierDocumentModal = document.getElementById('deleteSupplierDocumentModal');
        deleteSupplierDocumentModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            document.getElementById('deleteSupplierDocumentForm').action = trigger.dataset.deleteAction || '';
            document.getElementById('deleteSupplierDocumentMessage').textContent = <?= json_encode($supplierLang('delete.documentMessagePrefix')) ?> + (trigger.dataset.deleteLabel || '') + <?= json_encode($supplierLang('delete.messageSuffix')) ?>;
        });

        const activeDetailTab = <?= json_encode((string) $activeDetailTab) ?>;
        const tabMap = {
            brands: 'tab-supplier-brands-btn',
            purchase_orders: 'tab-supplier-pos-btn',
            stock_items: 'tab-supplier-stock-btn',
            evaluations: 'tab-supplier-evaluations-btn',
            documents: 'tab-supplier-documents-btn',
        };
        const activeTabButton = document.getElementById(tabMap[activeDetailTab] || tabMap.brands);
        if (activeTabButton) {
            bootstrap.Tab.getOrCreateInstance(activeTabButton).show();
        }

        <?php if ($modalState === 'supplier-brand-modal') : ?>
            new bootstrap.Modal(document.getElementById('supplierBrandMappingModal')).show();
            bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-supplier-brands-btn')).show();
        <?php elseif ($modalState === 'supplier-evaluation-modal') : ?>
            new bootstrap.Modal(document.getElementById('supplierEvaluationModal')).show();
            bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-supplier-evaluations-btn')).show();
        <?php elseif ($modalState === 'supplier-document-upload-modal') : ?>
            new bootstrap.Modal(document.getElementById('supplierDocumentUploadModal')).show();
            bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-supplier-documents-btn')).show();
        <?php endif ?>
    })();
</script>
<?= $this->endSection() ?>
