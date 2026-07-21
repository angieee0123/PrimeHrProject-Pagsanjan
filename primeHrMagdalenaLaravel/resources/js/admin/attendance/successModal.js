// Success Modal (generic confirmation shown after a correction is saved)
window.openSuccessModal = function() {
    document.getElementById('successModal').style.display = 'flex';
}

window.closeSuccessModal = function() {
    document.getElementById('successModal').style.display = 'none';
    if (window.reloadAfterSuccessModal) {
        window.reloadAfterSuccessModal = false;
        window.location.reload();
    }
}
