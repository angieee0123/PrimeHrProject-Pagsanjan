function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.filter-group').forEach(g => g.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').style.display = 'block';
    const group = document.getElementById(tabName + '-filter-group');
    if (group) group.style.display = 'contents';
}

window.switchTab = switchTab;
