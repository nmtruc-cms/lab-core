<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $validation = $validation ?? []; ?>
<?php $supplierPager = $supplierPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]]; ?>
<?php $supplierQuery = $supplierQuery ?? ['page' => 1, 'per_page' => 10]; ?>
<?php
$supplierLang = static fn (string $key, array $args = []): string => lang('IMS.suppliers.' . $key, $args);
$buildSupplierUrl = static function (array $overrides = []) use ($supplierQuery): string {
    return site_url('ims/suppliers?' . http_build_query(array_merge($supplierQuery, $overrides)));
};
$statusBadgeClass = static function (?string $status): string {
    return match (strtolower((string) $status)) {
        'approved preferred', 'approved' => 'success',
        'disqualified' => 'danger',
        'conditional approval', 'improvement required' => 'warning',
        default => 'neutral',
    };
};
?>


<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head">
            <h3><?= esc($supplierLang('title')) ?></h3>
            <span><?= esc($supplierLang('list.subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.suppliers.manage')) : ?>
            <div class="ims-toolbar-actions">
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#supplierFormModal" data-mode="create"><?= esc($supplierLang('actions.addSupplier')) ?></button>
                <div class="dropdown">
                    <button class="admin-btn secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?= esc($supplierLang('common.more')) ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= esc(site_url('ims/suppliers/template')) ?>"><?= esc($supplierLang('actions.downloadTemplate')) ?></a></li>
                        <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#supplierImportModal"><?= esc($supplierLang('actions.importCsv')) ?></button></li>
                    </ul>
                </div>
            </div>
        <?php endif ?>
    </div>
    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($supplierLang('columns.supplier')) ?></th>
                        <th><?= esc($supplierLang('columns.code')) ?></th>
                        <th><?= esc($supplierLang('columns.contact')) ?></th>
                        <th><?= esc($supplierLang('columns.email')) ?></th>
                        <th><?= esc($supplierLang('columns.approval')) ?></th>
                        <th><?= esc($supplierLang('columns.brandMapping')) ?></th>
                        <th><?= esc($supplierLang('columns.status')) ?></th>
                        <th><?= esc($supplierLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="8" class="admin-muted"><?= esc($supplierLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($supplierLang('columns.supplier')) ?>">
                                    <strong><?= esc($row['supplier_name']) ?></strong>
                                    <?php if (! empty($row['address'])) : ?>
                                        <div class="admin-muted"><?= esc($row['address']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($supplierLang('columns.code')) ?>"><?= esc($row['supplier_code'] ?? '-') ?></td>
                                <td data-label="<?= esc($supplierLang('columns.contact')) ?>">
                                    <strong><?= esc($row['contact_name'] ?? '-') ?></strong>
                                    <?php if (! empty($row['phone'])) : ?>
                                        <div class="admin-muted"><?= esc($row['phone']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($supplierLang('columns.email')) ?>"><?= esc($row['email'] ?? '-') ?></td>
                                <td data-label="<?= esc($supplierLang('columns.approval')) ?>">
                                    <?php $approvalName = (string) ($row['approved_status_name'] ?? '-'); ?>
                                    <span class="admin-badge <?= esc($statusBadgeClass($approvalName)) ?>"><?= esc($approvalName !== '' ? $approvalName : '-') ?></span>
                                </td>
                                <td data-label="<?= esc($supplierLang('columns.brandMapping')) ?>">
                                    <?php if (($row['mapped_brands'] ?? []) === []) : ?>
                                        <span class="admin-muted"><?= esc($supplierLang('list.noBrandsMapped')) ?></span>
                                    <?php else : ?>
                                        <div class="ims-brand-chip-list">
                                            <?php foreach ($row['mapped_brands'] as $brand) : ?>
                                                <span class="admin-chip muted"><?= esc($brand['brand_name']) ?></span>
                                            <?php endforeach ?>
                                        </div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($supplierLang('columns.status')) ?>"><span class="admin-badge <?= (int) ($row['is_active'] ?? 0) === 1 ? 'success' : '' ?>"><?= esc((int) ($row['is_active'] ?? 0) === 1 ? $supplierLang('status.active') : $supplierLang('status.inactive')) ?></span></td>
                                <td data-label="<?= esc($supplierLang('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.suppliers.view') || lab_core_can('ims.suppliers.manage')) : ?>
                                        <div class="ims-action-group">
                                            <a class="ims-action-btn" title="<?= esc($supplierLang('actions.viewDetail')) ?>" href="<?= esc(site_url('ims/suppliers/' . $row['id'])) ?>">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                            <?php if (lab_core_can('ims.suppliers.manage')) : ?>
                                                <button type="button" class="ims-action-btn" title="<?= esc($supplierLang('actions.editSupplier')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#supplierFormModal"
                                                    data-mode="edit"
                                                    data-action="<?= esc(site_url('ims/suppliers/' . $row['id'])) ?>"
                                                    data-supplier-name="<?= esc($row['supplier_name']) ?>"
                                                    data-supplier-code="<?= esc($row['supplier_code'] ?? '') ?>"
                                                    data-address="<?= esc($row['address'] ?? '') ?>"
                                                    data-vat-code="<?= esc($row['vat_code'] ?? '') ?>"
                                                    data-phone="<?= esc($row['phone'] ?? '') ?>"
                                                    data-email="<?= esc($row['email'] ?? '') ?>"
                                                    data-contact-name="<?= esc($row['contact_name'] ?? '') ?>"
                                                    data-is-active="<?= esc((string) ($row['is_active'] ?? 0)) ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                                <button type="button" class="ims-action-btn danger" title="<?= esc($supplierLang('actions.deleteSupplier')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteSupplierModal"
                                                    data-delete-title="<?= esc($supplierLang('delete.supplierTitle')) ?>"
                                                    data-delete-message="<?= esc($supplierLang('delete.supplierMessage', [$row['supplier_name']])) ?>"
                                                    data-delete-action="<?= esc(site_url('ims/suppliers/' . $row['id'] . '/delete')) ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="admin-muted"><?= esc($supplierLang('common.noActions')) ?></span>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
        <div class="results-footer">
            <div class="result-count">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <strong><?= esc((string) $supplierPager['total']) ?></strong>&nbsp;<?= esc($supplierLang('list.records')) ?>
                <span class="admin-muted">
                    <?= $supplierPager['total'] > 0 ? '(' . esc((string) $supplierPager['from']) . '-' . esc((string) $supplierPager['to']) . ')' : '' ?>
                </span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <span><?= esc($supplierLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $supplierPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($supplierPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildSupplierUrl(['page' => $supplierPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($supplierPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $supplierPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildSupplierUrl(['page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($supplierPager['page'] < $supplierPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildSupplierUrl(['page' => $supplierPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierImportModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/suppliers/import') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $supplierQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $supplierQuery['per_page']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('import.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="supplier-import-file"><?= esc($supplierLang('import.fileLabel')) ?></label>
                        <input id="supplier-import-file" class="app-file-input" name="import_file" type="file" accept=".csv,text/csv">
                        <?php if (isset($validation['import_file'])) : ?><small class="admin-error"><?= esc($validation['import_file']) ?></small><?php endif ?>
                        <small class="admin-muted"><?= esc($supplierLang('import.help')) ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($supplierLang('import.submit')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/suppliers') ?>" id="supplierForm">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $supplierQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $supplierQuery['per_page']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($supplierLang('forms.supplier')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="supplier-name"><?= esc($supplierLang('forms.supplierName')) ?></label>
                            <input id="supplier-name" name="supplier_name" type="text" value="<?= old('supplier_name') ?>">
                            <?php if (isset($validation['supplier_name'])) : ?><small class="admin-error"><?= esc($validation['supplier_name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="supplier-code"><?= esc($supplierLang('forms.supplierCode')) ?></label>
                            <input id="supplier-code" name="supplier_code" type="text" value="<?= old('supplier_code') ?>" readonly>
                            <small class="admin-muted"><?= esc($supplierLang('forms.supplierCodeHelp')) ?></small>
                        </div>
                        <div class="admin-form-group">
                            <label for="supplier-contact-name"><?= esc($supplierLang('forms.contactName')) ?></label>
                            <input id="supplier-contact-name" name="contact_name" type="text" value="<?= old('contact_name') ?>">
                            <?php if (isset($validation['contact_name'])) : ?><small class="admin-error"><?= esc($validation['contact_name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="supplier-phone"><?= esc($supplierLang('forms.phone')) ?></label>
                            <input id="supplier-phone" name="phone" type="text" value="<?= old('phone') ?>">
                            <?php if (isset($validation['phone'])) : ?><small class="admin-error"><?= esc($validation['phone']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="supplier-email"><?= esc($supplierLang('forms.email')) ?></label>
                            <input id="supplier-email" name="email" type="email" value="<?= old('email') ?>">
                            <?php if (isset($validation['email'])) : ?><small class="admin-error"><?= esc($validation['email']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="supplier-vat-code"><?= esc($supplierLang('forms.vatCode')) ?></label>
                            <input id="supplier-vat-code" name="vat_code" type="text" value="<?= old('vat_code') ?>">
                            <?php if (isset($validation['vat_code'])) : ?><small class="admin-error"><?= esc($validation['vat_code']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group full">
                            <label for="supplier-address"><?= esc($supplierLang('forms.address')) ?></label>
                            <textarea id="supplier-address" name="address" rows="3"><?= old('address') ?></textarea>
                            <?php if (isset($validation['address'])) : ?><small class="admin-error"><?= esc($validation['address']) ?></small><?php endif ?>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="supplier-is-active" name="is_active" <?= old('is_active') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="supplier-is-active"><?= esc($supplierLang('status.active')) ?></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($supplierLang('forms.saveSupplier')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteSupplierForm">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $supplierQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $supplierQuery['per_page']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSupplierModalTitle"><?= esc($supplierLang('delete.supplierTitle')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($supplierLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteSupplierModalMessage"><?= esc($supplierLang('delete.supplierDefaultMessage')) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($supplierLang('common.cancel')) ?></button>
                    <button type="submit" class="btn btn-danger"><?= esc($supplierLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    (() => {
        const supplierModal = document.getElementById('supplierFormModal');
        supplierModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('supplierForm');
            const title = supplierModal.querySelector('.modal-title');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = <?= json_encode($supplierLang('forms.addSupplier')) ?>;
                form.action = '<?= site_url('ims/suppliers') ?>';
                form.reset();
                return;
            }

            title.textContent = <?= json_encode($supplierLang('forms.editSupplier')) ?>;
            form.action = trigger.dataset.action;
            form.querySelector('[name="supplier_name"]').value = trigger.dataset.supplierName || '';
            form.querySelector('[name="supplier_code"]').value = trigger.dataset.supplierCode || '';
            form.querySelector('[name="address"]').value = trigger.dataset.address || '';
            form.querySelector('[name="vat_code"]').value = trigger.dataset.vatCode || '';
            form.querySelector('[name="phone"]').value = trigger.dataset.phone || '';
            form.querySelector('[name="email"]').value = trigger.dataset.email || '';
            form.querySelector('[name="contact_name"]').value = trigger.dataset.contactName || '';
            form.querySelector('[name="is_active"]').checked = trigger.dataset.isActive === '1';
        });

        const deleteModal = document.getElementById('deleteSupplierModal');
        deleteModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            document.getElementById('deleteSupplierForm').action = trigger.dataset.deleteAction;
            document.getElementById('deleteSupplierModalTitle').textContent = trigger.dataset.deleteTitle || <?= json_encode($supplierLang('delete.supplierTitle')) ?>;
            document.getElementById('deleteSupplierModalMessage').textContent = trigger.dataset.deleteMessage || <?= json_encode($supplierLang('delete.supplierDefaultMessage')) ?>;
        });

        <?php if (($modalState ?? null) === 'supplier-form-modal') : ?>
            new bootstrap.Modal(document.getElementById('supplierFormModal')).show();
        <?php elseif (($modalState ?? null) === 'supplier-import-modal') : ?>
            new bootstrap.Modal(document.getElementById('supplierImportModal')).show();
        <?php endif ?>
    })();
</script>
<?= $this->endSection() ?>
