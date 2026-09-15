<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    $mainCssVersion = (string) @filemtime(FCPATH . 'assets/css/main.css');
    $mainJsVersion = (string) @filemtime(FCPATH . 'assets/js/main.js');
    ?>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($pageTitle ?? 'SQS Lab Core') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/vendor/fontawesome/css/all.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/css/main.css') ?>?v=<?= esc($mainCssVersion) ?>" rel="stylesheet" />
    <?= $this->renderSection('page_css') ?>
    <?= $this->renderSection('page_head') ?>
</head>

<body>

    <?= $this->include('components/main-topnav.php') ?>
    <?= $this->include('components/main-sidebar.php') ?>


    <!-- ── MAIN ── -->
    <div class="main-wrap" id="mainWrap">
        <div class="content-area">
            <?= $this->renderSection('content') ?>

        </div>
        <?= $this->include('components/main-footer.php') ?>
    </div>


    <script src="<?= base_url('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/main.js') ?>?v=<?= esc($mainJsVersion) ?>"></script>
    <?= $this->renderSection('page_js') ?>
    <script>document.querySelectorAll('.ims-action-btn[title]').forEach(el => new bootstrap.Tooltip(el));</script>
</body>

</html>
