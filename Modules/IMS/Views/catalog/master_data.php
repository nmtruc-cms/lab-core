<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?= $this->include('Modules\IMS\Views\partials\page_header') ?>

<?php $activeTab = $activeTab ?? 'brands'; ?>
<?php $validation = $validation ?? []; ?>
<?php $masterQuery = $masterQuery ?? ['tab' => $activeTab]; ?>
<?php $paginationState = $paginationState ?? []; ?>
<?php
$buildMasterDataUrl = static function (array $overrides = []) use ($masterQuery): string {
    $query = array_merge($masterQuery, $overrides);

    return site_url('ims/master-data?' . http_build_query($query));
};

$renderQueryInputs = static function (array $params, array $exclude = []): string {
    $html = '';

    foreach ($params as $name => $value) {
        if (in_array($name, $exclude, true)) {
            continue;
        }

        $html .= '<input type="hidden" name="' . esc((string) $name) . '" value="' . esc((string) $value) . '">' . PHP_EOL;
    }

    return $html;
};

$imsMaster = static fn (string $key, array $args = []): string => lang('IMS.masterData.' . $key, $args);
?>

<div class="results-card ims-master-card">
    <div class="ims-master-tabs" role="tablist" aria-label="<?= esc($imsMaster('tabsAria')) ?>">
        <button type="button" class="ims-master-tab <?= $activeTab === 'brands' ? 'active' : '' ?>" data-tab-target="brands"><?= esc($imsMaster('tabs.brands')) ?></button>
        <button type="button" class="ims-master-tab <?= $activeTab === 'categories' ? 'active' : '' ?>" data-tab-target="categories"><?= esc($imsMaster('tabs.categories')) ?></button>
        <button type="button" class="ims-master-tab <?= $activeTab === 'units' ? 'active' : '' ?>" data-tab-target="units"><?= esc($imsMaster('tabs.units')) ?></button>
        <button type="button" class="ims-master-tab <?= $activeTab === 'locations' ? 'active' : '' ?>" data-tab-target="locations"><?= esc($imsMaster('tabs.locations')) ?></button>
        <button type="button" class="ims-master-tab <?= $activeTab === 'checklists' ? 'active' : '' ?>" data-tab-target="checklists"><?= esc($imsMaster('tabs.checklists')) ?></button>
        <button type="button" class="ims-master-tab <?= $activeTab === 'supplier_classifications' ? 'active' : '' ?>" data-tab-target="supplier_classifications"><?= esc($imsMaster('tabs.supplierClassifications')) ?></button>
    </div>

    <div class="ims-master-panel <?= $activeTab === 'brands' ? 'active' : '' ?>" data-tab-panel="brands">
        <div class="ims-master-toolbar">
            <div>
                <h3><?= esc($imsMaster('brands.title')) ?></h3>
                <span><?= esc($imsMaster('brands.subtitle')) ?></span>
            </div>
            <?php if (lab_core_can('ims.items.create')) : ?>
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#brandFormModal" data-mode="create"><?= esc($imsMaster('brands.add')) ?></button>
            <?php endif ?>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($imsMaster('columns.logo')) ?></th>
                        <th><?= esc($imsMaster('columns.brand')) ?></th>
                        <th><?= esc($imsMaster('columns.country')) ?></th>
                        <th><?= esc($imsMaster('columns.website')) ?></th>
                        <th><?= esc($imsMaster('columns.status')) ?></th>
                        <th><?= esc($imsMaster('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($brands === []) : ?>
                        <tr class="main-row"><td colspan="6" class="admin-muted"><?= esc($imsMaster('brands.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($brands as $brand) : ?>
                            <?php
                            $logo = (string) ($brand['logo'] ?? '');
                            $logoUrl = $logo !== '' && preg_match('~^(?:https?:)?//|^data:~', $logo)
                                ? $logo
                                : ($logo !== '' ? base_url(trim($logo, '/')) : null);
                            ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($imsMaster('columns.logo')) ?>">
                                    <?php if ($logoUrl) : ?>
                                        <img class="ims-brand-logo" src="<?= esc($logoUrl) ?>" alt="<?= esc($brand['brand_name']) ?>">
                                    <?php else : ?>
                                        <div class="ims-brand-logo placeholder"><?= esc(strtoupper(substr($brand['brand_name'], 0, 2))) ?></div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.brand')) ?>"><strong><?= esc($brand['brand_name']) ?></strong></td>
                                <td data-label="<?= esc($imsMaster('columns.country')) ?>"><?= esc($brand['country'] ?: '-') ?></td>
                                <td data-label="<?= esc($imsMaster('columns.website')) ?>">
                                    <?php if (! empty($brand['website'])) : ?>
                                        <a class="detail-link" href="<?= esc($brand['website']) ?>" target="_blank" rel="noreferrer"><?= esc($brand['website']) ?></a>
                                    <?php else : ?>
                                        <span class="admin-muted">-</span>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.status')) ?>"><span class="admin-badge <?= (int) ($brand['is_active'] ?? 0) === 1 ? 'success' : '' ?>"><?= (int) ($brand['is_active'] ?? 0) === 1 ? esc($imsMaster('status.active')) : esc($imsMaster('status.inactive')) ?></span></td>
                                <td data-label="<?= esc($imsMaster('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.items.update') || lab_core_can('ims.items.delete')) : ?>
                                        <div class="ims-action-group">
                                            <?php if (lab_core_can('ims.items.update')) : ?>
                                                <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editBrand')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#brandFormModal"
                                                    data-mode="edit"
                                                    data-action="<?= esc(site_url('ims/master-data/brands/' . $brand['id'])) ?>"
                                                    data-brand_name="<?= esc($brand['brand_name']) ?>"
                                                    data-country="<?= esc($brand['country'] ?? '') ?>"
                                                    data-website="<?= esc($brand['website'] ?? '') ?>"
                                                    data-logo="<?= esc($brand['logo'] ?? '') ?>"
                                                    data-logo-url="<?= esc($logoUrl ?? '') ?>"
                                                    data-is_active="<?= esc((string) ($brand['is_active'] ?? 0)) ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                            <?php endif ?>
                                            <?php if (lab_core_can('ims.items.delete')) : ?>
                                                <button type="button" class="ims-action-btn danger" title="<?= esc($imsMaster('actions.deleteBrand')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-delete-title="<?= esc($imsMaster('delete.titles.brand')) ?>"
                                                    data-delete-message="<?= esc($imsMaster('delete.messages.brand', [$brand['brand_name']])) ?>"
                                                    data-delete-action="<?= esc(site_url('ims/master-data/brands/' . $brand['id'] . '/delete')) ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="admin-muted"><?= esc($imsMaster('common.noActions')) ?></span>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <strong><?= esc((string) $brandsPager['total']) ?></strong>&nbsp;<?= esc($imsMaster('records.brands')) ?>
                <span class="admin-muted">
                    <?= $brandsPager['total'] > 0 ? '(' . esc((string) $brandsPager['from']) . '-' . esc((string) $brandsPager['to']) . ')' : '' ?>
                </span>
            </div>
            <form method="get" class="rows-selector">
                <?= $renderQueryInputs($masterQuery, ['brands_page', 'brands_per_page']) ?>
                <input type="hidden" name="tab" value="brands">
                <input type="hidden" name="brands_page" value="1">
                <span><?= esc($imsMaster('common.rows')) ?></span>
                <select class="rows-select" name="brands_per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $brandsPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($brandsPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'brands', 'brands_page' => $brandsPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($brandsPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $brandsPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildMasterDataUrl(['tab' => 'brands', 'brands_page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($brandsPager['page'] < $brandsPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'brands', 'brands_page' => $brandsPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="ims-master-panel <?= $activeTab === 'categories' ? 'active' : '' ?>" data-tab-panel="categories">
        <div class="ims-master-toolbar">
            <div>
                <h3><?= esc($imsMaster('categories.title')) ?></h3>
                <span><?= esc($imsMaster('categories.subtitle')) ?></span>
            </div>
            <?php if (lab_core_can('ims.items.create')) : ?>
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#categoryFormModal" data-mode="create"><?= esc($imsMaster('categories.add')) ?></button>
            <?php endif ?>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= esc($imsMaster('columns.category')) ?></th>
                        <th><?= esc($imsMaster('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories === []) : ?>
                        <tr class="main-row"><td colspan="3" class="admin-muted"><?= esc($imsMaster('categories.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($categories as $category) : ?>
                            <tr class="main-row">
                                <td data-label="ID">#<?= esc((string) $category['id']) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.category')) ?>"><strong><?= esc($category['category_name']) ?></strong></td>
                                <td data-label="<?= esc($imsMaster('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.items.update') || lab_core_can('ims.items.delete')) : ?>
                                        <div class="ims-action-group">
                                            <?php if (lab_core_can('ims.items.update')) : ?>
                                                <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editCategory')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#categoryFormModal"
                                                    data-mode="edit"
                                                    data-action="<?= esc(site_url('ims/master-data/categories/' . $category['id'])) ?>"
                                                    data-category_name="<?= esc($category['category_name']) ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                            <?php endif ?>
                                            <?php if (lab_core_can('ims.items.delete')) : ?>
                                                <button type="button" class="ims-action-btn danger" title="<?= esc($imsMaster('actions.deleteCategory')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-delete-title="<?= esc($imsMaster('delete.titles.category')) ?>"
                                                    data-delete-message="<?= esc($imsMaster('delete.messages.category', [$category['category_name']])) ?>"
                                                    data-delete-action="<?= esc(site_url('ims/master-data/categories/' . $category['id'] . '/delete')) ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="admin-muted"><?= esc($imsMaster('common.noActions')) ?></span>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <strong><?= esc((string) $categoriesPager['total']) ?></strong>&nbsp;<?= esc($imsMaster('records.categories')) ?>
                <span class="admin-muted">
                    <?= $categoriesPager['total'] > 0 ? '(' . esc((string) $categoriesPager['from']) . '-' . esc((string) $categoriesPager['to']) . ')' : '' ?>
                </span>
            </div>
            <form method="get" class="rows-selector">
                <?= $renderQueryInputs($masterQuery, ['categories_page', 'categories_per_page']) ?>
                <input type="hidden" name="tab" value="categories">
                <input type="hidden" name="categories_page" value="1">
                <span><?= esc($imsMaster('common.rows')) ?></span>
                <select class="rows-select" name="categories_per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $categoriesPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($categoriesPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'categories', 'categories_page' => $categoriesPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($categoriesPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $categoriesPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildMasterDataUrl(['tab' => 'categories', 'categories_page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($categoriesPager['page'] < $categoriesPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'categories', 'categories_page' => $categoriesPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>

    <div class="ims-master-panel <?= $activeTab === 'units' ? 'active' : '' ?>" data-tab-panel="units">
        <div class="ims-master-toolbar">
            <div>
                <h3><?= esc($imsMaster('units.title')) ?></h3>
                <span><?= esc($imsMaster('units.subtitle')) ?></span>
            </div>
            <?php if (lab_core_can('ims.items.create')) : ?>
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#unitFormModal" data-mode="create"><?= esc($imsMaster('units.add')) ?></button>
            <?php endif ?>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($imsMaster('columns.unit')) ?></th>
                        <th><?= esc($imsMaster('columns.type')) ?></th>
                        <th><?= esc($imsMaster('columns.baseUnit')) ?></th>
                        <th><?= esc($imsMaster('columns.factor')) ?></th>
                        <th><?= esc($imsMaster('columns.status')) ?></th>
                        <th><?= esc($imsMaster('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($units === []) : ?>
                        <tr class="main-row"><td colspan="6" class="admin-muted"><?= esc($imsMaster('units.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($units as $unit) : ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($imsMaster('columns.unit')) ?>"><strong><?= esc($unit['unit_name']) ?></strong></td>
                                <td data-label="<?= esc($imsMaster('columns.type')) ?>"><?= esc($unit['unit_type'] ?? '-') ?></td>
                                <td data-label="<?= esc($imsMaster('columns.baseUnit')) ?>"><?= esc($unit['base_unit_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($imsMaster('columns.factor')) ?>"><?= esc((string) ($unit['conversion_factor'] ?? '1')) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.status')) ?>"><span class="admin-badge <?= (int) ($unit['is_active'] ?? 0) === 1 ? 'success' : '' ?>"><?= (int) ($unit['is_active'] ?? 0) === 1 ? esc($imsMaster('status.active')) : esc($imsMaster('status.inactive')) ?></span></td>
                                <td data-label="<?= esc($imsMaster('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.items.update') || lab_core_can('ims.items.delete')) : ?>
                                        <div class="ims-action-group">
                                            <?php if (lab_core_can('ims.items.update')) : ?>
                                                <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editUnit')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#unitFormModal"
                                                    data-mode="edit"
                                                    data-action="<?= esc(site_url('ims/master-data/units/' . $unit['id'])) ?>"
                                                    data-unit_name="<?= esc($unit['unit_name']) ?>"
                                                    data-unit_type="<?= esc($unit['unit_type'] ?? '') ?>"
                                                    data-base_unit_id="<?= esc((string) ($unit['base_unit_id'] ?? '')) ?>"
                                                    data-conversion_factor="<?= esc((string) ($unit['conversion_factor'] ?? '1')) ?>"
                                                    data-is_active="<?= esc((string) ($unit['is_active'] ?? 0)) ?>">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </button>
                                            <?php endif ?>
                                            <?php if (lab_core_can('ims.items.delete')) : ?>
                                                <button type="button" class="ims-action-btn danger" title="<?= esc($imsMaster('actions.deleteUnit')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#deleteModal"
                                                    data-delete-title="<?= esc($imsMaster('delete.titles.unit')) ?>"
                                                    data-delete-message="<?= esc($imsMaster('delete.messages.unit', [$unit['unit_name']])) ?>"
                                                    data-delete-action="<?= esc(site_url('ims/master-data/units/' . $unit['id'] . '/delete')) ?>">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            <?php endif ?>
                                        </div>
                                    <?php else : ?>
                                        <span class="admin-muted"><?= esc($imsMaster('common.noActions')) ?></span>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    <line x1="12" y1="9" x2="12" y2="13" />
                    <line x1="12" y1="17" x2="12.01" y2="17" />
                </svg>
                <strong><?= esc((string) $unitsPager['total']) ?></strong>&nbsp;<?= esc($imsMaster('records.units')) ?>
                <span class="admin-muted">
                    <?= $unitsPager['total'] > 0 ? '(' . esc((string) $unitsPager['from']) . '-' . esc((string) $unitsPager['to']) . ')' : '' ?>
                </span>
            </div>
            <form method="get" class="rows-selector">
                <?= $renderQueryInputs($masterQuery, ['units_page', 'units_per_page']) ?>
                <input type="hidden" name="tab" value="units">
                <input type="hidden" name="units_page" value="1">
                <span><?= esc($imsMaster('common.rows')) ?></span>
                <select class="rows-select" name="units_per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $unitsPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($unitsPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'units', 'units_page' => $unitsPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($unitsPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $unitsPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildMasterDataUrl(['tab' => 'units', 'units_page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($unitsPager['page'] < $unitsPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'units', 'units_page' => $unitsPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>

    <?php
    $checklists = $checklists ?? [];
    $checklistDetails = $checklistDetails ?? [];
    $checklistClassifications = $checklistClassifications ?? [];
    $checklistsPager = $checklistsPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]];
    ?>
    <div class="ims-master-panel <?= $activeTab === 'checklists' ? 'active' : '' ?>" data-tab-panel="checklists">
        <div class="ims-master-toolbar">
            <div>
                <h3><?= esc($imsMaster('checklists.title')) ?></h3>
                <span><?= esc($imsMaster('checklists.subtitle')) ?></span>
            </div>
            <?php if (lab_core_can('ims.items.create')) : ?>
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#checklistFormModal" data-mode="create"><?= esc($imsMaster('checklists.add')) ?></button>
            <?php endif ?>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($imsMaster('columns.checklist')) ?></th>
                        <th><?= esc($imsMaster('columns.items')) ?></th>
                        <th><?= esc($imsMaster('columns.totalWeight')) ?></th>
                        <th><?= esc($imsMaster('columns.createdBy')) ?></th>
                        <th><?= esc($imsMaster('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($checklists === []) : ?>
                        <tr class="main-row"><td colspan="5" class="admin-muted"><?= esc($imsMaster('checklists.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($checklists as $checklist) : ?>
                            <?php $details = $checklistDetails[(int) $checklist['id']] ?? []; ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($imsMaster('columns.checklist')) ?>">
                                    <strong><?= esc($checklist['checklist_name']) ?></strong>
                                    <?php if (! empty($checklist['description'])) : ?><div class="admin-muted"><?= esc($checklist['description']) ?></div><?php endif ?>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.items')) ?>">
                                    <?php if ($details === []) : ?>
                                        <span class="admin-muted"><?= esc($imsMaster('checklists.noItems')) ?></span>
                                    <?php else : ?>
                                        <div class="d-flex flex-column gap-2">
                                            <?php foreach ($details as $detail) : ?>
                                                <div class="d-flex align-items-start justify-content-between gap-2">
                                                    <div>
                                                        <strong><?= esc($detail['check_item']) ?></strong>
                                                        <span class="admin-muted"> &middot; <?= esc(number_format((float) $detail['weight'], 4)) ?></span>
                                                        <?php if (! empty($detail['description'])) : ?><div class="admin-muted"><?= esc($detail['description']) ?></div><?php endif ?>
                                                    </div>
                                                    <?php if (lab_core_can('ims.items.update') || lab_core_can('ims.items.delete')) : ?>
                                                        <div class="ims-action-group">
                                                            <?php if (lab_core_can('ims.items.update')) : ?>
                                                                <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editChecklistItem')) ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#checklistDetailFormModal"
                                                                    data-mode="edit"
                                                                    data-action="<?= esc(site_url('ims/master-data/checklists/' . $checklist['id'] . '/details/' . $detail['id'])) ?>"
                                                                    data-checklist-id="<?= esc((string) $checklist['id']) ?>"
                                                                    data-checklist-name="<?= esc($checklist['checklist_name']) ?>"
                                                                    data-check-item="<?= esc($detail['check_item']) ?>"
                                                                    data-weight="<?= esc((string) $detail['weight']) ?>"
                                                                    data-description="<?= esc($detail['description'] ?? '') ?>">
                                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                                </button>
                                                            <?php endif ?>
                                                            <?php if (lab_core_can('ims.items.delete')) : ?>
                                                                <button type="button" class="ims-action-btn danger" title="<?= esc($imsMaster('actions.deleteChecklistItem')) ?>"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#deleteModal"
                                                                    data-delete-title="<?= esc($imsMaster('delete.titles.checklistItem')) ?>"
                                                                    data-delete-message="<?= esc($imsMaster('delete.messages.checklistItem', [$detail['check_item']])) ?>"
                                                                    data-delete-action="<?= esc(site_url('ims/master-data/checklists/' . $checklist['id'] . '/details/' . $detail['id'] . '/delete')) ?>">
                                                                    <i class="fa-solid fa-trash-can"></i>
                                                                </button>
                                                            <?php endif ?>
                                                        </div>
                                                    <?php endif ?>
                                                </div>
                                            <?php endforeach ?>
                                        </div>
                                    <?php endif ?>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.totalWeight')) ?>"><?= esc(number_format((float) ($checklist['total_weight'] ?? 0), 4)) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.createdBy')) ?>"><?= esc($checklist['created_by_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($imsMaster('columns.action')) ?>">
                                    <div class="ims-action-group">
                                        <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.classificationConfig')) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#checklistClassificationModal"
                                            data-action="<?= esc(site_url('ims/master-data/checklists/' . $checklist['id'] . '/classifications')) ?>"
                                            data-checklist-id="<?= esc((string) $checklist['id']) ?>"
                                            data-checklist-name="<?= esc($checklist['checklist_name']) ?>">
                                            <i class="fa-solid fa-sliders"></i>
                                        </button>
                                            <?php if (lab_core_can('ims.items.create') && (float) ($checklist['total_weight'] ?? 0) < 100) : ?>
                                                 <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.addChecklistItem')) ?>"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#checklistDetailFormModal"
                                                data-mode="create"
                                                data-action="<?= esc(site_url('ims/master-data/checklists/' . $checklist['id'] . '/details')) ?>"
                                                data-checklist-id="<?= esc((string) $checklist['id']) ?>"
                                                data-checklist-name="<?= esc($checklist['checklist_name']) ?>">
                                                <i class="fa-solid fa-plus"></i>
                                            </button>
                                        <?php endif ?>
                                        <?php if (lab_core_can('ims.items.update')) : ?>
                                            <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editChecklist')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#checklistFormModal"
                                                data-mode="edit"
                                                data-action="<?= esc(site_url('ims/master-data/checklists/' . $checklist['id'])) ?>"
                                                data-checklist-name="<?= esc($checklist['checklist_name']) ?>"
                                                data-description="<?= esc($checklist['description'] ?? '') ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        <?php endif ?>
                                        <?php if (lab_core_can('ims.items.delete')) : ?>
                                            <button type="button" class="ims-action-btn danger" title="<?= esc($imsMaster('actions.deleteChecklist')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-delete-title="<?= esc($imsMaster('delete.titles.checklist')) ?>"
                                                data-delete-message="<?= esc($imsMaster('delete.messages.checklist', [$checklist['checklist_name']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/master-data/checklists/' . $checklist['id'] . '/delete')) ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        <?php endif ?>
                                    </div>
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
                <strong><?= esc((string) $checklistsPager['total']) ?></strong>&nbsp;<?= esc($imsMaster('records.checklists')) ?>
                <span class="admin-muted"><?= $checklistsPager['total'] > 0 ? '(' . esc((string) $checklistsPager['from']) . '-' . esc((string) $checklistsPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <?= $renderQueryInputs($masterQuery, ['checklists_page', 'checklists_per_page']) ?>
                <input type="hidden" name="tab" value="checklists">
                <input type="hidden" name="checklists_page" value="1">
                <span><?= esc($imsMaster('common.rows')) ?></span>
                <select class="rows-select" name="checklists_per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $checklistsPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($checklistsPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'checklists', 'checklists_page' => $checklistsPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($checklistsPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $checklistsPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildMasterDataUrl(['tab' => 'checklists', 'checklists_page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($checklistsPager['page'] < $checklistsPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'checklists', 'checklists_page' => $checklistsPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>

    <?php
    $supplierClassifications = $supplierClassifications ?? [];
    $supplierClassificationsPager = $supplierClassificationsPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]];
    ?>
    <div class="ims-master-panel <?= $activeTab === 'supplier_classifications' ? 'active' : '' ?>" data-tab-panel="supplier_classifications">
        <div class="ims-master-toolbar">
            <div>
                <h3><?= esc($imsMaster('supplierClassifications.title')) ?></h3>
                <span><?= esc($imsMaster('supplierClassifications.subtitle')) ?></span>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th><?= esc($imsMaster('columns.classification')) ?></th>
                        <th><?= esc($imsMaster('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($supplierClassifications === []) : ?>
                        <tr class="main-row"><td colspan="3" class="admin-muted"><?= esc($imsMaster('supplierClassifications.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($supplierClassifications as $classification) : ?>
                            <tr class="main-row">
                                <td data-label="ID">#<?= esc((string) $classification['id']) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.classification')) ?>"><strong><?= esc($classification['class_name']) ?></strong></td>
                                <td data-label="<?= esc($imsMaster('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.items.update')) : ?>
                                        <div class="ims-action-group">
                                            <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editSupplierClassification')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#supplierClassificationFormModal"
                                                data-action="<?= esc(site_url('ims/master-data/supplier-classifications/' . $classification['id'])) ?>"
                                                data-class-name="<?= esc($classification['class_name']) ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                        </div>
                                    <?php else : ?>
                                        <span class="admin-muted"><?= esc($imsMaster('common.noActions')) ?></span>
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
                <strong><?= esc((string) $supplierClassificationsPager['total']) ?></strong>&nbsp;<?= esc($imsMaster('records.supplierClassifications')) ?>
                <span class="admin-muted"><?= $supplierClassificationsPager['total'] > 0 ? '(' . esc((string) $supplierClassificationsPager['from']) . '-' . esc((string) $supplierClassificationsPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <?= $renderQueryInputs($masterQuery, ['supplier_classifications_page', 'supplier_classifications_per_page']) ?>
                <input type="hidden" name="tab" value="supplier_classifications">
                <input type="hidden" name="supplier_classifications_page" value="1">
                <span><?= esc($imsMaster('common.rows')) ?></span>
                <select class="rows-select" name="supplier_classifications_per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $supplierClassificationsPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($supplierClassificationsPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'supplier_classifications', 'supplier_classifications_page' => $supplierClassificationsPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($supplierClassificationsPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $supplierClassificationsPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildMasterDataUrl(['tab' => 'supplier_classifications', 'supplier_classifications_page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($supplierClassificationsPager['page'] < $supplierClassificationsPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'supplier_classifications', 'supplier_classifications_page' => $supplierClassificationsPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>

    <?php
    $locationRows   = $locationRows ?? [];
    $locationsPager = $locationsPager ?? ['page' => 1, 'perPage' => 10, 'total' => 0, 'totalPages' => 1, 'from' => 0, 'to' => 0, 'pages' => [1]];
    $parentOptions  = $parentOptions ?? [];
    ?>
    <div class="ims-master-panel <?= $activeTab === 'locations' ? 'active' : '' ?>" data-tab-panel="locations">
        <div class="ims-master-toolbar">
            <div>
                <h3><?= esc($imsMaster('locations.title')) ?></h3>
                <span><?= esc($imsMaster('locations.subtitle')) ?></span>
            </div>
            <?php if (lab_core_can('ims.warehouses.manage')) : ?>
                <button type="button" class="admin-btn primary" data-bs-toggle="modal" data-bs-target="#locationFormModal" data-mode="create"><?= esc($imsMaster('locations.add')) ?></button>
            <?php endif ?>
        </div>
        <div style="overflow-x:auto;">
            <table class="results-table app-responsive-table">
                <thead>
                    <tr>
                        <th><?= esc($imsMaster('columns.location')) ?></th>
                        <th><?= esc($imsMaster('columns.code')) ?></th>
                        <th><?= esc($imsMaster('columns.parent')) ?></th>
                        <th><?= esc($imsMaster('columns.temperature')) ?></th>
                        <th><?= esc($imsMaster('columns.humidity')) ?></th>
                        <th><?= esc($imsMaster('columns.access')) ?></th>
                        <th><?= esc($imsMaster('columns.status')) ?></th>
                        <th><?= esc($imsMaster('columns.action')) ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($locationRows === []) : ?>
                        <tr class="main-row"><td colspan="8" class="admin-muted"><?= esc($imsMaster('locations.empty')) ?></td></tr>
                    <?php else : ?>
                        <?php foreach ($locationRows as $loc) : ?>
                            <tr class="main-row">
                                <td data-label="<?= esc($imsMaster('columns.location')) ?>">
                                    <strong><?= esc($loc['name']) ?></strong>
                                    <?php if (! empty($loc['description'])) : ?><div class="admin-muted"><?= esc($loc['description']) ?></div><?php endif ?>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.code')) ?>"><?= esc($loc['code']) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.parent')) ?>"><?= esc($loc['parent_name'] ?? '-') ?></td>
                                <td data-label="<?= esc($imsMaster('columns.temperature')) ?>"><?= esc(($loc['temperature_min'] !== null ? $loc['temperature_min'] : '-') . ' ' . $imsMaster('common.to') . ' ' . ($loc['temperature_max'] !== null ? $loc['temperature_max'] : '-')) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.humidity')) ?>"><?= esc(($loc['humidity_min'] !== null ? $loc['humidity_min'] : '-') . ' ' . $imsMaster('common.to') . ' ' . ($loc['humidity_max'] !== null ? $loc['humidity_max'] : '-')) ?></td>
                                <td data-label="<?= esc($imsMaster('columns.access')) ?>">
                                    <span class="admin-badge <?= (int) ($loc['requires_restricted_access'] ?? 0) === 1 ? 'warning' : 'neutral' ?>">
                                        <?= (int) ($loc['requires_restricted_access'] ?? 0) === 1 ? esc($imsMaster('status.restricted')) : esc($imsMaster('status.open')) ?>
                                    </span>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.status')) ?>">
                                    <span class="admin-badge <?= (int) ($loc['is_active'] ?? 0) === 1 ? 'success' : 'neutral' ?>">
                                        <?= (int) ($loc['is_active'] ?? 0) === 1 ? esc($imsMaster('status.active')) : esc($imsMaster('status.inactive')) ?>
                                    </span>
                                </td>
                                <td data-label="<?= esc($imsMaster('columns.action')) ?>">
                                    <?php if (lab_core_can('ims.warehouses.manage')) : ?>
                                        <div class="ims-action-group">
                                            <button type="button" class="ims-action-btn" title="<?= esc($imsMaster('actions.editLocation')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#locationFormModal"
                                                data-mode="edit"
                                                data-action="<?= esc(site_url('ims/locations/' . $loc['id'])) ?>"
                                                data-location-id="<?= esc((string) $loc['id']) ?>"
                                                data-name="<?= esc($loc['name']) ?>"
                                                data-code="<?= esc($loc['code']) ?>"
                                                data-parent-id="<?= esc((string) ($loc['parent_id'] ?? '')) ?>"
                                                data-temperature-min="<?= esc((string) ($loc['temperature_min'] ?? '')) ?>"
                                                data-temperature-max="<?= esc((string) ($loc['temperature_max'] ?? '')) ?>"
                                                data-humidity-min="<?= esc((string) ($loc['humidity_min'] ?? '')) ?>"
                                                data-humidity-max="<?= esc((string) ($loc['humidity_max'] ?? '')) ?>"
                                                data-requires-restricted-access="<?= esc((string) ($loc['requires_restricted_access'] ?? 0)) ?>"
                                                data-is-active="<?= esc((string) ($loc['is_active'] ?? 0)) ?>"
                                                data-description="<?= esc($loc['description'] ?? '') ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <button type="button" class="ims-action-btn danger" title="<?= esc($imsMaster('actions.deleteLocation')) ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteModal"
                                                data-delete-title="<?= esc($imsMaster('delete.titles.location')) ?>"
                                                data-delete-message="<?= esc($imsMaster('delete.messages.location', [$loc['name']])) ?>"
                                                data-delete-action="<?= esc(site_url('ims/locations/' . $loc['id'] . '/delete')) ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    <?php else : ?>
                                        <span class="admin-muted"><?= esc($imsMaster('common.noActions')) ?></span>
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
                <strong><?= esc((string) $locationsPager['total']) ?></strong>&nbsp;<?= esc($imsMaster('records.locations')) ?>
                <span class="admin-muted"><?= $locationsPager['total'] > 0 ? '(' . esc((string) $locationsPager['from']) . '-' . esc((string) $locationsPager['to']) . ')' : '' ?></span>
            </div>
            <form method="get" class="rows-selector">
                <?= $renderQueryInputs($masterQuery, ['locations_page', 'locations_per_page']) ?>
                <input type="hidden" name="tab" value="locations">
                <input type="hidden" name="locations_page" value="1">
                <span><?= esc($imsMaster('common.rows')) ?></span>
                <select class="rows-select" name="locations_per_page" onchange="this.form.submit()">
                    <?php foreach ([10, 25, 50] as $option) : ?>
                        <option value="<?= $option ?>" <?= (int) $locationsPager['perPage'] === $option ? 'selected' : '' ?>><?= $option ?></option>
                    <?php endforeach ?>
                </select>
            </form>
            <div class="d-flex gap-2">
                <?php if ($locationsPager['page'] > 1) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'locations', 'locations_page' => $locationsPager['page'] - 1])) ?>">&lsaquo;</a>
                <?php endif ?>
                <?php foreach ($locationsPager['pages'] as $page) : ?>
                    <a class="page-btn <?= (int) $locationsPager['page'] === $page ? 'active' : '' ?>" href="<?= esc($buildMasterDataUrl(['tab' => 'locations', 'locations_page' => $page])) ?>"><?= esc((string) $page) ?></a>
                <?php endforeach ?>
                <?php if ($locationsPager['page'] < $locationsPager['totalPages']) : ?>
                    <a class="page-btn" href="<?= esc($buildMasterDataUrl(['tab' => 'locations', 'locations_page' => $locationsPager['page'] + 1])) ?>">&rsaquo;</a>
                <?php endif ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="brandFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/brands') ?>" id="brandForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="brands">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.brand.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="brand-name"><?= esc($imsMaster('fields.brandName')) ?></label>
                            <input id="brand-name" name="brand_name" type="text" value="<?= old('brand_name') ?>">
                            <?php if (isset($validation['brand_name'])) : ?><small class="admin-error"><?= esc($validation['brand_name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="brand-country"><?= esc($imsMaster('fields.country')) ?></label>
                            <input id="brand-country" name="country" type="text" value="<?= old('country') ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="brand-website"><?= esc($imsMaster('fields.website')) ?></label>
                            <input id="brand-website" name="website" type="text" value="<?= old('website') ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="brand-logo-file"><?= esc($imsMaster('fields.logoFile')) ?></label>
                            <input id="brand-logo-file" class="app-file-input" name="logo_file" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,image/*">
                            <?php if (isset($validation['logo_file'])) : ?><small class="admin-error"><?= esc($validation['logo_file']) ?></small><?php endif ?>
                            <small class="admin-muted"><?= esc($imsMaster('help.logoUpload')) ?></small>
                            <div class="ims-logo-preview" id="brandLogoPreviewWrap" hidden>
                                <img id="brandLogoPreview" src="" alt="<?= esc($imsMaster('alt.brandLogoPreview')) ?>">
                                <span id="brandLogoPreviewLabel"><?= esc($imsMaster('common.currentLogo')) ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="brand-is-active" name="is_active" <?= old('is_active') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="brand-is-active"><?= esc($imsMaster('status.active')) ?></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.brand.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="categoryFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/categories') ?>" id="categoryForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="categories">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.category.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="category-name"><?= esc($imsMaster('fields.categoryName')) ?></label>
                        <input id="category-name" name="category_name" type="text" value="<?= old('category_name') ?>">
                        <?php if (isset($validation['category_name'])) : ?><small class="admin-error"><?= esc($validation['category_name']) ?></small><?php endif ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.category.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="unitFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/units') ?>" id="unitForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="units">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.unit.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="unit-name"><?= esc($imsMaster('fields.unitName')) ?></label>
                            <input id="unit-name" name="unit_name" type="text" value="<?= old('unit_name') ?>">
                            <?php if (isset($validation['unit_name'])) : ?><small class="admin-error"><?= esc($validation['unit_name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="unit-type"><?= esc($imsMaster('fields.unitType')) ?></label>
                            <input id="unit-type" name="unit_type" type="text" value="<?= old('unit_type') ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="base-unit-id"><?= esc($imsMaster('fields.baseUnit')) ?></label>
                            <select id="base-unit-id" name="base_unit_id" class="filter-select">
                                <option value=""><?= esc($imsMaster('placeholders.selectBaseUnit')) ?></option>
                                <?php foreach ($unitOptions as $option) : ?>
                                    <option value="<?= esc((string) $option['id']) ?>" <?= old('base_unit_id') == $option['id'] ? 'selected' : '' ?>><?= esc($option['unit_name']) ?></option>
                                <?php endforeach ?>
                            </select>
                            <?php if (isset($validation['base_unit_id'])) : ?><small class="admin-error"><?= esc($validation['base_unit_id']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="conversion-factor"><?= esc($imsMaster('fields.conversionFactor')) ?></label>
                            <input id="conversion-factor" name="conversion_factor" type="text" value="<?= old('conversion_factor', '1') ?>">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" value="1" id="unit-is-active" name="is_active" <?= old('is_active') ? 'checked' : '' ?>>
                        <label class="form-check-label" for="unit-is-active"><?= esc($imsMaster('status.active')) ?></label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.unit.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="locationFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/locations') ?>" id="locationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="locations">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.location.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="loc-name"><?= esc($imsMaster('fields.locationName')) ?></label>
                            <input id="loc-name" name="name" type="text" value="<?= old('name') ?>">
                            <?php if (isset($validation['name'])) : ?><small class="admin-error"><?= esc($validation['name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="loc-code"><?= esc($imsMaster('fields.locationCode')) ?></label>
                            <input id="loc-code" name="code" type="text" value="<?= old('code') ?>">
                            <?php if (isset($validation['code'])) : ?><small class="admin-error"><?= esc($validation['code']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="loc-parent-id"><?= esc($imsMaster('fields.parentLocation')) ?></label>
                            <select id="loc-parent-id" name="parent_id" class="filter-select">
                                <option value=""><?= esc($imsMaster('placeholders.noParent')) ?></option>
                                <?php foreach ($parentOptions as $opt) : ?><option value="<?= esc((string) $opt['id']) ?>" <?= old('parent_id') == $opt['id'] ? 'selected' : '' ?>><?= esc($opt['name']) ?></option><?php endforeach ?>
                            </select>
                        </div>
                        <div class="admin-form-group">
                            <label for="loc-temp-min"><?= esc($imsMaster('fields.temperatureMin')) ?></label>
                            <input id="loc-temp-min" name="temperature_min" type="number" step="0.01" value="<?= old('temperature_min') ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="loc-temp-max"><?= esc($imsMaster('fields.temperatureMax')) ?></label>
                            <input id="loc-temp-max" name="temperature_max" type="number" step="0.01" value="<?= old('temperature_max') ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="loc-humidity-min"><?= esc($imsMaster('fields.humidityMin')) ?></label>
                            <input id="loc-humidity-min" name="humidity_min" type="number" step="0.01" value="<?= old('humidity_min') ?>">
                        </div>
                        <div class="admin-form-group">
                            <label for="loc-humidity-max"><?= esc($imsMaster('fields.humidityMax')) ?></label>
                            <input id="loc-humidity-max" name="humidity_max" type="number" step="0.01" value="<?= old('humidity_max') ?>">
                        </div>
                        <div class="admin-form-group full">
                            <label for="loc-description"><?= esc($imsMaster('fields.description')) ?></label>
                            <textarea id="loc-description" name="description" rows="3"><?= old('description') ?></textarea>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="loc-restricted" name="requires_restricted_access" <?= old('requires_restricted_access') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="loc-restricted"><?= esc($imsMaster('fields.requiresRestrictedAccess')) ?></label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="1" id="loc-active" name="is_active" <?= old('is_active', '1') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="loc-active"><?= esc($imsMaster('status.active')) ?></label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.location.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="checklistFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/checklists') ?>" id="checklistForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="checklists">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.checklist.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="checklist-name"><?= esc($imsMaster('fields.checklistName')) ?></label>
                            <input id="checklist-name" name="checklist_name" type="text" value="<?= old('checklist_name') ?>">
                            <?php if (isset($validation['checklist_name'])) : ?><small class="admin-error"><?= esc($validation['checklist_name']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group full">
                            <label for="checklist-description"><?= esc($imsMaster('fields.description')) ?></label>
                            <textarea id="checklist-description" name="description" rows="3"><?= old('description') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.checklist.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="checklistDetailFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/checklists/0/details') ?>" id="checklistDetailForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="checklists">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.checklistItem.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-muted mb-3" id="checklistDetailParentName"></div>
                    <div class="admin-form-grid">
                        <div class="admin-form-group">
                            <label for="checklist-detail-check-item"><?= esc($imsMaster('fields.checkItem')) ?></label>
                            <input id="checklist-detail-check-item" name="check_item" type="text" value="<?= old('check_item') ?>">
                            <?php if (isset($validation['check_item'])) : ?><small class="admin-error"><?= esc($validation['check_item']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group">
                            <label for="checklist-detail-weight"><?= esc($imsMaster('fields.weight')) ?></label>
                            <input id="checklist-detail-weight" name="weight" type="number" min="0" step="0.0001" value="<?= old('weight') ?>">
                            <?php if (isset($validation['weight'])) : ?><small class="admin-error"><?= esc($validation['weight']) ?></small><?php endif ?>
                        </div>
                        <div class="admin-form-group full">
                            <label for="checklist-detail-description"><?= esc($imsMaster('fields.description')) ?></label>
                            <textarea id="checklist-detail-description" name="description" rows="3"><?= old('description') ?></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.checklistItem.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="checklistClassificationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/checklists/0/classifications') ?>" id="checklistClassificationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="checklists">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title"><?= esc($imsMaster('modals.classificationConfig.title')) ?></h5>
                        <div class="admin-muted" id="checklistClassificationName"></div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div style="overflow-x:auto;">
                        <table class="results-table app-responsive-table">
                            <thead>
                                <tr>
                                    <th><?= esc($imsMaster('columns.classification')) ?></th>
                                    <th><?= esc($imsMaster('columns.rateMin')) ?></th>
                                    <th><?= esc($imsMaster('columns.rateMax')) ?></th>
                                    <th><?= esc($imsMaster('columns.description')) ?></th>
                                </tr>
                            </thead>
                            <tbody id="checklistClassificationRows">
                                <tr><td colspan="4" class="admin-muted"><?= esc($imsMaster('classificationConfig.empty')) ?></td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('common.confirm')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="supplierClassificationFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="<?= site_url('ims/master-data/supplier-classifications/0') ?>" id="supplierClassificationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" value="supplier_classifications">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title"><?= esc($imsMaster('modals.supplierClassification.title')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-group">
                        <label for="supplier-classification-name"><?= esc($imsMaster('fields.classificationName')) ?></label>
                        <input id="supplier-classification-name" name="class_name" type="text" value="<?= old('class_name') ?>">
                        <?php if (isset($validation['class_name'])) : ?><small class="admin-error"><?= esc($validation['class_name']) ?></small><?php endif ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="admin-btn primary"><?= esc($imsMaster('modals.supplierClassification.save')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="" id="deleteForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active_tab" id="deleteActiveTab" value="<?= esc($activeTab) ?>">
                <?= $renderQueryInputs($masterQuery) ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalTitle"><?= esc($imsMaster('delete.titles.record')) ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= esc($imsMaster('common.close')) ?>"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteModalMessage"><?= esc($imsMaster('delete.messages.record')) ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= esc($imsMaster('common.cancel')) ?></button>
                    <button type="submit" class="btn btn-danger"><?= esc($imsMaster('common.delete')) ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('page_js') ?>
<script>
    (() => {
        const checklistClassificationMap = <?= json_encode($checklistClassifications ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const i18n = <?= json_encode([
            'addBrand' => $imsMaster('modals.brand.addTitle'),
            'editBrand' => $imsMaster('modals.brand.editTitle'),
            'currentLogo' => $imsMaster('common.currentLogo'),
            'selectedLogo' => $imsMaster('common.selectedLogo'),
            'addCategory' => $imsMaster('modals.category.addTitle'),
            'editCategory' => $imsMaster('modals.category.editTitle'),
            'addUnit' => $imsMaster('modals.unit.addTitle'),
            'editUnit' => $imsMaster('modals.unit.editTitle'),
            'addChecklist' => $imsMaster('modals.checklist.addTitle'),
            'editChecklist' => $imsMaster('modals.checklist.editTitle'),
            'addChecklistItem' => $imsMaster('modals.checklistItem.addTitle'),
            'editChecklistItem' => $imsMaster('modals.checklistItem.editTitle'),
            'checklistPrefix' => $imsMaster('common.checklistPrefix'),
            'noClassificationConfig' => $imsMaster('classificationConfig.empty'),
            'classification' => $imsMaster('columns.classification'),
            'rateMin' => $imsMaster('columns.rateMin'),
            'rateMax' => $imsMaster('columns.rateMax'),
            'description' => $imsMaster('columns.description'),
            'score' => $imsMaster('columns.score'),
            'deleteRecord' => $imsMaster('delete.titles.record'),
            'deleteRecordMessage' => $imsMaster('delete.messages.record'),
            'addLocation' => $imsMaster('modals.location.addTitle'),
            'editLocation' => $imsMaster('modals.location.editTitle'),
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        const tabs = document.querySelectorAll('[data-tab-target]');
        const panels = document.querySelectorAll('[data-tab-panel]');
        const syncTabInUrl = (target) => {
            const url = new URL(window.location.href);
            url.searchParams.set('tab', target);
            window.history.replaceState({}, '', url.toString());
        };

        const activateTab = (target, options = {}) => {
            const { syncUrl = true } = options;
            tabs.forEach((tab) => tab.classList.toggle('active', tab.dataset.tabTarget === target));
            panels.forEach((panel) => panel.classList.toggle('active', panel.dataset.tabPanel === target));

            if (syncUrl) {
                syncTabInUrl(target);
            }
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activateTab(tab.dataset.tabTarget));
        });

        const brandModal = document.getElementById('brandFormModal');
        brandModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('brandForm');
            const title = brandModal.querySelector('.modal-title');
            const previewWrap = document.getElementById('brandLogoPreviewWrap');
            const previewImage = document.getElementById('brandLogoPreview');
            const previewLabel = document.getElementById('brandLogoPreviewLabel');
            const fileInput = document.getElementById('brand-logo-file');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = i18n.addBrand;
                form.action = '<?= site_url('ims/master-data/brands') ?>';
                form.reset();
                previewImage.src = '';
                previewWrap.hidden = true;
                previewLabel.textContent = i18n.currentLogo;
                fileInput.value = '';
                return;
            }

            title.textContent = i18n.editBrand;
            form.action = trigger.dataset.action;
            form.querySelector('[name="brand_name"]').value = trigger.dataset.brand_name || '';
            form.querySelector('[name="country"]').value = trigger.dataset.country || '';
            form.querySelector('[name="website"]').value = trigger.dataset.website || '';
            form.querySelector('[name="is_active"]').checked = trigger.dataset.is_active === '1';
            fileInput.value = '';
            if (trigger.dataset.logoUrl) {
                previewImage.src = trigger.dataset.logoUrl;
                previewWrap.hidden = false;
                previewLabel.textContent = i18n.currentLogo;
            } else {
                previewImage.src = '';
                previewWrap.hidden = true;
            }
        });

        const brandLogoInput = document.getElementById('brand-logo-file');
        brandLogoInput?.addEventListener('change', () => {
            const previewWrap = document.getElementById('brandLogoPreviewWrap');
            const previewImage = document.getElementById('brandLogoPreview');
            const previewLabel = document.getElementById('brandLogoPreviewLabel');
            const file = brandLogoInput.files?.[0];

            if (!file) {
                return;
            }

            previewImage.src = URL.createObjectURL(file);
            previewWrap.hidden = false;
            previewLabel.textContent = i18n.selectedLogo;
        });

        const categoryModal = document.getElementById('categoryFormModal');
        categoryModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('categoryForm');
            const title = categoryModal.querySelector('.modal-title');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = i18n.addCategory;
                form.action = '<?= site_url('ims/master-data/categories') ?>';
                form.reset();
                return;
            }

            title.textContent = i18n.editCategory;
            form.action = trigger.dataset.action;
            form.querySelector('[name="category_name"]').value = trigger.dataset.category_name || '';
        });

        const unitModal = document.getElementById('unitFormModal');
        unitModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('unitForm');
            const title = unitModal.querySelector('.modal-title');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = i18n.addUnit;
                form.action = '<?= site_url('ims/master-data/units') ?>';
                form.reset();
                form.querySelector('[name="conversion_factor"]').value = '1';
                return;
            }

            title.textContent = i18n.editUnit;
            form.action = trigger.dataset.action;
            form.querySelector('[name="unit_name"]').value = trigger.dataset.unit_name || '';
            form.querySelector('[name="unit_type"]').value = trigger.dataset.unit_type || '';
            form.querySelector('[name="base_unit_id"]').value = trigger.dataset.base_unit_id || '';
            form.querySelector('[name="conversion_factor"]').value = trigger.dataset.conversion_factor || '1';
            form.querySelector('[name="is_active"]').checked = trigger.dataset.is_active === '1';
        });

        const checklistModal = document.getElementById('checklistFormModal');
        checklistModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('checklistForm');
            const title = checklistModal.querySelector('.modal-title');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = i18n.addChecklist;
                form.action = '<?= site_url('ims/master-data/checklists') ?>';
                form.reset();
                return;
            }

            title.textContent = i18n.editChecklist;
            form.action = trigger.dataset.action;
            form.querySelector('[name="checklist_name"]').value = trigger.dataset.checklistName || '';
            form.querySelector('[name="description"]').value = trigger.dataset.description || '';
        });

        const checklistDetailModal = document.getElementById('checklistDetailFormModal');
        document.querySelectorAll('[data-bs-target="#checklistDetailFormModal"][data-action]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const form = document.getElementById('checklistDetailForm');
                form.action = trigger.dataset.action;
            });
        });
        checklistDetailModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('checklistDetailForm');
            const title = checklistDetailModal.querySelector('.modal-title');
            const parentName = document.getElementById('checklistDetailParentName');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = i18n.addChecklistItem;
                form.action = trigger?.dataset.action || '';
                form.reset();
                parentName.textContent = trigger?.dataset.checklistName ? `${i18n.checklistPrefix}: ${trigger.dataset.checklistName}` : '';
                return;
            }

            title.textContent = i18n.editChecklistItem;
            form.action = trigger.dataset.action;
            parentName.textContent = trigger.dataset.checklistName ? `${i18n.checklistPrefix}: ${trigger.dataset.checklistName}` : '';
            form.querySelector('[name="check_item"]').value = trigger.dataset.checkItem || '';
            form.querySelector('[name="weight"]').value = trigger.dataset.weight || '';
            form.querySelector('[name="description"]').value = trigger.dataset.description || '';
        });

        const checklistClassificationModal = document.getElementById('checklistClassificationModal');
        document.querySelectorAll('[data-bs-target="#checklistClassificationModal"][data-action]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const form = document.getElementById('checklistClassificationForm');
                form.action = trigger.dataset.action;
            });
        });
        checklistClassificationModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('checklistClassificationForm');
            const rowsTarget = document.getElementById('checklistClassificationRows');
            const checklistName = document.getElementById('checklistClassificationName');
            const rows = checklistClassificationMap[String(trigger?.dataset.checklistId || '')] || [];

            form.action = trigger?.dataset.action || '';
            checklistName.textContent = trigger?.dataset.checklistName ? `${i18n.checklistPrefix}: ${trigger.dataset.checklistName}` : '';
            rowsTarget.innerHTML = '';

            if (rows.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 4;
                cell.className = 'admin-muted';
                cell.textContent = i18n.noClassificationConfig;
                row.appendChild(cell);
                rowsTarget.appendChild(row);
                return;
            }

            rows.forEach((item) => {
                const row = document.createElement('tr');
                row.className = 'main-row';

                const nameCell = document.createElement('td');
                nameCell.dataset.label = i18n.classification;
                const nameStrong = document.createElement('strong');
                nameStrong.textContent = item.class_name || '-';
                nameCell.appendChild(nameStrong);

                const minCell = document.createElement('td');
                minCell.dataset.label = i18n.rateMin;
                const minInput = document.createElement('input');
                minInput.type = 'number';
                minInput.step = '0.01';
                minInput.min = '0';
                minInput.className = 'ims-inline-number-input';
                minInput.name = `classifications[${item.id}][rate_min]`;
                minInput.value = Number(item.rate_min || 0).toFixed(2);
                minCell.appendChild(minInput);

                const maxCell = document.createElement('td');
                maxCell.dataset.label = i18n.rateMax;
                const maxInput = document.createElement('input');
                maxInput.type = 'number';
                maxInput.step = '0.01';
                maxInput.min = '0';
                maxInput.className = 'ims-inline-number-input';
                maxInput.name = `classifications[${item.id}][rate_max]`;
                maxInput.value = Number(item.rate_max || 0).toFixed(2);
                maxCell.appendChild(maxInput);

                const descriptionCell = document.createElement('td');
                descriptionCell.dataset.label = i18n.description;
                descriptionCell.className = 'admin-muted';

                const updateDescription = () => {
                    const className = item.class_name || '-';
                    const rateMin = Number(minInput.value || 0).toFixed(2);
                    const rateMax = Number(maxInput.value || 0).toFixed(2);
                    descriptionCell.textContent = `${className}: ${rateMin} < ${i18n.score} <= ${rateMax}`;
                };

                minInput.addEventListener('input', updateDescription);
                maxInput.addEventListener('input', updateDescription);
                updateDescription();

                row.append(nameCell, minCell, maxCell, descriptionCell);
                rowsTarget.appendChild(row);
            });
        });

        const supplierClassificationModal = document.getElementById('supplierClassificationFormModal');
        document.querySelectorAll('[data-bs-target="#supplierClassificationFormModal"][data-action]').forEach((trigger) => {
            trigger.addEventListener('click', () => {
                const form = document.getElementById('supplierClassificationForm');
                form.action = trigger.dataset.action;
            });
        });
        supplierClassificationModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('supplierClassificationForm');

            form.action = trigger?.dataset.action || '';
            form.querySelector('[name="class_name"]').value = trigger?.dataset.className || '';
        });

        ['checklistDetailForm', 'checklistClassificationForm', 'supplierClassificationForm', 'deleteForm'].forEach((formId) => {
            const form = document.getElementById(formId);
            form?.addEventListener('submit', (event) => {
                const action = form.getAttribute('action') || '';
                if (action === '' || action.endsWith('/master-data')) {
                    event.preventDefault();
                }
            });
        });

        const deleteModal = document.getElementById('deleteModal');
        deleteModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            if (!trigger) {
                return;
            }

            const activeTabButton = document.querySelector('.ims-master-tab.active');
            document.getElementById('deleteActiveTab').value = activeTabButton?.dataset.tabTarget || '<?= esc($activeTab) ?>';
            document.getElementById('deleteForm').action = trigger.dataset.deleteAction;
            document.getElementById('deleteModalTitle').textContent = trigger.dataset.deleteTitle || i18n.deleteRecord;
            document.getElementById('deleteModalMessage').textContent = trigger.dataset.deleteMessage || i18n.deleteRecordMessage;
        });

        const locationModal = document.getElementById('locationFormModal');
        locationModal?.addEventListener('show.bs.modal', (event) => {
            const trigger = event.relatedTarget;
            const form = document.getElementById('locationForm');
            const title = locationModal.querySelector('.modal-title');

            if (!trigger || trigger.dataset.mode === 'create') {
                title.textContent = i18n.addLocation;
                form.action = '<?= site_url('ims/locations') ?>';
                form.reset();
                form.querySelector('[name="is_active"]').checked = true;
                return;
            }

            title.textContent = i18n.editLocation;
            form.action = trigger.dataset.action;
            form.querySelector('[name="name"]').value = trigger.dataset.name || '';
            form.querySelector('[name="code"]').value = trigger.dataset.code || '';
            form.querySelector('[name="parent_id"]').value = trigger.dataset.parentId || '';
            form.querySelector('[name="temperature_min"]').value = trigger.dataset.temperatureMin || '';
            form.querySelector('[name="temperature_max"]').value = trigger.dataset.temperatureMax || '';
            form.querySelector('[name="humidity_min"]').value = trigger.dataset.humidityMin || '';
            form.querySelector('[name="humidity_max"]').value = trigger.dataset.humidityMax || '';
            form.querySelector('[name="description"]').value = trigger.dataset.description || '';
            form.querySelector('[name="requires_restricted_access"]').checked = trigger.dataset.requiresRestrictedAccess === '1';
            form.querySelector('[name="is_active"]').checked = trigger.dataset.isActive === '1';
        });

        activateTab('<?= esc($activeTab) ?>', { syncUrl: false });

        <?php if (($modalState ?? null) === 'brand-form-modal') : ?>
            new bootstrap.Modal(document.getElementById('brandFormModal')).show();
        <?php elseif (($modalState ?? null) === 'category-form-modal') : ?>
            new bootstrap.Modal(document.getElementById('categoryFormModal')).show();
        <?php elseif (($modalState ?? null) === 'unit-form-modal') : ?>
            new bootstrap.Modal(document.getElementById('unitFormModal')).show();
        <?php elseif (($modalState ?? null) === 'checklist-form-modal') : ?>
            {
                const form = document.getElementById('checklistForm');
                const title = document.querySelector('#checklistFormModal .modal-title');
                <?php $checklistModalId = $checklistModalId ?? null; ?>
                <?php if (! empty($checklistModalId)) : ?>
                    form.action = '<?= site_url('ims/master-data/checklists/' . (int) $checklistModalId) ?>';
                    title.textContent = i18n.editChecklist;
                <?php else : ?>
                    form.action = '<?= site_url('ims/master-data/checklists') ?>';
                    title.textContent = i18n.addChecklist;
                <?php endif ?>
                new bootstrap.Modal(document.getElementById('checklistFormModal')).show();
            }
        <?php elseif (($modalState ?? null) === 'checklist-detail-form-modal') : ?>
            {
                const form = document.getElementById('checklistDetailForm');
                const title = document.querySelector('#checklistDetailFormModal .modal-title');
                const parentName = document.getElementById('checklistDetailParentName');
                <?php
                $checklistModalId = $checklistModalId ?? null;
                $checklistDetailModalId = $checklistDetailModalId ?? null;
                $checklistModalName = '';
                foreach (($checklists ?? []) as $checklistOption) {
                    if ((int) $checklistOption['id'] === (int) $checklistModalId) {
                        $checklistModalName = (string) $checklistOption['checklist_name'];
                        break;
                    }
                }
                ?>
                <?php if (! empty($checklistModalId) && ! empty($checklistDetailModalId)) : ?>
                    form.action = '<?= site_url('ims/master-data/checklists/' . (int) $checklistModalId . '/details/' . (int) $checklistDetailModalId) ?>';
                    title.textContent = i18n.editChecklistItem;
                <?php elseif (! empty($checklistModalId)) : ?>
                    form.action = '<?= site_url('ims/master-data/checklists/' . (int) $checklistModalId . '/details') ?>';
                    title.textContent = i18n.addChecklistItem;
                <?php endif ?>
                parentName.textContent = <?= json_encode($checklistModalName !== '' ? $imsMaster('common.checklistPrefix') . ': ' . $checklistModalName : '') ?>;
                new bootstrap.Modal(document.getElementById('checklistDetailFormModal')).show();
            }
        <?php elseif (($modalState ?? null) === 'supplier-classification-form-modal') : ?>
            {
                const form = document.getElementById('supplierClassificationForm');
                <?php $supplierClassificationModalId = $supplierClassificationModalId ?? null; ?>
                <?php if (! empty($supplierClassificationModalId)) : ?>
                    form.action = '<?= site_url('ims/master-data/supplier-classifications/' . (int) $supplierClassificationModalId) ?>';
                <?php endif ?>
                new bootstrap.Modal(document.getElementById('supplierClassificationFormModal')).show();
            }
        <?php elseif (($modalState ?? null) === 'location-form-modal') : ?>
            {
                const form = document.getElementById('locationForm');
                const title = document.querySelector('#locationFormModal .modal-title');
                <?php $locationModalId = $locationModalId ?? null; ?>
                <?php if (! empty($locationModalId)) : ?>
                    form.action = '<?= site_url('ims/locations/' . (int) $locationModalId) ?>';
                    title.textContent = i18n.editLocation;
                <?php else : ?>
                    form.action = '<?= site_url('ims/locations') ?>';
                    title.textContent = i18n.addLocation;
                <?php endif ?>
                new bootstrap.Modal(document.getElementById('locationFormModal')).show();
            }
        <?php endif ?>
    })();
</script>
<?= $this->endSection() ?>
