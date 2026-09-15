<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $mainCssVersion = (string) @filemtime(FCPATH . 'assets/css/main.css');
    $mainJsVersion = (string) @filemtime(FCPATH . 'assets/js/main.js');
    ?>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($title ?? 'Lab Core Auth') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/css/main.css') ?>?v=<?= esc($mainCssVersion) ?>" rel="stylesheet" />
</head>

<body class="auth-shell">
    <div class="auth-stage">
        <div class="auth-brand-panel">
            <span class="auth-kicker">LAB CORE</span>
            <h1><?= esc(lab_core_company_name()) ?></h1>
            <p>Centralized authentication and administration for laboratory, operational, and quality modules.</p>
            <div class="auth-brand-points">
                <div class="auth-brand-point"><span></span>Shield-backed authentication</div>
                <div class="auth-brand-point"><span></span>Dynamic role base per lab deployment</div>
                <div class="auth-brand-point"><span></span>Core + IMS modular platform</div>
            </div>
        </div>
        <div class="auth-panel">
            <?= $this->renderSection('content') ?>
        </div>
    </div>
    <script src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>?v=<?= esc($mainJsVersion) ?>"></script>
</body>

</html>
