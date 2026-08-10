window.openBulkImportDesignationModal = function()  { document.getElementById('bulk-import-designation-modal').classList.add('open');  document.body.style.overflow = 'hidden'; }
window.closeBulkImportDesignationModal = function() { document.getElementById('bulk-import-designation-modal').classList.remove('open'); document.body.style.overflow = ''; }

window.handleDesigFileSelect = function(input) {
    if (input.files.length) updateDesigDropZone(input.files[0].name);
}

window.handleDesigDrop = function(e) {
    e.preventDefault();
    document.getElementById('desig-drop-zone').classList.remove('is-dragging');
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('desig-csv-input').files = dt.files;
        updateDesigDropZone(file.name);
    }
}

function updateDesigDropZone(name) {
    // State via a class, matching bulkImportDepartment.js — an inline border
    // colour here was the one part of the modal that ignored the palette.
    const zone = document.getElementById('desig-drop-zone');
    zone.classList.remove('is-dragging');
    zone.classList.add('has-file');
    document.getElementById('desig-drop-label').textContent = name;
    document.getElementById('desig-import-submit-btn').disabled = false;
}
