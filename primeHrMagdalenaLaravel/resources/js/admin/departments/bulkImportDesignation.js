window.openBulkImportDesignationModal = function()  { document.getElementById('bulk-import-designation-modal').classList.add('open');  document.body.style.overflow = 'hidden'; }
window.closeBulkImportDesignationModal = function() { document.getElementById('bulk-import-designation-modal').classList.remove('open'); document.body.style.overflow = ''; }

window.handleDesigFileSelect = function(input) {
    if (input.files.length) updateDesigDropZone(input.files[0].name);
}

window.handleDesigDrop = function(e) {
    e.preventDefault();
    document.getElementById('desig-drop-zone').style.borderColor = '#c7c5e8';
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('desig-csv-input').files = dt.files;
        updateDesigDropZone(file.name);
    }
}

function updateDesigDropZone(name) {
    document.getElementById('desig-drop-label').textContent = '✓ ' + name;
    document.getElementById('desig-drop-zone').style.borderColor = '#15803d';
    document.getElementById('desig-import-submit-btn').disabled = false;
}
