<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation = $validation ?? []; ?>
<?php $pager      = $pager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]]; ?>
<?php $poQuery    = $poQuery ?? ['page' => 1, 'per_page' => 10, 'q' => '']; ?>
<?php $modalState = is_array($modalState ?? null) ? $modalState : []; ?>
<?php
$poLang = static function (string $key, array $args = [], ?string $fallback = null): string {
    $line = 'IMS.purchaseOrders.' . $key;
    $text = lang($line, $args);

    return $text === $line ? ($fallback ?? $key) : $text;
};
$buildUrl = static function (array $overrides = []) use ($poQuery): string {
    return site_url('ims/purchase-orders?' . http_build_query(array_merge($poQuery, $overrides)));
};
$statusCfg = [
    'draft'              => ['label' => $poLang('status.draft'),              'class' => ''],
    'approved'           => ['label' => $poLang('status.approved'),           'class' => 'neutral'],
    'ordered'            => ['label' => $poLang('status.ordered'),            'class' => 'info'],
    'partially_received' => ['label' => $poLang('status.partiallyReceived'),  'class' => 'warning'],
    'received'           => ['label' => $poLang('status.received'),           'class' => 'success'],
    'cancelled'          => ['label' => $poLang('status.cancelled'),          'class' => 'danger'],
];
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3><?= esc($poLang('title')) ?></h3>
            <span><?= esc($poLang('list.subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.stock.receive')) : ?>
            <div class="ims-toolbar-actions">
                <button type="button" class="admin-btn primary"
                    data-bs-toggle="modal" data-bs-target="#poFormModal" data-mode="create">
                    <?= esc($poLang('actions.newPurchaseOrder')) ?>
                </button>
            </div>
        <?php endif ?>
    </div>

    <div style="padding:0 20px 16px 20px;">
        <form method="get" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="per_page" value="<?= esc((string) $poQuery['per_page']) ?>">
            <div class="admin-form-group" style="flex:1;margin:0;">
                <input type="search" name="q" value="<?= esc($poQuery['q'] ?? '') ?>" placeholder="<?= esc($poLang('list.searchPlaceholder')) ?>">
            </div>
            <?php if (($poQuery['q'] ?? '') !== '') : ?>
                <a href="<?= esc(site_url('ims/purchase-orders?' . http_build_query(['page' => 1, 'per_page' => $poQuery['per_page']]))) ?>" class="admin-btn secondary"><?= esc($poLang('common.clear')) ?></a>
            <?php endif ?>
        </form>
    </div>

    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($poLang('columns.poNumber')) ?></th>
                        <th><?= esc($poLang('columns.supplier')) ?></th>
                        <th><?= esc($poLang('columns.status')) ?></th>
                        <th><?= esc($poLang('columns.grandTotal')) ?></th>
                        <th><?= esc($poLang('columns.orderDate')) ?></th>
                        <th><?= esc($poLang('columns.expectedDeliveryShort')) ?></th>
                        <th><?= esc($poLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="7" class="admin-muted"><?= esc($poLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <?php
                            $sc          = $statusCfg[$row['status'] ?? 'draft'] ?? $statusCfg['draft'];
                            $isDraft     = $row['status'] === 'draft';
                            $isApproved  = $row['status'] === 'approved';
                            $cancellable = in_array($row['status'], ['draft', 'approved', 'ordered'], true);
                            ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($poLang('columns.poNumber')) ?>">
                                    <strong><?= esc($row['po_number']) ?></strong>
                                    <?php if (! empty($row['notes'])) : ?>
                                        <div class="admin-muted" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= esc($row['notes']) ?>"><?= esc($row['notes']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($poLang('columns.supplier')) ?>">
                                    <strong><?= esc($row['supplier_name'] ?? '—') ?></strong>
                                    <?php if (! empty($row['payment_terms'])) : ?>
                                        <div class="admin-muted"><?= esc($row['payment_terms']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($poLang('columns.status')) ?>">
                                    <span class="admin-badge <?= esc($sc['class']) ?>"><?= esc($sc['label']) ?></span>
                                </td>
                                <td data-label="<?= esc($poLang('columns.grandTotal')) ?>">
                                    <strong><?= number_format((float) $row['grand_total'], 2) ?></strong>
                                    <?php if ((float) $row['tax_amount'] > 0 || (float) $row['shipping_cost'] > 0) : ?>
                                        <div class="admin-muted">
                                            <?php if ((float) $row['tax_amount'] > 0) : ?><?= esc($poLang('columns.tax')) ?>: <?= number_format((float) $row['tax_amount'], 2) ?> <?php endif ?>
                                            <?php if ((float) $row['shipping_cost'] > 0) : ?><?= esc($poLang('columns.shippingShort')) ?>: <?= number_format((float) $row['shipping_cost'], 2) ?><?php endif ?>
                                        </div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($poLang('columns.orderDate')) ?>"><?= esc($row['order_date'] ?? '—') ?></td>
                                <td data-label="<?= esc($poLang('columns.expectedDeliveryShort')) ?>"><?= esc($row['expected_delivery_date'] ?? '—') ?></td>
                                <td data-label="<?= esc($poLang('columns.action')) ?>">
                                    <div class="ims-action-group">
                                        <!-- Detail -->
                                        <a class="ims-action-btn" href="<?= esc(site_url('ims/purchase-orders/' . $row['id'])) ?>" title="<?= esc($poLang('actions.viewDetail')) ?>">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        <a class="ims-action-btn" href="<?= esc(site_url('ims/purchase-orders/' . $row['id'] . '/pdf')) ?>" target="_blank" rel="noopener" title="<?= esc($poLang('actions.printPdf')) ?>">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>
                                        <!-- Edit (draft only) -->
                                        <?php if ($isDraft && lab_core_can('ims.stock.receive')) : ?>
                                            <button type="button" class="ims-action-btn"
                                                data-bs-toggle="modal" data-bs-target="#poFormModal"
                                                data-mode="edit"
                                                data-po-id="<?= (int) $row['id'] ?>"
                                                data-po-number="<?= esc($row['po_number']) ?>"
                                                data-supplier-id="<?= (int) $row['supplier_id'] ?>"
                                                data-order-date="<?= esc($row['order_date'] ?? '') ?>"
                                                data-expected-delivery-date="<?= esc($row['expected_delivery_date'] ?? '') ?>"
                                                data-tax-amount="<?= esc((string) $row['tax_amount']) ?>"
                                                data-shipping-cost="<?= esc((string) $row['shipping_cost']) ?>"
                                                data-payment-terms="<?= esc($row['payment_terms'] ?? '') ?>"
                                                data-delivery-address="<?= esc($row['delivery_address'] ?? '') ?>"
                                                data-notes="<?= esc($row['notes'] ?? '') ?>"
                                                data-action="<?= esc(site_url('ims/purchase-orders/' . $row['id'])) ?>"
                                                title="<?= esc($poLang('actions.edit')) ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        <?php endif ?>
                                        <!-- Approve (draft) -->
                                        <?php if ($isDraft && lab_core_can('ims.stock.adjust')) : ?>
                                            <button type="button" class="ims-action-btn success"
                                                data-bs-toggle="modal" data-bs-target="#approvePoModal"
                                                data-po-id="<?= (int) $row['id'] ?>"
                                                data-po-number="<?= esc($row['po_number']) ?>"
                                                data-action="<?= esc(site_url('ims/purchase-orders/' . $row['id'] . '/approve')) ?>"
                                                title="<?= esc($poLang('actions.approve')) ?>">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        <?php endif ?>
                                        <!-- Mark Ordered (approved) -->
                                        <?php if ($isApproved && lab_core_can('ims.stock.adjust')) : ?>
                                            <button type="button" class="ims-action-btn success"
                                                data-bs-toggle="modal" data-bs-target="#orderPoModal"
                                                data-po-id="<?= (int) $row['id'] ?>"
                                                data-po-number="<?= esc($row['po_number']) ?>"
                                                data-order-date="<?= esc(date('Y-m-d')) ?>"
                                                data-action="<?= esc(site_url('ims/purchase-orders/' . $row['id'] . '/order')) ?>"
                                                title="<?= esc($poLang('actions.markOrdered')) ?>">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                        <?php endif ?>
                                        <!-- Cancel -->
                                        <?php if ($cancellable && lab_core_can('ims.stock.adjust')) : ?>
                                            <button type="button" class="ims-action-btn danger"
                                                data-bs-toggle="modal" data-bs-target="#cancelPoModal"
                                                data-po-id="<?= (int) $row['id'] ?>"
                                                data-po-number="<?= esc($row['po_number']) ?>"
                                                data-action="<?= esc(site_url('ims/purchase-orders/' . $row['id'] . '/cancel')) ?>"
                                                title="<?= esc($poLang('actions.cancel')) ?>">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        <?php endif ?>
                                        <!-- Delete (draft only) -->
                                        <?php if ($isDraft && lab_core_can('ims.stock.adjust')) : ?>
                                            <button type="button" class="ims-action-btn danger"
                                                data-bs-toggle="modal" data-bs-target="#deletePoModal"
                                                data-delete-message="<?= esc($poLang('delete.message', [$row['po_number']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/purchase-orders/' . $row['id'] . '/delete')) ?>"
                                                title="<?= esc($poLang('actions.delete')) ?>">
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

        <div class="results-footer">
            <div class="result-count">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <strong><?= esc((string) $pager['total']) ?></strong>&nbsp;<?= esc($poLang('list.records')) ?>
                <span class="admin-muted"><?= $pager['total'] > 0 ? '(' . $pager['from'] . '–' . $pager['to'] . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= esc($poQuery['q'] ?? '') ?>">
                <span><?= esc($poLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $opt) : ?>
                        <option value="<?= $opt ?>" <?= (int) $pager['perPage'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($pager['page'] > 1) : ?><a class="page-btn" href="<?= esc($buildUrl(['page' => $pager['page'] - 1])) ?>">&lsaquo;</a><?php endif ?>
                <?php foreach ($pager['pages'] as $pg) : ?><a class="page-btn <?= (int) $pager['page'] === $pg ? 'active' : '' ?>" href="<?= esc($buildUrl(['page' => $pg])) ?>"><?= esc((string) $pg) ?></a><?php endforeach ?>
                <?php if ($pager['page'] < $pager['totalPages']) : ?><a class="page-btn" href="<?= esc($buildUrl(['page' => $pager['page'] + 1])) ?>">&rsaquo;</a><?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- ── PO Create / Edit Modal ───────────────────────────────────────────── -->
<div class="modal fade app-crud-modal" id="poFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/purchase-orders') ?>" id="poForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('actions.newPurchaseOrder')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (($modalState['modal'] ?? '') === 'po-form-modal' && $validation !== []) : ?>
                        <div class="admin-alert danger"><?= esc(implode(' ', $validation)) ?></div>
                    <?php endif ?>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label><?= esc($poLang('columns.supplier')) ?> <span class="required">*</span></label>
                            <select name="supplier_id" id="po-supplier" required>
                                <option value=""><?= esc($poLang('placeholders.selectSupplier')) ?></option>
                                <?php foreach ($suppliers as $s) : ?>
                                    <option value="<?= (int) $s['id'] ?>"
                                        <?= (($modalState['modal'] ?? '') === 'po-form-modal') && (string) old('supplier_id') === (string) $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['supplier_name']) ?>
                                    </option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label><?= esc($poLang('columns.orderDate')) ?></label>
                            <input type="date" name="order_date" id="po-order-date"
                                value="<?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('order_date', '')) : '' ?>">
                        </div>
                        <div class="admin-form-group">
                            <label><?= esc($poLang('columns.expectedDelivery')) ?></label>
                            <input type="date" name="expected_delivery_date" id="po-delivery-date"
                                value="<?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('expected_delivery_date', '')) : '' ?>">
                        </div>
                        <div class="admin-form-group">
                            <label><?= esc($poLang('columns.taxAmount')) ?></label>
                            <input type="number" name="tax_amount" id="po-tax" step="any" min="0"
                                value="<?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('tax_amount', '0')) : '0' ?>">
                        </div>
                        <div class="admin-form-group">
                            <label><?= esc($poLang('columns.shippingCost')) ?></label>
                            <input type="number" name="shipping_cost" id="po-shipping" step="any" min="0"
                                value="<?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('shipping_cost', '0')) : '0' ?>">
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label><?= esc($poLang('columns.paymentTerms')) ?></label>
                            <input type="text" name="payment_terms" id="po-payment-terms" placeholder="<?= esc($poLang('placeholders.paymentTerms')) ?>"
                                value="<?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('payment_terms', '')) : '' ?>">
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label><?= esc($poLang('columns.deliveryAddress')) ?></label>
                            <textarea name="delivery_address" id="po-delivery-address" rows="2"><?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('delivery_address', '')) : '' ?></textarea>
                        </div>
                        <div class="admin-form-group" style="grid-column:1/-1;">
                            <label><?= esc($poLang('columns.notes')) ?></label>
                            <textarea name="notes" id="po-notes" rows="2"><?= (($modalState['modal'] ?? '') === 'po-form-modal') ? esc(old('notes', '')) : '' ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary" id="poFormSubmitBtn"><?= esc($poLang('actions.createPurchaseOrder')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Approve PO Modal ──────────────────────────────────────────────────── -->
<div class="modal fade" id="approvePoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="approvePoForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.approveTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="approvePoMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn success"><?= esc($poLang('actions.approve')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Mark Ordered Modal ────────────────────────────────────────────────── -->
<div class="modal fade" id="orderPoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="orderPoForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.orderTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="orderPoMessage"></p>
                    <div class="admin-form-group">
                        <label><?= esc($poLang('columns.orderDate')) ?></label>
                        <input type="date" name="order_date" id="orderPoDate">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($poLang('actions.confirmOrdered')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Cancel PO Modal ───────────────────────────────────────────────────── -->
<div class="modal fade" id="cancelPoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="cancelPoForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.cancelTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="cancelPoMessage"></p>
                    <div class="admin-form-group">
                        <label><?= esc($poLang('fields.reason')) ?></label>
                        <textarea name="cancel_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn danger"><?= esc($poLang('actions.cancelOrder')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete PO Modal ───────────────────────────────────────────────────── -->
<div class="modal fade" id="deletePoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deletePoForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($poLang('modal.deleteTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deletePoMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($poLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn danger"><?= esc($poLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const poBaseUrl = '<?= esc(site_url('ims/purchase-orders')) ?>';
const poText = <?= json_encode([
    'newPurchaseOrder' => $poLang('actions.newPurchaseOrder'),
    'editPurchaseOrder' => $poLang('actions.editPurchaseOrder'),
    'createPurchaseOrder' => $poLang('actions.createPurchaseOrder'),
    'saveChanges' => $poLang('actions.saveChanges'),
    'approveMessage' => $poLang('modal.approveMessage'),
    'orderMessage' => $poLang('modal.orderMessage'),
    'cancelMessage' => $poLang('modal.cancelMessage'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;

document.getElementById('poFormModal').addEventListener('show.bs.modal', function (e) {
    const btn  = e.relatedTarget;
    const mode = btn ? btn.dataset.mode : 'create';
    const form = document.getElementById('poForm');

    if (mode === 'edit') {
        document.querySelector('#poFormModal .modal-title').textContent = poText.editPurchaseOrder + ' - ' + (btn.dataset.poNumber || '');
        document.getElementById('poFormSubmitBtn').textContent = poText.saveChanges;
        form.action = btn.dataset.action || '';
        document.getElementById('po-supplier').value         = btn.dataset.supplierId      || '';
        document.getElementById('po-order-date').value       = btn.dataset.orderDate       || '';
        document.getElementById('po-delivery-date').value    = btn.dataset.expectedDeliveryDate || '';
        document.getElementById('po-tax').value              = btn.dataset.taxAmount       || '0';
        document.getElementById('po-shipping').value         = btn.dataset.shippingCost    || '0';
        document.getElementById('po-payment-terms').value    = btn.dataset.paymentTerms    || '';
        document.getElementById('po-delivery-address').value = btn.dataset.deliveryAddress || '';
        document.getElementById('po-notes').value            = btn.dataset.notes           || '';
    } else {
        document.querySelector('#poFormModal .modal-title').textContent = poText.newPurchaseOrder;
        document.getElementById('poFormSubmitBtn').textContent = poText.createPurchaseOrder;
        form.action = poBaseUrl;
        <?php if (($modalState['modal'] ?? '') !== 'po-form-modal') : ?>
        document.getElementById('po-supplier').value = '';
        document.getElementById('po-order-date').value = '';
        document.getElementById('po-delivery-date').value = '';
        document.getElementById('po-tax').value = '0';
        document.getElementById('po-shipping').value = '0';
        document.getElementById('po-payment-terms').value = '';
        document.getElementById('po-delivery-address').value = '';
        document.getElementById('po-notes').value = '';
        <?php endif ?>
    }
});

document.getElementById('approvePoModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const approvePoNumber = btn ? (btn.dataset.poNumber || '') : '';
    document.getElementById('approvePoMessage').textContent = poText.approveMessage.replace('{0}', approvePoNumber);
    document.getElementById('approvePoForm').action     = btn ? (btn.dataset.action || '') : '';
});

document.getElementById('orderPoModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const orderPoNumber = btn ? (btn.dataset.poNumber || '') : '';
    document.getElementById('orderPoMessage').textContent = poText.orderMessage.replace('{0}', orderPoNumber);
    document.getElementById('orderPoForm').action     = btn ? (btn.dataset.action || '') : '';
    document.getElementById('orderPoDate').value      = btn ? (btn.dataset.orderDate || '') : '';
});

document.getElementById('cancelPoModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const cancelPoNumber = btn ? (btn.dataset.poNumber || '') : '';
    document.getElementById('cancelPoMessage').textContent = poText.cancelMessage.replace('{0}', cancelPoNumber);
    document.getElementById('cancelPoForm').action     = btn ? (btn.dataset.action || '') : '';
    document.querySelector('#cancelPoForm [name="cancel_reason"]').value = '';
});

document.getElementById('deletePoModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('deletePoMessage').innerHTML = btn ? (btn.dataset.deleteMessage || '') : '';
    document.getElementById('deletePoForm').action       = btn ? (btn.dataset.deleteAction || '') : '';
});

(() => {
    <?php if (($modalState['modal'] ?? null) === 'po-form-modal') : ?>
        {
            const form = document.getElementById('poForm');
            <?php if (! empty($modalState['po_id'])) : ?>
                document.querySelector('#poFormModal .modal-title').textContent = poText.editPurchaseOrder;
                document.getElementById('poFormSubmitBtn').textContent = poText.saveChanges;
                form.action = '<?= site_url('ims/purchase-orders/' . (int) ($modalState['po_id'] ?? 0)) ?>';
            <?php endif ?>
            new bootstrap.Modal(document.getElementById('poFormModal')).show();
        }
    <?php endif ?>
})();
</script>
<?= $this->endSection() ?>
