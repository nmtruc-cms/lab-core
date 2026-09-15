// ── SIDEBAR TOGGLE ──
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('sidebarOverlay');
const mainWrap = document.getElementById('mainWrap');
const pageFooter = document.querySelector('.page-footer');
const isMobileSidebar = () => window.innerWidth <= 767;

function updateFooterLeft(isOpen) {
    if (!pageFooter) {
        return;
    }

    if (window.innerWidth <= 767) {
        pageFooter.style.left = '0px';
    } else {
        pageFooter.style.left = isOpen
            ? 'var(--sidebar-width)'
            : 'var(--sidebar-width-collapsed)';
    }
}

function openSidebar(showOverlay = false) {
    if (!sidebar) {
        return;
    }

    sidebar.classList.add('open');
    overlay?.classList.toggle('active', showOverlay && isMobileSidebar());
    updateFooterLeft(true);
}

function closeSidebar() {
    if (!sidebar) {
        return;
    }

    sidebar.classList.remove('open');
    overlay?.classList.remove('active');
    updateFooterLeft(false);
}

if (menuToggle && sidebar && overlay) {
    menuToggle.addEventListener('click', () => {
        if (sidebar.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar(true);
        }
    });

    overlay.addEventListener('click', () => {
        closeSidebar();
    });

    window.addEventListener('resize', () => {
        updateFooterLeft(sidebar.classList.contains('open'));
        if (!isMobileSidebar()) {
            overlay.classList.remove('active');
        }
    });
}

if (sidebar) {
    sidebar.addEventListener('mouseenter', () => {
        if (!isMobileSidebar()) {
            openSidebar(false);
        }
    });

    sidebar.addEventListener('mouseleave', () => {
        if (!isMobileSidebar()) {
            closeSidebar();
        }
    });

    sidebar.querySelectorAll('.sidebar-sub-item').forEach((subItem) => {
        subItem.addEventListener('click', () => {
            const group = subItem.closest('.sidebar-group');
            const parent = group?.querySelector('.sidebar-item.has-sub');
            const sub = group?.querySelector('.sidebar-sub');

            openSidebar(false);
            parent?.classList.add('open', 'active');
            sub?.classList.add('open');
        });
    });
}

// ── SIDEBAR SUBMENU ──
function toggleSub(itemEl, subId) {
    const sub = document.getElementById(subId);

    if (!sidebar || !sub) {
        return;
    }

    if (!sidebar.classList.contains('open')) {
        openSidebar(true);
        sub.classList.add('open');
        itemEl.classList.add('open', 'active');
        return;
    }

    const isOpen = sub.classList.toggle('open');
    itemEl.classList.toggle('open', isOpen);

    document.querySelectorAll('.sidebar-item').forEach((item) => {
        if (!item.classList.contains('has-sub')) {
            item.classList.remove('active');
        }
    });

    if (isOpen) {
        itemEl.classList.add('active');
    }
}

function setSidebarActive(el) {
    document.querySelectorAll('.sidebar-sub').forEach((sub) => sub.classList.remove('open'));
    document.querySelectorAll('.sidebar-item.has-sub').forEach((item) => item.classList.remove('open', 'active'));
    document.querySelectorAll('.sidebar-item:not(.has-sub)').forEach((item) => item.classList.remove('active'));
    el.classList.add('active');
}

// ── INDUSTRY TABS ──
function setTab(el) {
    document.querySelectorAll('.ind-tab').forEach((tab) => tab.classList.remove('active'));
    el.classList.add('active');
}

// ── TOGGLE FILTERS ──
let filtersVisible = true;

function toggleFilters() {
    filtersVisible = !filtersVisible;
    const filterDropdowns = document.getElementById('filterDropdowns');
    const hideFiltersBtn = document.getElementById('hideFiltersBtn');

    if (!filterDropdowns || !hideFiltersBtn) {
        return;
    }

    filterDropdowns.style.display = filtersVisible ? '' : 'none';
    hideFiltersBtn.classList.toggle('collapsed', !filtersVisible);
    hideFiltersBtn.innerHTML = filtersVisible
        ? `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> Hide Filters <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg>`
        : `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg> Show Filters <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>`;
}

// ── ROW EXPAND/COLLAPSE ──
function toggleDetail(rowId, trEl) {
    const inner = document.getElementById('inner-' + rowId);
    const btn = document.getElementById('btn-' + rowId);

    if (!inner || !btn) {
        return;
    }

    const isOpen = inner.classList.toggle('show');
    btn.classList.toggle('open', isOpen);
}

if (document.getElementById('inner-row1') && document.getElementById('btn-row1')) {
    toggleDetail('row1', null);
}
