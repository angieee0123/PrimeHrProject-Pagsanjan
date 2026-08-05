import './bootstrap';

/* ── Shared table row action menu (⋮) ───────────────────────────────────────
   One implementation for every table whose row has two or more actions, on
   both layouts (app.blade.php and employee.blade.php each load this file).

   Markup contract:
     <button class="row-menu-btn" data-menu="rowMenu{id}" onclick="toggleRowMenu(event)"
             aria-haspopup="menu" aria-expanded="false"> ⋮ </button>
     <div class="row-menu" id="rowMenu{id}" role="menu"> …row-menu-item… </div>

   The menu cannot stay inside its <td>: .table-section sets overflow:hidden, so
   an absolutely positioned menu is clipped at the card edge, and position:fixed
   does not escape either, because .glass-shell puts backdrop-filter on
   .table-section, which makes it the containing block for fixed descendants.
   So the open menu is moved to <body>, positioned from the button's viewport
   rect, and put back on close. */
let _openRowMenu = null;
let _rowMenuHome = null;

window.closeRowMenu = function () {
    document.querySelectorAll('.row-menu-btn.is-open').forEach(btn => {
        btn.classList.remove('is-open');
        btn.setAttribute('aria-expanded', 'false');
    });

    if (!_openRowMenu) return;
    _openRowMenu.classList.remove('is-open');
    _openRowMenu.style.top = '';
    _openRowMenu.style.left = '';
    // insertBefore with a null reference appends, which is what we want when the
    // menu was the last child of its cell.
    _rowMenuHome.parent.insertBefore(_openRowMenu, _rowMenuHome.next);
    _openRowMenu = null;
    _rowMenuHome = null;
};

window.toggleRowMenu = function (e) {
    e.stopPropagation();
    const btn = e.currentTarget;
    const menu = document.getElementById(btn.dataset.menu);
    if (!menu) return;

    const wasOpen = menu === _openRowMenu;
    window.closeRowMenu();
    if (wasOpen) return;

    _rowMenuHome = { parent: menu.parentNode, next: menu.nextSibling };
    document.body.appendChild(menu);
    menu.classList.add('is-open');

    const rect = btn.getBoundingClientRect();
    let top = rect.bottom + 6;
    if (top + menu.offsetHeight > window.innerHeight - 8) {
        top = Math.max(8, rect.top - menu.offsetHeight - 6);
    }
    menu.style.top = top + 'px';
    menu.style.left = Math.max(8, rect.right - menu.offsetWidth) + 'px';

    btn.classList.add('is-open');
    btn.setAttribute('aria-expanded', 'true');
    _openRowMenu = menu;
};

document.addEventListener('click', function (e) {
    // A handler can detach its own target — a form in the menu submitting, or a
    // row being re-rendered. Anything no longer in the document cannot be judged
    // as inside or outside, so leave the menu alone.
    if (!document.contains(e.target)) return;
    if (_openRowMenu && _openRowMenu.contains(e.target)) return;
    window.closeRowMenu();
});

document.addEventListener('keydown', e => { if (e.key === 'Escape') window.closeRowMenu(); });
// Capture phase: a scroll container's own scroll does not bubble to window.
window.addEventListener('scroll', () => window.closeRowMenu(), true);
window.addEventListener('resize', () => window.closeRowMenu());

// ── Logout confirmation modal ──
// Exposed on window because the sidebar buttons wire up via inline onclick.
window.openLogoutModal = function () {
    const modal = document.getElementById('logoutModal');
    if (!modal) return;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
};

window.closeLogoutModal = function () {
    const modal = document.getElementById('logoutModal');
    if (!modal) return;
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
};

window.confirmLogout = function () {
    const form = document.getElementById('logout-form');
    if (form) form.submit();
};

// Esc closes the logout confirmation (only when it's actually open).
document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    const modal = document.getElementById('logoutModal');
    if (modal && modal.classList.contains('open')) window.closeLogoutModal();
});

// ── Sidebar toggle (desktop collapse + mobile open/close) ──
document.addEventListener('DOMContentLoaded', () => {
    const sidebar       = document.getElementById('sidebar');
    const toggleBtn     = document.getElementById('toggle-btn');
    const logoText      = document.getElementById('logo-text');
    const navLabel      = document.getElementById('nav-label');
    const userInfo      = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn     = document.getElementById('mobile-menu-btn');
    const overlay       = document.getElementById('mobile-overlay');

    if (!sidebar) return;

    // Desktop collapse
    toggleBtn?.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText)  logoText.style.display  = collapsed ? 'none' : '';
        if (navLabel)  navLabel.style.display  = collapsed ? 'none' : '';
        if (userInfo)  userInfo.style.display  = collapsed ? 'none' : '';
        sidebarFooter?.classList.toggle('collapsed-footer', collapsed);
        // Group headers are hidden by CSS in the collapsed state, not here — an
        // inline display would fight the `.sidebar.collapsed` rules that also
        // force every group open while the rail is icons-only.
        sidebar.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
        // Every nav click is a full page load, not an SPA transition, so
        // without this the rail silently re-expanded on the very next page —
        // the click that just collapsed it was undone by navigating anywhere.
        // Mirrors the openNavGroups cookie below: plain (unencrypted, see
        // bootstrap/app.php) so the Blade sidebar can read it at render time.
        document.cookie = 'sidebarCollapsed=' + (collapsed ? '1' : '0') +
            ';path=/;max-age=31536000;samesite=lax';
    });

    // Mobile open
    mobileBtn?.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay?.classList.toggle('active');
    });

    // Mobile close via overlay
    overlay?.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });

    initNavGroups(sidebar);
});

