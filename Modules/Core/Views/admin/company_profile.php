<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\Core\Views\admin\partials\page_header') ?>

<?php if ($message) : ?>
    <div class="admin-alert success"><?= esc($message) ?></div>
<?php endif ?>

<div class="results-card">
    <div class="admin-card-head">
        <h3>Organization Details</h3>
        <span>Used across reports, headers, auth branding, and future documentation flows.</span>
    </div>
    <form method="post" action="<?= site_url('admin/company-profile') ?>" class="admin-form">
        <?= csrf_field() ?>
        <div class="admin-form-grid">
            <div class="admin-form-group">
                <label for="name_header">Header Name</label>
                <input id="name_header" name="name_header" type="text" value="<?= esc(old('name_header', $profile['name_header'] ?? '')) ?>">
            </div>
            <div class="admin-form-group">
                <label for="name">Company Name</label>
                <input id="name" name="name" type="text" value="<?= esc(old('name', $profile['name'] ?? '')) ?>">
                <?php if (isset($validation['name'])) : ?>
                    <small class="admin-error"><?= esc($validation['name']) ?></small>
                <?php endif ?>
            </div>
            <div class="admin-form-group">
                <label for="vat_code">VAT Code</label>
                <input id="vat_code" name="vat_code" type="text" value="<?= esc(old('vat_code', $profile['vat_code'] ?? '')) ?>">
            </div>
            <div class="admin-form-group">
                <label for="phone">Phone</label>
                <input id="phone" name="phone" type="text" value="<?= esc(old('phone', $profile['phone'] ?? '')) ?>">
            </div>
            <div class="admin-form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= esc(old('email', $profile['email'] ?? '')) ?>">
                <?php if (isset($validation['email'])) : ?>
                    <small class="admin-error"><?= esc($validation['email']) ?></small>
                <?php endif ?>
            </div>
            <div class="admin-form-group">
                <label for="web">Website</label>
                <input id="web" name="web" type="text" value="<?= esc(old('web', $profile['web'] ?? '')) ?>">
            </div>
            <div class="admin-form-group full">
                <label for="logo">Logo URL or Path</label>
                <input id="logo" name="logo" type="text" value="<?= esc(old('logo', $profile['logo'] ?? '')) ?>">
            </div>
            <div class="admin-form-group full">
                <label for="address">Address</label>
                <textarea id="address" name="address" rows="4"><?= esc(old('address', $profile['address'] ?? '')) ?></textarea>
            </div>
        </div>
        <button type="submit" class="admin-btn primary">Save Company Profile</button>
    </form>
</div>
<?= $this->endSection() ?>
