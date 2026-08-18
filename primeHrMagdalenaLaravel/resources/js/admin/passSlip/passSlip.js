// Admin Pass Slip Dashboard — tab switching, filters, form preview modal
//
// Row action menus use the shared ⋮ menu in resources/js/app.js
// (window.toggleRowMenu), which both layouts load.

window.currentPassSlipId = null;

window.openPassSlipFormModal = function(id, name, empId, type, slipNumber) {
    window.currentPassSlipId = id;
    document.getElementById('psFormSlipNumber').textContent = 'PASS SLIP · ' + slipNumber;
    document.getElementById('psFormEmployeeName').textContent = name;
    document.getElementById('psFormEmployeeId').textContent = empId;
    document.getElementById('psFormType').textContent = type;

    const frame = document.getElementById('psFormFrame');
    frame.src = `/admin/passslip/${id}/view-form?embed=1`;

    document.getElementById('passSlipFormModal').style.display = 'flex';
}

window.closePassSlipFormModal = function() {
    document.getElementById('psFormFrame').src = 'about:blank';
    document.getElementById('passSlipFormModal').style.display = 'none';
    window.currentPassSlipId = null;
}

window.printPassSlipForm = function() {
    const frame = document.getElementById('psFormFrame');
    if (frame?.contentWindow) {
        frame.contentWindow.print();
        return;
    }
    if (!window.currentPassSlipId) return;
    const printWindow = window.open(`/admin/passslip/${window.currentPassSlipId}/view-form`, '_blank');
    if (printWindow) {
        printWindow.addEventListener('load', () => printWindow.print());
    }
}

window.downloadPassSlipForm = function() {
    if (!window.currentPassSlipId) return;
    window.location.href = `/admin/passslip/${window.currentPassSlipId}/download-form`;
}

window.switchPassSlipTab = function(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById('passslip-' + tabName + '-tab').style.display = 'block';
}

window.filterPassSlipRows = function(tabName) {
    const dept = document.getElementById('passSlipFilterDept').value;
    const type = document.getElementById('passSlipFilterType').value;
    const dateFrom = document.getElementById('passSlipFilterDateFrom').value;
    const dateTo = document.getElementById('passSlipFilterDateTo').value;
    const search = (document.getElementById('passSlipSearchInput')?.value || '').toLowerCase().trim();
    document.querySelectorAll('.passslip-' + tabName + '-row').forEach(row => {
        const matchDept = dept === 'all' || row.dataset.department === dept;
        const matchType = type === 'all' || row.dataset.type === type;
        const matchDateFrom = !dateFrom || row.dataset.slipDate >= dateFrom;
        const matchDateTo = !dateTo || row.dataset.slipDate <= dateTo;
        const matchSearch = search === '' || row.textContent.toLowerCase().includes(search);
        row.style.display = matchDept && matchType && matchDateFrom && matchDateTo && matchSearch ? '' : 'none';
    });
}

window.searchPassSlips = function() {
    filterPassSlipRows('pending');
    filterPassSlipRows('approved');
    filterPassSlipRows('disapproved');
}

// NOTE: disapprovePassSlip() lived here and asked for the reason with prompt() —
// no label, no 500-character ceiling, and nothing naming the slip being refused.
// Both decisions now open #passSlipDecisionModal instead; see
// passSlipDecisionModal.js.