// ── Collapsible sidebar sections ──
// Up to NAV_MAX_OPEN sections stay open; opening one more closes whichever was
// opened earliest (first in, first out).
//
// The cap alone is not enough to keep the sidebar off a scrollbar: three open
// sections need roughly 805px of sidebar, which overflows a typical laptop
// viewport of 600–650px. So after applying the cap we also measure, and keep
// closing the oldest until the nav actually fits. On a tall monitor you get the
// full three; on a short window it quietly settles for what there is room for.
const NAV_GROUP_STORE = 'primehris.openNavGroups';
const NAV_GROUP_COOKIE = 'openNavGroups';
const NAV_MAX_OPEN = 3;

function readOpenNavGroups() {
    try {
        const raw = JSON.parse(localStorage.getItem(NAV_GROUP_STORE));
        return Array.isArray(raw) ? raw : [];
    } catch (e) {
        return [];   // private mode / storage blocked / corrupt value
    }
}

function writeOpenNavGroups(names) {
    const value = JSON.stringify(names);
    try {
        localStorage.setItem(NAV_GROUP_STORE, value);
    } catch (e) {
        /* storage unavailable — the accordion still works for this page view */
    }
    // Mirrored into a cookie because localStorage is invisible to PHP, and the
    // Blade sidebar needs this state at render time. Without it the server
    // ships every section expanded and the paint() below snaps them shut after
    // the browser has already drawn the open rail — a flicker on every page.
    // Exempted from encryption in bootstrap/app.php so PHP reads plain JSON.
    document.cookie = NAV_GROUP_COOKIE + '=' + encodeURIComponent(value) +
        ';path=/;max-age=31536000;samesite=lax';
}

function setNavGroup(group, collapsed) {
    group.classList.toggle('is-collapsed', collapsed);
    group.querySelector('.nav-section-toggle')?.setAttribute('aria-expanded', String(!collapsed));
}

function initNavGroups(sidebar) {
    const nav = sidebar.querySelector('.sidebar-nav');
    const groups = Array.from(sidebar.querySelectorAll('.nav-group'));
    if (!nav || !groups.length) return;

    const byName = name => groups.find(g => g.dataset.navGroup === name);

    // `open` is ordered oldest first. Order is never shuffled, so eviction stays
    // honestly first-in-first-out.
    let open = readOpenNavGroups().map(byName).filter(Boolean);

    const current = groups.find(g => g.hasAttribute('data-holds-current'));
    if (current && !open.includes(current)) open.push(current);
    if (!open.length) open = [groups[0]];

    const paint = () => groups.forEach(g => setNavGroup(g, !open.includes(g)));

    // Drop the oldest section, but never the one holding the page being viewed —
    // skip past it to the next oldest. Promoting it to newest instead would only
    // delay the problem: further opens push it back to the front and it gets
    // closed anyway, folding away the page you are actually on.
    const evictOldest = () => {
        const i = open.findIndex(g => g !== current);
        if (i === -1) return false;   // only the current section is left
        open.splice(i, 1);
        return true;
    };

    const fit = () => {
        while (open.length > NAV_MAX_OPEN && evictOldest()) { /* trim to the cap */ }
        paint();
        // Measuring beats hardcoding a height: the rail is styled in CSS and
        // group sizes change whenever a nav item is added.
        while (open.length > 1 && nav.scrollHeight > nav.clientHeight && evictOldest()) {
            paint();
        }
        // Persisted on every run, not only on click. The cap and the height
        // eviction above also happen on load and on resize, and the next page
        // render has to reproduce whatever they settled on or it renders a
        // state this function will immediately undo.
        writeOpenNavGroups(open.map(g => g.dataset.navGroup));
    };

    fit();

    groups.forEach(group => {
        group.querySelector('.nav-section-toggle')?.addEventListener('click', () => {
            if (open.includes(group)) {
                open = open.filter(g => g !== group);   // clicking an open one shuts it
            } else {
                open.push(group);                       // newest at the end
            }
            fit();   // fit() persists the result
        });
    });

    // A taller window may now have room for a section that was evicted, and a
    // shorter one may need to drop another.
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(fit, 150);
    });
}
