<?= $this->extend(setting('Auth.views')['layout']) ?>

<?= $this->section('content') ?>
<div class="auth-card">
    <div class="auth-card-header">
        <span class="auth-kicker">SIGN IN</span>
        <h2>Welcome back</h2>
        <p>Use one of the seeded implementation accounts to start configuring the platform.</p>
    </div>

    <?php if (session('error') !== null) : ?>
        <div class="admin-alert danger"><?= esc(session('error')) ?></div>
    <?php endif ?>

    <?php if (session('message') !== null) : ?>
        <div class="admin-alert success"><?= esc(session('message')) ?></div>
    <?php endif ?>

    <form action="<?= site_url('login') ?>" method="post" class="auth-form">
        <?= csrf_field() ?>
        <div class="admin-form-grid single">
            <div class="admin-form-group">
                <label for="login-email">Email</label>
                <input id="login-email" name="email" type="email" value="<?= old('email') ?>" placeholder="lab_manager@lab-core.com" required>
            </div>
            <div class="admin-form-group">
                <label for="login-password">Password</label>
                <input id="login-password" name="password" type="password" placeholder="Enter your password" required>
            </div>
        </div>
        <div class="auth-form-footer">
            <button type="submit" class="admin-btn primary">Sign In</button>
            <a class="auth-inline-link" href="<?= site_url('register') ?>">Create account</a>
        </div>
    </form>

    <div class="auth-demo-card">
        <h3>Demo accounts</h3>
        <p>Default password for all seeded users: <strong>123456</strong></p>
        <div class="auth-demo-list">
            <span>super_user@lab-core.com</span>
            <span>lab_manager@lab-core.com</span>
            <span>director@lab-core.com</span>
            <span>qa@lab-core.com</span>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
