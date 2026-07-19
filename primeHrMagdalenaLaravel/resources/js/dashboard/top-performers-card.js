const perfPeriods = window.perfPeriods;

window.switchPerfTab = function (tab) {
    const isMonth = tab === 'month';
    document.getElementById('perfPanelMonth').style.display = isMonth ? 'flex' : 'none';
    document.getElementById('perfPanelWeek').style.display = isMonth ? 'none' : 'flex';
    document.getElementById('perfTabMonth').classList.toggle('active', isMonth);
    document.getElementById('perfTabWeek').classList.toggle('active', !isMonth);
    document.getElementById('perfPeriodSub').textContent = perfPeriods[tab];
};
