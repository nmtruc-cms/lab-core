<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation  = $validation  ?? []; ?>
<?php $fm          = $fm          ?? []; ?>
<?php $components  = $components  ?? []; ?>
<?php $attachments = $attachments ?? []; ?>
<?php $allStockLots    = $allStockLots    ?? []; ?>
<?php $allFormulations = $allFormulations ?? []; ?>
<?php $units           = $units           ?? []; ?>
<?php $modalState = is_array($modalState ?? null) ? $modalState : []; ?>
<?php
$fmLang = static fn (string $key, array $args = []): string => lang('IMS.formulations.' . $key, $args);
$attachmentTypeLabels = [
    'certificate' => $fmLang('attachmentTypes.certificate'),
    'protocol' => $fmLang('attachmentTypes.protocol'),
    'report' => $fmLang('attachmentTypes.report'),
    'photo' => $fmLang('attachmentTypes.photo'),
    'document' => $fmLang('attachmentTypes.document'),
    'other' => $fmLang('attachmentTypes.other'),
];
$fmId      = (int) ($fm['id'] ?? 0);
$fmLot     = $fm['formulation_lot'] ?? ('#' . $fmId);
$statusCfg = [
    'active'   => ['label' => $fmLang('status.active'),   'class' => 'neutral'],
    'approved' => ['label' => $fmLang('status.approved'), 'class' => 'success'],
    'depleted' => ['label' => $fmLang('status.depleted'), 'class' => ''],
    'expired'  => ['label' => $fmLang('status.expired'),  'class' => 'danger'],
];
$sc = $statusCfg[$fm['status'] ?? 'active'] ?? $statusCfg['active'];
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar" style="flex-wrap:wrap;gap:12px;">
        <div class="admin-card-head" style="padding-left:0;flex:1;min-width:0;">
            <h3 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tool-id-badge"><?= esc($fmLot) ?></span>
                <?= esc($fm['name'] ?? '') ?>
                <span class="admin-muted" style="font-size:.85em;font-weight:400;"><?= esc($fm['formulation_type'] ?? '') ?></span>
                <span class="admin-badge <?= esc($sc['class']) ?>"><?= esc($sc['label']) ?></span>
            </h3>
            <span>
                <?php if (($fm['qty'] ?? '') !== '' || ($fm['unit_name'] ?? '')) : ?>
                    <?= esc((string) ($fm['qty'] ?? '0')) ?> <?= esc($fm['unit_name'] ?? '') ?>
                <?php endif ?>
                <?php if (! empty($fm['concentration'])) : ?>
                    &middot; <?= esc((string) $fm['concentration']) ?> <?= esc($fm['concentration_unit_name'] ?? '') ?>
                <?php endif ?>
                <?php if (! empty($fm['storage_location_name'])) : ?>
                    &middot; <?= esc($fm['storage_location_name']) ?>
                <?php endif ?>
                <?php if (! empty($fm['prepared_date'])) : ?>
                    &middot; <?= esc($fmLang('fields.preparedDate')) ?>: <?= esc($fm['prepared_date']) ?>
                    <?php if (! empty($fm['prepared_by_name'])) : ?><?= esc($fmLang('columns.by')) ?> <?= esc($fm['prepared_by_name']) ?><?php endif ?>
                <?php endif ?>
                <?php if (! empty($fm['expired_date'])) : ?>
                    &middot; <?= esc($fmLang('columns.expiry')) ?>: <?= esc($fm['expired_date']) ?>
                <?php endif ?>
            </span>
        </div>
        <div class="ims-toolbar-actions">
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/formulations/' . $fmId . '/label')) ?>" target="_blank" rel="noopener">
                <i class="fa-solid fa-tag"></i> <?= esc($fmLang('actions.printLabel')) ?>
            </a>
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/formulations')) ?>">
                <i class="fa-solid fa-arrow-left"></i> <?= esc($fmLang('common.backToFormulations')) ?>
            </a>
        </div>
    </div>

    <div style="padding: 0 20px 0 20px; border-bottom: 1px solid var(--border-color);">
        <ul class="nav nav-tabs border-0" id="fmDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-components-btn" data-bs-toggle="tab" data-bs-target="#tabComponents" type="button" role="tab">
                    <i class="fa-solid fa-list-check"></i> <?= esc($fmLang('tabs.components')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($components) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-attachments-btn" data-bs-toggle="tab" data-bs-target="#tabAttachments" type="button" role="tab">
                    <i class="fa-solid fa-paperclip"></i> <?= esc($fmLang('tabs.attachments')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($attachments) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- ── Components Tab ── -->
        <div class="tab-pane fade show active" id="tabComponents" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($fmLang('detail.componentCount', [count($components)])) ?></span>
                <?php if (lab_core_can('ims.stock.adjust')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#formulationComponentModal">
                        <i class="fa-solid fa-list-check"></i> <?= esc($fmLang('actions.manageComponents')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($fmLang('columns.source')) ?></th>
                            <th><?= esc($fmLang('columns.usedQty')) ?></th>
                            <th><?= esc($fmLang('fields.unit')) ?></th>
                            <th><?= esc($fmLang('columns.concentration')) ?></th>
                            <th><?= esc($fmLang('columns.concUnit')) ?></th>
                            <th><?= esc($fmLang('columns.solvent')) ?></th>
                            <th><?= esc($fmLang('columns.markup')) ?></th>
                            <th><?= esc($fmLang('columns.remark')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($components === []) : ?>
                            <tr class="main-row"><td colspan="8" class="admin-muted"><?= esc($fmLang('detail.noComponents')) ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($components as $comp) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($fmLang('columns.source')) ?>">
                                        <?php if (($comp['source_type'] ?? '') === 'stock_lot') : ?>
                                            <span class="admin-badge neutral" style="font-size:.7rem;padding:1px 6px;">Lot</span>
                                        <?php else : ?>
                                            <span class="admin-badge" style="font-size:.7rem;padding:1px 6px;">FM</span>
                                        <?php endif ?>
                                        &nbsp;<strong><?= esc($comp['source_label'] ?? '') ?></strong>
                                        <?php if (! empty($comp['source_sub'])) : ?>
                                            <div class="admin-muted"><?= esc($comp['source_sub']) ?></div>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($fmLang('columns.usedQty')) ?>"><strong><?= esc((string) ($comp['used_qty'] ?? '')) ?></strong></td>
                                    <td data-label="<?= esc($fmLang('fields.unit')) ?>" class="admin-muted"><?= esc($comp['used_unit_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($fmLang('columns.concentration')) ?>">
                                        <?php if (! empty($comp['concentration'])) : ?>
                                            <?= esc((string) $comp['concentration']) ?>
                                        <?php else : ?>
                                            <span class="admin-muted">-</span>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($fmLang('columns.concUnit')) ?>" class="admin-muted"><?= esc($comp['concentration_unit_name'] ?? '-') ?></td>
                                    <td data-label="<?= esc($fmLang('columns.solvent')) ?>">
                                        <?php if (! empty($comp['is_solvent'])) : ?>
                                            <span class="admin-badge neutral"><?= esc($fmLang('common.yes')) ?></span>
                                        <?php else : ?>
                                            <span class="admin-muted">-</span>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($fmLang('columns.markup')) ?>">
                                        <?php if (! empty($comp['is_markup'])) : ?>
                                            <span class="admin-badge warning"><?= esc($fmLang('common.yes')) ?></span>
                                        <?php else : ?>
                                            <span class="admin-muted">-</span>
                                        <?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($fmLang('columns.remark')) ?>" class="admin-muted"><?= esc($comp['remark'] ?? '-') ?></td>
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
                <span class="admin-muted"><?= esc($fmLang('detail.attachmentCount', [count($attachments)])) ?></span>
                <?php if (lab_core_can('ims.stock.adjust')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#formulationAttachmentModal">
                        <i class="fa-solid fa-upload"></i> <?= esc($fmLang('actions.uploadDocument')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($fmLang('columns.file')) ?></th>
                            <th><?= esc($fmLang('columns.type')) ?></th>
                            <th><?= esc($fmLang('columns.remarks')) ?></th>
                            <th><?= esc($fmLang('columns.uploaded')) ?></th>
                            <th><?= esc($fmLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($attachments === []) : ?>
                            <tr class="main-row"><td colspan="5" class="admin-muted"><?= esc($fmLang('detail.noAttachments')) ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($attachments as $att) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($fmLang('columns.file')) ?>"><strong><?= esc($att['file_name'] ?? '') ?></strong></td>
                                    <td data-label="<?= esc($fmLang('columns.type')) ?>"><span class="admin-badge neutral"><?= esc($attachmentTypeLabels[$att['attachment_type'] ?? 'document'] ?? strtoupper($att['attachment_type'] ?? 'document')) ?></span></td>
                                    <td data-label="<?= esc($fmLang('columns.remarks')) ?>" class="admin-muted"><?= esc($att['remarks'] ?? '-') ?></td>
                                    <td data-label="<?= esc($fmLang('columns.uploaded')) ?>" class="admin-muted"><?= esc($att['uploaded_at'] ?? '-') ?></td>
                                    <td data-label="<?= esc($fmLang('columns.action')) ?>">
                                        <a class="ims-action-btn" title="<?= esc($fmLang('actions.viewFile')) ?>" href="<?= esc(site_url('ims/formulations/' . $fm['id'] . '/attachments/' . $att['id'])) ?>" target="_blank" rel="noopener">
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

<!-- ── Manage Components Modal ─────────────────────────────────────────── -->
<div class="modal fade" id="formulationComponentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc($fmLang('actions.manageComponents')) ?> - <span id="compModalCaption"><?= esc($fmLot) ?></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($fmLang('common.close')) ?>"></button>
            </div>
            <div class="modal-body">
                <!-- Source picker tabs -->
                <div class="fm-tabs" style="display:flex;gap:6px;margin-bottom:10px;">
                    <button type="button" class="admin-btn secondary fm-tab-btn active" data-tab="tab-stock-lot"><?= esc($fmLang('detail.fromStockLot')) ?></button>
                    <button type="button" class="admin-btn secondary fm-tab-btn" data-tab="tab-formulation"><?= esc($fmLang('detail.fromFormulation')) ?></button>
                </div>

                <!-- Stock Lot picker -->
                <div id="tab-stock-lot" class="fm-tab-pane">
                    <div class="admin-form-group" style="margin-bottom:8px;">
                        <input type="search" id="stockLotPickerSearch" placeholder="<?= esc($fmLang('detail.stockLotSearch')) ?>">
                    </div>
                    <div style="max-height:200px;overflow-y:auto;margin-bottom:4px;">
                        <table class="results-table app-responsive-table" style="font-size:.84rem;">
                            <thead><tr><th><?= esc($fmLang('columns.lot')) ?></th><th><?= esc($fmLang('columns.item')) ?></th><th><?= esc($fmLang('columns.qty')) ?></th><th><?= esc($fmLang('columns.location')) ?></th><th></th></tr></thead>
                            <tbody id="stockLotPickerTbody"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Formulation picker -->
                <div id="tab-formulation" class="fm-tab-pane" style="display:none;">
                    <div class="admin-form-group" style="margin-bottom:8px;">
                        <input type="search" id="fmPickerSearch" placeholder="<?= esc($fmLang('detail.formulationSearch')) ?>">
                    </div>
                    <div style="max-height:200px;overflow-y:auto;margin-bottom:4px;">
                        <table class="results-table app-responsive-table" style="font-size:.84rem;">
                            <thead><tr><th><?= esc($fmLang('columns.fmLot')) ?></th><th><?= esc($fmLang('columns.nameType')) ?></th><th><?= esc($fmLang('columns.qty')) ?></th><th><?= esc($fmLang('columns.status')) ?></th><th></th></tr></thead>
                            <tbody id="fmPickerTbody"></tbody>
                        </table>
                    </div>
                </div>

                <hr style="margin:14px 0;">

                <!-- Component lines -->
                <h6 style="margin-bottom:8px;"><?= esc($fmLang('detail.componentLines')) ?></h6>
                <div style="overflow-x:auto;">
                    <table class="results-table" id="componentLinesTable" style="font-size:.83rem;min-width:900px;">
                        <thead>
                            <tr>
                                <th><?= esc($fmLang('columns.source')) ?></th>
                                <th style="min-width:90px;"><?= esc($fmLang('columns.usedQty')) ?></th>
                                <th style="min-width:110px;"><?= esc($fmLang('fields.unit')) ?></th>
                                <th style="min-width:100px;"><?= esc($fmLang('columns.concentration')) ?></th>
                                <th style="min-width:110px;"><?= esc($fmLang('columns.concUnit')) ?></th>
                                <th><?= esc($fmLang('columns.solvent')) ?></th>
                                <th><?= esc($fmLang('columns.markup')) ?></th>
                                <th style="min-width:130px;"><?= esc($fmLang('columns.remark')) ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="componentLinesTbody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($fmLang('common.close')) ?></button>
                <button type="button" class="admin-btn primary" onclick="saveAllComponents()"><?= esc($fmLang('common.save')) ?></button>
            </div>
        </div>
    </div>
</div>

<!-- ── Upload Attachment Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="formulationAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/formulations/' . $fmId . '/attachments')) ?>" id="formulationAttachmentForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($fmLang('actions.uploadDocument')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($fmLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label for="f-attach-type"><?= esc($fmLang('fields.attachmentType')) ?></label>
                            <select id="f-attach-type" name="attachment_type" class="filter-select">
                                <?php foreach ($attachmentTypeLabels as $val => $lbl) : ?>
                                    <option value="<?= esc($val) ?>" <?= old('attachment_type', 'document') === $val ? 'selected' : '' ?>><?= esc($lbl) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="f-attach-file"><?= esc($fmLang('columns.file')) ?> <span class="admin-error">*</span></label>
                            <input id="f-attach-file" class="app-file-input" name="attachment_file" type="file">
                            <?php if (isset($validation['attachment_file'])) : ?><small class="admin-error"><?= esc($validation['attachment_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($fmLang('form.maxFileSize')) ?></small>
                        </div>
                        <div class="admin-form-group">
                            <label for="f-attach-remarks"><?= esc($fmLang('columns.remarks')) ?></label>
                            <textarea id="f-attach-remarks" name="remarks" rows="3"><?= esc((string) old('remarks')) ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($fmLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($fmLang('actions.upload')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden forms for component operations -->
<form id="addComponentForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="source_type" id="addCompSourceType">
    <input type="hidden" name="source_lot_id" id="addCompLotId">
    <input type="hidden" name="source_formulation_id" id="addCompFmId">
</form>
<form id="saveAllComponentsForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
</form>
<form id="deleteComponentForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
</form>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<style>
#componentLinesTable td { vertical-align: middle; }
#componentLinesTable input[type="number"],
#componentLinesTable input[type="text"] {
    border: 1.5px solid var(--border-color);
    border-radius: 9px;
    padding: 6px 10px;
    font-size: .83rem;
    color: #1e293b;
    background: #fff;
    outline: none;
    width: 100%;
    transition: border-color .15s, box-shadow .15s;
}
#componentLinesTable input[type="number"]:focus,
#componentLinesTable input[type="text"]:focus {
    border-color: var(--bs-navy-light);
    box-shadow: 0 0 0 3px rgba(26,74,122,.08);
}
#componentLinesTable .filter-select { min-width: unset; width: 100%; font-size: .83rem; padding: 6px 28px 6px 10px; }
#componentLinesTable input[type="checkbox"] { width: 16px; height: 16px; cursor: pointer; accent-color: var(--bs-navy); }
.fm-tab-btn.active { background: var(--bs-navy); color: #fff; border-color: var(--bs-navy); }
#stockLotPickerTbody .add-comp-btn,
#fmPickerTbody .add-comp-btn { padding: 4px 12px; font-size: .8rem; }
</style>
<script>
const allStockLots    = <?= json_encode(array_map(static fn ($l) => [
    'id'                    => $l['id'],
    'internal_lot_no'       => $l['internal_lot_no'] ?? '',
    'lot_no'                => $l['lot_no'] ?? '',
    'item_code'             => $l['item_code'] ?? '',
    'item_name'             => $l['item_name'] ?? '',
    'current_qty'           => $l['current_qty'] ?? '0',
    'unit_name'             => $l['unit_name'] ?? '',
    'storage_location_name' => $l['storage_location_name'] ?? '',
], $allStockLots), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const allFormulations = <?= json_encode(array_map(static fn ($f) => [
    'id'                      => $f['id'],
    'formulation_lot'         => $f['formulation_lot'] ?? '',
    'name'                    => $f['name'] ?? '',
    'formulation_type'        => $f['formulation_type'] ?? '',
    'qty'                     => $f['qty'] ?? '0',
    'unit_name'               => $f['unit_name'] ?? '',
    'status'                  => $f['status'] ?? 'active',
    'concentration'           => $f['concentration'] ?? '',
    'concentration_unit_name' => $f['concentration_unit_name'] ?? '',
], $allFormulations), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

const allUnits = <?= json_encode(array_map(static fn ($u) => ['id' => $u['id'], 'unit_name' => $u['unit_name']], $units), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const baseUrl  = '<?= rtrim(site_url(), '/') ?>';
window.currentFId = <?= (int) $fm['id'] ?>;
const fmText = <?= json_encode([
    'none' => $fmLang('common.none'),
    'add' => $fmLang('common.add'),
    'delete' => $fmLang('common.delete'),
    'noLotsFound' => $fmLang('detail.noLotsFound'),
    'noFormulationsFound' => $fmLang('detail.noFormulationsFound'),
    'noComponentsYet' => $fmLang('detail.noComponentsYet'),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

(() => {
    const escHtml = (v) => String(v ?? '').replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;').replaceAll("'",'&#039;');

    const unitsOpts = (selId) => '<option value="">' + escHtml(fmText.none) + '</option>'
        + allUnits.map(u => '<option value="' + u.id + '"' + (String(u.id) === String(selId) ? ' selected' : '') + '>' + escHtml(u.unit_name) + '</option>').join('');

    // ── Stock Lot picker ────────────────────────────────────────────────
    const renderStockLots = (q) => {
        const filtered = q === '' ? allStockLots : allStockLots.filter(l =>
            [l.item_name, l.item_code, l.internal_lot_no, l.lot_no].some(v => v.toLowerCase().includes(q.toLowerCase()))
        );
        document.getElementById('stockLotPickerTbody').innerHTML = filtered.length === 0
            ? '<tr><td colspan="5" class="admin-muted">' + escHtml(fmText.noLotsFound) + '</td></tr>'
            : filtered.map(l => '<tr>'
                + '<td><strong>' + escHtml(l.internal_lot_no || l.lot_no || '-') + '</strong></td>'
                + '<td>' + escHtml(l.item_name) + '<div class="admin-muted">' + escHtml(l.item_code) + '</div></td>'
                + '<td>' + escHtml(l.current_qty) + ' ' + escHtml(l.unit_name) + '</td>'
                + '<td>' + escHtml(l.storage_location_name || '-') + '</td>'
                + '<td><button type="button" class="admin-btn primary add-comp-btn" onclick="addComponent(\'stock_lot\',' + l.id + ')">' + escHtml(fmText.add) + '</button></td>'
                + '</tr>').join('');
    };

    // ── Formulation picker ──────────────────────────────────────────────
    const renderFmPicker = (q) => {
        const filtered = allFormulations.filter(f =>
            String(f.id) !== String(window.currentFId) &&
            (q === '' || [f.formulation_lot, f.name, f.formulation_type].some(v => v.toLowerCase().includes(q.toLowerCase())))
        );
        document.getElementById('fmPickerTbody').innerHTML = filtered.length === 0
            ? '<tr><td colspan="5" class="admin-muted">' + escHtml(fmText.noFormulationsFound) + '</td></tr>'
            : filtered.map(f => {
                const concTxt = f.concentration ? escHtml(f.concentration) + ' ' + escHtml(f.concentration_unit_name) : '—';
                return '<tr>'
                    + '<td><strong>' + escHtml(f.formulation_lot) + '</strong></td>'
                    + '<td>' + escHtml(f.name) + '<div class="admin-muted">' + escHtml(f.formulation_type) + '</div></td>'
                    + '<td>' + escHtml(f.qty) + ' ' + escHtml(f.unit_name) + '<div class="admin-muted">' + concTxt + '</div></td>'
                    + '<td><span class="admin-badge ' + (f.status === 'approved' ? 'success' : f.status === 'expired' ? 'danger' : 'neutral') + '">' + escHtml(f.status) + '</span></td>'
                    + '<td><button type="button" class="admin-btn primary add-comp-btn" onclick="addComponent(\'formulation\',' + f.id + ')">' + escHtml(fmText.add) + '</button></td>'
                    + '</tr>';
            }).join('');
    };

    // ── Component lines ─────────────────────────────────────────────────
    const renderComponentLines = (comps) => {
        const tbody = document.getElementById('componentLinesTbody');
        if (comps.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="admin-muted">' + escHtml(fmText.noComponentsYet) + '</td></tr>';
            return;
        }
        tbody.innerHTML = comps.map(c => {
            const badge = c.source_type === 'stock_lot'
                ? '<span class="admin-badge neutral" style="font-size:.7rem;padding:1px 6px;">Lot</span>'
                : '<span class="admin-badge" style="font-size:.7rem;padding:1px 6px;">FM</span>';
            return '<tr data-comp-id="' + c.id + '">'
                + '<td>' + badge + '&nbsp;<strong>' + escHtml(c.source_label) + '</strong><div class="admin-muted">' + escHtml(c.source_sub) + '</div></td>'
                + '<td><input type="number" step="0.0001" data-field="used_qty" value="' + escHtml(c.used_qty) + '"></td>'
                + '<td><select class="filter-select" data-field="used_unit_id">' + unitsOpts(c.used_unit_id) + '</select></td>'
                + '<td><input type="number" step="0.000001" data-field="concentration" value="' + escHtml(c.concentration ?? '') + '"></td>'
                + '<td><select class="filter-select" data-field="concentration_unit_id">' + unitsOpts(c.concentration_unit_id) + '</select></td>'
                + '<td style="text-align:center;"><input type="checkbox" data-field="is_solvent"' + (Number(c.is_solvent) ? ' checked' : '') + '></td>'
                + '<td style="text-align:center;"><input type="checkbox" data-field="is_markup"' + (Number(c.is_markup) ? ' checked' : '') + '></td>'
                + '<td><input type="text" data-field="remark" value="' + escHtml(c.remark ?? '') + '"></td>'
                + '<td><button type="button" class="ims-action-btn danger" title="' + escHtml(fmText.delete) + '" onclick="deleteComponent(' + c.id + ')"><i class="fa-solid fa-trash-can"></i></button></td>'
                + '</tr>';
        }).join('');
    };

    // ── Component modal open ────────────────────────────────────────────
    const compModal = document.getElementById('formulationComponentModal');
    compModal?.addEventListener('show.bs.modal', () => {
        document.getElementById('compModalCaption').textContent = <?= json_encode($fmLot) ?>;
        document.getElementById('stockLotPickerSearch').value = '';
        document.getElementById('fmPickerSearch').value = '';
        renderStockLots('');
        renderFmPicker('');

        const comps = <?= json_encode(array_values($components), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES) ?>;
        renderComponentLines(comps);

        // reset tabs
        document.querySelectorAll('.fm-tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.fm-tab-pane').forEach(p => p.style.display = 'none');
        document.querySelector('.fm-tab-btn[data-tab="tab-stock-lot"]').classList.add('active');
        document.getElementById('tab-stock-lot').style.display = '';
    });

    // Tab switching
    document.querySelectorAll('.fm-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.fm-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.fm-tab-pane').forEach(p => p.style.display = 'none');
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab).style.display = '';
        });
    });

    // Search events
    document.getElementById('stockLotPickerSearch')?.addEventListener('input', (e) => renderStockLots(e.target.value));
    document.getElementById('fmPickerSearch')?.addEventListener('input', (e) => renderFmPicker(e.target.value));

    // ── Attachment modal: no-op (action is hardcoded in the form) ───────
    document.getElementById('formulationAttachmentModal')?.addEventListener('show.bs.modal', () => {
        // nothing to do — form action is set statically in PHP
    });

    // ── Modal restore ───────────────────────────────────────────────────
    <?php if (($modalState['modal'] ?? null) === 'formulation-component-modal') : ?>
        new bootstrap.Modal(document.getElementById('formulationComponentModal')).show();
    <?php elseif (($modalState['modal'] ?? null) === 'formulation-attachment-modal') : ?>
        new bootstrap.Modal(document.getElementById('formulationAttachmentModal')).show();
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-attachments-btn')).show();
    <?php endif ?>
})();

function addComponent(sourceType, sourceId) {
    const form = document.getElementById('addComponentForm');
    form.action = baseUrl + '/ims/formulations/' + window.currentFId + '/components';
    document.getElementById('addCompSourceType').value = sourceType;
    document.getElementById('addCompLotId').value      = sourceType === 'stock_lot'   ? sourceId : '';
    document.getElementById('addCompFmId').value       = sourceType === 'formulation' ? sourceId : '';
    form.submit();
}

function saveAllComponents() {
    const form = document.getElementById('saveAllComponentsForm');
    form.action = baseUrl + '/ims/formulations/' + window.currentFId + '/components/batch';
    form.querySelectorAll('.comp-batch-input').forEach(el => el.remove());
    document.querySelectorAll('#componentLinesTbody tr[data-comp-id]').forEach((row, i) => {
        const id  = row.dataset.compId;
        const add = (name, val) => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'components[' + i + '][' + name + ']';
            inp.value = val; inp.className = 'comp-batch-input';
            form.appendChild(inp);
        };
        add('id', id);
        add('used_qty',              row.querySelector('[data-field="used_qty"]').value);
        add('used_unit_id',          row.querySelector('[data-field="used_unit_id"]').value);
        add('concentration',         row.querySelector('[data-field="concentration"]').value);
        add('concentration_unit_id', row.querySelector('[data-field="concentration_unit_id"]').value);
        add('is_solvent',            row.querySelector('[data-field="is_solvent"]').checked ? '1' : '0');
        add('is_markup',             row.querySelector('[data-field="is_markup"]').checked  ? '1' : '0');
        add('remark',                row.querySelector('[data-field="remark"]').value);
    });
    form.submit();
}

function deleteComponent(compId) {
    const form = document.getElementById('deleteComponentForm');
    form.action = baseUrl + '/ims/formulations/' + window.currentFId + '/components/' + compId + '/delete';
    form.submit();
}
</script>
<?= $this->endSection() ?>
