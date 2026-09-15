<?= $this->extend(setting('Auth.views')['layout']) ?>

<?= $this->section('content') ?>
<?php $validation = $validation ?? []; ?>

<div class="auth-card">
    <div class="auth-card-header">
        <span class="auth-kicker">ACCOUNT SETUP</span>
        <h2>Set your password</h2>
        <p>Choose a strong password to secure your account. You'll use it to sign in from now on.</p>
    </div>

    <?php if (session('error') !== null) : ?>
        <div class="admin-alert danger"><?= esc(session('error')) ?></div>
    <?php endif ?>

    <form action="<?= site_url('set-password') ?>" method="post" class="auth-form">
        <?= csrf_field() ?>
        <div class="admin-form-grid single">
            <div class="admin-form-group">
                <label for="sp-password">New Password</label>
                <input id="sp-password" name="password" type="password"
                    placeholder="Minimum 8 characters" required autocomplete="new-password">
                <?php if (isset($validation['password'])) : ?>
                    <small class="admin-error"><?= esc($validation['password']) ?></small>
                <?php endif ?>
            </div>
            <div class="admin-form-group">
                <label for="sp-password-confirm">Confirm Password</label>
                <input id="sp-password-confirm" name="password_confirm" type="password"
                    placeholder="Repeat your password" required autocomplete="new-password">
                <?php if (isset($validation['password_confirm'])) : ?>
                    <small class="admin-error"><?= esc($validation['password_confirm']) ?></small>
                <?php endif ?>
            </div>
        </div>
        <div class="auth-form-footer">
            <button type="submit" class="admin-btn primary">Save &amp; Continue</button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
