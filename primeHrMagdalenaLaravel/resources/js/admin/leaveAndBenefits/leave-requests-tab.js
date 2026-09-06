// Leave Requests Tab

window.approveLeaveRequest = function(id, appNumber) {
    if (!confirm(`Are you sure you want to approve leave request ${appNumber}?`)) {
        return;
    }

    fetch(`/admin/leave/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Leave request approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve leave request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the leave request');
    });
};

window.openRejectModal = function(id, appNumber) {
    window.currentRejectId = id;
    window.currentRejectAppNumber = appNumber;
    document.getElementById('rejectModalTitle').textContent = `Reject ${appNumber}`;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectModal').style.display = 'flex';
};

window.closeRejectModal = function() {
    document.getElementById('rejectModal').style.display = 'none';
    window.currentRejectId = null;
    window.currentRejectAppNumber = null;
};

// Leave Request Pagination
let leaveRequestCurrentPage = 1;
let leaveRequestRowsPerPage = 10;
let leaveRequestTotalRows = 0;

window.changeLeaveRequestRowsPerPage = function() {
    leaveRequestRowsPerPage = parseInt(document.getElementById('leaveRequestRowsPerPage').value);
    leaveRequestCurrentPage = 1;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
};

window.renderLeaveRequestPagination = function() {
    const totalPages = Math.ceil(leaveRequestTotalRows / leaveRequestRowsPerPage);
    const paginationControls = document.getElementById('leaveRequestPaginationControls');
    if (!paginationControls) return;

    let html = '';

    html += `<button class="page-btn" ${leaveRequestCurrentPage === 1 ? 'disabled' : ''} onclick="changeLeaveRequestPage(${leaveRequestCurrentPage - 1})">‹</button>`;

    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === leaveRequestCurrentPage ? 'active' : ''}" onclick="changeLeaveRequestPage(${i})">${i}</button>`;
    }

    html += `<button class="page-btn" ${leaveRequestCurrentPage === totalPages ? 'disabled' : ''} onclick="changeLeaveRequestPage(${leaveRequestCurrentPage + 1})">›</button>`;

    paginationControls.innerHTML = html;
};

window.changeLeaveRequestPage = function(page) {
    const totalPages = Math.ceil(leaveRequestTotalRows / leaveRequestRowsPerPage);
    if (page < 1 || page > totalPages) return;
    leaveRequestCurrentPage = page;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
};

window.paginateLeaveRequestTable = function() {
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    const start = (leaveRequestCurrentPage - 1) * leaveRequestRowsPerPage;
    const end = start + leaveRequestRowsPerPage;
    let visibleCount = 0;

    rows.forEach((row, index) => {
        if (row.querySelector('.emp-cell')) {
            if (index >= start && index < end && row.style.display !== 'none') {
                row.style.display = '';
                visibleCount++;
            } else if (index < start || index >= end) {
                row.style.display = 'none';
            }
        }
    });

    const startEl = document.getElementById('leaveRequestRowStart');
    const endEl = document.getElementById('leaveRequestRowEnd');
    if (startEl) startEl.textContent = visibleCount > 0 ? start + 1 : 0;
    if (endEl) endEl.textContent = start + visibleCount;
};

window.applyAdminLeaveFilters = function() {
    const department = document.getElementById('filterDepartment')?.value || '';
    const type = document.getElementById('filterLeaveType')?.value || '';
    const status = document.getElementById('filterLeaveStatus')?.value || '';
    const dateFrom = document.getElementById('filterLeaveDateFrom')?.value || '';
    const dateTo = document.getElementById('filterLeaveDateTo')?.value || '';
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    let visible = 0;

    rows.forEach(row => {
        if (row.querySelector('.emp-cell')) {
            const matchDept = !department || row.dataset.department === department;
            const matchType = !type || row.dataset.type === type;
            const matchStatus = !status || row.dataset.status === status;
            // A leave that overlaps the window belongs in it: a request running
            // Jun 28 -> Jul 3 is part of both months. Both dates are ISO
            // (Y-m-d), so string comparison is date comparison. This mirrors
            // the export endpoint exactly, so the CSV holds the rows the
            // filtered table shows.
            const matchFrom = !dateFrom || row.dataset.endDate >= dateFrom;
            const matchTo = !dateTo || row.dataset.startDate <= dateTo;
            const show = matchDept && matchType && matchStatus && matchFrom && matchTo;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        }
    });

    const total = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);

    leaveRequestTotalRows = visible;
    leaveRequestCurrentPage = 1;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();

    const totalEl = document.getElementById('leaveRequestRowTotal');
    if (totalEl) totalEl.textContent = total;
};

