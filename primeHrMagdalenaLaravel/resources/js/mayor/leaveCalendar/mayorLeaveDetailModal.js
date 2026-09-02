// The mayor's leave-detail modal (CS Form No. 6 preview) — read-only.
//
// Markup: views/mayor/leaveCalendar/modals/mayorLeaveDetailModal.blade.php
//
// A mirror of the admin's leaveDetailModal.js pointed at /mayor/leave/{id}/…,
// because EnsureRoleForArea closes the /admin prefix to the mayor: the admin
// module's iframe would have framed a 403 page rather than the form. The
// signature matches the admin's argument-for-argument — shared/leaveCalendar.js
// unpacks one marker payload for both surfaces, so the two openers have to
// take the same twelve values in the same order.

window.openMayorLeaveDetailModal = function (id, name, empId, type, from, to, days, reason, status, appNumber, attachmentUrl, remarks) {
    window.currentMayorLeaveApplicationId = id;
    document.getElementById('mayorLeaveAppNumber').textContent = 'CS FORM NO. 6 · ' + appNumber;
    document.getElementById('mayorLeaveEmployeeName').textContent = name;
    document.getElementById('mayorLeaveEmployeeId').textContent = empId;
    document.getElementById('mayorLeaveType').textContent = type;

    const statusBadge = document.getElementById('mayorLeaveStatus');
    statusBadge.textContent = status;
    statusBadge.className = 'badge-status ' +
        (status === 'Approved' ? 'processed' :
         status === 'Pending' ? 'pending' :
         status === 'Rejected' || status === 'Disapproved' ? 'on-hold' : 'cancelled');

    const formFrame = document.getElementById('mayorLeaveFormFrame');
    if (formFrame) {
        formFrame.src = `/mayor/leave/${id}/view-form?embed=1`;
    }

    const attachmentBtn = document.getElementById('mayorLeaveAttachmentBtn');
    if (attachmentUrl && attachmentUrl.trim() !== '') {
        attachmentBtn.style.display = 'flex';
        attachmentBtn.onclick = () => window.open(attachmentUrl, '_blank');
    } else {
        attachmentBtn.style.display = 'none';
    }

    document.getElementById('mayorLeaveDetailModal').style.display = 'flex';
};

window.closeMayorLeaveDetailModal = function () {
    const formFrame = document.getElementById('mayorLeaveFormFrame');
    if (formFrame) {
        formFrame.src = 'about:blank';
    }
    document.getElementById('mayorLeaveDetailModal').style.display = 'none';
};

window.printMayorLeaveForm = function () {
    const frame = document.getElementById('mayorLeaveFormFrame');
    if (frame?.contentWindow) {
        frame.contentWindow.print();
        return;
    }
    if (!window.currentMayorLeaveApplicationId) return;
    const printWindow = window.open(`/mayor/leave/${window.currentMayorLeaveApplicationId}/view-form`, '_blank');
    if (printWindow) {
        printWindow.addEventListener('load', () => printWindow.print());
    }
};

window.downloadMayorLeaveForm = function () {
    if (!window.currentMayorLeaveApplicationId) return;
    window.location.href = `/mayor/leave/${window.currentMayorLeaveApplicationId}/download-form`;
};
