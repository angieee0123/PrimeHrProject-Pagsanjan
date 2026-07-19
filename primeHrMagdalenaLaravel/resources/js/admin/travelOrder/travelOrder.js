// Admin Travel Order Dashboard — tab switching

window.switchTab = function(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.table-section').forEach(section => section.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').style.display = 'block';
}

window.navigateToPage = function(url) {
    window.location.href = url;
}

document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    if (activeTab === 'approved') {
        switchTab('approved');
    } else if (activeTab === 'disapproved') {
        switchTab('disapproved');
    } else {
        document.getElementById('pending-tab').style.display = 'block';
    }
});
