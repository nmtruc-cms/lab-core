<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<div class="admin-stat-grid">
    <?php foreach ($stats as $stat) : ?>
        <div class="admin-stat-card <?= esc($stat['accent']) ?>">
            <span><?= esc($stat['label']) ?></span>
            <strong><?= esc((string) $stat['value']) ?></strong>
        </div>
    <?php endforeach ?>
</div>

<div class="admin-grid two-col">
    <div class="results-card">
        <div class="admin-card-head">
            <h3><?= esc(lang('IMS.dashboard.recentTransactions.title')) ?></h3>
            <span><?= esc(lang('IMS.dashboard.recentTransactions.subtitle')) ?></span>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table">
                <thead>
                    <tr>
                        <th><?= esc(lang('IMS.dashboard.recentTransactions.columns.no')) ?></th>
                        <th><?= esc(lang('IMS.dashboard.recentTransactions.columns.type')) ?></th>
                        <th><?= esc(lang('IMS.dashboard.recentTransactions.columns.item')) ?></th>
                        <th><?= esc(lang('IMS.dashboard.recentTransactions.columns.qty')) ?></th>
                        <th><?= esc(lang('IMS.dashboard.recentTransactions.columns.date')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recentTransactions === []) : ?>
                        <tr class="main-row"><td colspan="5" class="admin-muted"><?= esc(lang('IMS.dashboard.recentTransactions.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($recentTransactions as $row) : ?>
                            <tr class="main-row">
                                <td><?= esc($row['transaction_no']) ?></td>
                                <td><span class="admin-chip"><?= esc($row['transaction_type']) ?></span></td>
                                <td><?= esc($row['item_name'] ?? '-') ?></td>
                                <td><?= esc((string) $row['qty']) ?> <?= esc($row['unit_name'] ?? '') ?></td>
                                <td><?= esc($row['transaction_date']) ?></td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="results-card">
        <div class="admin-card-head">
            <h3><?= esc(lang('IMS.dashboard.lotAttention.title')) ?></h3>
            <span><?= esc(lang('IMS.dashboard.lotAttention.subtitle')) ?></span>
        </div>
        <div class="admin-link-list compact">
            <?php if ($lotAlerts === []) : ?>
                <div class="admin-link-item static">
                    <strong><?= esc(lang('IMS.dashboard.lotAttention.emptyTitle')) ?></strong>
                    <span><?= esc(lang('IMS.dashboard.lotAttention.emptyText')) ?></span>
                </div>
            <?php else : ?>
                <?php foreach ($lotAlerts as $lot) : ?>
                    <div class="admin-link-item static">
                        <strong><?= esc($lot['item_name'] ?? lang('IMS.dashboard.common.unknownItem')) ?></strong>
                        <span>
                            <?= esc(lang('IMS.dashboard.common.lot')) ?> <?= esc($lot['internal_lot_no'] ?? '-') ?>
                            &middot; <?= esc(lang('IMS.dashboard.common.expiry')) ?> <?= esc($lot['expiry_date'] ?? '-') ?>
                            &middot; <?= esc(lang('IMS.dashboard.common.qty')) ?> <?= esc((string) $lot['current_qty']) ?>
                        </span>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>
</div>

<div class="admin-grid two-col">
    <div class="results-card">
        <div class="admin-card-head">
            <h3><?= esc(lang('IMS.dashboard.requestPipeline.title')) ?></h3>
            <span><?= esc(lang('IMS.dashboard.requestPipeline.subtitle')) ?></span>
        </div>
        <div class="admin-link-list compact">
            <?php if ($requestSummary === []) : ?>
                <div class="admin-link-item static">
                    <strong><?= esc(lang('IMS.dashboard.requestPipeline.emptyTitle')) ?></strong>
                    <span><?= esc(lang('IMS.dashboard.requestPipeline.emptyText')) ?></span>
                </div>
            <?php else : ?>
                <?php foreach ($requestSummary as $request) : ?>
                    <div class="admin-link-item static">
                        <strong><?= esc($request['request_name']) ?></strong>
                        <span>
                            <?= esc(lang('IMS.dashboard.common.status')) ?> <?= esc($request['status']) ?>
                            &middot; <?= esc(lang('IMS.dashboard.common.created')) ?> <?= esc($request['created_date'] ?? '-') ?>
                        </span>
                    </div>
                <?php endforeach ?>
            <?php endif ?>
        </div>
    </div>

    <div class="results-card">
        <div class="admin-card-head">
            <h3><?= esc(lang('IMS.dashboard.nextSteps.title')) ?></h3>
            <span><?= esc(lang('IMS.dashboard.nextSteps.subtitle')) ?></span>
        </div>
        <div class="admin-note-list">
            <?php foreach (['masterData', 'flows', 'operations'] as $noteKey) : ?>
                <div class="admin-note-item"><?= esc(lang('IMS.dashboard.nextSteps.items.' . $noteKey)) ?></div>
            <?php endforeach ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
