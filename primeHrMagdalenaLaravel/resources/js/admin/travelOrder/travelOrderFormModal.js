// Printable Travel Order form — preview modal, print and PDF download.
//
// The sheet is rendered server-side and shown in an iframe, so the preview,
// the printout and the downloaded PDF are all the same document. Nothing here
// composes the form itself: an admin must never be shown a form built in the
// browser from the row's own text, which is a copy that can disagree with the
// record it claims to be.
//
// The row's ⋮ menu is the shared one in resources/js/app.js
// (window.toggleRowMenu), which both layouts load.

window.currentTravelOrderFormId = null;

window.openTravelOrderFormModal = function(id, name, empId, orderNumber, destination) {
    window.currentTravelOrderFormId = id;

    document.getElementById('toFormOrderNumber').textContent = 'TRAVEL ORDER · ' + orderNumber;
    document.getElementById('toFormEmployeeName').textContent = name;
    document.getElementById('toFormEmployeeId').textContent = empId;
    document.getElementById('toFormDestination').textContent = destination;

    document.getElementById('toFormFrame').src = `/admin/travelorder/${id}/view-form?embed=1`;

    document.getElementById('travelOrderFormModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

window.closeTravelOrderFormModal = function() {
    // Blanking the frame first stops the previous order's sheet flashing up
    // behind the next one while its request is still in flight.
    document.getElementById('toFormFrame').src = 'about:blank';
    document.getElementById('travelOrderFormModal').style.display = 'none';
    document.body.style.overflow = '';
    window.currentTravelOrderFormId = null;
}

window.printTravelOrderForm = function() {
    const frame = document.getElementById('toFormFrame');

    // Printing the frame prints what is already on screen. The window fallback
    // is for a browser that refuses print() on a frame it considers cross-origin
    // after a redirect, which would otherwise be a dead button.
    if (frame?.contentWindow) {
        frame.contentWindow.print();
        return;
    }

    if (!window.currentTravelOrderFormId) return;

    const printWindow = window.open(`/admin/travelorder/${window.currentTravelOrderFormId}/view-form`, '_blank');
    if (printWindow) {
        printWindow.addEventListener('load', () => printWindow.print());
    }
}

window.downloadTravelOrderForm = function() {
    if (!window.currentTravelOrderFormId) return;

    window.location.href = `/admin/travelorder/${window.currentTravelOrderFormId}/download-form`;
}
