// Admin Travel Order Dashboard — tab switching and the toolbar's CSV export

// The tabs in the order they are rendered. The Export button follows the open
// one, so it has to be knowable without reading the DOM's active class back.
const TRAVEL_TABS = ['pending', 'approved', 'disapproved'];

window.currentTravelTab = 'pending';

window.switchTab = function(tabName, clicked) {
    if (!TRAVEL_TABS.includes(tabName)) return;

    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    // The button used to be taken from `event.target`, which only exists when a
    // click is what called this. Approving an order redirects to ?tab=approved,
    // and the load handler below calls switchTab() directly — with no event,
    // `event.target` was `document`, so this threw *after* hiding every section
    // and the page came back with no table on it at all.
    const button = clicked
        || document.querySelectorAll('.seg-tabs .tab-btn')[TRAVEL_TABS.indexOf(tabName)];
    if (button) button.classList.add('active');

    const section = document.getElementById(tabName + '-tab');
    if (section) section.style.display = 'block';

    window.currentTravelTab = tabName;
    syncTravelOrderExportButton(tabName);
}

/**
 * The Export button names the tab it would export.
 *
 * One button above three tabs is ambiguous the moment the files differ, and
 * these three do: Pending carries days-pending and no approver, Approved names
 * who signed it, Disapproved carries the reason. Labelling it says which file
 * is coming before the download starts.
 */
function syncTravelOrderExportButton(tabName) {
    const label = document.getElementById('travelOrderExportLabel');
    if (!label) return;

    label.textContent = 'Export ' + tabName.charAt(0).toUpperCase() + tabName.slice(1);
}

// The toolbar controls and the topbar search box, as the endpoint names them.
// Both Export buttons send the same set: "Export All" means every *status*,
// not every record — a file that ignored the department you had selected would
// contradict the parameter block it prints at the top of itself.
const TRAVEL_EXPORT_FILTERS = {
    date_from:  'travelOrderFilterDateFrom',
    date_to:    'travelOrderFilterDateTo',
    department: 'travelOrderFilterDept',
    mode:       'travelOrderFilterMode',
    search:     'travelOrderSearchInput',
};

function travelOrderFilterQuery() {
    const params = new URLSearchParams();

    Object.entries(TRAVEL_EXPORT_FILTERS).forEach(([param, elementId]) => {
        const value = (document.getElementById(elementId)?.value || '').trim();
        // "all" is the selects' own word for no filter; sending it would print
        // "Department: all" where the report should read "All Departments".
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
function downloadTravelOrderExport(url) {
    if (!url) {
        alert('The export endpoint is unavailable. Please reload the page and try again.');
        return;
    }

    const query = travelOrderFilterQuery();
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
window.exportTravelOrders = function() {
    const button = document.getElementById('travelOrderExportBtn');
    const tab = window.currentTravelTab || 'pending';

    downloadTravelOrderExport(button?.dataset['exportUrl' + tab.charAt(0).toUpperCase() + tab.slice(1)]);
}

/** Export every travel order on file — all three statuses in one register. */
window.exportAllTravelOrders = function() {
    downloadTravelOrderExport(document.getElementById('travelOrderExportAllBtn')?.dataset.exportUrl);
}

window.navigateToPage = function(url) {
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    switchTab(TRAVEL_TABS.includes(activeTab) ? activeTab : 'pending');

    const highlightId = urlParams.get('highlight');
    if (highlightId && typeof window.viewOrder === 'function') {
        window.viewOrder(highlightId);
    }
});
