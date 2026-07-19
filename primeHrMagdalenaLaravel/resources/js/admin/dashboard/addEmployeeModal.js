window.openAddEmployee = function () {
    document.getElementById('addEmployeeModal').classList.add('show');
};

window.closeAddEmployee = function () {
    document.getElementById('addEmployeeModal').classList.remove('show');
    document.getElementById('addEmployeeForm').reset();
};

window.submitAddEmployee = function (e) {
    e.preventDefault();
    const form = document.getElementById('addEmployeeForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const data = Object.fromEntries(new FormData(form));
    alert('Employee added successfully!\\n\\n' + data.first_name + ' ' + data.last_name + ' (' + data.emp_type + ')\\n' + data.position + ' · ' + data.department);
    closeAddEmployee();
};

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeAddEmployee();
    }
});
