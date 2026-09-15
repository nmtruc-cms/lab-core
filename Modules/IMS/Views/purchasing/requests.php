<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation = $validation ?? []; ?>
<?php $rPager     = $rPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]]; ?>
<?php $rQuery     = $rQuery ?? ['page' => 1, 'per_page' => 10, 'q' => '']; ?>
<?php $modalState = is_array($modalState ?? null) ? $modalState : []; ?>
<?php $approvers  = $approvers ?? []; ?>
<?php $currentUserId = isset($currentUserId) ? (int) $currentUserId : 0; ?>
<?php
$requestLang = static function (string $key, array $args = [], ?string $fallback = null): string {
    $line = 'IMS.requests.' . $key;
    $text = lang($line, $args);

    return $text === $line ? ($fallback ?? $key) : $text;
};
$buildRUrl = static function (array $overrides = []) use ($rQuery): string {
    return site_url('ims/requests?' . http_build_query(array_merge($rQuery, $overrides)));
};
$statusCfg = [
    'draft'     => ['label' => $requestLang('status.draft'),     'class' => ''],
    'submitted' => ['label' => $requestLang('status.submitted'), 'class' => 'neutral'],
    'approved'  => ['label' => $requestLang('status.approved'),  'class' => 'success'],
    'rejected'  => ['label' => $requestLang('status.rejected'),  'class' => 'danger'],
];
?>


