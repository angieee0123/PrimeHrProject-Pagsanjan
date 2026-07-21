window.switchBirdsTab = function (tab) {
    const isEarly = tab === 'early';
    document.getElementById('panelEarly').style.display = isEarly ? 'grid' : 'none';
    document.getElementById('panelLate').style.display = isEarly ? 'none' : 'grid';
    document.getElementById('birdsTabTitle').textContent = isEarly ? 'Earliest Time-ins Today' : 'Late Arrivals Today';
    document.getElementById('tabEarly').classList.toggle('active', isEarly);
    document.getElementById('tabLate').classList.toggle('active', !isEarly);
};
