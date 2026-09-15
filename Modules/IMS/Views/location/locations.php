<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php
$locationLang = static fn (string $key, array $args = []): string => lang('IMS.locations.' . $key, $args);
?>

<div class="results-card">
    <div class="ims-master-toolbar">
        <div class="admin-card-head">
            <h3><?= esc($locationLang('title')) ?></h3>
            <span><?= esc($locationLang('subtitle')) ?></span>
        </div>
        <?php if (lab_core_can('ims.warehouses.manage')) : ?>
            <div class="ims-toolbar-actions">
                <a href="<?= site_url('ims/master-data?tab=locations') ?>" class="admin-btn secondary"><?= esc($locationLang('actions.manage')) ?></a>
            </div>
        <?php endif ?>
    </div>

    <div class="row g-3 align-items-stretch" style="padding:0 1.25rem 1.25rem;">

        <!-- Left: location list -->
        <div class="col-12 col-md-4 d-flex flex-column">
            <div style="border:1px solid var(--bs-border-color, #dee2e6); border-radius:.5rem; overflow-y:auto; flex:1; min-height:0;">
                <?php if (empty($locations)) : ?>
                    <div class="p-4 text-center admin-muted"><?= esc($locationLang('empty.locations')) ?></div>
                <?php else : ?>
                    <ul class="list-unstyled mb-0" id="location-list">
                        <?php foreach ($locations as $loc) : ?>
                            <li class="location-list-item"
                                data-location-id="<?= esc((string) $loc['id']) ?>"
                                data-location-name="<?= esc($loc['name']) ?>"
                                style="padding:.75rem 1rem; border-bottom:1px solid var(--bs-border-color, #dee2e6); cursor:pointer; transition:background .15s;">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <div style="min-width:0;">
                                        <strong><?= esc($loc['name']) ?></strong>
                                        <?php if (! empty($loc['code'])) : ?>
                                            <span class="admin-muted ms-1" style="font-size:.82rem;"><?= esc($loc['code']) ?></span>
                                        <?php endif ?>
                                        <?php if (! empty($loc['parent_name'])) : ?>
                                            <div class="admin-muted" style="font-size:.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= esc($loc['parent_name']) ?></div>
                                        <?php endif ?>
                                    </div>
                                    <span class="admin-badge <?= (int) $loc['lot_count'] > 0 ? 'primary' : 'neutral' ?> flex-shrink-0">
                                        <?= esc((string) (int) $loc['lot_count']) ?>
                                    </span>
                                </div>
                            </li>
                        <?php endforeach ?>
                    </ul>
                <?php endif ?>
            </div>
        </div>

        <!-- Right: lots panel -->
        <div class="col-12 col-md-8 d-flex flex-column">
            <div id="location-lots-pane" style="border:1px solid var(--bs-border-color, #dee2e6); border-radius:.5rem; flex:1; min-height:0; display:flex; flex-direction:column;">

                <div id="loc-placeholder" class="d-flex flex-column align-items-center justify-content-center admin-muted" style="flex:1; gap:.75rem; padding:3rem 0;">
                    <svg style="width:48px;height:48px;opacity:.25;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    <span><?= esc($locationLang('placeholder')) ?></span>
                </div>

                <div id="loc-content" style="display:none; flex:1; flex-direction:column;">
                    <div class="d-flex justify-content-between align-items-center" style="padding:1rem 1.25rem .75rem; border-bottom:1px solid var(--bs-border-color, #dee2e6);">
                        <h6 class="mb-0" id="loc-title"></h6>
                        <span class="admin-muted" id="loc-count" style="font-size:.85rem;"></span>
                    </div>

                    <div style="flex:1; overflow-x:auto; min-height:0;">
                        <div id="loc-table-wrap">
                            <table class="results-table app-responsive-table">
                                <thead>
                                    <tr>
                                        <th><?= esc($locationLang('columns.lotNo')) ?></th>
                                        <th><?= esc($locationLang('columns.item')) ?></th>
                                        <th><?= esc($locationLang('columns.qty')) ?></th>
                                        <th><?= esc($locationLang('columns.expiry')) ?></th>
                                        <th><?= esc($locationLang('columns.supplier')) ?></th>
                                        <th><?= esc($locationLang('columns.status')) ?></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="loc-tbody"></tbody>
                            </table>
                        </div>
                        <div id="loc-empty" class="admin-muted text-center py-4" style="display:none;"><?= esc($locationLang('empty.lots')) ?></div>
                    </div>

                    <div id="loc-pagination" class="results-footer" style="display:none; border-top:1px solid var(--bs-border-color, #dee2e6);">
                        <span class="admin-muted" id="loc-page-info" style="font-size:.85rem;"></span>
                        <div class="d-flex gap-2" id="loc-page-btns"></div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
