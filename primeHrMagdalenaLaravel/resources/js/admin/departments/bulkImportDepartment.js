window.openBulkImportModal = function()  { document.getElementById('bulk-import-modal').classList.add('open');  document.body.style.overflow = 'hidden'; }
window.closeBulkImportModal = function() { document.getElementById('bulk-import-modal').classList.remove('open'); document.body.style.overflow = ''; }

window.handleFileSelect = function(input) {
    if (input.files.length) updateDropZone(input.files[0].name);
}

window.handleDrop = function(e) {
    e.preventDefault();
    document.getElementById('drop-zone').style.borderColor = '#c7c5e8';
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('csv-file-input').files = dt.files;
        updateDropZone(file.name);
    }
}

function updateDropZone(name) {
    document.getElementById('drop-label').textContent = '✓ ' + name;
    document.getElementById('drop-zone').style.borderColor = '#15803d';
    document.getElementById('import-submit-btn').disabled = false;
}
