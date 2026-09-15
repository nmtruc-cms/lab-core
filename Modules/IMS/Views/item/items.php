<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $itemPager = $itemPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]]; ?>
<?php $itemQuery = $itemQuery ?? ['page' => 1, 'per_page' => 10, 'q' => '']; ?>
<?php
$itemLang = static fn (string $key, array $args = []): string => lang('IMS.items.' . $key, $args);

$buildItemUrl = static function (array $overrides = []) use ($itemQuery): string {
    return site_url('ims/items?' . http_build_query(array_merge($itemQuery, $overrides)));
};
$itemStatusClass = static function (?string $status): string {
    return match (strtolower((string) $status)) {
        'active' => 'success',
        'blocked' => 'danger',
        'obsolete' => 'warning',
        default => 'neutral',
    };
};
$formatStockValue = static function (mixed $value): ?string {
    if ($value === null || $value === '') {
        return null;
    }

    $text = (string) $value;

    if (str_contains($text, '.')) {
        $text = rtrim(rtrim($text, '0'), '.');
    }

    return $text === '' ? '0' : $text;
};
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3><?= esc(lang('IMS.items.title')) ?></h3>
            <span><?= esc($itemLang('list.subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.items.create')) : ?>
            <div class="ims-toolbar-actions">
                <a href="<?= site_url('ims/items/create') ?>" class="admin-btn primary"><?= (string) lang('IMS.items.add') ?></a>
            </div>
        <?php endif ?>
    </div>

    <div style="padding: 0 20px 16px 20px;">
        <form method="get" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="per_page" value="<?= esc((string) $itemQuery['per_page']) ?>">
            <div class="admin-form-group" style="flex:1;margin:0;">
                <input type="search" name="q" value="<?= esc($itemQuery['q'] ?? '') ?>" placeholder="<?= esc($itemLang('list.searchPlaceholder')) ?>">
            </div>
            <?php if (($itemQuery['q'] ?? '') !== '') : ?>
                <a href="<?= esc(site_url('ims/items?' . http_build_query(['page' => 1, 'per_page' => $itemQuery['per_page']]))) ?>" class="admin-btn secondary"><?= esc($itemLang('common.clear')) ?></a>
            <?php endif ?>
        </form>
    </div>

    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th style="min-width:220px"><?= esc($itemLang('columns.itemName')) ?></th>
                        <th style="min-width:110px;white-space:nowrap"><?= esc($itemLang('columns.casNo')) ?></th>
                        <th><?= esc($itemLang('columns.category')) ?></th>
                        <th><?= esc($itemLang('columns.unit')) ?></th>
                        <th style="min-width:80px;white-space:nowrap"><?= esc($itemLang('columns.minStock')) ?></th>
                        <th style="min-width:80px;white-space:nowrap"><?= esc($itemLang('columns.maxStock')) ?></th>
                        <th><?= esc($itemLang('columns.controls')) ?></th>
                        <th><?= esc($itemLang('columns.status')) ?></th>
                        <th><?= esc($itemLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="10" class="admin-muted"><?= esc($itemLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <?php
                            $minStock = $formatStockValue($row['min_stock_level'] ?? null);
                            $maxStock = $formatStockValue($row['max_stock_level'] ?? null);
                            /** @var string $itemName */
                            $itemName = $row['item_name'] ?? '';
                            /** @var string $itemCode */
                            $itemCode = $row['item_code'] ?? '';
                            /** @var string $altName */
                            $altName = $row['alternate_name'] ?? '';
                            /** @var string $casNo */
                            $casNo = $row['cas_no'] ?? '';
                            ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($itemLang('columns.item')) ?>">
                                    <strong><?= esc($itemName) ?></strong>
                                    <div class="admin-muted" style="font-size:0.82em"><?= esc($itemCode ?: '-') ?></div>
                                    <?php if ($altName !== '') : ?>
                                        <div class="admin-muted" style="font-size:0.82em;font-style:italic"><?= esc($altName) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($itemLang('columns.casNo')) ?>" class="admin-muted" style="font-size:0.9em"><?= esc($casNo ?: '-') ?></td>
                                <td data-label="<?= esc($itemLang('columns.category')) ?>"><?= esc($row['category_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($itemLang('columns.unit')) ?>"><?= esc($row['unit_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($itemLang('columns.minStock')) ?>" class="admin-muted" style="font-size:0.9em;white-space:nowrap"><?= $minStock !== null ? esc($minStock) : '-' ?></td>
                                <td data-label="<?= esc($itemLang('columns.maxStock')) ?>" class="admin-muted" style="font-size:0.9em;white-space:nowrap"><?= $maxStock !== null ? esc($maxStock) : '-' ?></td>
                                <td data-label="<?= esc($itemLang('columns.controls')) ?>">
                                    <div class="ims-brand-chip-list">
                                        <?php if ((int) ($row['requires_lot_tracking'] ?? 0) === 1) : ?><span class="admin-chip muted"><?= esc($itemLang('controls.lot')) ?></span><?php endif ?>
                                        <?php if ((int) ($row['requires_expiry_tracking'] ?? 0) === 1) : ?><span class="admin-chip muted"><?= esc($itemLang('controls.expiry')) ?></span><?php endif ?>
                                        <?php if ((int) ($row['requires_coa'] ?? 0) === 1) : ?><span class="admin-chip muted"><?= esc($itemLang('controls.coa')) ?></span><?php endif ?>
                                        <?php if ((int) ($row['is_controlled_substance'] ?? 0) === 1) : ?><span class="admin-chip muted"><?= esc($itemLang('controls.controlled')) ?></span><?php endif ?>
                                    </div>
                                </td>
                                <td data-label="<?= esc($itemLang('columns.status')) ?>">
                                    <span class="admin-badge <?= esc($itemStatusClass($row['status'] ?? 'active')) ?>"><?= esc($itemLang('status.' . strtolower((string) ($row['status'] ?? 'active')))) ?></span>
                                </td>
                                <td data-label="<?= esc($itemLang('columns.action')) ?>">
                                    <div class="ims-action-group">
                                        <a class="ims-action-btn" title="<?= esc($itemLang('actions.viewDetail')) ?>" href="<?= esc(site_url('ims/items/' . $row['id'])) ?>">
                                            <i class="fa-solid fa-arrow-right"></i>
                                        </a>
                                        <?php if (lab_core_can('ims.items.update')) : ?>
                                            <a class="ims-action-btn" title="<?= esc($itemLang('actions.editItem')) ?>" href="<?= esc(site_url('ims/items/' . $row['id'] . '/edit')) ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        <?php endif ?>
                                        <?php if (lab_core_can('ims.items.delete')) : ?>
                                            <button type="button" class="ims-action-btn danger" title="<?= esc($itemLang('actions.deleteItem')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteItemModal"
                                                data-delete-message="<?= esc($itemLang('delete.itemMessage', [$row['item_name']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/items/' . $row['id'] . '/delete')) ?>">
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                <strong><?= esc((string) $itemPager['total']) ?></strong>&nbsp;<?= esc($itemLang('list.records')) ?>
                <span class="admin-muted"><?= $itemPager['total'] > 0 ? '(' . esc((string) $itemPager['from']) . '-' . esc((string) $itemPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= esc($itemQuery['q'] ?? '') ?>">
                <span><?= esc($itemLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $itemPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($itemPager['page'] > 1) : ?><a class="page-btn" href="<?= esc($buildItemUrl(['page' => $itemPager['page'] - 1])) ?>">&lsaquo;</a><?php endif ?>
                <?php foreach ($itemPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $itemPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildItemUrl(['page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($itemPager['page'] < $itemPager['totalPages']) : ?><a class="page-btn" href="<?= esc($buildItemUrl(['page' => $itemPager['page'] + 1])) ?>">&rsaquo;</a><?php endif ?>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="deleteItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteItemForm">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $itemQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $itemQuery['per_page']) ?>">
                <div class="modal-header"><h5 class="modal-title"><?= esc($itemLang('delete.itemTitle')) ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($itemLang('common.close')) ?>"></button></div>
                <div class="modal-body"><p id="deleteItemModalMessage"><?= esc($itemLang('delete.itemFallback')) ?></p></div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($itemLang('common.cancel')) ?></button><button type="submit" class="btn btn-danger"><?= esc($itemLang('common.delete')) ?></button></div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
(() => {
    const deleteModal = document.getElementById('deleteItemModal');
    deleteModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        document.getElementById('deleteItemForm').action = trigger.dataset.deleteAction;
        document.getElementById('deleteItemModalMessage').textContent = trigger.dataset.deleteMessage || <?= json_encode($itemLang('delete.itemFallback')) ?>;
    });
})();
</script>
<?= $this->endSection() ?>
