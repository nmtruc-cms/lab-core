<?= $this->extend(setting('Auth.views')['layout']) ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-card-header">
        <span class="auth-kicker">REGISTER</span>
        <h2>Create an account</h2>
        <p>Initial registration is available for controlled setup and implementation.</p>
    </div>

    <?php if (session('errors') !== null) : ?>
        <div class="admin-alert danger">
            <?php foreach (session('errors') as $error) : ?>
                <div><?= esc($error) ?></div>
            <?php endforeach ?>
        </div>
    <?php endif ?>

    <form action="<?= site_url('register') ?>" method="post" class="auth-form">
        <?= csrf_field() ?>
        <div class="admin-form-grid single">
            <div class="admin-form-group">
                <label for="register-username">Username</label>
                <input id="register-username" name="username" type="text" value="<?= old('username') ?>" required>
            </div>
            <div class="admin-form-group">
                <label for="register-email">Email</label>
                <input id="register-email" name="email" type="email" value="<?= old('email') ?>" required>
            </div>
            <div class="admin-form-group">
                <label for="register-password">Password</label>
                <input id="register-password" name="password" type="password" required>
            </div>
            <div class="admin-form-group">
                <label for="register-password-confirm">Confirm Password</label>
                <input id="register-password-confirm" name="password_confirm" type="password" required>
            </div>
        </div>
        <div class="auth-form-footer">
            <button type="submit" class="admin-btn primary">Create Account</button>
            <a class="auth-inline-link" href="<?= site_url('login') ?>">Back to sign in</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
