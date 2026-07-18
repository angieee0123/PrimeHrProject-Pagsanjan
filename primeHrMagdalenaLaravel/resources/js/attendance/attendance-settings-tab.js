// Attendance Settings Tab - Exemption Configuration
const exemptionCheckboxFields = [
    'exempt_from_abandoned',
    'exempt_from_incomplete',
    'am_in_not_required',
    'am_out_not_required',
    'pm_in_not_required',
    'pm_out_not_required',
    'auto_fill_am_out',
    'auto_fill_pm_in',
];

function loadExemptionOptions() {
    const type = document.getElementById('exemptionType').value;
    const select = document.getElementById('exemptionReference');
    const label = document.getElementById('exemptionTypeLabel');

    select.innerHTML = '<option value="">Loading...</option>';

    if (!type) {
        select.innerHTML = '<option value="">Select an option</option>';
        label.textContent = 'Item';
        return;
    }

    label.textContent = type.charAt(0).toUpperCase() + type.slice(1);

    fetch(`/admin/attendance/exemptions/options?type=${type}`)
        .then(response => response.json())
        .then(data => {
            select.innerHTML = '<option value="">Select ' + type + '</option>';
            data.forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                select.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading options:', error);
            select.innerHTML = '<option value="">Error loading options</option>';
        });
}

function openAddExemptionModal() {
    document.getElementById('exemptionModalTitle').textContent = 'Add Exemption';
    document.getElementById('exemptionForm').reset();
    document.getElementById('exemptionId').value = '';
    document.getElementById('autoFillAmOut').checked = true;
    document.getElementById('autoFillPmIn').checked = true;
    document.getElementById('exemptionModal').style.display = 'flex';
}

function closeExemptionModal() {
    document.getElementById('exemptionModal').style.display = 'none';
}

function saveExemption(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const exemptionId = document.getElementById('exemptionId').value;
    const url = exemptionId ? `/admin/attendance/exemptions/${exemptionId}` : '/admin/attendance/exemptions';
    const method = exemptionId ? 'PUT' : 'POST';

    const data = {};
    formData.forEach((value, key) => {
        if (key === 'exemption_id') {
            return;
        }
        if (exemptionCheckboxFields.includes(key)) {
            data[key] = true;
        } else {
            data[key] = value || null;
        }
    });

    exemptionCheckboxFields.forEach(field => {
        if (!formData.has(field)) {
            data[field] = false;
        }
    });

    fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeExemptionModal();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to save exemption'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while saving the exemption');
    });
}

function editExemption(id) {
    fetch(`/admin/attendance/exemptions/${id}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('exemptionModalTitle').textContent = 'Edit Exemption';
            document.getElementById('exemptionId').value = data.id;
            document.getElementById('exemptionType').value = data.exemption_type;
            document.getElementById('exemptionStartDate').value = data.start_date ? data.start_date.substring(0, 10) : '';
            document.getElementById('exemptionEndDate').value = data.end_date ? data.end_date.substring(0, 10) : '';
            document.getElementById('amInNotRequired').checked = !!data.am_in_not_required;
            document.getElementById('amOutNotRequired').checked = !!data.am_out_not_required;
            document.getElementById('pmInNotRequired').checked = !!data.pm_in_not_required;
            document.getElementById('pmOutNotRequired').checked = !!data.pm_out_not_required;
            document.getElementById('autoFillAmOut').checked = data.auto_fill_am_out !== false;
            document.getElementById('autoFillPmIn').checked = data.auto_fill_pm_in !== false;
            document.getElementById('exemptAbandoned').checked = !!data.exempt_from_abandoned;
            document.getElementById('exemptIncomplete').checked = !!data.exempt_from_incomplete;
            document.getElementById('exemptionReason').value = data.reason || '';

            loadExemptionOptions();
            setTimeout(() => {
                document.getElementById('exemptionReference').value = data.reference_id;
            }, 500);

            document.getElementById('exemptionModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load exemption details');
        });
}

function deleteExemption(id) {
    if (!confirm('Are you sure you want to delete this exemption?')) {
        return;
    }

    fetch(`/admin/attendance/exemptions/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to delete exemption'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while deleting the exemption');
    });
}

window.loadExemptionOptions = loadExemptionOptions;
window.openAddExemptionModal = openAddExemptionModal;
window.closeExemptionModal = closeExemptionModal;
window.saveExemption = saveExemption;
window.editExemption = editExemption;
window.deleteExemption = deleteExemption;