<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3><?= esc($requestLang('title')) ?></h3>
            <span><?= esc($requestLang('list.subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.requests.create')) : ?>
            <div class="ims-toolbar-actions">
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#requestFormModal" data-mode="create"><?= esc($requestLang('actions.newRequest')) ?></button>
            </div>
        <?php endif ?>
    </div>

    <div style="padding: 0 20px 16px 20px;">
        <form method="get" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="per_page" value="<?= esc((string) $rQuery['per_page']) ?>">
            <div class="admin-form-group" style="flex:1;margin:0;">
                <input type="search" name="q" value="<?= esc($rQuery['q'] ?? '') ?>" placeholder="<?= esc($requestLang('list.searchPlaceholder')) ?>">
            </div>
            <?php if (($rQuery['q'] ?? '') !== '') : ?>
                <a href="<?= esc(site_url('ims/requests?' . http_build_query(['page' => 1, 'per_page' => $rQuery['per_page']]))) ?>" class="admin-btn secondary"><?= esc($requestLang('common.clear')) ?></a>
            <?php endif ?>
        </form>
    </div>

    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($requestLang('columns.requestNo')) ?></th>
                        <th><?= esc($requestLang('columns.request')) ?></th>
                        <th><?= esc($requestLang('columns.status')) ?></th>
                        <th><?= esc($requestLang('columns.items')) ?></th>
                        <th><?= esc($requestLang('columns.docs')) ?></th>
                        <th><?= esc($requestLang('columns.createdBy')) ?></th>
                        <th><?= esc($requestLang('columns.createdDate')) ?></th>
                        <th><?= esc($requestLang('columns.approver')) ?></th>
                        <th><?= esc($requestLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="9" class="admin-muted"><?= esc($requestLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <?php
                            $sc          = $statusCfg[$row['status'] ?? 'draft'] ?? $statusCfg['draft'];
                            $isDraft     = $row['status'] === 'draft';
                            $isSubmitted = $row['status'] === 'submitted';
                            $isAssignedApprover = $currentUserId > 0 && (int) ($row['approved_by'] ?? 0) === $currentUserId;
                            $itemCount   = count($row['items'] ?? []);
                            $attachCount = count($row['attachments'] ?? []);
                            ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($requestLang('columns.requestNo')) ?>"><strong><?= esc($row['request_number'] ?? '-') ?></strong></td>
                                <td data-label="<?= esc($requestLang('columns.request')) ?>">
                                    <strong><?= esc($row['request_name']) ?></strong>
                                    <?php if (! empty($row['remark'])) : ?>
                                        <div class="admin-muted"><?= esc($row['remark']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($requestLang('columns.status')) ?>">
                                    <span class="admin-badge <?= esc($sc['class']) ?>"><?= esc($sc['label']) ?></span>
                                </td>
                                <td data-label="<?= esc($requestLang('columns.items')) ?>">
                                    <span class="admin-badge neutral"><?= esc((string) $itemCount) ?></span>
                                </td>
                                <td data-label="<?= esc($requestLang('columns.docs')) ?>">
                                    <span class="admin-badge neutral"><?= esc((string) $attachCount) ?></span>
                                </td>
                                <td data-label="<?= esc($requestLang('columns.createdBy')) ?>"><?= esc($row['created_by_name'] ?? '—') ?></td>
                                <td data-label="<?= esc($requestLang('columns.createdDate')) ?>"><?= esc($row['created_date'] ?? '—') ?></td>
                                <td data-label="<?= esc($requestLang('columns.approver')) ?>">
                                    <?php if (! empty($row['approved_by_name'])) : ?>
                                        <?= esc($row['approved_by_name']) ?>
                                        <?php if (! empty($row['approved_date'])) : ?>
                                            <div class="admin-muted"><?= esc($row['approved_date']) ?></div>
                                        <?php endif ?>
                                    <?php else : ?>
                                        <span class="admin-muted">—</span>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($requestLang('columns.action')) ?>">
                                    <div class="ims-action-group">
                                        <a class="ims-action-btn" title="<?= esc($requestLang('actions.viewDetail')) ?>" href="<?= esc(site_url('ims/requests/' . $row['id'])) ?>">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        <?php if ($isDraft && (lab_core_can('ims.requests.update') || lab_core_can('ims.requests.submit'))) : ?>
                                            <?php if (lab_core_can('ims.requests.update')) : ?>
                                            <button type="button" class="ims-action-btn"
                                                data-bs-toggle="modal" data-bs-target="#requestFormModal"
                                                data-mode="edit"
                                                data-request-id="<?= (int) $row['id'] ?>"
                                                data-request-name="<?= esc($row['request_name']) ?>"
                                                data-request-approved-by="<?= esc((string) ($row['approved_by'] ?? '')) ?>"
                                                data-request-remark="<?= esc($row['remark'] ?? '') ?>"
                                                data-action="<?= esc(site_url('ims/requests/' . $row['id'])) ?>"
                                                title="<?= esc($requestLang('actions.editRequest')) ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <?php endif ?>
                                            <?php if (lab_core_can('ims.requests.submit')) : ?>
                                            <button type="button" class="ims-action-btn success"
                                                data-bs-toggle="modal" data-bs-target="#submitRequestModal"
                                                data-request-id="<?= (int) $row['id'] ?>"
                                                data-request-name="<?= esc($row['request_name']) ?>"
                                                data-action="<?= esc(site_url('ims/requests/' . $row['id'] . '/submit')) ?>"
                                                title="<?= esc($requestLang('actions.submitForApproval')) ?>">
                                                <i class="fa-solid fa-paper-plane"></i>
                                            </button>
                                            <?php endif ?>
                                        <?php endif ?>
                                        <?php if ($isSubmitted && $isAssignedApprover && lab_core_can('ims.requests.approve')) : ?>
                                            <button type="button" class="ims-action-btn success"
                                                data-bs-toggle="modal" data-bs-target="#approveRequestModal"
                                                data-request-id="<?= (int) $row['id'] ?>"
                                                data-request-name="<?= esc($row['request_name']) ?>"
                                                data-action="<?= esc(site_url('ims/requests/' . $row['id'] . '/approve')) ?>"
                                                title="<?= esc($requestLang('actions.approve')) ?>">
                                                <i class="fa-solid fa-circle-check"></i>
                                            </button>
                                        <?php endif ?>
                                        <?php if ($isSubmitted && $isAssignedApprover && lab_core_can('ims.requests.reject')) : ?>
                                            <button type="button" class="ims-action-btn danger"
                                                data-bs-toggle="modal" data-bs-target="#rejectRequestModal"
                                                data-request-id="<?= (int) $row['id'] ?>"
                                                data-request-name="<?= esc($row['request_name']) ?>"
                                                data-action="<?= esc(site_url('ims/requests/' . $row['id'] . '/reject')) ?>"
                                                title="<?= esc($requestLang('actions.reject')) ?>">
                                                <i class="fa-solid fa-circle-xmark"></i>
                                            </button>
                                        <?php endif ?>
                                        <?php if ($isDraft && lab_core_can('ims.requests.delete')) : ?>
                                            <button type="button" class="ims-action-btn danger"
                                                data-bs-toggle="modal" data-bs-target="#deleteRequestModal"
                                                data-delete-message="<?= esc($requestLang('delete.requestMessage', [$row['request_name']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/requests/' . $row['id'] . '/delete')) ?>"
                                                title="<?= esc($requestLang('actions.delete')) ?>">
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
                <strong><?= esc((string) $rPager['total']) ?></strong>&nbsp;<?= esc($requestLang('list.records')) ?>
                <span class="admin-muted"><?= $rPager['total'] > 0 ? '(' . esc((string) $rPager['from']) . '–' . esc((string) $rPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= esc($rQuery['q'] ?? '') ?>">
                <span><?= esc($requestLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $opt) : ?>
                        <option value="<?= $opt ?>" <?= (int) $rPager['perPage'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($rPager['page'] > 1) : ?><a class="page-btn" href="<?= esc($buildRUrl(['page' => $rPager['page'] - 1])) ?>">&lsaquo;</a><?php endif ?>
                <?php foreach ($rPager['pages'] as $pg) : ?><a class="page-btn <?= (int) $rPager['page'] === $pg ? 'active' : '' ?>" href="<?= esc($buildRUrl(['page' => $pg])) ?>"><?= esc((string) $pg) ?></a><?php endforeach ?>
                <?php if ($rPager['page'] < $rPager['totalPages']) : ?><a class="page-btn" href="<?= esc($buildRUrl(['page' => $rPager['page'] + 1])) ?>">&rsaquo;</a><?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- ── Request Create / Edit Modal ──────────────────────────────────────── -->
<div class="modal fade app-crud-modal" id="requestFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/requests') ?>" id="requestForm">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('actions.newRequest')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (($modalState['modal'] ?? '') === 'request-form-modal' && $validation !== []) : ?>
                        <div class="admin-alert danger"><?= esc(implode(' ', $validation)) ?></div>
                    <?php endif ?>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('fields.requestName')) ?> <span class="required">*</span></label>
                        <input type="text" name="request_name" required
                            value="<?= (($modalState['modal'] ?? '') === 'request-form-modal') ? esc(old('request_name', '')) : '' ?>">
                    </div>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('fields.approver')) ?> <span class="required">*</span></label>
                        <select name="approved_by" class="filter-select" required>
                            <option value=""><?= esc($requestLang('placeholders.selectApprover')) ?></option>
                            <?php foreach ($approvers as $approver) : ?>
                                <option value="<?= (int) $approver['id'] ?>"
                                    <?= (($modalState['modal'] ?? '') === 'request-form-modal' && (string) old('approved_by', '') === (string) $approver['id']) ? 'selected' : '' ?>>
                                    <?= esc($approver['username'] ?? ('User #' . (string) $approver['id'])) ?>
                                </option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('fields.remarkPurpose')) ?></label>
                        <textarea name="remark" rows="3"><?= (($modalState['modal'] ?? '') === 'request-form-modal') ? esc(old('remark', '')) : '' ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary" id="requestFormSubmitBtn"><?= esc($requestLang('actions.createRequest')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Request Items Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="requestItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc($requestLang('detail.tabs.items')) ?> - <span id="rItemsModalName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <div id="rItemsListPanel" style="overflow-x:auto;">
                    <table class="results-table" style="margin-bottom:0;">
                        <thead>
                            <tr>
                                <th><?= esc($requestLang('fields.itemName')) ?></th>
                                <th>CAS / EC</th>
                                <th><?= esc($requestLang('fields.grade')) ?></th>
                                <th><?= esc($requestLang('columns.qty')) ?></th>
                                <th><?= esc($requestLang('fields.unitPrice')) ?></th>
                                <th><?= esc($requestLang('columns.brandSupplier')) ?></th>
                                <th style="width:80px;"><?= esc($requestLang('columns.action')) ?></th>
                            </tr>
                        </thead>
                        <tbody id="rItemsTableBody"></tbody>
                    </table>
                </div>
                <!-- Add / Edit Item Form -->
                <div id="rItemFormPanel" style="display:none;padding:20px;border-top:1px solid var(--border-color, #e5e7eb);">
                    <h6 id="rItemFormTitle" style="margin-bottom:16px;font-weight:600;"><?= esc($requestLang('actions.addItem')) ?></h6>
                    <form id="rItemFormEl" method="post" action="">
                        <?= csrf_field() ?>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="admin-form-group" style="grid-column:1/-1;">
                                <label><?= esc($requestLang('fields.itemName')) ?> <span class="required">*</span></label>
                                <input type="text" name="item_name" id="ri-name" required>
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.alternateName')) ?></label>
                                <input type="text" name="alternate_name" id="ri-alt-name">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.category')) ?></label>
                                <select name="category_id" id="ri-category">
                                    <option value=""><?= esc($requestLang('placeholders.none')) ?></option>
                                    <?php foreach ($categories as $cat) : ?>
                                        <option value="<?= (int) $cat['id'] ?>"><?= esc($cat['category_name']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.casNo')) ?></label>
                                <input type="text" name="cas_no" id="ri-cas">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.ecNo')) ?></label>
                                <input type="text" name="ec_no" id="ri-ec">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.catalogNo')) ?></label>
                                <input type="text" name="catalog_no" id="ri-catalog">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.gradePurity')) ?></label>
                                <input type="text" name="grade" id="ri-grade">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('columns.qty')) ?></label>
                                <input type="number" name="qty" id="ri-qty" step="any" min="0">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.unit')) ?></label>
                                <select name="default_unit_id" id="ri-unit">
                                    <option value=""><?= esc($requestLang('placeholders.none')) ?></option>
                                    <?php foreach ($units as $u) : ?>
                                        <option value="<?= (int) $u['id'] ?>"><?= esc($u['unit_name']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.packSize')) ?></label>
                                <input type="text" name="pack_size" id="ri-pack-size">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.unitPrice')) ?></label>
                                <input type="number" name="unit_price" id="ri-unit-price" step="any" min="0">
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.suggestedBrand')) ?></label>
                                <select name="suggested_brand_id" id="ri-brand">
                                    <option value=""><?= esc($requestLang('placeholders.none')) ?></option>
                                    <?php foreach ($brands as $b) : ?>
                                        <option value="<?= (int) $b['id'] ?>"><?= esc($b['brand_name']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="admin-form-group">
                                <label><?= esc($requestLang('fields.suggestedSupplier')) ?></label>
                                <select name="suggested_supplier_id" id="ri-supplier">
                                    <option value=""><?= esc($requestLang('placeholders.none')) ?></option>
                                    <?php foreach ($suppliers as $s) : ?>
                                        <option value="<?= (int) $s['id'] ?>"><?= esc($s['supplier_name']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            <div class="admin-form-group" style="grid-column:1/-1;">
                                <label><?= esc($requestLang('fields.descriptionNotes')) ?></label>
                                <textarea name="description" id="ri-description" rows="2"></textarea>
                            </div>
                        </div>
                        <div style="display:flex;gap:8px;margin-top:8px;">
                            <button type="button" class="admin-btn secondary" onclick="cancelRItemForm()"><?= esc($requestLang('common.cancel')) ?></button>
                            <button type="submit" class="admin-btn primary" id="rItemFormSubmitBtn"><?= esc($requestLang('actions.addItem')) ?></button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" id="rItemsAddBtn" class="admin-btn primary" style="display:none;" onclick="showRItemForm()">
                    <i class="fa-solid fa-plus"></i> <?= esc($requestLang('actions.addItem')) ?>
                </button>
                <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.close')) ?></button>
            </div>
        </div>
    </div>
</div>

<!-- ── Request Documents Modal ──────────────────────────────────────────── -->
<div class="modal fade" id="requestDocumentsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?= esc($requestLang('modal.documentsTitle')) ?> - <span id="rDocsModalName"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:0;">
                <table class="results-table" style="margin-bottom:0;">
                    <thead>
                        <tr>
                            <th><?= esc($requestLang('columns.type')) ?></th>
                            <th><?= esc($requestLang('columns.file')) ?></th>
                            <th><?= esc($requestLang('columns.remarks')) ?></th>
                            <th><?= esc($requestLang('columns.uploaded')) ?></th>
                        </tr>
                    </thead>
                    <tbody id="rDocsTableBody"></tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.close')) ?></button>
            </div>
        </div>
    </div>
</div>

<!-- ── Request Attachment Upload Modal ─────────────────────────────────── -->
<div class="modal fade app-crud-modal" id="requestAttachmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" enctype="multipart/form-data" id="requestAttachForm" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('actions.uploadDocument')) ?> - <span id="rAttachModalName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php if (($modalState['modal'] ?? '') === 'request-attachment-modal' && $validation !== []) : ?>
                        <div class="admin-alert danger"><?= esc(implode(' ', $validation)) ?></div>
                    <?php endif ?>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('fields.attachmentType')) ?></label>
                        <select name="attachment_type">
                            <option value="quote"><?= esc($requestLang('attachmentTypes.quote')) ?></option>
                            <option value="certificate"><?= esc($requestLang('attachmentTypes.certificate')) ?></option>
                            <option value="protocol"><?= esc($requestLang('attachmentTypes.protocol')) ?></option>
                            <option value="report"><?= esc($requestLang('attachmentTypes.report')) ?></option>
                            <option value="photo"><?= esc($requestLang('attachmentTypes.photo')) ?></option>
                            <option value="document" selected><?= esc($requestLang('attachmentTypes.document')) ?></option>
                            <option value="other"><?= esc($requestLang('attachmentTypes.other')) ?></option>
                        </select>
                    </div>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('columns.file')) ?> <span class="required">*</span></label>
                        <input type="file" name="attachment_file" required>
                        <small class="admin-muted"><?= esc($requestLang('help.maxFileSizeShort')) ?></small>
                    </div>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('columns.remarks')) ?></label>
                        <textarea name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($requestLang('actions.uploadFile')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Submit Request Modal ─────────────────────────────────────────────── -->
<div class="modal fade" id="submitRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="submitRequestForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('modal.submitTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="submitRequestMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($requestLang('actions.submitForApproval')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Approve Request Modal ────────────────────────────────────────────── -->
<div class="modal fade" id="approveRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="approveRequestForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('modal.approveTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="approveRequestMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn success"><?= esc($requestLang('actions.approve')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Reject Request Modal ──────────────────────────────────────────────── -->
<div class="modal fade" id="rejectRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="rejectRequestForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('modal.rejectTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="rejectRequestMessage"></p>
                    <div class="admin-form-group">
                        <label><?= esc($requestLang('fields.reasonRemark')) ?></label>
                        <textarea name="remark" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn danger"><?= esc($requestLang('actions.reject')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ── Delete Request Modal ─────────────────────────────────────────────── -->
<div class="modal fade" id="deleteRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="deleteRequestForm" method="post" action="">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($requestLang('delete.requestTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteRequestMessage"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="admin-btn secondary" data-bs-dismiss="modal"><?= esc($requestLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn danger"><?= esc($requestLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hidden shared delete-item form -->
<form id="rItemDeleteForm" method="post" action="" style="display:none;">
    <?= csrf_field() ?>
</form>

<script>
const rBaseUrl = '<?= esc(site_url('ims/requests')) ?>';
const requestText = <?= json_encode([
    'newRequest' => $requestLang('actions.newRequest'),
    'editRequest' => $requestLang('actions.editRequest'),
    'createRequest' => $requestLang('actions.createRequest'),
    'saveChanges' => $requestLang('actions.saveChanges'),
    'addItem' => $requestLang('actions.addItem'),
    'editItem' => $requestLang('actions.editItem'),
    'remove' => $requestLang('actions.deleteItem'),
    'noItems' => $requestLang('detail.noItems'),
    'noDocuments' => $requestLang('detail.noDocuments'),
    'submitMessage' => $requestLang('modal.submitMessage'),
    'approveMessage' => $requestLang('modal.approveMessage'),
    'rejectMessage' => $requestLang('modal.rejectMessage'),
    'removeItemConfirm' => $requestLang('delete.inlineMessage', [$requestLang('columns.item')]),
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
let currentRId     = null;
let currentRStatus = null;

// ── Request Form Modal ───────────────────────────────────────────────────
document.getElementById('requestFormModal').addEventListener('show.bs.modal', function (e) {
    const btn  = e.relatedTarget;
    const mode = btn ? btn.dataset.mode : 'create';
    const form = document.getElementById('requestForm');
    if (mode === 'edit') {
        document.querySelector('#requestFormModal .modal-title').textContent = requestText.editRequest;
        document.getElementById('requestFormSubmitBtn').textContent = requestText.saveChanges;
        form.action = btn.dataset.action;
        form.querySelector('[name="request_name"]').value  = btn.dataset.requestName  || '';
        form.querySelector('[name="approved_by"]').value    = btn.dataset.requestApprovedBy || '';
        form.querySelector('[name="remark"]').value        = btn.dataset.requestRemark || '';
    } else {
        document.querySelector('#requestFormModal .modal-title').textContent = requestText.newRequest;
        document.getElementById('requestFormSubmitBtn').textContent = requestText.createRequest;
        form.action = rBaseUrl;
        <?php if (($modalState['modal'] ?? '') !== 'request-form-modal') : ?>
        form.querySelector('[name="request_name"]').value = '';
        form.querySelector('[name="approved_by"]').value  = '';
        form.querySelector('[name="remark"]').value       = '';
        <?php endif ?>
    }
});

// ── Items Modal ──────────────────────────────────────────────────────────
document.getElementById('requestItemsModal').addEventListener('show.bs.modal', function (e) {
    const btn    = e.relatedTarget;
    const id     = btn ? parseInt(btn.dataset.requestId, 10) : null;
    const items  = btn ? JSON.parse(btn.dataset.items  || '[]') : [];
    const name   = btn ? (btn.dataset.requestName   || '') : '';
    const status = btn ? (btn.dataset.requestStatus || 'draft') : 'draft';

    currentRId     = id;
    currentRStatus = status;

    document.getElementById('rItemsModalName').textContent = name;
    renderRItems(items, status === 'draft');

    const addBtn = document.getElementById('rItemsAddBtn');
    addBtn.style.display = (status === 'draft' && <?= lab_core_can('ims.requests.update') ? 'true' : 'false' ?>) ? 'inline-block' : 'none';
    document.getElementById('rItemFormPanel').style.display = 'none';
});

function renderRItems(items, isDraft) {
    const tbody = document.getElementById('rItemsTableBody');
    if (!items || items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="admin-muted" style="text-align:center;padding:20px;">' + h(requestText.noItems) + '</td></tr>';
        return;
    }
    tbody.innerHTML = items.map(function (item) {
        const editBtn = (isDraft && <?= lab_core_can('ims.requests.update') ? 'true' : 'false' ?>)
            ? `<button type="button" class="ims-action-btn" title="${h(requestText.editItem)}" onclick='editRItem(${JSON.stringify(item)})'><i class="fa-solid fa-pen-to-square"></i></button>`
            : '';
        const delBtn = (isDraft && <?= lab_core_can('ims.requests.update') ? 'true' : 'false' ?>)
            ? `<button type="button" class="ims-action-btn danger" title="${h(requestText.remove)}" onclick="deleteRItem(${item.id})"><i class="fa-solid fa-trash-can"></i></button>`
            : '';
        return `<tr>
            <td><strong>${h(item.item_name)}</strong>${item.alternate_name ? '<br><span class="admin-muted">' + h(item.alternate_name) + '</span>' : ''}</td>
            <td>${item.cas_no ? h(item.cas_no) : '—'}${item.ec_no ? '<br><span class="admin-muted">EC: ' + h(item.ec_no) + '</span>' : ''}</td>
            <td>${item.grade ? h(item.grade) : '—'}</td>
            <td>${item.qty != null ? h(String(item.qty)) : '—'}${item.unit_name ? ' <span class="admin-muted">' + h(item.unit_name) + '</span>' : ''}</td>
            <td>${item.unit_price != null ? h(String(item.unit_price)) : '—'}</td>
            <td>${item.brand_name ? h(item.brand_name) : '—'}${item.supplier_name ? '<br><span class="admin-muted">' + h(item.supplier_name) + '</span>' : ''}</td>
            <td style="white-space:nowrap;">${editBtn}${delBtn}</td>
        </tr>`;
    }).join('');
}

function showRItemForm() {
    document.getElementById('rItemFormTitle').textContent    = requestText.addItem;
    document.getElementById('rItemFormSubmitBtn').textContent = requestText.addItem;
    document.getElementById('rItemFormEl').action             = rBaseUrl + '/' + currentRId + '/items';
    clearRItemForm();
    document.getElementById('rItemFormPanel').style.display = 'block';
    document.getElementById('rItemsAddBtn').style.display   = 'none';
}

function editRItem(item) {
    document.getElementById('rItemFormTitle').textContent    = requestText.editItem;
    document.getElementById('rItemFormSubmitBtn').textContent = requestText.saveChanges;
    document.getElementById('rItemFormEl').action             = rBaseUrl + '/' + currentRId + '/items/' + item.id;

    document.getElementById('ri-name').value       = item.item_name    || '';
    document.getElementById('ri-alt-name').value   = item.alternate_name || '';
    document.getElementById('ri-category').value   = item.category_id  || '';
    document.getElementById('ri-cas').value        = item.cas_no       || '';
    document.getElementById('ri-ec').value         = item.ec_no        || '';
    document.getElementById('ri-catalog').value    = item.catalog_no   || '';
    document.getElementById('ri-grade').value      = item.grade        || '';
    document.getElementById('ri-qty').value        = item.qty != null ? item.qty : '';
    document.getElementById('ri-unit').value       = item.default_unit_id || '';
    document.getElementById('ri-pack-size').value  = item.pack_size    || '';
    document.getElementById('ri-unit-price').value = item.unit_price != null ? item.unit_price : '';
    document.getElementById('ri-brand').value      = item.suggested_brand_id    || '';
    document.getElementById('ri-supplier').value   = item.suggested_supplier_id || '';
    document.getElementById('ri-description').value = item.description  || '';

    document.getElementById('rItemFormPanel').style.display = 'block';
    document.getElementById('rItemsAddBtn').style.display   = 'none';
    document.getElementById('rItemFormPanel').scrollIntoView({ behavior: 'smooth' });
}

function cancelRItemForm() {
    document.getElementById('rItemFormPanel').style.display = 'none';
    document.getElementById('rItemsAddBtn').style.display   =
        (currentRStatus === 'draft' && <?= lab_core_can('ims.requests.update') ? 'true' : 'false' ?>) ? 'inline-block' : 'none';
}

function clearRItemForm() {
    ['ri-name', 'ri-alt-name', 'ri-cas', 'ri-ec', 'ri-catalog', 'ri-grade', 'ri-pack-size', 'ri-description'].forEach(function (id) {
        document.getElementById(id).value = '';
    });
    document.getElementById('ri-qty').value        = '';
    document.getElementById('ri-unit-price').value = '';
    ['ri-category', 'ri-unit', 'ri-brand', 'ri-supplier'].forEach(function (id) {
        document.getElementById(id).value = '';
    });
}

function deleteRItem(itemId) {
    if (!confirm(requestText.removeItemConfirm)) return;
    const form = document.getElementById('rItemDeleteForm');
    form.action = rBaseUrl + '/' + currentRId + '/items/' + itemId + '/delete';
    form.submit();
}

// ── Documents Modal ──────────────────────────────────────────────────────
document.getElementById('requestDocumentsModal').addEventListener('show.bs.modal', function (e) {
    const btn         = e.relatedTarget;
    const name        = btn ? (btn.dataset.requestName || '') : '';
    const attachments = btn ? JSON.parse(btn.dataset.attachments || '[]') : [];

    document.getElementById('rDocsModalName').textContent = name;
    const tbody = document.getElementById('rDocsTableBody');

    if (!attachments || attachments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="admin-muted" style="text-align:center;padding:20px;">' + h(requestText.noDocuments) + '</td></tr>';
        return;
    }
    tbody.innerHTML = attachments.map(function (a) {
        return `<tr>
            <td>${h(a.attachment_type || 'document')}</td>
            <td><a href="/${h(a.file_path || '')}" target="_blank">${h(a.file_name || '')}</a></td>
            <td>${h(a.remarks || '—')}</td>
            <td>${h(a.created_at || '—')}</td>
        </tr>`;
    }).join('');
});

// ── Attachment Upload Modal ──────────────────────────────────────────────
document.getElementById('requestAttachmentModal').addEventListener('show.bs.modal', function (e) {
    const btn  = e.relatedTarget;
    const name = btn ? (btn.dataset.requestName || '') : '';
    const action = btn ? (btn.dataset.action || '') : '';
    document.getElementById('rAttachModalName').textContent = name;
    document.getElementById('requestAttachForm').action     = action;
});

// ── Submit Modal ─────────────────────────────────────────────────────────
document.getElementById('submitRequestModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const name = btn ? (btn.dataset.requestName || '') : '';
    document.getElementById('submitRequestMessage').textContent = requestText.submitMessage.replace('{0}', name);
    document.getElementById('submitRequestForm').action      = btn ? (btn.dataset.action || '') : '';
});

// ── Approve Modal ────────────────────────────────────────────────────────
document.getElementById('approveRequestModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const name = btn ? (btn.dataset.requestName || '') : '';
    document.getElementById('approveRequestMessage').textContent = requestText.approveMessage.replace('{0}', name);
    document.getElementById('approveRequestForm').action      = btn ? (btn.dataset.action || '') : '';
});

// ── Reject Modal ─────────────────────────────────────────────────────────
document.getElementById('rejectRequestModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    const name = btn ? (btn.dataset.requestName || '') : '';
    document.getElementById('rejectRequestMessage').textContent = requestText.rejectMessage.replace('{0}', name);
    document.getElementById('rejectRequestForm').action      = btn ? (btn.dataset.action || '') : '';
    document.querySelector('#rejectRequestForm [name="remark"]').value = '';
});

// ── Delete Modal ─────────────────────────────────────────────────────────
document.getElementById('deleteRequestModal').addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    document.getElementById('deleteRequestMessage').innerHTML = btn ? (btn.dataset.deleteMessage || '') : '';
    document.getElementById('deleteRequestForm').action       = btn ? (btn.dataset.deleteAction || '') : '';
});

// ── XSS escape helper ────────────────────────────────────────────────────
function h(str) {
    if (str == null) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

// ── Session restore ──────────────────────────────────────────────────────
(() => {
    <?php if (($modalState['modal'] ?? null) === 'request-form-modal') : ?>
        {
            const form = document.getElementById('requestForm');
            <?php if (! empty($modalState['r_id'])) : ?>
                document.querySelector('#requestFormModal .modal-title').textContent = requestText.editRequest;
                document.getElementById('requestFormSubmitBtn').textContent = requestText.saveChanges;
                form.action = '<?= site_url('ims/requests/' . (int) ($modalState['r_id'] ?? 0)) ?>';
            <?php endif ?>
            new bootstrap.Modal(document.getElementById('requestFormModal')).show();
        }
    <?php elseif (($modalState['modal'] ?? null) === 'request-items-modal' && ! empty($modalState['r_id'])) : ?>
        {
            const trigger = document.querySelector('[data-bs-target="#requestItemsModal"][data-request-id="<?= (int) ($modalState['r_id'] ?? 0) ?>"]');
            if (trigger) trigger.click();
        }
    <?php elseif (($modalState['modal'] ?? null) === 'request-attachment-modal' && ! empty($modalState['r_id'])) : ?>
        {
            const trigger = document.querySelector('[data-bs-target="#requestAttachmentModal"][data-request-id="<?= (int) ($modalState['r_id'] ?? 0) ?>"]');
            if (trigger) trigger.click();
        }
    <?php endif ?>
})();
</script>
<?= $this->endSection() ?>
