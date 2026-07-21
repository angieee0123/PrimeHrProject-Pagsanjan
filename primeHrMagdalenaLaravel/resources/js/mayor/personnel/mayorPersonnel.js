const mayorPersonnelData = window.mayorPersonnelData;
const totalEmployeesLabel = mayorPersonnelData.totalEmployeesLabel;
const totalDepartmentsLabel = mayorPersonnelData.totalDepartmentsLabel;

window.switchPersonnelTab = function (tab) {
    const isEmployees = tab === 'employees';
    document.getElementById('panelPersEmployees').style.display = isEmployees ? 'block' : 'none';
    document.getElementById('panelPersDepartments').style.display = isEmployees ? 'none' : 'block';
    document.getElementById('tabPersEmployees').classList.toggle('active', isEmployees);
    document.getElementById('tabPersDepartments').classList.toggle('active', !isEmployees);
    document.getElementById('personnelTabTitle').textContent = isEmployees ? 'Employee Roster' : 'Departments';
    document.getElementById('personnelTabSub').textContent = isEmployees
        ? totalEmployeesLabel + ' employees · read-only'
        : totalDepartmentsLabel + ' departments · read-only';
};

window.mayorFilterPersonnel = function () {
    const search = document.getElementById('mayorPersonnelSearch').value.toLowerCase().trim();
    const dept = document.getElementById('mayorPersonnelDept').value;
    const status = document.getElementById('mayorPersonnelStatus').value;
    const rows = document.querySelectorAll('#mayorPersonnelTable tbody tr[data-search]');
    let visibleCount = 0;

    rows.forEach(row => {
        const matchesSearch = !search || row.dataset.search.includes(search);
        const matchesDept = !dept || row.dataset.dept === dept;
        const matchesStatus = !status || row.dataset.status === status;
        const visible = matchesSearch && matchesDept && matchesStatus;
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    document.getElementById('mayorPersonnelNoResults').style.display = (rows.length && visibleCount === 0) ? 'block' : 'none';
};
