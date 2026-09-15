<?php
$fm = $fm ?? [];
$fmLang = static fn (string $key, array $args = []): string => lang('IMS.formulations.' . $key, $args);

$value = static function (string $key, string $fallback = '-') use ($fm): string {
    $raw = trim((string) ($fm[$key] ?? ''));

    return $raw !== '' ? $raw : $fallback;
};

$qty = trim((string) ($fm['qty'] ?? ''));
$unit = trim((string) ($fm['unit_name'] ?? ''));
$qtyText = trim($qty . ' ' . $unit) ?: '-';

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
            size: 5cm 3cm;
            margin: 0;
        }

        * {
            padding: 0;
            margin: 0;
        }

        html,
        body {
            width: 5cm;
            height: 3cm;
            margin: 0;
            padding: 0;
            color: #111827;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 5.8pt;
            line-height: 1.08;
        }

        .label {
            position: absolute;
            top: 0.14cm;
            right: 0.06cm;
            bottom: 0.06cm;
            left: 0.16cm;
            overflow: hidden;
        }

        .top {
            border-bottom: 0.02cm solid #111827;
            padding-bottom: 0.02cm;
            margin-bottom: 0.02cm;
        }

        .lot {
            font-size: 7.8pt;
            font-weight: 700;
            letter-spacing: 0;
            white-space: nowrap;
            height: 0.3cm;
        }

        .name {
            font-size: 5.8pt;
            font-weight: 700;
            height: 0.27cm;
            overflow: hidden;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 0.003cm 0;
            vertical-align: top;
        }

        .meta .key {
            width: 0.78cm;
            color: #4b5563;
            font-size: 5pt;
            white-space: nowrap;
        }

        .meta .val {
            font-weight: 700;
            font-size: 5.4pt;
        }

        .footer {
            margin-top: 0.015cm;
            padding-top: 0.015cm;
            border-top: 0.02cm solid #d1d5db;
            color: #374151;
            font-size: 4.8pt;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="top">
            <div class="lot"><?= esc($value('formulation_lot')) ?></div>
            <div class="name"><?= esc($value('name')) ?></div>
        </div>

        <table class="meta">
            <tr>
                <td class="key"><?= esc($fmLang('label.type')) ?></td>
                <td class="val"><?= esc($value('formulation_type')) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($fmLang('label.qty')) ?></td>
                <td class="val"><?= esc($qtyText) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($fmLang('label.conc')) ?></td>
                <td class="val"><?= esc($concentrationText) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($fmLang('label.prep')) ?></td>
                <td class="val"><?= esc($value('prepared_date')) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($fmLang('label.exp')) ?></td>
                <td class="val"><?= esc($value('expired_date')) ?></td>
            </tr>
        </table>

        <div class="footer">
            <?= esc($fmLang('label.loc')) ?>: <?= esc($value('storage_location_name')) ?> | <?= esc($fmLang('label.by')) ?>: <?= esc($value('prepared_by_name')) ?>
        </div>
    </div>
</body>
</html>
