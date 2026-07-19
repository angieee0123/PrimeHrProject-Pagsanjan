window.switchPendingRequestsTab = function (tab) {
    const isLeave = tab === 'leave';
    document.getElementById('pendingLeaveTabPanel').style.display = isLeave ? 'block' : 'none';
    document.getElementById('pendingPassSlipTabPanel').style.display = isLeave ? 'none' : 'block';
    document.getElementById('pendingTabLeaveBtn').classList.toggle('active', isLeave);
    document.getElementById('pendingTabPassSlipBtn').classList.toggle('active', !isLeave);
    document.getElementById('pendingRequestsViewAllBtn').onclick = () => window.location.href = isLeave ? '/admin/leave' : '/admin/passslip';
    document.querySelectorAll('.leave-action-menu').forEach(m => m.style.display = 'none');
};

window.togglePassSlipMenuDash = function (e) {
    e.stopPropagation();
    const menu = e.target.closest('button').nextElementSibling;
    const allMenus = document.querySelectorAll('.leave-action-menu');
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
};

window.toggleLeaveMenu = function (e) {
    e.stopPropagation();
    const menu = e.target.closest('button').nextElementSibling;
    const allMenus = document.querySelectorAll('.leave-action-menu');
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
};

window.approveLeave = function (e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been approved!');
    listItem.querySelector('.leave-action-menu').style.display = 'none';
};

window.disapproveLeave = function (e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been disapproved.');
    listItem.querySelector('.leave-action-menu').style.display = 'none';
};

window.viewLeaveDetails = function (e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    const details = listItem.querySelector('.enterprise-person span').textContent;
    alert('Leave details for ' + name + ':\n' + details);
    listItem.querySelector('.leave-action-menu').style.display = 'none';
};

document.addEventListener('click', function () {
    document.querySelectorAll('.leave-action-menu').forEach(m => m.style.display = 'none');
});
