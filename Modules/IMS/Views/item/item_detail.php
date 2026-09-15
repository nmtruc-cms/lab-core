<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation = $validation ?? []; ?>
<?php $item = $item ?? []; ?>
<?php $stockLots = $stockLots ?? []; ?>
<?php $attachments = $attachments ?? []; ?>
<?php $modalState = is_array($modalState ?? null) ? $modalState : []; ?>
<?php $itemLang = static fn (string $key, array $args = []): string => lang('IMS.items.' . $key, $args); ?>


<div class="results-card ims-master-card">
    <div class="ims-master-toolbar" style="flex-wrap:wrap;gap:12px;">
        <div class="admin-card-head" style="padding-left:0;flex:1;min-width:0;">
            <h3 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tool-id-badge"><?= esc($item['item_code'] ?? '') ?></span>
                <?= esc($item['item_name'] ?? '') ?>
            </h3>
            <span>
                <?= esc($item['category_name'] ?? '') ?>
                <?php if ($item['unit_name'] ?? '') : ?> &middot; <?= esc($item['unit_name']) ?><?php endif ?>
                    <?php if ($item['cas_no'] ?? '') : ?> &middot; <?= esc($itemLang('columns.casNo')) ?> <?= esc($item['cas_no']) ?><?php endif ?>
                        <span class="admin-badge <?= match (strtolower($item['status'] ?? 'active')) {
                                                        'active' => 'success',
                                                        'blocked' => 'danger',
                                                        'obsolete' => 'warning',
                                                        default => 'neutral'
                                                    } ?>" style="margin-left:6px;"><?= esc($itemLang('status.' . strtolower((string) ($item['status'] ?? 'active')))) ?></span>
            </span>
        </div>
        <div class="ims-toolbar-actions">
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/items')) ?>">
                <i class="fa-solid fa-arrow-left"></i> <?= esc($itemLang('common.backToItems')) ?>
            </a>
        </div>
    </div>

    <div style="padding: 0 20px 0 20px; border-bottom: 1px solid var(--border-color);">
        <ul class="nav nav-tabs border-0" id="itemDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-lots-btn" data-bs-toggle="tab" data-bs-target="#tabStockLots" type="button" role="tab">
                    <i class="fa-solid fa-boxes-stacked"></i> <?= esc($itemLang('detail.tabs.stockLots')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($stockLots) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-attachments-btn" data-bs-toggle="tab" data-bs-target="#tabAttachments" type="button" role="tab">
                    <i class="fa-solid fa-paperclip"></i> <?= esc($itemLang('detail.tabs.attachments')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($attachments) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- ── Stock Lots Tab ── -->
        <div class="tab-pane fade show active" id="tabStockLots" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($itemLang('detail.lotCount', [count($stockLots)])) ?></span>
                <?php if (lab_core_can('ims.stock.receive')) : ?>
                    <a href="<?= esc(site_url('ims/items/' . ($item['id'] ?? 0) . '/lots/create')) ?>" class="admin-btn primary">
                        <i class="fa-solid fa-plus"></i> <?= esc($itemLang('addStockLot')) ?>
                    </a>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($itemLang('columns.lot')) ?></th>
                            <th><?= esc($itemLang('columns.supplierBrand')) ?></th>
                            <th><?= esc($itemLang('columns.dates')) ?></th>
                            <th><?= esc($itemLang('columns.qty')) ?></th>
                            <th><?= esc($itemLang('columns.location')) ?></th>
                            <th><?= esc($itemLang('columns.ownership')) ?></th>
                            <?php if (lab_core_can('ims.stock.adjust')) : ?><th><?= esc($itemLang('columns.action')) ?></th><?php endif ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($stockLots === []) : ?>
                            <tr class="main-row">
                                <td colspan="7" class="admin-muted"><?= esc($itemLang('detail.emptyLots')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($stockLots as $lot) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($itemLang('columns.lot')) ?>">
                                        <strong><?= esc($lot['internal_lot_no'] ?: ($lot['lot_no'] ?: '-')) ?></strong>
                                        <?php if ($lot['lot_no'] && $lot['internal_lot_no']) : ?><div class="admin-muted"><?= esc($itemLang('columns.lot')) ?>: <?= esc($lot['lot_no']) ?></div><?php endif ?>
                                        <?php if ($lot['supplier_lot_no'] ?? '') : ?><div class="admin-muted"><?= esc($itemLang('columns.supplier')) ?>: <?= esc($lot['supplier_lot_no']) ?></div><?php endif ?>
                                        <?php if ($lot['serial_no'] ?? '') : ?><div class="admin-muted">S/N: <?= esc($lot['serial_no']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($itemLang('columns.supplierBrand')) ?>">
                                        <?php if ($lot['supplier_name'] ?? '') : ?><div><?= esc($lot['supplier_name']) ?></div><?php endif ?>
                                        <?php if ($lot['brand_name'] ?? '') : ?><div class="admin-muted"><?= esc($lot['brand_name']) ?></div><?php endif ?>
                                        <?php if ($lot['catalog_no'] ?? '') : ?><div class="admin-muted"><?= esc($lot['catalog_no']) ?></div><?php endif ?>
                                        <?php if ($lot['grade'] ?? '') : ?><div class="admin-muted"><?= esc($lot['grade']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($itemLang('columns.dates')) ?>">
                                        <?php if ($lot['received_date'] ?? '') : ?><div><?= esc($itemLang('dateLabels.received')) ?>: <?= esc($lot['received_date']) ?></div><?php endif ?>
                                        <?php if ($lot['expiry_date'] ?? '') : ?>
                                            <div class="<?= (strtotime($lot['expiry_date']) !== false && strtotime($lot['expiry_date']) < time()) ? 'admin-error' : '' ?>">
                                                <?= esc($itemLang('dateLabels.expiry')) ?>: <?= esc($lot['expiry_date']) ?>
                                            </div>
                                        <?php endif ?>
                                        <?php if ($lot['manufacture_date'] ?? '') : ?><div class="admin-muted"><?= esc($itemLang('dateLabels.manufacture')) ?>: <?= esc($lot['manufacture_date']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($itemLang('columns.qty')) ?>">
                                        <strong><?= esc((string) ($lot['current_qty'] ?? '0')) ?> <?= esc($lot['unit_name'] ?? '') ?></strong>
                                        <?php if (($lot['initial_qty'] ?? '') && $lot['initial_qty'] != $lot['current_qty']) : ?>
                                            <div class="admin-muted"><?= esc($itemLang('fields.initialQty')) ?>: <?= esc((string) $lot['initial_qty']) ?> <?= esc($lot['initial_unit_name'] ?? '') ?></div>
                                        <?php endif ?>
                                        <?php if ($lot['concentration_value'] ?? '') : ?>
                                            <div class="admin-muted"><?= esc((string) $lot['concentration_value']) ?> <?= esc($lot['concentration_unit_id'] ?? '') ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($itemLang('columns.location')) ?>"><?= esc($lot['storage_location_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($itemLang('columns.ownership')) ?>"><span class="admin-badge neutral"><?= esc($itemLang('ownership.' . strtolower((string) ($lot['ownership_status'] ?? 'owned')))) ?></span></td>
                                    <?php if (lab_core_can('ims.stock.adjust')) : ?>
                                        <td data-label="<?= esc($itemLang('columns.action')) ?>">
                                            <div class="ims-action-group">
                                                <a class="ims-action-btn" title="<?= esc($itemLang('actions.editLot')) ?>" href="<?= esc(site_url('ims/items/' . ($item['id'] ?? 0) . '/lots/' . $lot['id'] . '/edit')) ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                <button type="button" class="ims-action-btn danger js-delete-lot" title="<?= esc($itemLang('actions.deleteLot')) ?>"
                                                    data-lot-id="<?= esc((string) $lot['id']) ?>"
                                                    data-lot-label="<?= esc($lot['internal_lot_no'] ?: ($lot['lot_no'] ?: '#' . $lot['id'])) ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </div>
                                        </td>
                                    <?php endif ?>
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
                <span class="admin-muted"><?= esc($itemLang('detail.attachmentCount', [count($attachments)])) ?></span>
                <?php if (lab_core_can('ims.items.update')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#itemAttachmentUploadModal">
                        <i class="fa-solid fa-upload"></i> <?= esc($itemLang('detail.uploadDocument')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($itemLang('columns.file')) ?></th>
                            <th><?= esc($itemLang('columns.type')) ?></th>
                            <th><?= esc($itemLang('fields.remarks')) ?></th>
                            <th><?= esc($itemLang('columns.uploaded')) ?></th>
                            <th><?= esc($itemLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($attachments === []) : ?>
                            <tr class="main-row">
                                <td colspan="5" class="admin-muted"><?= esc($itemLang('detail.emptyAttachments')) ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($attachments as $att) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($itemLang('columns.file')) ?>"><strong><?= esc($att['file_name']) ?></strong></td>
                                    <td data-label="<?= esc($itemLang('columns.type')) ?>"><span class="admin-badge neutral"><?= esc(strtoupper($att['attachment_type'] ?? 'document')) ?></span></td>
                                    <td data-label="<?= esc($itemLang('fields.remarks')) ?>" class="admin-muted"><?= esc($att['remarks'] ?? '-') ?></td>
                                    <td data-label="<?= esc($itemLang('columns.uploaded')) ?>" class="admin-muted"><?= esc($att['uploaded_at'] ?? '-') ?></td>
                                    <td data-label="<?= esc($itemLang('columns.action')) ?>">
                                        <a class="ims-action-btn" title="<?= esc($itemLang('actions.viewFile')) ?>" href="<?= esc(site_url('ims/items/' . $item['id'] . '/attachments/' . $att['id'])) ?>" target="_blank" rel="noopener">
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


<!-- ── Delete Lot Modal ── -->
<div class="modal fade" id="deleteItemStockLotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteItemStockLotForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($itemLang('delete.stockLotTitle')) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($itemLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteItemStockLotMessage"><?= esc($itemLang('delete.stockLotFallback')) ?></p>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($itemLang('common.cancel')) ?></button><button type="submit" class="btn btn-danger"><?= esc($itemLang('common.delete')) ?></button></div>
            </form>
        </div>
    </div>
</div>

<!-- ── Attachment Upload Modal ── -->
<div class="modal fade" id="itemAttachmentUploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/items/' . ($item['id'] ?? 0) . '/attachments')) ?>" id="itemAttachmentUploadForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($itemLang('detail.uploadDocument')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($itemLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label for="att-type"><?= esc($itemLang('fields.attachmentType')) ?></label>
                            <select id="att-type" name="attachment_type" class="filter-select">
                                <?php foreach (['coa' => 'COA', 'sds' => 'SDS', 'photo' => $itemLang('attachmentTypes.photo'), 'document' => $itemLang('attachmentTypes.document'), 'other' => $itemLang('attachmentTypes.other')] as $v => $l) : ?>
                                    <option value="<?= esc($v) ?>" <?= old('attachment_type', 'document') === $v ? 'selected' : '' ?>><?= esc($l) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="att-file"><?= esc($itemLang('columns.file')) ?></label>
                            <input id="att-file" class="app-file-input" name="attachment_file" type="file">
                            <?php if (isset($validation['attachment_file'])) : ?><small class="admin-error"><?= esc($validation['attachment_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($itemLang('help.maxFileSize')) ?></small>
                        </div>
                        <div class="admin-form-group">
                            <label for="att-remarks"><?= esc($itemLang('fields.remarks')) ?></label>
                            <textarea id="att-remarks" name="remarks" rows="3"><?= old('remarks') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($itemLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($itemLang('detail.uploadFile')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    (() => {
        const itemId = <?= (int) ($item['id'] ?? 0) ?>;
        const baseLotsUrl = '<?= rtrim(site_url('ims/items'), '/') ?>/' + itemId + '/lots';
        const deleteLotTemplate = <?= json_encode($itemLang('delete.stockLotMessage', ['__LOT__'])) ?>;

        // ── Delete lot ──
        document.querySelectorAll('.js-delete-lot').forEach((btn) => {
            btn.addEventListener('click', () => {
                document.getElementById('deleteItemStockLotForm').action = baseLotsUrl + '/' + btn.dataset.lotId + '/delete';
                document.getElementById('deleteItemStockLotMessage').textContent = deleteLotTemplate.replace('__LOT__', btn.dataset.lotLabel || '#' + btn.dataset.lotId);
                bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteItemStockLotModal')).show();
            });
        });

        // ── Attachment modal restore ──
        <?php if (($modalState['modal'] ?? null) === 'item-attachment-modal') : ?>
            new bootstrap.Modal(document.getElementById('itemAttachmentUploadModal')).show();
            bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-attachments-btn')).show();
        <?php endif ?>
    })();
</script>
<?= $this->endSection() ?>
