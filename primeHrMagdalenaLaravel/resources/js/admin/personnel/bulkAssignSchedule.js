// Bulk Assign Schedule Modal

function closeBulkScheduleModal() {
    document.getElementById('bulkScheduleModal').style.display = 'none';
    document.body.style.overflow = '';
    clearBulkFilters();
}

function toggleAllEmployees(checkbox) {
    document.querySelectorAll('.employee-checkbox').forEach(cb => {
        if (cb.closest('.bulk-emp-item').style.display !== 'none') {
            cb.checked = checkbox.checked;
        }
    });
    updateSelectedCount();
}

function filterBulkEmployees() {
    const deptFilter  = document.getElementById('bulkFilterDepartment').value;
    const desigFilter = document.getElementById('bulkFilterDesignation').value;
    document.querySelectorAll('.bulk-emp-item').forEach(item => {
        const match = (!deptFilter  || item.dataset.departmentId  === deptFilter)
                   && (!desigFilter || item.dataset.designationId === desigFilter);
        item.style.display = match ? 'flex' : 'none';
        if (!match) item.querySelector('.employee-checkbox').checked = false;
    });
    updateSelectedCount();
    updateSelectAllCheckbox();
}

function selectFilteredEmployees() {
    document.querySelectorAll('.bulk-emp-item').forEach(item => {
        if (item.style.display !== 'none') item.querySelector('.employee-checkbox').checked = true;
    });
    updateSelectedCount();
    updateSelectAllCheckbox();
}

function clearBulkFilters() {
    document.getElementById('bulkFilterDepartment').value  = '';
    document.getElementById('bulkFilterDesignation').value = '';
    document.querySelectorAll('.bulk-emp-item').forEach(item => item.style.display = 'flex');
    updateSelectAllCheckbox();
}

function updateSelectAllCheckbox() {
    const visible   = Array.from(document.querySelectorAll('.bulk-emp-item')).filter(i => i.style.display !== 'none');
    const checked   = visible.filter(i => i.querySelector('.employee-checkbox').checked);
    const selectAll = document.getElementById('selectAllEmployees');
    selectAll.checked       = visible.length > 0 && checked.length === visible.length;
    selectAll.indeterminate = checked.length > 0 && checked.length < visible.length;
}

function updateSelectedCount() {
    document.getElementById('selectedCount').textContent = document.querySelectorAll('.employee-checkbox:checked').length;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.employee-checkbox').forEach(cb => {
        cb.addEventListener('change', () => { updateSelectedCount(); updateSelectAllCheckbox(); });
    });

    document.getElementById('bulkFilterDepartment').addEventListener('change', function () {
        const deptId = this.value;
        const desigSelect = document.getElementById('bulkFilterDesignation');
        if (!deptId) { desigSelect.innerHTML = '<option value="">All Designations</option>'; return; }
        fetch(`/admin/departments/${deptId}/designations`)
            .then(r => r.json())
            .then(data => {
                desigSelect.innerHTML = '<option value="">All Designations</option>' +
                    data.map(d => `<option value="${d.id}">${d.title}</option>`).join('');
            });
    });
});

window.closeBulkScheduleModal = closeBulkScheduleModal;
window.toggleAllEmployees = toggleAllEmployees;
window.filterBulkEmployees = filterBulkEmployees;
window.selectFilteredEmployees = selectFilteredEmployees;
window.clearBulkFilters = clearBulkFilters;
