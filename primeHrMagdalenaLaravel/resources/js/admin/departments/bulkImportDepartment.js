window.openBulkImportModal = function()  { document.getElementById('bulk-import-modal').classList.add('open');  document.body.style.overflow = 'hidden'; }
window.closeBulkImportModal = function() { document.getElementById('bulk-import-modal').classList.remove('open'); document.body.style.overflow = ''; }

window.handleFileSelect = function(input) {
    if (input.files.length) updateDropZone(input.files[0].name);
}

window.handleDrop = function(e) {
    e.preventDefault();
    document.getElementById('drop-zone').classList.remove('is-dragging');
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('csv-file-input').files = dt.files;
        updateDropZone(file.name);
    }
}

function updateDropZone(name) {
    // State via a class, not an inline border colour: the previous version
    // wrote literal hexes here and in the markup's drag handlers, so the drop
    // zone was the one part of the modal that ignored the palette.
    const zone = document.getElementById('drop-zone');
    zone.classList.remove('is-dragging');
    zone.classList.add('has-file');
    document.getElementById('drop-label').textContent = name;
    document.getElementById('import-submit-btn').disabled = false;
}
