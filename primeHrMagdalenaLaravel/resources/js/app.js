import './bootstrap';

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
    try {
        localStorage.setItem(NAV_GROUP_STORE, JSON.stringify(names));
    } catch (e) {
        /* storage unavailable — the accordion still works for this page view */
    }
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
    };

    fit();

    groups.forEach(group => {
        group.querySelector('.nav-section-toggle')?.addEventListener('click', () => {
            if (open.includes(group)) {
                open = open.filter(g => g !== group);   // clicking an open one shuts it
            } else {
                open.push(group);                       // newest at the end
            }
            fit();
            writeOpenNavGroups(open.map(g => g.dataset.navGroup));
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