(() => {
    const lotsByLocation = <?= json_encode($lotsByLocation, JSON_HEX_TAG | JSON_HEX_AMP) ?: '{}' ?>;
    const baseUrl        = <?= json_encode(site_url('/')) ?>;
    const PER_PAGE       = 10;
    const locText        = <?= json_encode([
        'lotNo' => $locationLang('columns.lotNo'),
        'item' => $locationLang('columns.item'),
        'qty' => $locationLang('columns.qty'),
        'expiry' => $locationLang('columns.expiry'),
        'supplier' => $locationLang('columns.supplier'),
        'status' => $locationLang('columns.status'),
        'viewDetail' => $locationLang('actions.viewDetail'),
        'lotSingular' => $locationLang('count.lotSingular'),
        'lotPlural' => $locationLang('count.lotPlural'),
        'of' => $locationLang('count.of'),
        'ownership' => [
            'owned' => $locationLang('ownership.owned'),
            'consignment' => $locationLang('ownership.consignment'),
            'consigned' => $locationLang('ownership.consigned'),
            'borrowed' => $locationLang('ownership.borrowed'),
        ],
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const listItems    = document.querySelectorAll('.location-list-item');
    const placeholder  = document.getElementById('loc-placeholder');
    const content      = document.getElementById('loc-content');
    const title        = document.getElementById('loc-title');
    const countEl      = document.getElementById('loc-count');
    const tableWrap    = document.getElementById('loc-table-wrap');
    const tbody        = document.getElementById('loc-tbody');
    const emptyMsg     = document.getElementById('loc-empty');
    const pagination   = document.getElementById('loc-pagination');
    const pageInfo     = document.getElementById('loc-page-info');
    const pageBtns     = document.getElementById('loc-page-btns');

    const ownershipClass = { owned: 'primary', consignment: 'warning', borrowed: 'neutral' };

    let currentLots = [];
    let currentPage = 1;

    function esc(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderPage() {
        const total      = currentLots.length;
        const totalPages = Math.max(1, Math.ceil(total / PER_PAGE));
        currentPage      = Math.max(1, Math.min(currentPage, totalPages));

        const start    = (currentPage - 1) * PER_PAGE;
        const end      = Math.min(start + PER_PAGE, total);
        const pageLots = currentLots.slice(start, end);

        tbody.innerHTML = '';

        if (total === 0) {
            tableWrap.style.display  = 'none';
            emptyMsg.style.display   = '';
            pagination.style.display = 'none';
            return;
        }

        tableWrap.style.display = '';
        emptyMsg.style.display  = 'none';

        pageLots.forEach(lot => {
            const label  = lot.internal_lot_no || lot.lot_no || ('#' + lot.id);
            const expiry = lot.expiry_date || '-';
            const badge  = ownershipClass[lot.ownership_status] || 'neutral';

            tbody.insertAdjacentHTML('beforeend',
                `<tr class="main-row">
                    <td data-label="${esc(locText.lotNo)}"><strong>${esc(label)}</strong></td>
                    <td data-label="${esc(locText.item)}">
                        <div>${esc(lot.item_name)}</div>
                        <div class="admin-muted">${esc(lot.item_code)}</div>
                    </td>
                    <td data-label="${esc(locText.qty)}">${esc(lot.current_qty)} <span class="admin-muted">${esc(lot.unit_name)}</span></td>
                    <td data-label="${esc(locText.expiry)}">${esc(expiry)}</td>
                    <td data-label="${esc(locText.supplier)}">${esc(lot.supplier_name || '-')}</td>
                    <td data-label="${esc(locText.status)}"><span class="admin-badge ${badge}">${esc(locText.ownership[lot.ownership_status] || lot.ownership_status || '-')}</span></td>
                    <td><a class="ims-action-btn" title="${esc(locText.viewDetail)}" href="${esc(baseUrl + 'ims/lots/' + lot.id)}"><i class="fa-solid fa-arrow-right"></i></a></td>
                </tr>`
            );
        });

        renderPagination(totalPages, start + 1, end, total);
    }

    function renderPagination(totalPages, from, to, total) {
        pageInfo.textContent = `${from}-${to} ${locText.of} ${total}`;
        pageBtns.innerHTML   = '';

        if (totalPages <= 1) {
            pagination.style.display = 'none';
            return;
        }

        pagination.style.display = '';

        if (currentPage > 1) {
            const btn = document.createElement('button');
            btn.className = 'page-btn';
            btn.innerHTML = '&lsaquo;';
            btn.addEventListener('click', () => { currentPage--; renderPage(); });
            pageBtns.appendChild(btn);
        }

        const rangeStart = Math.max(1, currentPage - 2);
        const rangeEnd   = Math.min(totalPages, currentPage + 2);

        for (let p = rangeStart; p <= rangeEnd; p++) {
            const btn = document.createElement('button');
            btn.className   = 'page-btn' + (p === currentPage ? ' active' : '');
            btn.textContent = p;
            btn.addEventListener('click', (p => () => { currentPage = p; renderPage(); })(p));
            pageBtns.appendChild(btn);
        }

        if (currentPage < totalPages) {
            const btn = document.createElement('button');
            btn.className = 'page-btn';
            btn.innerHTML = '&rsaquo;';
            btn.addEventListener('click', () => { currentPage++; renderPage(); });
            pageBtns.appendChild(btn);
        }
    }

    function renderLots(locationId, locationName) {
        currentLots = lotsByLocation[locationId] || [];
        currentPage = 1;

        title.textContent   = locationName;
        countEl.textContent = currentLots.length + ' ' + (currentLots.length === 1 ? locText.lotSingular : locText.lotPlural);

        renderPage();

        placeholder.classList.add('d-none');
        content.style.display = 'flex';
    }

    listItems.forEach(item => {
        item.addEventListener('click', () => {
            listItems.forEach(i => i.style.background = '');
            item.style.background = 'var(--bs-primary-bg-subtle, #cfe2ff)';
            renderLots(item.dataset.locationId, item.dataset.locationName);
        });
    });
})();
</script>
<?= $this->endSection() ?>
