window.applyFilters = function () {
    const dept = document.getElementById('filterDept').value;
    const type = document.getElementById('filterType').value;
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const matchDept = !dept || row.dataset.dept === dept;
        const matchType = !type || row.dataset.type === type;
        const show = matchDept && matchType;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees';
};

window.searchEmployees = function (query) {
    const searchTerm = query.toLowerCase();
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const position = row.querySelector('.position-cell')?.textContent.toLowerCase() || '';
        const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
        const show = name.includes(searchTerm) || id.includes(searchTerm) || position.includes(searchTerm) || dept.includes(searchTerm);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees (filtered)';
};
