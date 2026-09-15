<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php
$request = $request ?? [];
$items = $items ?? [];
$attachments = $attachments ?? [];
$categories = $categories ?? [];
$brands = $brands ?? [];
$suppliers = $suppliers ?? [];
$units = $units ?? [];
$validation = $validation ?? [];
$modalState = $modalState ?? null;
$activeDetailTab = $activeDetailTab ?? 'items';
$isDraft = ($request['status'] ?? 'draft') === 'draft';
$requestLang = static function (string $key, array $args = [], ?string $fallback = null): string {
    $line = 'IMS.requests.' . $key;
    $text = lang($line, $args);

    return $text === $line ? ($fallback ?? $key) : $text;
};
$statusCfg = [
    'draft' => ['label' => $requestLang('status.draft'), 'class' => 'neutral'],
    'submitted' => ['label' => $requestLang('status.submitted'), 'class' => 'warning'],
    'approved' => ['label' => $requestLang('status.approved'), 'class' => 'success'],
    'rejected' => ['label' => $requestLang('status.rejected'), 'class' => 'danger'],
];
$status = $statusCfg[$request['status'] ?? 'draft'] ?? $statusCfg['draft'];
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar" style="flex-wrap:wrap;gap:12px;">
        <div class="admin-card-head" style="padding-left:0;flex:1;min-width:0;">
            <h3 style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <span class="tool-id-badge"><?= esc($request['request_number'] ?? ('REQ-' . (string) ($request['id'] ?? ''))) ?></span>
                <?= esc($request['request_name'] ?? $requestLang('fallback.request')) ?>
            </h3>
            <span>
                <span class="admin-badge <?= esc($status['class']) ?>"><?= esc($status['label']) ?></span>
                <?php if ($request['created_by_name'] ?? '') : ?> &middot; <?= esc($requestLang('detail.createdBy')) ?> <?= esc($request['created_by_name']) ?><?php endif ?>
                <?php if ($request['created_date'] ?? '') : ?> &middot; <?= esc($request['created_date']) ?><?php endif ?>
            </span>
            <?php if (! empty($request['remark'])) : ?><span><?= esc($request['remark']) ?></span><?php endif ?>
        </div>
        <div class="ims-toolbar-actions">
            <a class="admin-btn secondary" href="<?= esc(site_url('ims/requests')) ?>">
                <i class="fa-solid fa-arrow-left"></i> <?= esc($requestLang('actions.backToRequests')) ?>
            </a>
        </div>
    </div>

    <div style="padding: 0 20px; border-bottom: 1px solid var(--border-color);">
        <ul class="nav nav-tabs border-0" id="requestDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-request-items-btn" data-bs-toggle="tab" data-bs-target="#tabRequestItems" type="button" role="tab">
                    <i class="fa-solid fa-list-ul"></i> <?= esc($requestLang('detail.tabs.items')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($items) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-request-documents-btn" data-bs-toggle="tab" data-bs-target="#tabRequestDocuments" type="button" role="tab">
                    <i class="fa-solid fa-paperclip"></i> <?= esc($requestLang('detail.tabs.documents')) ?>
                    <span class="admin-badge neutral" style="margin-left:4px;"><?= count($attachments) ?></span>
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="tabRequestItems" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($requestLang('detail.itemCount', [count($items)])) ?></span>
                <?php if ($isDraft && lab_core_can('ims.requests.update')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#requestItemModal" data-mode="create">
                        <i class="fa-solid fa-plus"></i> <?= esc($requestLang('actions.addItem')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($requestLang('columns.item')) ?></th>
                            <th><?= esc($requestLang('fields.category')) ?></th>
                            <th>CAS / EC</th>
                            <th><?= esc($requestLang('columns.qty')) ?></th>
                            <th><?= esc($requestLang('fields.unitPrice')) ?></th>
                            <th><?= esc($requestLang('columns.brandSupplier')) ?></th>
                            <th><?= esc($requestLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($items === []) : ?>
                            <tr class="main-row"><td colspan="7" class="admin-muted"><?= esc($requestLang('detail.noItems')) ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($items as $item) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($requestLang('columns.item')) ?>">
                                        <strong><?= esc($item['item_name']) ?></strong>
                                        <?php if (! empty($item['alternate_name'])) : ?><div class="admin-muted"><?= esc($item['alternate_name']) ?></div><?php endif ?>
                                        <?php if (! empty($item['description'])) : ?><div class="admin-muted"><?= esc($item['description']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($requestLang('fields.category')) ?>"><?= esc($item['category_name'] ?? '-') ?></td>
                                    <td data-label="CAS / EC">
                                        <?= esc($item['cas_no'] ?? '-') ?>
                                        <?php if (! empty($item['ec_no'])) : ?><div class="admin-muted">EC: <?= esc($item['ec_no']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($requestLang('columns.qty')) ?>">
                                        <?= esc((string) ($item['qty'] ?? '-')) ?>
                                        <?php if (! empty($item['unit_name'])) : ?><span class="admin-muted"><?= esc($item['unit_name']) ?></span><?php endif ?>
                                        <?php if (! empty($item['pack_size'])) : ?><div class="admin-muted"><?= esc($item['pack_size']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($requestLang('fields.unitPrice')) ?>"><?= esc((string) ($item['unit_price'] ?? '-')) ?></td>
                                    <td data-label="<?= esc($requestLang('columns.brandSupplier')) ?>">
                                        <?= esc($item['brand_name'] ?? '-') ?>
                                        <?php if (! empty($item['supplier_name'])) : ?><div class="admin-muted"><?= esc($item['supplier_name']) ?></div><?php endif ?>
                                    </td>
                                    <td data-label="<?= esc($requestLang('columns.action')) ?>">
                                        <?php if ($isDraft && lab_core_can('ims.requests.update')) : ?>
                                            <div class="ims-action-group">
                                                    <button type="button" class="ims-action-btn" title="<?= esc($requestLang('actions.editItem')) ?>"
                                                        data-bs-toggle="modal" data-bs-target="#requestItemModal"
                                                        data-mode="edit"
                                                        data-action="<?= esc(site_url('ims/requests/' . $request['id'] . '/items/' . $item['id'])) ?>"
                                                        data-item='<?= esc(json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES)) ?>'>
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </button>
                                                    <button type="button" class="ims-action-btn danger js-request-delete" title="<?= esc($requestLang('actions.deleteItem')) ?>"
                                                        data-delete-action="<?= esc(site_url('ims/requests/' . $request['id'] . '/items/' . $item['id'] . '/delete')) ?>"
                                                        data-delete-label="<?= esc($item['item_name']) ?>">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                            </div>
                                        <?php else : ?>
                                            <span class="admin-muted"><?= esc($requestLang('common.noActions')) ?></span>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="tab-pane fade" id="tabRequestDocuments" role="tabpanel">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;">
                <span class="admin-muted"><?= esc($requestLang('detail.documentCount', [count($attachments)])) ?></span>
                <?php if (lab_core_can('ims.requests.update')) : ?>
                    <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#requestAttachmentModal">
                        <i class="fa-solid fa-upload"></i> <?= esc($requestLang('actions.uploadDocument')) ?>
                    </button>
                <?php endif ?>
            </div>
            <div style="overflow-x:auto;">
                <table class="results-table app-responsive-table">
                    <thead>
                        <tr>
                            <th><?= esc($requestLang('columns.type')) ?></th>
                            <th><?= esc($requestLang('columns.file')) ?></th>
                            <th><?= esc($requestLang('columns.remarks')) ?></th>
                            <th><?= esc($requestLang('columns.uploaded')) ?></th>
                            <th><?= esc($requestLang('columns.action')) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($attachments === []) : ?>
                            <tr class="main-row"><td colspan="5" class="admin-muted"><?= esc($requestLang('detail.noDocuments')) ?></td></tr>
                        <?php else : ?>
                            <?php foreach ($attachments as $attachment) : ?>
                                <tr class="main-row">
                                    <td data-label="<?= esc($requestLang('columns.type')) ?>"><span class="admin-badge neutral"><?= esc(strtoupper($attachment['attachment_type'] ?? 'document')) ?></span></td>
                                    <td data-label="<?= esc($requestLang('columns.file')) ?>"><a class="detail-link" href="<?= esc(base_url(trim((string) ($attachment['file_path'] ?? ''), '/'))) ?>" target="_blank" rel="noopener"><?= esc($attachment['file_name'] ?? '-') ?></a></td>
                                    <td data-label="<?= esc($requestLang('columns.remarks')) ?>" class="admin-muted"><?= esc($attachment['remarks'] ?? '-') ?></td>
                                    <td data-label="<?= esc($requestLang('columns.uploaded')) ?>" class="admin-muted"><?= esc($attachment['created_at'] ?? '-') ?></td>
                                    <td data-label="<?= esc($requestLang('columns.action')) ?>">
                                        <?php if (lab_core_can('ims.requests.update')) : ?>
                                            <button type="button" class="ims-action-btn danger js-request-delete" title="<?= esc($requestLang('actions.deleteDocument')) ?>"
                                                data-delete-action="<?= esc(site_url('ims/requests/' . $request['id'] . '/attachments/' . $attachment['id'] . '/delete')) ?>"
                                                data-delete-label="<?= esc($attachment['file_name'] ?? '-') ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php else : ?>
                                            <span class="admin-muted"><?= esc($requestLang('common.noActions')) ?></span>
                                        <?php endif ?>
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

<div class="modal fade" id="requestItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/requests/' . ($request['id'] ?? 0) . '/items')) ?>" id="requestItemForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('actions.addItem')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group full">
                            <label><?= esc($requestLang('fields.itemName')) ?> <span class="required">*</span></label>
                            <input type="text" name="item_name" id="ri-name" required>
                        </div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.alternateName')) ?></label><input type="text" name="alternate_name" id="ri-alt-name"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.category')) ?></label><select name="category_id" id="ri-category" class="filter-select"><option value=""><?= esc($requestLang('placeholders.selectCategory')) ?></option><?php foreach ($categories as $cat) : ?><option value="<?= esc((string) $cat['id']) ?>"><?= esc($cat['category_name']) ?></option><?php endforeach ?></select></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.casNo')) ?></label><input type="text" name="cas_no" id="ri-cas"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.ecNo')) ?></label><input type="text" name="ec_no" id="ri-ec"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.catalogNo')) ?></label><input type="text" name="catalog_no" id="ri-catalog"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.grade')) ?></label><input type="text" name="grade" id="ri-grade"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('columns.qty')) ?></label><input type="number" step="0.0001" name="qty" id="ri-qty"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.defaultUnit')) ?></label><select name="default_unit_id" id="ri-unit" class="filter-select"><option value=""><?= esc($requestLang('placeholders.selectUnit')) ?></option><?php foreach ($units as $unit) : ?><option value="<?= esc((string) $unit['id']) ?>"><?= esc($unit['unit_name']) ?></option><?php endforeach ?></select></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.packSize')) ?></label><input type="text" name="pack_size" id="ri-pack-size"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.unitPrice')) ?></label><input type="number" step="0.0001" name="unit_price" id="ri-unit-price"></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.suggestedBrand')) ?></label><select name="suggested_brand_id" id="ri-brand" class="filter-select"><option value=""><?= esc($requestLang('placeholders.noBrand')) ?></option><?php foreach ($brands as $brand) : ?><option value="<?= esc((string) $brand['id']) ?>"><?= esc($brand['brand_name']) ?></option><?php endforeach ?></select></div>
                        <div class="admin-form-group"><label><?= esc($requestLang('fields.suggestedSupplier')) ?></label><select name="suggested_supplier_id" id="ri-supplier" class="filter-select"><option value=""><?= esc($requestLang('placeholders.noSupplier')) ?></option><?php foreach ($suppliers as $supplier) : ?><option value="<?= esc((string) $supplier['id']) ?>"><?= esc($supplier['supplier_name']) ?></option><?php endforeach ?></select></div>
                        <div class="admin-form-group full"><label><?= esc($requestLang('fields.description')) ?></label><textarea name="description" id="ri-description" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary" id="requestItemSubmitBtn"><?= esc($requestLang('actions.addItem')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="requestAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= esc(site_url('ims/requests/' . ($request['id'] ?? 0) . '/attachments')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header"><h5 class="modal-title"><?= esc($requestLang('actions.uploadDocument')) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="admin-form-grid single">
                        <div class="admin-form-group">
                            <label><?= esc($requestLang('fields.attachmentType')) ?></label>
                            <select name="attachment_type" class="filter-select">
                                <?php foreach (['document', 'spec', 'quote', 'photo', 'other'] as $value) : ?>
                                    <?php $label = $requestLang('attachmentTypes.' . $value); ?>
                                    <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label><?= esc($requestLang('columns.file')) ?></label>
                            <input class="app-file-input" name="attachment_file" type="file">
                            <?php if (isset($validation['attachment_file'])) : ?><small class="admin-error"><?= esc($validation['attachment_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($requestLang('help.maxFileSize')) ?></small>
                        </div>
                        <div class="admin-form-group"><label><?= esc($requestLang('columns.remarks')) ?></label><textarea name="remarks" rows="3"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button><button type="submit" class="admin-btn primary"><?= esc($requestLang('actions.uploadFile')) ?></button></div>
            </form>
        </div>
    </div>
</div>

<form id="requestDeleteForm" method="post" action="" style="display:none;"><?= csrf_field() ?></form>

<script>
(() => {
    const activeTab = <?= json_encode((string) $activeDetailTab) ?>;
    const requestText = <?= json_encode([
        'addItem' => $requestLang('actions.addItem'),
        'editItem' => $requestLang('actions.editItem'),
        'saveChanges' => $requestLang('actions.saveChanges'),
        'deleteFallback' => $requestLang('delete.recordFallback'),
        'deleteMessage' => $requestLang('delete.inlineMessage'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const tabs = { items: 'tab-request-items-btn', documents: 'tab-request-documents-btn' };
    const activeButton = document.getElementById(tabs[activeTab] || tabs.items);
    if (activeButton) bootstrap.Tab.getOrCreateInstance(activeButton).show();

    const itemModal = document.getElementById('requestItemModal');
    itemModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = document.getElementById('requestItemForm');
        const title = itemModal.querySelector('.modal-title');
        const submit = document.getElementById('requestItemSubmitBtn');

        form.reset();
        form.action = '<?= esc(site_url('ims/requests/' . ($request['id'] ?? 0) . '/items')) ?>';
        title.textContent = requestText.addItem;
        submit.textContent = requestText.addItem;

        if (!trigger || trigger.dataset.mode !== 'edit') return;

        const item = JSON.parse(trigger.dataset.item || '{}');
        form.action = trigger.dataset.action || form.action;
        title.textContent = requestText.editItem;
        submit.textContent = requestText.saveChanges;
        document.getElementById('ri-name').value = item.item_name || '';
        document.getElementById('ri-alt-name').value = item.alternate_name || '';
        document.getElementById('ri-category').value = item.category_id || '';
        document.getElementById('ri-cas').value = item.cas_no || '';
        document.getElementById('ri-ec').value = item.ec_no || '';
        document.getElementById('ri-catalog').value = item.catalog_no || '';
        document.getElementById('ri-grade').value = item.grade || '';
        document.getElementById('ri-qty').value = item.qty || '';
        document.getElementById('ri-unit').value = item.default_unit_id || '';
        document.getElementById('ri-pack-size').value = item.pack_size || '';
        document.getElementById('ri-unit-price').value = item.unit_price || '';
        document.getElementById('ri-brand').value = item.suggested_brand_id || '';
        document.getElementById('ri-supplier').value = item.suggested_supplier_id || '';
        document.getElementById('ri-description').value = item.description || '';
    });

    document.querySelectorAll('.js-request-delete').forEach((button) => {
        button.addEventListener('click', () => {
            if (!confirm(requestText.deleteMessage.replace('{0}', button.dataset.deleteLabel || requestText.deleteFallback))) return;
            const form = document.getElementById('requestDeleteForm');
            form.action = button.dataset.deleteAction || '';
            form.submit();
        });
    });

    <?php if ($modalState === 'request-attachment-modal') : ?>
        bootstrap.Tab.getOrCreateInstance(document.getElementById('tab-request-documents-btn')).show();
        new bootstrap.Modal(document.getElementById('requestAttachmentModal')).show();
    <?php endif ?>
})();
</script>
<?= $this->endSection() ?>
