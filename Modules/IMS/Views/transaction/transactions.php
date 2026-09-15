<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php
$validation = $validation ?? [];
$txPager = $txPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]];
$txQuery = $txQuery ?? ['page' => 1, 'per_page' => 10];
$modalState = is_array($modalState ?? null) ? $modalState : [];
$txLang = static function (string $key, array $args = [], ?string $fallback = null): string {
    $line = 'IMS.transactions.' . $key;
    $text = lang($line, $args);

    return $text === $line ? ($fallback ?? $key) : $text;
};
$buildTxUrl = static function (array $overrides = []) use ($txQuery): string {
    return site_url('ims/transactions?' . http_build_query(array_merge($txQuery, $overrides)));
};
$typeClass = static function (?string $type): string {
    return match (strtolower((string) $type)) {
        'issue'      => 'neutral',
        'transfer'   => 'neutral',
        'adjustment' => 'warning',
        'return'     => 'success',
        'disposal'   => 'danger',
        default      => 'success',
    };
};
?>


<div class="results-card ims-master-card">
    <div class="ims-master-toolbar ims-supplier-toolbar">
        <div class="admin-card-head">
            <h3><?= esc($txLang('title')) ?></h3>
            <span><?= esc($txLang('list.subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.stock.adjust')) : ?>
            <div class="ims-toolbar-actions">
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#txFormModal" data-mode="create"><?= esc($txLang('actions.record')) ?></button>
            </div>
        <?php endif ?>
    </div>

    <div class="ims-master-panel ims-supplier-panel active">
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($txLang('columns.no')) ?></th>
                        <th><?= esc($txLang('columns.type')) ?></th>
                        <th><?= esc($txLang('columns.item')) ?></th>
                        <th><?= esc($txLang('columns.lot')) ?></th>
                        <th><?= esc($txLang('columns.qty')) ?></th>
                        <th><?= esc($txLang('columns.locations')) ?></th>
                        <th><?= esc($txLang('columns.date')) ?></th>
                        <th><?= esc($txLang('columns.by')) ?></th>
                        <th><?= esc($txLang('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rows === []) : ?>
                        <tr class="main-row"><td colspan="9" class="admin-muted"><?= esc($txLang('list.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($rows as $row) : ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($txLang('columns.no')) ?>"><strong><?= esc($row['transaction_no']) ?></strong></td>
                                <td data-label="<?= esc($txLang('columns.type')) ?>">
                                    <span class="admin-badge <?= esc($typeClass($row['transaction_type'])) ?>"><?= esc($txLang('transactionTypes.' . (string) ($row['transaction_type'] ?? ''), [], ucfirst((string) ($row['transaction_type'] ?? '-')))) ?></span>
                                </td>
                                <td data-label="<?= esc($txLang('columns.item')) ?>">
                                    <strong><?= esc($row['item_name'] ?? '-') ?></strong>
                                    <div class="admin-muted"><?= esc($row['item_code'] ?? '') ?></div>
                                </td>
                                <td data-label="<?= esc($txLang('columns.lot')) ?>"><?= esc($row['internal_lot_no'] ?? '-') ?></td>
                                <td data-label="<?= esc($txLang('columns.qty')) ?>">
                                    <strong><?= esc((string) $row['qty']) ?> <?= esc($row['unit_name'] ?? '') ?></strong>
                                </td>
                                <td data-label="<?= esc($txLang('columns.locations')) ?>">
                                    <?php if (($row['from_location_name'] ?? null) || ($row['to_location_name'] ?? null)) : ?>
                                        <?= esc($row['from_location_name'] ?? '–') ?> → <?= esc($row['to_location_name'] ?? '–') ?>
                                    <?php else : ?>
                                        <span class="admin-muted">–</span>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($txLang('columns.date')) ?>"><?= esc(substr((string) ($row['transaction_date'] ?? ''), 0, 16)) ?></td>
                                <td data-label="<?= esc($txLang('columns.by')) ?>"><?= esc($row['performed_by_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($txLang('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.stock.adjust')) : ?>
                                        <div class="ims-action-group">
                                            <button type="button" class="ims-action-btn" title="<?= esc($txLang('actions.edit')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#txFormModal"
                                                data-mode="edit"
                                                data-action="<?= esc(site_url('ims/transactions/' . $row['id'])) ?>"
                                                <?php foreach ($row as $key => $value) : ?>
                                                    <?php if (is_scalar($value) || $value === null) : ?>
                                                        data-<?= esc(str_replace('_', '-', $key)) ?>="<?= esc((string) ($value ?? '')) ?>"
                                                    <?php endif ?>
                                                <?php endforeach ?>>
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="ims-action-btn danger" title="<?= esc($txLang('actions.delete')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteTxModal"
                                                data-delete-message="<?= esc($txLang('delete.message', [$row['transaction_no']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/transactions/' . $row['id'] . '/delete')) ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                </tbody>
            </table>
        </div>

        <div class="results-footer">
            <div class="result-count">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" /><line x1="12" y1="9" x2="12" y2="13" /><line x1="12" y1="17" x2="12.01" y2="17" /></svg>
                <strong><?= esc((string) $txPager['total']) ?></strong>&nbsp;<?= esc($txLang('list.records')) ?>
                <span class="admin-muted"><?= $txPager['total'] > 0 ? '(' . esc((string) $txPager['from']) . '-' . esc((string) $txPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <input type="hidden" name="page" value="1">
                <span><?= esc($txLang('common.rows')) ?></span>
                <select class="rows-select" name="per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $txPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($txPager['page'] > 1) : ?><a class="page-btn" href="<?= esc($buildTxUrl(['page' => $txPager['page'] - 1])) ?>">&lsaquo;</a><?php endif ?>
                <?php foreach ($txPager['pages'] as $page) : ?><a class="page-btn <?= (int) $txPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildTxUrl(['page' => $page])) ?>"><?= esc((string) $page) ?></a><?php endforeach ?>
                <?php if ($txPager['page'] < $txPager['totalPages']) : ?><a class="page-btn" href="<?= esc($buildTxUrl(['page' => $txPager['page'] + 1])) ?>">&rsaquo;</a><?php endif ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade app-crud-modal" id="txFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/transactions') ?>" id="txForm">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $txQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $txQuery['per_page']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($txLang('actions.record')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($txLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="tx-type"><?= esc($txLang('fields.type')) ?></label>
                            <select id="tx-type" name="transaction_type" class="filter-select">
                                <?php foreach (['receipt', 'issue', 'transfer', 'adjustment', 'return', 'disposal'] as $value) : ?>
                                    <?php $label = $txLang('transactionTypes.' . $value); ?>
                                    <option value="<?= esc($value) ?>" <?= old('transaction_type', 'receipt') === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['transaction_type'])) : ?><small class="admin-error"><?= esc($validation['transaction_type']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-date"><?= esc($txLang('fields.transactionDate')) ?></label>
                            <input id="tx-date" name="transaction_date" type="datetime-local" value="<?= old('transaction_date') ?>">
                            <?php if (isset($validation['transaction_date'])) : ?><small class="admin-error"><?= esc($validation['transaction_date']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-item-id"><?= esc($txLang('fields.item')) ?></label>
                            <select id="tx-item-id" name="item_id" class="filter-select">
                                <option value=""><?= esc($txLang('placeholders.selectItem')) ?></option>
                                <?php foreach ($items as $item) : ?>
                                    <option value="<?= esc((string) $item['id']) ?>" <?= old('item_id') == $item['id'] ? 'selected' : '' ?>><?= esc($item['item_code'] . ' – ' . $item['item_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['item_id'])) : ?><small class="admin-error"><?= esc($validation['item_id']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-stock-lot-id"><?= esc($txLang('fields.stockLot')) ?></label>
                            <select id="tx-stock-lot-id" name="stock_lot_id" class="filter-select">
                                <option value=""><?= esc($txLang('placeholders.noSpecificLot')) ?></option>
                            </select>
                            <?php if (isset($validation['stock_lot_id'])) : ?><small class="admin-error"><?= esc($validation['stock_lot_id']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-qty"><?= esc($txLang('fields.quantity')) ?></label>
                            <input id="tx-qty" name="qty" type="number" step="0.0001" min="0.0001" value="<?= old('qty') ?>">
                            <?php if (isset($validation['qty'])) : ?><small class="admin-error"><?= esc($validation['qty']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-unit-id"><?= esc($txLang('fields.unit')) ?></label>
                            <select id="tx-unit-id" name="unit_id" class="filter-select">
                                <option value=""><?= esc($txLang('placeholders.selectUnit')) ?></option>
                                <?php foreach ($units as $unit) : ?>
                                    <option value="<?= esc((string) $unit['id']) ?>" <?= old('unit_id') == $unit['id'] ? 'selected' : '' ?>><?= esc($unit['unit_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['unit_id'])) : ?><small class="admin-error"><?= esc($validation['unit_id']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-from-location-id"><?= esc($txLang('fields.fromLocation')) ?></label>
                            <select id="tx-from-location-id" name="from_location_id" class="filter-select">
                                <option value=""><?= esc($txLang('placeholders.none')) ?></option>
                                <?php foreach ($locations as $loc) : ?>
                                    <option value="<?= esc((string) $loc['id']) ?>" <?= old('from_location_id') == $loc['id'] ? 'selected' : '' ?>><?= esc($loc['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="tx-to-location-id"><?= esc($txLang('fields.toLocation')) ?></label>
                            <select id="tx-to-location-id" name="to_location_id" class="filter-select">
                                <option value=""><?= esc($txLang('placeholders.none')) ?></option>
                                <?php foreach ($locations as $loc) : ?>
                                    <option value="<?= esc((string) $loc['id']) ?>" <?= old('to_location_id') == $loc['id'] ? 'selected' : '' ?>><?= esc($loc['name']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group full">
                            <label for="tx-reason"><?= esc($txLang('fields.reason')) ?></label>
                            <textarea id="tx-reason" name="reason" rows="3"><?= old('reason') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($txLang('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($txLang('actions.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteTxModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteTxForm">
                <?= csrf_field() ?>
                <input type="hidden" name="page" value="<?= esc((string) $txQuery['page']) ?>">
                <input type="hidden" name="per_page" value="<?= esc((string) $txQuery['per_page']) ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($txLang('delete.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($txLang('common.close')) ?>"></button>
                </div>
                <div class="modal-body"><p id="deleteTxModalMessage"><?= esc($txLang('delete.fallback')) ?></p></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($txLang('common.cancel')) ?></button>
                    <button type="submit" class="btn btn-danger"><?= esc($txLang('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
(() => {
    const fields = ['transaction_type', 'transaction_date', 'item_id', 'stock_lot_id', 'qty', 'unit_id', 'from_location_id', 'to_location_id', 'reason'];
    const txText = <?= json_encode([
        'record' => $txLang('actions.record'),
        'edit' => $txLang('actions.edit'),
        'noSpecificLot' => $txLang('placeholders.noSpecificLot'),
        'deleteFallback' => $txLang('delete.fallback'),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    const allLots = <?= json_encode(
        array_map(static fn (array $l): array => [
            'id'      => (int) $l['id'],
            'item_id' => (int) $l['item_id'],
            'label'   => $l['internal_lot_no'] ?: ($l['lot_no'] ?: ('#' . $l['id'])),
        ], $lots),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    ) ?>;

    const lotsByItem = {};
    allLots.forEach((lot) => {
        if (!lotsByItem[lot.item_id]) lotsByItem[lot.item_id] = [];
        lotsByItem[lot.item_id].push(lot);
    });

    function populateLots(itemId, selectedLotId) {
        const lotSelect = document.getElementById('tx-stock-lot-id');
        lotSelect.innerHTML = '<option value="">' + txText.noSpecificLot + '</option>';
        (lotsByItem[String(itemId)] || lotsByItem[Number(itemId)] || []).forEach((lot) => {
            const opt = document.createElement('option');
            opt.value = lot.id;
            opt.textContent = lot.label;
            if (String(lot.id) === String(selectedLotId)) opt.selected = true;
            lotSelect.appendChild(opt);
        });
    }

    document.getElementById('tx-item-id')?.addEventListener('change', function () {
        populateLots(this.value, '');
    });

    const modal = document.getElementById('txFormModal');
    modal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        const form = document.getElementById('txForm');
        if (!trigger && form.dataset.preserveOld === '1') return;
        const isCreate = !trigger || trigger.dataset.mode === 'create';
        modal.querySelector('.modal-title').textContent = isCreate ? txText.record : txText.edit;
        form.action = isCreate ? '<?= site_url('ims/transactions') ?>' : trigger.dataset.action;
        form.dataset.preserveOld = '0';
        form.reset();
        document.getElementById('tx-stock-lot-id').innerHTML = '<option value="">' + txText.noSpecificLot + '</option>';
        if (isCreate) {
            const now = new Date();
            document.getElementById('tx-date').value = now.toISOString().slice(0, 16);
            return;
        }
        fields.forEach((name) => {
            const input = form.querySelector('[name="' + name + '"]');
            if (!input) return;
            if (name === 'transaction_date') {
                const raw = trigger.getAttribute('data-transaction-date') || '';
                input.value = raw.length >= 16 ? raw.slice(0, 10) + 'T' + raw.slice(11, 16) : raw;
            } else {
                input.value = trigger.getAttribute('data-' + name.replaceAll('_', '-')) || '';
            }
        });
        const itemId = trigger.getAttribute('data-item-id') || '';
        const lotId = trigger.getAttribute('data-stock-lot-id') || '';
        populateLots(itemId, lotId);
    });

    const deleteModal = document.getElementById('deleteTxModal');
    deleteModal?.addEventListener('show.bs.modal', (event) => {
        const trigger = event.relatedTarget;
        if (!trigger) return;
        document.getElementById('deleteTxForm').action = trigger.dataset.deleteAction;
        document.getElementById('deleteTxModalMessage').textContent = trigger.dataset.deleteMessage || txText.deleteFallback;
    });

    <?php if (($modalState['modal'] ?? null) === 'transaction-form-modal') : ?>
        const form = document.getElementById('txForm');
        form.dataset.preserveOld = '1';
        populateLots('<?= (int) old('item_id') ?>', '<?= (int) old('stock_lot_id') ?>');
        <?php if (! empty($modalState['tx_id'])) : ?>
            form.action = '<?= site_url('ims/transactions/' . (int) $modalState['tx_id']) ?>';
            document.querySelector('#txFormModal .modal-title').textContent = txText.edit;
        <?php else : ?>
            document.querySelector('#txFormModal .modal-title').textContent = txText.record;
        <?php endif ?>
        new bootstrap.Modal(document.getElementById('txFormModal')).show();
    <?php endif ?>
})();
</script>
<?= $this->endSection() ?>
