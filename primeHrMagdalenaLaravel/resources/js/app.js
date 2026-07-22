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
});
