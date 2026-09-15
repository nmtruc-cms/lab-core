<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\Core\Views\admin\partials\page_header') ?>

<div class="results-card">
    <div class="admin-card-head">
        <h3>Dynamic Role Base</h3>
        <span>Default registration group: <strong><?= esc((string) $defaultGroup) ?></strong></span>
    </div>
    <div class="admin-role-grid">
        <?php foreach ($groups as $alias => $group) : ?>
            <div class="admin-role-card">
                <h4><?= esc($group['title']) ?></h4>
                <span class="admin-chip"><?= esc($alias) ?></span>
                <p><?= esc($group['description']) ?></p>
                <div class="admin-role-perms">
                    <?php foreach (($matrix[$alias] ?? []) as $permission) : ?>
                        <span class="admin-chip muted"><?= esc($permission) ?></span>
                    <?php endforeach ?>
                </div>
            </div>
        <?php endforeach ?>
    </div>
</div>

<div class="results-card">
    <div class="admin-card-head">
        <h3>Permission Catalog</h3>
        <span>Atomic permissions used by route filters and `can()` checks.</span>
    </div>
    <div style="overflow-x:auto;">
        <table class="results-table admin-table">
            <thead>
                <tr>
                    <th>Permission</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permissions as $permission => $description) : ?>
                    <tr class="main-row">
                        <td><span class="tool-id-badge"><?= esc($permission) ?></span></td>
                        <td><?= esc($description) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
