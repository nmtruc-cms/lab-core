<?php
$lot = $lot ?? [];
$lotLang = static fn (string $key, array $args = []): string => lang('IMS.lots.' . $key, $args);

$value = static function (string $key, string $fallback = '-') use ($lot): string {
    $raw = trim((string) ($lot[$key] ?? ''));

    return $raw !== '' ? $raw : $fallback;
};

$lotNo = trim((string) ($lot['internal_lot_no'] ?? ''));
if ($lotNo === '') {
    $lotNo = trim((string) ($lot['lot_no'] ?? ''));
}
if ($lotNo === '') {
    $lotNo = '#' . (string) ($lot['id'] ?? '-');
}

$qty = trim((string) ($lot['current_qty'] ?? ''));
$unit = trim((string) ($lot['unit_name'] ?? ''));
$qtyText = trim($qty . ' ' . $unit) ?: '-';

$concentration = trim((string) ($lot['concentration_value'] ?? ''));
$concentrationUnit = trim((string) ($lot['concentration_unit'] ?? ''));
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
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 5cm;
            height: 3cm;
            margin: 0;
            padding: 0;
            color: #111827;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 5.6pt;
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
            margin-bottom: 0.025cm;
        }

        .lot {
            height: 0.3cm;
            overflow: hidden;
            font-size: 7.6pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .item {
            height: 0.28cm;
            overflow: hidden;
            font-size: 5.7pt;
            font-weight: 700;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 0.004cm 0;
            vertical-align: top;
        }

        .key {
            width: 0.82cm;
            color: #4b5563;
            font-size: 4.7pt;
            white-space: nowrap;
        }

        .val {
            font-size: 5.2pt;
            font-weight: 700;
        }

        .footer {
            margin-top: 0.018cm;
            padding-top: 0.018cm;
            border-top: 0.02cm solid #d1d5db;
            color: #374151;
            font-size: 4.6pt;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="label">
        <div class="top">
            <div class="lot"><?= esc($lotNo) ?></div>
            <div class="item"><?= esc($value('item_name')) ?></div>
        </div>

        <table class="meta">
            <tr>
                <td class="key"><?= esc($lotLang('label.code')) ?></td>
                <td class="val"><?= esc($value('item_code')) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($lotLang('label.qty')) ?></td>
                <td class="val"><?= esc($qtyText) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($lotLang('label.conc')) ?></td>
                <td class="val"><?= esc($concentrationText) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($lotLang('label.recv')) ?></td>
                <td class="val"><?= esc($value('received_date')) ?></td>
            </tr>
            <tr>
                <td class="key"><?= esc($lotLang('label.exp')) ?></td>
                <td class="val"><?= esc($value('expiry_date')) ?></td>
            </tr>
        </table>

        <div class="footer">
            <?= esc($lotLang('label.loc')) ?>: <?= esc($value('storage_location_name')) ?> | <?= esc($lotLang('label.supp')) ?>: <?= esc($value('supplier_name')) ?>
        </div>
    </div>
</body>
</html>
