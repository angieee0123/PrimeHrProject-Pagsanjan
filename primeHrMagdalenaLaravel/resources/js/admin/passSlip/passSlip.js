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

// The tabs in the order they are rendered. The Export button follows the open
// one, so it has to be knowable without reading the DOM's active class back.
const PASS_SLIP_TABS = ['pending', 'approved', 'disapproved'];

window.currentPassSlipTab = 'pending';

window.switchPassSlipTab = function(tabName, clicked) {
    if (!PASS_SLIP_TABS.includes(tabName)) return;

    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    // The button used to be taken from `event.target`, which only exists when a
    // click is what called this — so this could not be called programmatically
    // at all: with no event it threw *after* hiding every section, leaving the
    // page with no table on it.
    const button = clicked
        || document.querySelectorAll('.seg-tabs .tab-btn')[PASS_SLIP_TABS.indexOf(tabName)];
    if (button) button.classList.add('active');

    const section = document.getElementById('passslip-' + tabName + '-tab');
    if (section) section.style.display = 'block';

    window.currentPassSlipTab = tabName;
    syncPassSlipExportButton(tabName);
}

/**
 * The Export button names the tab it would export.
 *
 * One button above three tabs is ambiguous the moment the files differ, and
 * these three do: only an approved slip has minutes that reach the Daily Time
 * Record, and only a disapproved one carries a reason.
 */
function syncPassSlipExportButton(tabName) {
    const label = document.getElementById('passSlipExportLabel');
    if (!label) return;

    label.textContent = 'Export ' + tabName.charAt(0).toUpperCase() + tabName.slice(1);
}

// The toolbar controls and the topbar search box, as the endpoint names them.
// Both Export buttons send the same set: "Export All" means every *status*,
// not every record — a file that ignored the department you had selected would
// contradict the parameter block it prints at the top of itself.
const PASS_SLIP_EXPORT_FILTERS = {
    date_from:  'passSlipFilterDateFrom',
    date_to:    'passSlipFilterDateTo',
    department: 'passSlipFilterDept',
    type:       'passSlipFilterType',
    search:     'passSlipSearchInput',
};

function passSlipFilterQuery() {
    const params = new URLSearchParams();

    Object.entries(PASS_SLIP_EXPORT_FILTERS).forEach(([param, elementId]) => {
        const value = (document.getElementById(elementId)?.value || '').trim();
        // "all" is the selects' own word for no filter; sending it would print
        // "Type of Pass Slip: all" where the report should read "All Types".
        if (value && value !== 'all') params.set(param, value);
    });

    return params.toString();
}

/**
 * Send the browser to an export endpoint with the toolbar's filters attached.
 *
 * The endpoint answers with a Content-Disposition attachment, so the page does
 * not navigate away — which matters, because leaving would reset the open tab
 * and every filter on it.
 */
function downloadPassSlipExport(url) {
    if (!url) {
        alert('The export endpoint is unavailable. Please reload the page and try again.');
        return;
    }

    const query = passSlipFilterQuery();
    window.location.href = query ? url + '?' + query : url;
}

/**
 * Export the open tab as CSV.
 *
 * The filters are handed to the endpoint, which re-runs the query server-side.
 * Scraping the rendered table instead would export the ten rows on page one —
 * every tab paginates — with the filters silently applied and nothing in the
 * file recording them.
 */
window.exportPassSlips = function() {
    const button = document.getElementById('passSlipExportBtn');
    const tab = window.currentPassSlipTab || 'pending';

    downloadPassSlipExport(button?.dataset['exportUrl' + tab.charAt(0).toUpperCase() + tab.slice(1)]);
}

/** Export every pass slip on file — all three statuses in one register. */
window.exportAllPassSlips = function() {
    downloadPassSlipExport(document.getElementById('passSlipExportAllBtn')?.dataset.exportUrl);
}

// Approving or disapproving a slip redirects back with ?tab=approved /
// ?tab=disapproved. Nothing read it, so both decisions landed on Pending with
// the slip you had just acted on nowhere in sight.
document.addEventListener('DOMContentLoaded', function() {
    const requested = new URLSearchParams(window.location.search).get('tab');

    switchPassSlipTab(PASS_SLIP_TABS.includes(requested) ? requested : 'pending');
});

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
