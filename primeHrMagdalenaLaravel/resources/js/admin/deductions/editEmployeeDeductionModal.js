function openEditEmployeeDeductionModal() {
    document.getElementById('editEmployeeDeductionModal').classList.add('active');
}

function closeEditEmployeeDeductionModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editEmployeeDeductionModal').classList.remove('active');
    document.getElementById('editEmployeeDeductionForm').reset();
}

window.openEditEmployeeDeductionModal = openEditEmployeeDeductionModal;
window.closeEditEmployeeDeductionModal = closeEditEmployeeDeductionModal;
