<?php
$po = $po ?? [];
$items = $items ?? [];

$poLang = static function (string $key, array $args = [], ?string $fallback = null): string {
    $line = 'IMS.purchaseOrders.' . $key;
    $text = lang($line, $args);

    return $text === $line ? ($fallback ?? $key) : $text;
};
$statusLabel = static function (?string $status) use ($poLang): string {
    $status = (string) ($status ?: 'draft');
    $key = match ($status) {
        'partially_received' => 'partiallyReceived',
        default => $status,
    };

    return $poLang('status.' . $key, [], ucwords(str_replace('_', ' ', $status)));
};
$money = static fn ($value): string => number_format((float) ($value ?? 0), 2);
$date = static function ($value): string {
    $raw = trim((string) ($value ?? ''));

    return $raw !== '' ? $raw : '-';
};

$subtotal = (float) ($po['subtotal'] ?? 0);
if ($subtotal <= 0 && $items !== []) {
    foreach ($items as $item) {
        $subtotal += (float) ($item['line_total'] ?? 0);
    }
}

$tax = (float) ($po['tax_amount'] ?? 0);
$shipping = (float) ($po['shipping_cost'] ?? 0);
$grandTotal = (float) ($po['grand_total'] ?? ($subtotal + $tax + $shipping));
?>
<!doctype html>
<html lang="<?= esc($poLang('pdf.lang')) ?>">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            size: A4 portrait;
            margin: 16mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid #0f766e;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header td {
            vertical-align: top;
        }

        .title {
            color: #0f3f76;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .po-no {
            margin-top: 4px;
            font-size: 12px;
            font-weight: 700;
        }

        .status {
            display: inline-block;
            margin-top: 5px;
            padding: 3px 8px;
            border: 1px solid #0f3f76;
            color: #0f3f76;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 2px 0 2px 8px;
            font-size: 10px;
        }

        .meta .key {
            width: 88px;
            color: #6b7280;
            text-align: right;
        }

        .meta .val {
            font-weight: 700;
            text-align: right;
        }

        .section-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .section-grid td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }

        .box-title {
            margin-bottom: 5px;
            color: #0f3f76;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .box {
            min-height: 76px;
            border: 1px solid #d1d5db;
            padding: 8px;
        }

        .name {
            font-size: 12px;
            font-weight: 700;
        }

        .muted {
            color: #6b7280;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .items th {
            background: #0f3f76;
            color: #fff;
            padding: 7px 6px;
            font-size: 9px;
            text-align: left;
        }

        .items td {
            border-bottom: 1px solid #e5e7eb;
            padding: 7px 6px;
            vertical-align: top;
            font-size: 9.5px;
        }

        .right {
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .totals {
            width: 245px;
            margin-left: auto;
            margin-top: 10px;
            border-collapse: collapse;
        }

        .totals td {
            padding: 4px 6px;
            font-size: 10px;
        }

        .totals .label {
            color: #4b5563;
            text-align: right;
        }

        .totals .amount {
            width: 95px;
            text-align: right;
            font-weight: 700;
        }

        .grand td {
            border-top: 2px solid #0f3f76;
            color: #0f3f76;
            font-size: 12px;
            font-weight: 700;
            padding-top: 7px;
        }

        .notes {
            margin-top: 14px;
            border-top: 1px solid #e5e7eb;
            padding-top: 8px;
            color: #4b5563;
        }

        .footer {
            position: fixed;
            right: 14mm;
            bottom: 8mm;
            left: 14mm;
            color: #9ca3af;
            font-size: 8px;
            text-align: right;
        }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td style="width:55%;">
                <div class="title"><?= esc($poLang('pdf.title')) ?></div>
                <div class="po-no"><?= esc($po['po_number'] ?? '-') ?></div>
                <div class="status"><?= esc($statusLabel($po['status'] ?? 'draft')) ?></div>
            </td>
            <td style="width:45%;">
                <table class="meta">
                    <tr>
                        <td class="key"><?= esc($poLang('columns.orderDate')) ?></td>
                        <td class="val"><?= esc($date($po['order_date'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="key"><?= esc($poLang('columns.expectedDeliveryShort')) ?></td>
                        <td class="val"><?= esc($date($po['expected_delivery_date'] ?? null)) ?></td>
                    </tr>
                    <tr>
                        <td class="key"><?= esc($poLang('columns.createdBy')) ?></td>
                        <td class="val"><?= esc($po['created_by_name'] ?? '-') ?></td>
                    </tr>
                    <tr>
                        <td class="key"><?= esc($poLang('columns.approvedBy')) ?></td>
                        <td class="val"><?= esc($po['approved_by_name'] ?? '-') ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="section-grid">
        <tr>
            <td>
                <div class="box-title"><?= esc($poLang('columns.supplier')) ?></div>
                <div class="box">
                    <div class="name"><?= esc($po['supplier_name'] ?? '-') ?></div>
                    <?php if (! empty($po['supplier_code'])) : ?><div class="muted"><?= esc($po['supplier_code']) ?></div><?php endif ?>
                    <?php if (! empty($po['contact_name'])) : ?><div><?= esc($poLang('pdf.contact')) ?>: <?= esc($po['contact_name']) ?></div><?php endif ?>
                    <?php if (! empty($po['phone'])) : ?><div><?= esc($poLang('pdf.phone')) ?>: <?= esc($po['phone']) ?></div><?php endif ?>
                    <?php if (! empty($po['email'])) : ?><div><?= esc($poLang('columns.email')) ?>: <?= esc($po['email']) ?></div><?php endif ?>
                    <?php if (! empty($po['address'])) : ?><div><?= esc($po['address']) ?></div><?php endif ?>
                </div>
            </td>
            <td>
                <div class="box-title"><?= esc($poLang('pdf.deliveryTerms')) ?></div>
                <div class="box">
                    <div><strong><?= esc($poLang('columns.paymentTerms')) ?>:</strong> <?= esc($po['payment_terms'] ?? '-') ?></div>
                    <div><strong><?= esc($poLang('columns.deliveryAddress')) ?>:</strong> <?= esc($po['delivery_address'] ?? '-') ?></div>
                    <?php if (! empty($po['approved_date'])) : ?>
                        <div><strong><?= esc($poLang('columns.approvedDate')) ?>:</strong> <?= esc($po['approved_date']) ?></div>
                    <?php endif ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="box-title"><?= esc($poLang('pdf.lineItems')) ?></div>
    <table class="items">
        <thead>
            <tr>
                <th style="width:28px;">#</th>
                <th><?= esc($poLang('columns.item')) ?></th>
                <th style="width:72px;"><?= esc($poLang('columns.catalogNo')) ?></th>
                <th style="width:70px;"><?= esc($poLang('columns.brand')) ?></th>
                <th class="right" style="width:60px;"><?= esc($poLang('columns.qty')) ?></th>
                <th style="width:46px;"><?= esc($poLang('columns.unit')) ?></th>
                <th class="right" style="width:68px;"><?= esc($poLang('columns.price')) ?></th>
                <th class="right" style="width:42px;"><?= esc($poLang('columns.discountShort')) ?></th>
                <th class="right" style="width:42px;"><?= esc($poLang('columns.tax')) ?></th>
                <th class="right" style="width:76px;"><?= esc($poLang('columns.total')) ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ($items === []) : ?>
                <tr>
                    <td colspan="10" class="center muted"><?= esc($poLang('pdf.noLineItems')) ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($items as $index => $item) : ?>
                    <tr>
                        <td><?= esc((string) ($index + 1)) ?></td>
                        <td>
                            <strong><?= esc($item['item_name'] ?? '-') ?></strong>
                            <?php if (! empty($item['cas_no'])) : ?><div class="muted"><?= esc($poLang('columns.casNo')) ?>: <?= esc($item['cas_no']) ?></div><?php endif ?>
                            <?php if (! empty($item['grade'])) : ?><div class="muted"><?= esc($poLang('columns.grade')) ?>: <?= esc($item['grade']) ?></div><?php endif ?>
                            <?php if (! empty($item['pack_size'])) : ?><div class="muted"><?= esc($poLang('columns.packSize')) ?>: <?= esc($item['pack_size']) ?></div><?php endif ?>
                            
                        </td>
                        <td><?= esc($item['catalog_no'] ?? '-') ?></td>
                        <td><?= esc($item['brand_name'] ?? '-') ?></td>
                        <td class="right"><?= esc((string) ($item['qty_ordered'] ?? '0')) ?></td>
                        <td><?= esc($item['unit_name'] ?? '-') ?></td>
                        <td class="right"><?= esc($money($item['unit_price'] ?? 0)) ?></td>
                        <td class="right"><?= esc((string) ($item['discount_percent'] ?? 0)) ?>%</td>
                        <td class="right"><?= esc((string) ($item['tax_rate'] ?? 0)) ?>%</td>
                        <td class="right"><strong><?= esc($money($item['line_total'] ?? 0)) ?></strong></td>
                    </tr>
                <?php endforeach ?>
            <?php endif ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label"><?= esc($poLang('columns.subtotal')) ?></td>
            <td class="amount"><?= esc($money($subtotal)) ?></td>
        </tr>
        <tr>
            <td class="label"><?= esc($poLang('columns.tax')) ?></td>
            <td class="amount"><?= esc($money($tax)) ?></td>
        </tr>
        <tr>
            <td class="label"><?= esc($poLang('columns.shipping')) ?></td>
            <td class="amount"><?= esc($money($shipping)) ?></td>
        </tr>
        <tr class="grand">
            <td class="label"><?= esc($poLang('columns.grandTotal')) ?></td>
            <td class="amount"><?= esc($money($grandTotal)) ?></td>
        </tr>
    </table>

    <?php if (! empty($po['notes'])) : ?>
        <div class="notes">
            <strong><?= esc($poLang('columns.notes')) ?>:</strong><br>
            <?= nl2br(esc($po['notes'])) ?>
        </div>
    <?php endif ?>

    <div class="footer">
        <?= esc($poLang('pdf.generatedFrom')) ?>
    </div>
</body>
</html>
