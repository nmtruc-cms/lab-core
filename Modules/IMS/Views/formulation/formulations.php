<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $fPager = $fPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]]; ?>
<?php $fQuery  = $fQuery  ?? ['page' => 1, 'per_page' => 10, 'q' => '']; ?>
<?php
$fmLang = static fn (string $key, array $args = []): string => lang('IMS.formulations.' . $key, $args);
$buildFUrl = static function (array $overrides = []) use ($fQuery): string {
    return site_url('ims/formulations?' . http_build_query(array_merge($fQuery, $overrides)));
};
$statusCfg = [
    'active'   => ['label' => $fmLang('status.active'),   'class' => 'neutral'],
    'approved' => ['label' => $fmLang('status.approved'), 'class' => 'success'],
    'depleted' => ['label' => $fmLang('status.depleted'), 'class' => ''],
    'expired'  => ['label' => $fmLang('status.expired'),  'class' => 'danger'],
];
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3><?= esc($fmLang('title')) ?></h3>
            <span><?= esc($fmLang('list.subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.stock.adjust')) : ?>
            <div class="ims-toolbar-actions">
                <a class="admin-btn primary" href="<?= esc(site_url('ims/formulations/create')) ?>">
                    <i class="fa-solid fa-plus"></i> <?= esc($fmLang('actions.add')) ?>
                </a>
            </div>
        <?php endif ?>
    </div>

    <div style="padding: 0 20px 16px 20px;">
        <form method="get" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="per_page" value="<?= esc((string) $fQuery['per_page']) ?>">
            <div class="admin-form-group" style="flex:1;margin:0;">
                <input type="search" name="q" value="<?= esc($fQuery['q'] ?? '') ?>" placeholder="<?= esc($fmLang('list.searchPlaceholder')) ?>">
            </div>
            <?php if (($fQuery['q'] ?? '') !== '') : ?>
                <a href="<?= esc(site_url('ims/formulations?' . http_build_query(['page' => 1, 'per_page' => $fQuery['per_page']]))) ?>" class="admin-btn secondary"><?= esc($fmLang('common.clear')) ?></a>
            <?php endif ?>
        </form>
    </div>

    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($fmLang('columns.lot')) ?></th>
                        <th><?= esc($fmLang('columns.nameType')) ?></th>
                        <th><?= esc($fmLang('columns.qty')) ?></th>
                        <th><?= esc($fmLang('columns.concentration')) ?></th>
                        <th><?= esc($fmLang('columns.status')) ?></th>
                        <th><?= esc($fmLang('columns.location')) ?></th>
                        <th><?= esc($fmLang('columns.prepared')) ?></th>
                        <th><?= esc($fmLang('columns.expiry')) ?></th>
                        <th><?= esc($fmLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="9" class="admin-muted"><?= esc($fmLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <?php $sc = $statusCfg[$row['status'] ?? 'active'] ?? $statusCfg['active']; ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($fmLang('columns.lot')) ?>"><strong><?= esc($row['formulation_lot']) ?></strong></td>
                                <td data-label="<?= esc($fmLang('columns.nameType')) ?>">
                                    <strong><?= esc($row['name']) ?></strong>
                                    <div class="admin-muted"><?= esc($row['formulation_type']) ?></div>
                                </td>
                                <td data-label="<?= esc($fmLang('columns.qty')) ?>">
                                    <strong><?= esc((string) $row['qty']) ?></strong> <span class="admin-muted"><?= esc($row['unit_name'] ?? '') ?></span>
                                </td>
                                <td data-label="<?= esc($fmLang('columns.concentration')) ?>">
                                    <?php if (! empty($row['concentration'])) : ?>
                                        <strong><?= esc((string) $row['concentration']) ?></strong>
                                        <span class="admin-muted"><?= esc($row['concentration_unit_name'] ?? '') ?></span>
                                    <?php else : ?>
                                        <span class="admin-muted">-</span>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($fmLang('columns.status')) ?>">
                                    <span class="admin-badge <?= esc($sc['class']) ?>"><?= esc($sc['label']) ?></span>
                                </td>
                                <td data-label="<?= esc($fmLang('columns.location')) ?>">
                                    <?php if (! empty($row['storage_location_name'])) : ?>
                                        <span><?= esc($row['storage_location_name']) ?></span>
                                    <?php else : ?>
                                        <span class="admin-muted">-</span>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($fmLang('columns.prepared')) ?>">
                                    <?= esc($row['prepared_date'] ?? '-') ?>
                                    <?php if (! empty($row['prepared_by_name'])) : ?>
                                        <div class="admin-muted"><?= esc($row['prepared_by_name']) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($fmLang('columns.expiry')) ?>"><?= esc($row['expired_date'] ?? '-') ?></td>
                                <td data-label="<?= esc($fmLang('columns.action')) ?>">
                                    <div class="ims-action-group">
                                        <?php if (lab_core_can('ims.stock.adjust')) : ?>
                                            <a class="ims-action-btn" title="<?= esc($fmLang('actions.edit')) ?>"
                                                href="<?= esc(site_url('ims/formulations/' . $row['id'] . '/edit')) ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                            <button type="button" class="ims-action-btn danger" title="<?= esc($fmLang('actions.delete')) ?>"
                                                data-bs-toggle="modal" data-bs-target="#deleteFormulationModal"
                                                data-delete-message="<?= esc($fmLang('delete.message', [$row['formulation_lot'] . ' - ' . $row['name']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/formulations/' . $row['id'] . '/delete')) ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif ?>
                                        <a class="ims-action-btn" title="<?= esc($fmLang('actions.printLabel')) ?>" href="<?= esc(site_url('ims/formulations/' . $row['id'] . '/label')) ?>" target="_blank" rel="noopener">
                                            <i class="fa-solid fa-tag"></i>
                                        </a>
                                        <a class="ims-action-btn" title="<?= esc($fmLang('actions.printSmallLabel')) ?>" href="<?= esc(site_url('ims/formulations/' . $row['id'] . '/label-3x2')) ?>" target="_blank" rel="noopener">
                                            <i class="fa-solid fa-vial"></i>
                                        </a>
                                        <a class="ims-action-btn" title="<?= esc($fmLang('actions.viewDetail')) ?>" href="<?= esc(site_url('ims/formulations/' . $row['id'])) ?>">
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

        <div class="results-footer">
            <div class="result-count">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <strong><?= esc((string) $fPager['total']) ?></strong>&nbsp;<?= esc($fmLang('list.records')) ?>
                <span class="admin-muted"><?= $fPager['total'] > 0 ? '(' . esc((string) $fPager['from']) . '-' . esc((string) $fPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= esc($fQuery['q'] ?? '') ?>">
                <span><?= esc($fmLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $opt) : ?>
                        <option value="<?= $opt ?>" <?= (int) $fPager['perPage'] === $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($fPager['page'] > 1) : ?><a class="page-btn" href="<?= esc($buildFUrl(['page' => $fPager['page'] - 1])) ?>">&lsaquo;</a><?php endif ?>
                <?php foreach ($fPager['pages'] as $pg) : ?><a class="page-btn <?= (int) $fPager['page'] === $pg ? 'active' : '' ?>" href="<?= esc($buildFUrl(['page' => $pg])) ?>"><?= esc((string) $pg) ?></a><?php endforeach ?>
                <?php if ($fPager['page'] < $fPager['totalPages']) : ?><a class="page-btn" href="<?= esc($buildFUrl(['page' => $fPager['page'] + 1])) ?>">&rsaquo;</a><?php endif ?>
            </div>
        </div>
    </div>
</div>

<!-- Delete Formulation -->
<div class="modal fade" id="deleteFormulationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteFormulationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $fQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $fQuery['per_page']) ?>">
                <div class="modal-header"><h5 class="modal-title"><?= esc($fmLang('delete.title')) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($fmLang('common.close')) ?>"></button></div>
                <div class="modal-body"><p id="deleteFormulationMsg"><?= esc($fmLang('delete.defaultMessage')) ?></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($fmLang('common.cancel')) ?></button><button type="submit" class="btn btn-danger"><?= esc($fmLang('common.delete')) ?></button></div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
(() => {
    document.getElementById('deleteFormulationModal')?.addEventListener('show.bs.modal', (e) => {
        const t = e.relatedTarget;
        if (!t) return;
        document.getElementById('deleteFormulationForm').action = t.dataset.deleteAction;
        document.getElementById('deleteFormulationMsg').textContent = t.dataset.deleteMessage || <?= json_encode($fmLang('delete.shortDefault')) ?>;
    });
})();
</script>
<?= $this->endSection() ?>
