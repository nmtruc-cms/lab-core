<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\Core\Views\admin\partials\page_header') ?>

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
            <h3>Quick Links</h3>
            <span>Start from the most important admin areas.</span>
        </div>
        <div class="admin-link-list">
            <?php foreach ($quickLinks as $link) : ?>
                <a class="admin-link-item" href="<?= esc($link['url']) ?>">
                    <strong><?= esc($link['label']) ?></strong>
                    <span><?= esc($link['description']) ?></span>
                </a>
            <?php endforeach ?>
        </div>
    </div>

    <div class="results-card">
        <div class="admin-card-head">
            <h3>Implementation Notes</h3>
            <span>The current setup already follows the Core + IMS modular direction.</span>
        </div>
        <div class="admin-note-list">
            <div class="admin-note-item">Use `super_user@lab-core.com` to validate every admin screen and permission path.</div>
            <div class="admin-note-item">Roles are backed by Shield settings, so labs can rename or reshape role bases without rewriting authorization checks.</div>
            <div class="admin-note-item">Departments and company profile now live in Core, ready to support user administration and future modules.</div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
