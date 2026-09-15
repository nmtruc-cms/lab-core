<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $rows = $rows ?? []; ?>
<?php $lotPager = $lotPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]]; ?>
<?php $lotQuery = $lotQuery ?? ['page' => 1, 'per_page' => 10, 'q' => '']; ?>
<?php
$lotLang = static fn (string $key, array $args = []): string => lang('IMS.lots.' . $key, $args);
$buildLotUrl = static function (array $overrides = []) use ($lotQuery): string {
    return site_url('ims/lots?' . http_build_query(array_merge($lotQuery, $overrides)));
};
$ownershipClass = static function (?string $status): string {
    return match (strtolower((string) $status)) {
        'consigned' => 'warning',
        'borrowed'  => 'neutral',
        default     => 'success',
    };
};
?>

<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head" style="padding-left:0;">
            <h3><?= esc($lotLang('title')) ?></h3>
            <span><?= esc($lotLang('list.subtitle')) ?></span>
        </div>
    </div>

    <div style="padding: 0 20px 16px 20px;">
        <form method="get" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="per_page" value="<?= esc((string) $lotQuery['per_page']) ?>">
            <div class="admin-form-group" style="flex:1;margin:0;">
                <input type="search" name="q" value="<?= esc($lotQuery['q'] ?? '') ?>" placeholder="<?= esc($lotLang('list.searchPlaceholder')) ?>">
            </div>
            <?php if (($lotQuery['q'] ?? '') !== '') : ?>
                <a href="<?= esc(site_url('ims/lots?' . http_build_query(['page' => 1, 'per_page' => $lotQuery['per_page']]))) ?>" class="admin-btn secondary"><?= esc($lotLang('common.clear')) ?></a>
            <?php endif ?>
        </form>
    </div>

    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($lotLang('columns.item')) ?></th>
                        <th><?= esc($lotLang('columns.lot')) ?></th>
                        <th><?= esc($lotLang('columns.dates')) ?></th>
                        <th><?= esc($lotLang('columns.qty')) ?></th>
                        <th><?= esc($lotLang('columns.location')) ?></th>
                        <th><?= esc($lotLang('columns.supplier')) ?></th>
                        <th><?= esc($lotLang('columns.spec')) ?></th>
                        <th><?= esc($lotLang('columns.ownership')) ?></th>
                        <th><?= esc($lotLang('columns.status')) ?></th>
                        <th><?= esc($lotLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="10" class="admin-muted"><?= esc($lotLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($lotLang('columns.item')) ?>">
                                    <strong><?= esc($row['item_name'] ?? '-') ?></strong>
                                    <div class="admin-muted"><?= esc($row['item_code'] ?? '-') ?></div>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.lot')) ?>">
                                    <strong><?= esc($row['internal_lot_no'] ?: ($row['lot_no'] ?: '-')) ?></strong>
                                    <div class="admin-muted"><?= esc($row['supplier_lot_no'] ?: ($row['serial_no'] ?: '-')) ?></div>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.dates')) ?>">
                                    <strong><?= esc($lotLang('labels.exp')) ?>: <?= esc($row['expiry_date'] ?? '-') ?></strong>
                                    <div class="admin-muted"><?= esc($lotLang('labels.received')) ?>: <?= esc($row['received_date'] ?? '-') ?></div>
                                    <div class="admin-muted"><?= esc($lotLang('labels.opened')) ?>: <?= esc($row['opened_date'] ?? '-') ?></div>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.qty')) ?>">
                                    <strong><?= esc((string) ($row['current_qty'] ?? '0')) ?> <?= esc($row['unit_name'] ?? '') ?></strong>
                                    <div class="admin-muted"><?= esc($lotLang('labels.initial')) ?>: <?= esc((string) ($row['initial_qty'] ?? '0')) ?> <?= esc($row['initial_unit_name'] ?? '') ?></div>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.location')) ?>"><?= esc($row['storage_location_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($lotLang('columns.supplier')) ?>"><?= esc($row['supplier_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($lotLang('columns.spec')) ?>">
                                    <strong><?= esc($row['brand_name'] ?? '-') ?></strong>
                                    <div class="admin-muted"><?= esc($row['catalog_no'] ?: ($row['grade'] ?: '-')) ?></div>
                                    <div class="admin-muted"><?= esc($row['pack_size'] ?: '-') ?></div>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.ownership')) ?>">
                                    <span class="admin-badge <?= esc($ownershipClass($row['ownership_status'] ?? 'owned')) ?>"><?= esc(ucfirst((string) ($row['ownership_status'] ?? 'owned'))) ?></span>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.status')) ?>">
                                    <?php
                                    $expiryDate = $row['expiry_date'] ?? '';
                                    $isExpired  = $expiryDate !== '' && $expiryDate !== null && strtotime($expiryDate) < strtotime('today');
                                    $initialQty = (float) ($row['initial_qty'] ?? 0);
                                    $currentQty = (float) ($row['current_qty'] ?? 0);
                                    $isLowStock = $initialQty > 0 && $currentQty <= 0.03 * $initialQty;
                                    ?>
                                    <?php if ($isExpired) : ?>
                                        <span class="admin-badge danger"><?= esc($lotLang('status.expired')) ?></span>
                                    <?php endif ?>
                                    <?php if ($isLowStock) : ?>
                                        <span class="admin-badge warning"><?= esc($lotLang('status.lowStock')) ?></span>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($lotLang('columns.action')) ?>">
                                    <div class="ims-action-group">
                                        <a class="ims-action-btn" title="<?= esc($lotLang('actions.printLabel')) ?>" href="<?= esc(site_url('ims/lots/' . $row['id'] . '/label')) ?>" target="_blank" rel="noopener">
                                            <i class="fa-solid fa-tag"></i>
                                        </a>
                                        <a class="ims-action-btn" title="<?= esc($lotLang('actions.viewDetail')) ?>" href="<?= esc(site_url('ims/lots/' . $row['id'])) ?>">
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                <strong><?= esc((string) $lotPager['total']) ?></strong>&nbsp;<?= esc($lotLang('list.records')) ?>
                <span class="admin-muted"><?= $lotPager['total'] > 0 ? '(' . esc((string) $lotPager['from']) . '-' . esc((string) $lotPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <input type="hidden" name="q" value="<?= esc($lotQuery['q'] ?? '') ?>">
                <span><?= esc($lotLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $lotPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($lotPager['page'] > 1) : ?><a class="page-btn" href="<?= esc($buildLotUrl(['page' => $lotPager['page'] - 1])) ?>">&lsaquo;</a><?php endif ?>
                <?php foreach ($lotPager['pages'] as $page) : ?><a class="page-btn <?= (int) $lotPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildLotUrl(['page' => $page])) ?>"><?= esc((string) $page) ?></a><?php endforeach ?>
                <?php if ($lotPager['page'] < $lotPager['totalPages']) : ?><a class="page-btn" href="<?= esc($buildLotUrl(['page' => $lotPager['page'] + 1])) ?>">&rsaquo;</a><?php endif ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
