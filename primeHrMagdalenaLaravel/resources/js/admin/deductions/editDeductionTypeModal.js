function openEditDeductionTypeModal() {
    document.getElementById('editDeductionTypeModal').classList.add('active');
}

function closeEditDeductionTypeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editDeductionTypeModal').classList.remove('active');
}

function editDeductionType(code) {
    // Fetch deduction type data
    fetch(`/admin/deductions/types/${code}`)
        .then(response => response.json())
        .then(data => {
            // Populate form fields
            document.getElementById('edit_code').value = data.code;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_category').value = data.category;
            document.getElementById('edit_computation_type').value = data.computation_type;
            document.getElementById('edit_rate').value = data.percentage_rate || '';
            document.getElementById('edit_base_salary').value = data.base_salary_type || '';
            document.getElementById('edit_max_amount').value = data.max_amount || '';
            document.getElementById('edit_is_active').value = data.is_active ? '1' : '0';
            document.getElementById('edit_deducted_from_employee').value = data.deducted_from_employee ? '1' : '0';
            document.getElementById('edit_description').value = data.description || '';

            // Set form action
            document.getElementById('editDeductionTypeForm').action = `/admin/deductions/types/${code}`;

            // Update label based on computation type
            const rateLabel = document.getElementById('edit_rateLabel');
            if (data.computation_type === 'PERCENTAGE') {
                rateLabel.textContent = 'Rate (%)';
            } else if (data.computation_type === 'FIXED') {
                rateLabel.textContent = 'Amount';
            } else {
                rateLabel.textContent = 'Rate/Amount';
            }

            // Open modal
            openEditDeductionTypeModal();
        })
        .catch(error => {
            console.error('Error fetching deduction type:', error);
            alert('Failed to load deduction type data. Please try again.');
        });
}

document.getElementById('edit_computation_type')?.addEventListener('change', function() {
    const rateLabel = document.getElementById('edit_rateLabel');
    const rateInput = document.getElementById('edit_rate');

    if (this.value === 'PERCENTAGE') {
        rateLabel.textContent = 'Rate (%)';
        rateInput.placeholder = 'e.g., 9.00';
    } else if (this.value === 'FIXED') {
        rateLabel.textContent = 'Amount';
        rateInput.placeholder = 'e.g., 500.00';
    } else {
        rateLabel.textContent = 'Rate/Amount';
        rateInput.placeholder = 'N/A';
    }
});

window.openEditDeductionTypeModal = openEditDeductionTypeModal;
window.closeEditDeductionTypeModal = closeEditDeductionTypeModal;
window.editDeductionType = editDeductionType;
