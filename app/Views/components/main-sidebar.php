<?php
$menu = lab_core_admin_menu();

if (! function_exists('lab_core_sidebar_icon')) {
    function lab_core_sidebar_icon(string $icon): string
    {
        return match ($icon) {
            'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'inventory' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="7.5 4.21 12 6.81 16.5 4.21"/><polyline points="7.5 19.79 7.5 14.6 3 12"/><polyline points="21 12 16.5 14.6 16.5 19.79"/><polyline points="12 22.08 12 16.81 21 12"/><polyline points="12 16.81 3 12"/></svg>',
            default => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" /><polyline points="9 22 9 12 15 12 15 22" /></svg>',
        };
    }
}
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside class="sidebar" id="sidebar">
    <?php foreach ($menu as $index => $item) : ?>
        <?php $children = $item['children'] ?? []; ?>
        <?php $isActive = lab_core_menu_is_active($item['path'], $children, $item['match'] ?? 'prefix'); ?>
        <?php if ($children !== []) : ?>
            <?php $subId = 'sub-' . $index; ?>
            <div class="sidebar-group">
                <div class="sidebar-item has-sub <?= $isActive ? 'active open' : '' ?>" onclick="toggleSub(this, '<?= esc($subId) ?>')">
                    <?= lab_core_sidebar_icon($item['icon'] ?? 'home') ?>
                    <span><?= esc($item['label']) ?></span>
                    <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </div>
                <div class="sidebar-sub <?= $isActive ? 'open' : '' ?>" id="<?= esc($subId) ?>">
                    <?php foreach ($children as $child) : ?>
                        <a class="sidebar-sub-item <?= lab_core_menu_is_active($child['path'], [], $child['match'] ?? 'prefix') ? 'active' : '' ?>" href="<?= esc($child['url']) ?>"><?= esc($child['label']) ?></a>
                    <?php endforeach ?>
                </div>
            </div>
        <?php else : ?>
            <a class="sidebar-item <?= $isActive ? 'active' : '' ?>" href="<?= esc($item['url']) ?>">
                <?= lab_core_sidebar_icon($item['icon'] ?? 'home') ?>
                <span><?= esc($item['label']) ?></span>
            </a>
        <?php endif ?>
    <?php endforeach ?>

    <a class="sidebar-item" style="margin-top: auto;" href="<?= site_url('logout') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
            <polyline points="16 17 21 12 16 7" />
            <line x1="21" y1="12" x2="9" y2="12" />
        </svg>
        <span>Logout</span>
    </a>
</aside>
