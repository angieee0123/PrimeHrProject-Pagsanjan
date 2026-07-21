function adjustDeptDistribution() {
    const container = document.getElementById('deptDistContainer');
    if (!container) return;

    const containerHeight = container.parentElement.offsetHeight - 70;
    const itemHeight = 46;
    const visibleRows = Math.max(3, Math.floor(containerHeight / itemHeight));

    const items = container.querySelectorAll('[style*="height:46px"]');
    items.forEach((item, idx) => {
        item.style.display = idx < visibleRows ? 'flex' : 'none';
    });
}

window.addEventListener('load', adjustDeptDistribution);
window.addEventListener('resize', adjustDeptDistribution);