// The row action menu.
//
// An open menu is moved to <body> and positioned against the viewport.
// `.table-wrapper` scrolls and `.glass-shell .table-section` carries a
// backdrop-filter, so an absolutely positioned dropdown inside the row is
// clipped — only its first item ("View Details") stayed visible while
// Approve/Disapprove were cut off. It is put back where it came from on
// close, so the Blade stays the one copy of this markup.

let openLeaveMenu = null;
let openLeaveMenuHome = null;
let openLeaveMenuButton = null;

function closeLeaveActionMenu() {
    if (!openLeaveMenu) return;

    openLeaveMenu.style.display = 'none';
    openLeaveMenu.style.position = 'absolute';
    openLeaveMenu.style.left = '';
    openLeaveMenu.style.top = '';
    openLeaveMenu.style.right = '0';
    openLeaveMenu.style.marginTop = '6px';
    openLeaveMenuHome?.appendChild(openLeaveMenu);

    openLeaveMenu = null;
    openLeaveMenuHome = null;
    openLeaveMenuButton = null;
}

window.toggleLeaveActionMenu = function(event, btn) {
    event.stopPropagation();

    if (openLeaveMenuButton === btn) {
        closeLeaveActionMenu();
        return;
    }

    closeLeaveActionMenu();

    const menu = btn.nextElementSibling;
    if (!menu) return;

    openLeaveMenu = menu;
    openLeaveMenuHome = btn.parentElement;
    openLeaveMenuButton = btn;

    document.body.appendChild(menu);
    menu.style.position = 'fixed';
    menu.style.right = 'auto';
    menu.style.marginTop = '0';
    menu.style.display = 'block';

    const anchor = btn.getBoundingClientRect();
    const box = menu.getBoundingClientRect();
    const gap = 6, margin = 8;

    let left = anchor.right - box.width;
    left = Math.min(left, window.innerWidth - box.width - margin);
    left = Math.max(margin, left);

    let top = anchor.bottom + gap;
    if (top + box.height > window.innerHeight - margin) {
        const above = anchor.top - box.height - gap;
        top = above >= margin ? above : Math.max(margin, window.innerHeight - box.height - margin);
    }

    menu.style.left = `${Math.round(left)}px`;
    menu.style.top = `${Math.round(top)}px`;
}

document.addEventListener('click', closeLeaveActionMenu);
// A menu placed against the viewport does not travel with its row, so any
// scroll or resize dismisses it rather than leaving it over an unrelated row.
window.addEventListener('scroll', closeLeaveActionMenu, true);
window.addEventListener('resize', closeLeaveActionMenu);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeLeaveActionMenu();
});

// Initialize reject button handler + pagination
document.addEventListener('DOMContentLoaded', function() {
    const confirmRejectBtn = document.getElementById('confirmRejectBtn');
    if (confirmRejectBtn) {
        confirmRejectBtn.addEventListener('click', function() {
            const reason = document.getElementById('rejectionReason').value.trim();

            if (!reason) {
                alert('Please provide a reason for rejection');
                return;
            }

            const btn = this;
            const originalContent = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/></svg> Rejecting...';

            fetch(`/admin/leave/${window.currentRejectId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ remarks: reason })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Rejected!';
                    btn.style.background = 'var(--theme-success)';
                    setTimeout(() => {
                        closeRejectModal();
                        location.reload();
                    }, 1000);
                } else {
                    alert(data.message || 'Failed to reject leave request');
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while rejecting the leave request');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            });
        });
    }

    // Initialize leave request pagination
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    leaveRequestTotalRows = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);

    renderLeaveRequestPagination();
    paginateLeaveRequestTable();

    // Open the detail modal directly when arriving from a notification link
    const highlightId = new URLSearchParams(window.location.search).get('highlight');
    if (highlightId) {
        const targetRow = document.querySelector(`[data-leave-app-id="${highlightId}"]`)?.closest('tr');
        if (targetRow) {
            targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetRow.style.background = 'var(--gp-bg-tint-2)';
        }
        document.querySelector(`[data-leave-app-id="${highlightId}"]`)?.click();
    }
});
