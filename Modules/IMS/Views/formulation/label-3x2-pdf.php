<?php
$fm = $fm ?? [];
$fmLang = static fn (string $key, array $args = []): string => lang('IMS.formulations.' . $key, $args);

$value = static function (string $key, string $fallback = '-') use ($fm): string {
    $raw = trim((string) ($fm[$key] ?? ''));

    return $raw !== '' ? $raw : $fallback;
};

$concentration = trim((string) ($fm['concentration'] ?? ''));
$concentrationUnit = trim((string) ($fm['concentration_unit_name'] ?? ''));
$concentrationText = $concentration !== '' ? trim($concentration . ' ' . $concentrationUnit) : '-';
?>
<!doctype html>
<html lang="<?= esc(service('request')->getLocale() ?: 'en') ?>">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: 3cm 2cm;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 3cm;
            height: 2cm;
            margin: 0;
            padding: 0;
            color: #111827;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 4.8pt;
            line-height: 1.05;
        }

        .label {
            position: absolute;
            top: 0.12cm;
            right: 0.05cm;
            bottom: 0.04cm;
            left: 0.14cm;
            overflow: hidden;
        }

        .name {
            height: 0.46cm;
            overflow: hidden;
            font-size: 5.6pt;
            font-weight: 700;
            line-height: 1.05;
        }

        .row {
            margin-top: 0.04cm;
            white-space: nowrap;
        }

        .key {
            display: inline-block;
            width: 0.58cm;
            color: #4b5563;
            font-size: 4.2pt;
        }

        .val {
            font-size: 4.8pt;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="name"><?= esc($value('name')) ?></div>
        <div class="row"><span class="key"><?= esc($fmLang('label.concShort')) ?></span><span class="val"><?= esc($concentrationText) ?></span></div>
        <div class="row"><span class="key"><?= esc($fmLang('label.prep')) ?></span><span class="val"><?= esc($value('prepared_date')) ?></span></div>
        <div class="row"><span class="key"><?= esc($fmLang('label.exp')) ?></span><span class="val"><?= esc($value('expired_date')) ?></span></div>
    </div>
</body>
</html>
