<?php
$currentUser = lab_core_current_user();
$hideSidebar = $hideSidebar ?? false;
$currentLocale = lab_core_current_locale();
$supportedLocales = lab_core_supported_locales();
?>
<nav class="top-nav">
    <?php if (! $hideSidebar) : ?>
        <button class="menu-toggle-btn" id="menuToggle" title="Toggle menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="6" x2="21" y2="6" />
                <line x1="3" y1="12" x2="21" y2="12" />
                <line x1="3" y1="18" x2="21" y2="18" />
            </svg>
        </button>
    <?php endif; ?>
    <a href="<?= lab_core_home_url() ?>" class="nav-brand"><?= esc(lab_core_company_name()) ?></a>


    <div class="nav-actions">
        <a class="nav-icon-btn" title="<?= esc(lang('App.nav.dashboard')) ?>" href="<?= lab_core_home_url() ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                <polyline points="9 22 9 12 15 12 15 22" />
            </svg>
        </a>
        <?php if (lab_core_can('core.roles.view')) : ?>
            <a class="nav-icon-btn" title="<?= esc(lang('App.nav.roles')) ?>" href="<?= site_url('admin/roles') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="7" r="4" />
                    <path d="M17 11v-1a4 4 0 0 0-4-4" />
                    <path d="M5 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2" />
                    <path d="M17 8h4" />
                    <path d="M19 6v4" />
                </svg>
            </a>
        <?php endif; ?>
        <?php if (lab_core_can('core.company_profile.view')) : ?>
            <a class="nav-icon-btn" title="<?= esc(lang('App.nav.companyProfile')) ?>" href="<?= site_url('admin/company-profile') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="16" rx="2" />
                    <path d="M7 8h10" />
                    <path d="M7 12h10" />
                    <path d="M7 16h6" />
                </svg>
            </a>
        <?php endif; ?>
        <div class="nav-language-switch" title="<?= esc(lang('App.language.label')) ?>">
            <button type="button" class="nav-language-current" aria-label="<?= esc(lang('App.language.label')) ?>">
                <?= esc(strtoupper($currentLocale)) ?>
            </button>
            <div class="nav-language-menu">
                <?php foreach ($supportedLocales as $locale => $label) : ?>
                    <a class="<?= $currentLocale === $locale ? 'active' : '' ?>" href="<?= site_url('language/' . $locale) ?>">
                        <?= esc($label) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php if ($currentUser !== null) : ?>
            <a class="nav-icon-btn" title="<?= esc(lang('App.nav.logout')) ?>" href="<?= site_url('logout') ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                    <polyline points="16 17 21 12 16 7" />
                    <line x1="21" y1="12" x2="9" y2="12" />
                </svg>
            </a>
        <?php endif; ?>
        <div class="nav-avatar"><?= esc(lab_core_user_initials()) ?></div>
        <div class="nav-user-info">
            <span class="name"><?= esc(lab_core_user_display_name()) ?></span>
            <span class="org"><?= esc(lab_core_user_org()) ?></span>
        </div>
    </div>
</nav>
