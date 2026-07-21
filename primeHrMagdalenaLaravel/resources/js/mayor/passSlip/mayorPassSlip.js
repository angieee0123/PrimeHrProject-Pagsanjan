window.mayorFilterSlip = function () {
    const search = document.getElementById('mayorSlipSearch').value.toLowerCase().trim();
    const status = document.getElementById('mayorSlipStatus').value;
    const rows = document.querySelectorAll('#mayorSlipTable tbody tr[data-search]');
    let visibleCount = 0;

    rows.forEach(row => {
        const matchesSearch = !search || row.dataset.search.includes(search);
        const matchesStatus = !status || row.dataset.status === status;
        const visible = matchesSearch && matchesStatus;
        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
    });

    document.getElementById('mayorSlipNoResults').style.display = (rows.length && visibleCount === 0) ? 'block' : 'none';
};
