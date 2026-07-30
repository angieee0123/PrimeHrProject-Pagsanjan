// Admin leave-detail modal (CS Form No. 6 preview). Split out of
// leave-requests-tab.js so pages that only need the modal — e.g. the admin
// dashboard's "On Leave Today" card — can load it without the whole
// requests-table script (filters, pagination, approve/reject flows).
// Markup: views/admin/leaveAndBenefits/modals/leave-detail-modal.blade.php

window.openAdminLeaveDetailModal = function(id, name, empId, type, from, to, days, reason, status, appNumber, attachmentUrl, remarks) {
    window.currentLeaveApplicationId = id;
    document.getElementById('adminLeaveAppNumber').textContent = 'CS FORM NO. 6 · ' + appNumber;
    document.getElementById('adminLeaveEmployeeName').textContent = name;
    document.getElementById('adminLeaveEmployeeId').textContent = empId;
    document.getElementById('adminLeaveType').textContent = type;

    const statusBadge = document.getElementById('adminLeaveStatus');
    statusBadge.textContent = status;
    statusBadge.className = 'badge-status ' +
        (status === 'Approved' ? 'processed' :
         status === 'Pending' ? 'pending' :
         status === 'Rejected' || status === 'Disapproved' ? 'on-hold' : 'cancelled');

    const formFrame = document.getElementById('adminLeaveFormFrame');
    if (formFrame) {
        formFrame.src = `/admin/leave/${id}/view-form?embed=1`;
    }

    const downloadBtn = document.getElementById('adminDownloadBtn');
    if (attachmentUrl && attachmentUrl.trim() !== '') {
        downloadBtn.style.display = 'flex';
        downloadBtn.onclick = () => window.open(attachmentUrl, '_blank');
    } else {
        downloadBtn.style.display = 'none';
    }

    document.getElementById('adminLeaveDetailModal').style.display = 'flex';
};

window.closeAdminLeaveDetailModal = function() {
    const formFrame = document.getElementById('adminLeaveFormFrame');
    if (formFrame) {
        formFrame.src = 'about:blank';
    }
    document.getElementById('adminLeaveDetailModal').style.display = 'none';
};

window.printLeaveForm = function() {
    const frame = document.getElementById('adminLeaveFormFrame');
    if (frame?.contentWindow) {
        frame.contentWindow.print();
        return;
    }
    if (!window.currentLeaveApplicationId) return;
    const printWindow = window.open(`/admin/leave/${window.currentLeaveApplicationId}/view-form`, '_blank');
    if (printWindow) {
        printWindow.addEventListener('load', () => printWindow.print());
    }
};

window.downloadLeaveForm = function() {
    if (!window.currentLeaveApplicationId) return;
    window.location.href = `/admin/leave/${window.currentLeaveApplicationId}/download-form`;
};
