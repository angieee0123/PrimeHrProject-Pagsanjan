window.mayorFilterLeave = function () {
    const search = document.getElementById('mayorLeaveSearch').value.toLowerCase().trim();
    const type = document.getElementById('mayorLeaveType').value;
    const status = document.getElementById('mayorLeaveStatus').value;
    const rows = document.querySelectorAll('#mayorLeaveTable tbody tr[data-search]');
    let visibleCount = 0;

    rows.forEach(row => {
        const matchesSearch = !search || row.dataset.search.includes(search);
        const matchesType = !type || row.dataset.type === type;
        const matchesStatus = !status || row.dataset.status === status;
        const visible = matchesSearch && matchesType && matchesStatus;
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    document.getElementById('mayorLeaveNoResults').style.display = (rows.length && visibleCount === 0) ? 'block' : 'none';
};
