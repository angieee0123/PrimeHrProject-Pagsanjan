window.switchPendingRequestsTab = function (tab) {
    const isLeave = tab === 'leave';
    document.getElementById('pendingLeaveTabPanel').style.display = isLeave ? 'block' : 'none';
    document.getElementById('pendingPassSlipTabPanel').style.display = isLeave ? 'none' : 'block';
    document.getElementById('pendingTabLeaveBtn').classList.toggle('active', isLeave);
    document.getElementById('pendingTabPassSlipBtn').classList.toggle('active', !isLeave);
    document.getElementById('pendingRequestsViewAllBtn').onclick = () => window.location.href = isLeave ? '/admin/leave' : '/admin/passslip';
    closeAllRowMenus();
};

function closeAllRowMenus() {
    document.querySelectorAll('.leave-action-menu').forEach(m => {
        m.style.display = 'none';
        m.classList.remove('is-up');
    });
    document.querySelectorAll('.pr-action-btn').forEach(b => {
        b.classList.remove('is-open');
        b.setAttribute('aria-expanded', 'false');
    });
}

// Shared by both tabs' ⋮ buttons.
function toggleRowMenu(e) {
    e.stopPropagation();
    const btn = e.target.closest('button');
    const menu = btn.nextElementSibling;
    const wasOpen = menu.style.display !== 'none';

    closeAllRowMenus();
    if (wasOpen) return;

    menu.style.display = 'block';
    btn.classList.add('is-open');
    btn.setAttribute('aria-expanded', 'true');

    // The card clips its overflow, so a menu on a low row would be cut off —
    // flip it above the button when there isn't room underneath.
    const card = btn.closest('.table-section');
    if (card) {
        const menuBottom = menu.getBoundingClientRect().bottom;
        if (menuBottom > card.getBoundingClientRect().bottom - 8) {
            menu.classList.add('is-up');
        }
    }
}

window.togglePassSlipMenuDash = toggleRowMenu;
window.toggleLeaveMenu = toggleRowMenu;

window.approveLeave = function (e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been approved!');
    closeAllRowMenus();
};

window.disapproveLeave = function (e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been disapproved.');
    closeAllRowMenus();
};

window.viewLeaveDetails = function (e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    const details = listItem.querySelector('.enterprise-person span').textContent;
    alert('Leave details for ' + name + ':\n' + details);
    closeAllRowMenus();
};

document.addEventListener('click', closeAllRowMenus);

// Escape closes an open row menu.
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeAllRowMenus();
});
