<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation = $validation ?? []; ?>
<?php $lot = $lot ?? []; ?>
<?php $transactions = $transactions ?? []; ?>
<?php $attachments = $attachments ?? []; ?>
<?php $locations = $locations ?? []; ?>
<?php $modalState = is_array($modalState ?? null) ? $modalState : []; ?>
<?php
$lotLang = static fn (string $key, array $args = []): string => lang('IMS.lots.' . $key, $args);
$transactionTypeLabels = [
    'receipt' => $lotLang('transactionTypes.receipt'),
    'issue' => $lotLang('transactionTypes.issue'),
    'transfer' => $lotLang('transactionTypes.transfer'),
    'adjustment' => $lotLang('transactionTypes.adjustment'),
    'return' => $lotLang('transactionTypes.return'),
    'disposal' => $lotLang('transactionTypes.disposal'),
];
$attachmentTypeLabels = [
    'coa' => $lotLang('attachmentTypes.coa'),
    'sds' => $lotLang('attachmentTypes.sds'),
    'photo' => $lotLang('attachmentTypes.photo'),
    'document' => $lotLang('attachmentTypes.document'),
    'other' => $lotLang('attachmentTypes.other'),
];
$lotId      = (int) ($lot['id'] ?? 0);
$lotLabel   = $lot['internal_lot_no'] ?: ($lot['lot_no'] ?: '#' . $lotId);
$expiryDate = $lot['expiry_date'] ?? '';
$isExpired  = $expiryDate !== '' && $expiryDate !== null && strtotime((string) $expiryDate) < strtotime('today');
$initialQty = (float) ($lot['initial_qty'] ?? 0);
$currentQty = (float) ($lot['current_qty'] ?? 0);
$isLowStock = $initialQty > 0 && $currentQty <= 0.03 * $initialQty;
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar" style="flex-wrap:wrap;gap:12px;">
        <div class="admin-card-head" style="padding-left:0;flex:1;min-width:0;">
            <h3 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tool-id-badge"><?= esc($lotLabel) ?></span>
                <?= esc($lot['item_name'] ?? '') ?>
                <span class="admin-muted" style="font-size:.85em;font-weight:400;"><?= esc($lot['item_code'] ?? '') ?></span>
                <?php if ($isExpired) : ?><span class="admin-badge danger"><?= esc($lotLang('status.expired')) ?></span><?php endif ?>
                <?php if ($isLowStock) : ?><span class="admin-badge warning"><?= esc($lotLang('status.lowStock')) ?></span><?php endif ?>
            </h3>
            <span>
                <?php if ($lot['received_date'] ?? '') : ?><?= esc($lotLang('labels.received')) ?>: <?= esc($lot['received_date']) ?><?php endif ?>
                <?php if ($expiryDate) : ?> &middot; <?= esc($lotLang('labels.expiry')) ?>: <span class="<?= $isExpired ? 'admin-error' : '' ?>"><?= esc($expiryDate) ?></span><?php endif ?>
                <?php if ($currentQty || ($lot['unit_name'] ?? '')) : ?> &middot; <?= esc((string) $currentQty) ?> <?= esc($lot['unit_name'] ?? '') ?><?php endif ?>
                <?php if ($lot['storage_location_name'] ?? '') : ?> &middot; <?= esc($lot['storage_location_name']) ?><?php endif ?>
                <?php if ($lot['supplier_name'] ?? '') : ?> &middot; <?= esc($lot['supplier_name']) ?><?php endif ?>
                <?php if ($lot['brand_name'] ?? '') : ?> &middot; <?= esc($lot['brand_name']) ?><?php endif ?>
                <span class="admin-badge <?= match(strtolower($lot['ownership_status'] ?? 'owned')) { 'consigned' => 'warning', 'borrowed' => 'neutral', default => 'success' } ?>" style="margin-left:6px;"><?= esc(ucfirst($lot['ownership_status'] ?? 'owned')) ?></span>
            </span>
        </div>
        <div class="ims-toolbar-actions">
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/lots/' . $lotId . '/label')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-tag"></i> <?= esc($lotLang('actions.printLabel')) ?>
            </a>
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/lots')) ?>">
                <i class="fa-solid fa-arrow-left"></i> <?= esc($lotLang('common.backToLots')) ?>
            </a>
        </div>
    </div>

    <div style="padding: 0 20px 0 20px; border-bottom: 1px solid var(--border-color);">
        <ul class="nav nav-tabs border-0" id="lotDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-transactions-btn" data-bs-toggle="tab" data-bs-target="#tabTransactions" type="button" role="tab">
                    <i class="fa-solid fa-right-left"></i> <?= esc($lotLang('tabs.transactions')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($transactions) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-attachments-btn" data-bs-toggle="tab" data-bs-target="#tabAttachments" type="button" role="tab">
                    <i class="fa-solid fa-paperclip"></i> <?= esc($lotLang('tabs.attachments')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($attachments) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- ── Transactions Tab ── -->
        <div class="tab-pane fade show active" id="tabTransactions" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($lotLang('detail.transactionCount', [count($transactions)])) ?></span>
                <?php if (lab_core_can('ims.stock.adjust')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#lotTransactionFormModal">
                        <i class="fa-solid fa-plus"></i> <?= esc($lotLang('actions.newTransaction')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($lotLang('columns.no')) ?></th>
                            <th><?= esc($lotLang('columns.type')) ?></th>
                            <th><?= esc($lotLang('columns.qty')) ?></th>
                            <th><?= esc($lotLang('columns.from')) ?></th>
                            <th><?= esc($lotLang('columns.to')) ?></th>
                            <th><?= esc($lotLang('columns.date')) ?></th>
                            <th><?= esc($lotLang('columns.by')) ?></th>
                            <th><?= esc($lotLang('columns.reason')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($transactions === []) : ?>
                            <tr class="main-row"><td colspan="8" class="admin-muted"><?= esc($lotLang('detail.noTransactions')) ?></td></tr>
                        <?php else : ?>
                            <?php
                            $txTypeClass = static fn (?string $t): string => match(strtolower((string) $t)) {
                                'receipt'    => 'success',
                                'issue'      => 'danger',
                                'adjustment' => 'warning',
                                'disposal'   => 'danger',
                                default      => 'neutral',
                            };
                            ?>
                            <?php foreach ($transactions as $tx) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($lotLang('columns.no')) ?>"><strong><?= esc($tx['transaction_no'] ?? '-') ?></strong></td>
                                    <td data-label="<?= esc($lotLang('columns.type')) ?>">
                                        <?php $txType = (string) ($tx['transaction_type'] ?? ''); ?>
                                        <span class="admin-badge <?= esc($txTypeClass($txType)) ?>"><?= esc($transactionTypeLabels[$txType] ?? ($txType !== '' ? $txType : '-')) ?></span>
                                    </td>
                                    <td data-label="<?= esc($lotLang('columns.qty')) ?>"><strong><?= esc((string) ($tx['qty'] ?? '0')) ?></strong> <?= esc($tx['unit_name'] ?? '') ?></td>
                                    <td data-label="<?= esc($lotLang('columns.from')) ?>"><?= esc($tx['from_location_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($lotLang('columns.to')) ?>"><?= esc($tx['to_location_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($lotLang('columns.date')) ?>" class="admin-muted"><?= esc(substr((string) ($tx['transaction_date'] ?? ''), 0, 16)) ?></td>
                                    <td data-label="<?= esc($lotLang('columns.by')) ?>" class="admin-muted"><?= esc($tx['performed_by_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($lotLang('columns.reason')) ?>" class="admin-muted"><?= esc($tx['reason'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── Attachments Tab ── -->
        <div class="tab-pane fade" id="tabAttachments" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($lotLang('detail.attachmentCount', [count($attachments)])) ?></span>
                <?php if (lab_core_can('ims.stock.adjust')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#lotAttachmentUploadModal">
                        <i class="fa-solid fa-upload"></i> <?= esc($lotLang('actions.uploadDocument')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($lotLang('columns.file')) ?></th>
                            <th><?= esc($lotLang('columns.type')) ?></th>
                            <th><?= esc($lotLang('columns.remarks')) ?></th>
                            <th><?= esc($lotLang('columns.uploaded')) ?></th>
                            <th><?= esc($lotLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($attachments === []) : ?>
                            <tr class="main-row"><td colspan="5" class="admin-muted"><?= esc($lotLang('detail.noAttachments')) ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($attachments as $att) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($lotLang('columns.file')) ?>"><strong><?= esc($att['file_name'] ?? '') ?></strong></td>
                                    <td data-label="<?= esc($lotLang('columns.type')) ?>"><span class="admin-badge neutral"><?= esc($attachmentTypeLabels[$att['attachment_type'] ?? 'document'] ?? strtoupper($att['attachment_type'] ?? 'document')) ?></span></td>
                                    <td data-label="<?= esc($lotLang('columns.remarks')) ?>" class="admin-muted"><?= esc($att['remarks'] ?? '-') ?></td>
                                    <td data-label="<?= esc($lotLang('columns.uploaded')) ?>" class="admin-muted"><?= esc($att['uploaded_at'] ?? '-') ?></td>
                                    <td data-label="<?= esc($lotLang('columns.action')) ?>">
                                        <a class="ims-action-btn" title="<?= esc($lotLang('actions.viewFile')) ?>" href="<?= esc(site_url('ims/lots/' . $lot['id'] . '/attachments/' . $att['id'])) ?>" target="_blank" rel="noopener">
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

<!-- ── Transaction Form Modal ── -->
<div class="modal fade app-crud-modal" id="lotTransactionFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/lots/' . $lotId . '/transactions')) ?>" id="lotTransactionForm">
                <?= csrf_field() ?>
                <input type="hidden" name="item_id" value="<?= esc((string) ($lot['item_id'] ?? '')) ?>">
                <input type="hidden" name="stock_lot_id" value="<?= esc((string) $lotId) ?>">
                <input type="hidden" name="unit_id" value="<?= esc((string) ($lot['current_unit_id'] ?? '')) ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($lotLang('actions.newTransaction')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($lotLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="tx-type"><?= esc($lotLang('forms.transactionType')) ?></label>
                            <select id="tx-type" name="transaction_type" class="filter-select">
                                <?php foreach ($transactionTypeLabels as $val => $label) : ?>
                                    <option value="<?= esc($val) ?>" <?= old('transaction_type') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['transaction_type'])) : ?><small class="admin-error"><?= esc($validation['transaction_type']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-date"><?= esc($lotLang('forms.transactionDate')) ?></label>
                            <input id="tx-date" name="transaction_date" type="datetime-local" value="<?= esc((string) old('transaction_date')) ?>">
                            <?php if (isset($validation['transaction_date'])) : ?><small class="admin-error"><?= esc($validation['transaction_date']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-qty"><?= esc($lotLang('forms.quantity')) ?></label>
                            <input id="tx-qty" name="qty" type="number" step="0.0001" min="0.0001" value="<?= esc((string) old('qty')) ?>">
                            <?php if (isset($validation['qty'])) : ?><small class="admin-error"><?= esc($validation['qty']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label><?= esc($lotLang('forms.unit')) ?></label>
                            <input type="text" value="<?= esc($lot['unit_name'] ?? '') ?>" readonly>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-from-loc"><?= esc($lotLang('forms.fromLocation')) ?></label>
                            <select id="tx-from-loc" name="from_location_id" class="filter-select">
                                <option value=""><?= esc($lotLang('forms.none')) ?></option>
                                <?php foreach ($locations as $loc) : ?><option value="<?= esc((string) $loc['id']) ?>" <?= old('from_location_id', (string) ($lot['storage_location_id'] ?? '')) == $loc['id'] ? 'selected' : '' ?>><?= esc($loc['name']) ?></option><?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-to-loc"><?= esc($lotLang('forms.toLocation')) ?></label>
                            <select id="tx-to-loc" name="to_location_id" class="filter-select">
                                <option value=""><?= esc($lotLang('forms.none')) ?></option>
                                <?php foreach ($locations as $loc) : ?><option value="<?= esc((string) $loc['id']) ?>" <?= old('to_location_id') == $loc['id'] ? 'selected' : '' ?>><?= esc($loc['name']) ?></option><?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group full">
                            <label for="tx-reason"><?= esc($lotLang('columns.reason')) ?></label>
                            <textarea id="tx-reason" name="reason" rows="3"><?= old('reason') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($lotLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($lotLang('forms.saveTransaction')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Attachment Upload Modal ── -->
<div class="modal fade" id="lotAttachmentUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/lots/' . $lotId . '/attachments')) ?>" id="lotAttachmentUploadForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($lotLang('actions.uploadDocument')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($lotLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label for="att-type"><?= esc($lotLang('forms.attachmentType')) ?></label>
                            <select id="att-type" name="attachment_type" class="filter-select">
                                <?php foreach ($attachmentTypeLabels as $v => $l) : ?>
                                    <option value="<?= esc($v) ?>" <?= old('attachment_type', 'document') === $v ? 'selected' : '' ?>><?= esc($l) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="att-file"><?= esc($lotLang('columns.file')) ?></label>
                            <input id="att-file" class="app-file-input" name="attachment_file" type="file">
                            <?php if (isset($validation['attachment_file'])) : ?><small class="admin-error"><?= esc($validation['attachment_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($lotLang('forms.maxFileSize')) ?></small>
                        </div>
                        <div class="admin-form-group">
                            <label for="att-remarks"><?= esc($lotLang('columns.remarks')) ?></label>
                            <textarea id="att-remarks" name="remarks" rows="3"><?= old('remarks') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($lotLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($lotLang('forms.uploadFile')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
(() => {
    const txModal = document.getElementById('lotTransactionFormModal');
    txModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = document.getElementById('lotTransactionForm');
        if (!trigger && form.dataset.preserveOld === '1') return;
        form.dataset.preserveOld = '0';
        form.reset();
        const dateInput = form.querySelector('[name="transaction_date"]');
        if (dateInput) dateInput.value = new Date().toISOString().slice(0, 16);
    });

    <?php if (($modalState['modal'] ?? null) === 'lot-tx-modal') : ?>
        {
            const form = document.getElementById('lotTransactionForm');
            form.dataset.preserveOld = '1';
            new bootstrap.Modal(document.getElementById('lotTransactionFormModal')).show();
        }
    <?php elseif (($modalState['modal'] ?? null) === 'lot-attachment-modal') : ?>
        new bootstrap.Modal(document.getElementById('lotAttachmentUploadModal')).show();
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-attachments-btn')).show();
    <?php endif ?>
})();
</script>
<?= $this->endSection() ?>
