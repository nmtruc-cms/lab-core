/* ═══════════════════════════════════════════════════
   ModernAdmin — Shared JS
═══════════════════════════════════════════════════ */

/* ─── DARK MODE ─── */
(function() {
  const saved = localStorage.getItem('ma-theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
})();

function initDarkToggle() {
  const btn = document.getElementById('darkToggle');
  if (!btn) return;
  const icon = btn.querySelector('i');

  function syncIcon() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    icon.className = isDark ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
    btn.title = isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode';
  }
  syncIcon();

  btn.addEventListener('click', () => {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const next   = isDark ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('ma-theme', next);
    syncIcon();
  });
}

/* ─── SIDEBAR ─── */
function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const topbar  = document.getElementById('topbar');
  const main    = document.getElementById('main');
  const overlay = document.getElementById('sidebarOverlay');
  if (window.innerWidth < 992) {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
  } else {
    sidebar.classList.toggle('collapsed');
    topbar.classList.toggle('collapsed');
    main.classList.toggle('collapsed');
  }
}
function closeMobileSidebar() {
  document.getElementById('sidebar').classList.remove('mobile-open');
  document.getElementById('sidebarOverlay').classList.remove('active');
}
function toggleSub(el) {
  el.classList.toggle('open');
  el.nextElementSibling.classList.toggle('open');
}

/* ─── TOPBAR DROPDOWNS ─── */
function toggleDropdown(id) {
  document.querySelectorAll('.topbar-dropdown').forEach(d => {
    if (d.id !== id) d.classList.remove('open');
  });
  document.getElementById(id).classList.toggle('open');
}
document.addEventListener('click', e => {
  if (!e.target.closest('.topbar-dropdown'))
    document.querySelectorAll('.topbar-dropdown').forEach(d => d.classList.remove('open'));
});

/* ─── PAGINATION HELPER ─── */
function initPagination(opts) {
  /*
    opts = {
      tableId:      '#myTable',      // tbody selector
      paginationId: '#myPagination', // container
      infoId:       '#myInfo',       // optional info text
      rowsPerPage:  10,
      data: [ ...rows ]              // array of HTML strings
    }
  */
  const tbody   = document.querySelector(opts.tableId);
  const pagEl   = document.querySelector(opts.paginationId);
  const infoEl  = opts.infoId ? document.querySelector(opts.infoId) : null;
  const rpp     = opts.rowsPerPage || 10;
  let   current = 1;

  function totalPages() { return Math.ceil(opts.data.length / rpp); }

  function render(page) {
    current = page;
    const start = (page - 1) * rpp;
    const slice = opts.data.slice(start, start + rpp);
    tbody.innerHTML = slice.join('');

    // info text
    if (infoEl) {
      const end = Math.min(start + rpp, opts.data.length);
      infoEl.textContent = `Showing ${start + 1}–${end} of ${opts.data.length} entries`;
    }

    // pagination buttons
    const tp = totalPages();
    let html = '';

    html += `<button class="page-btn" onclick="window._pag['${opts.tableId}'].go(${current-1})" ${current===1?'disabled':''}>
               <i class="bi bi-chevron-left"></i></button>`;

    // page numbers with ellipsis
    const pages = [];
    for (let i = 1; i <= tp; i++) {
      if (i === 1 || i === tp || (i >= current - 1 && i <= current + 1)) pages.push(i);
      else if (pages[pages.length - 1] !== '…') pages.push('…');
    }
    pages.forEach(p => {
      if (p === '…') {
        html += `<button class="page-btn" disabled>…</button>`;
      } else {
        html += `<button class="page-btn${p===current?' active':''}" onclick="window._pag['${opts.tableId}'].go(${p})">${p}</button>`;
      }
    });

    html += `<button class="page-btn" onclick="window._pag['${opts.tableId}'].go(${current+1})" ${current===tp?'disabled':''}>
               <i class="bi bi-chevron-right"></i></button>`;

    pagEl.innerHTML = html;
  }

  // Register globally so onclick can reach
  window._pag = window._pag || {};
  window._pag[opts.tableId] = { go: (p) => { if (p >= 1 && p <= totalPages()) render(p); } };

  render(1);
}

/* ─── INIT ON DOM READY ─── */
document.addEventListener('DOMContentLoaded', initDarkToggle);