// Admin Dashboard Enhanced JavaScript
let employeeChartInstance = null;
let attendanceChartInstance = null;

// Search employees
function searchEmployees(query) {
    const rows = document.querySelectorAll('.payroll-table tbody tr');
    const lowerQuery = query.toLowerCase();
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        
        const nameCell = row.querySelector('.emp-name');
        const idCell = row.querySelector('.emp-id');
        const positionCell = row.querySelector('.position-cell');
        
        if (nameCell && (
            nameCell.textContent.toLowerCase().includes(lowerQuery) ||
            (idCell && idCell.textContent.toLowerCase().includes(lowerQuery)) ||
            (positionCell && positionCell.textContent.toLowerCase().includes(lowerQuery))
        )) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
}

// Apply filters
function applyFilters() {
    const dept = document.getElementById('filterDept').value;
    const type = document.getElementById('filterType').value;
    const rows = document.querySelectorAll('.payroll-table tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (row.querySelector('td[colspan]')) return;
        
        const rowDept = row.getAttribute('data-dept');
        const rowType = row.getAttribute('data-type');
        
        const deptMatch = !dept || rowDept === dept;
        const typeMatch = !type || rowType === type;
        
        if (deptMatch && typeMatch) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    const countElement = document.getElementById('filterCount');
    if (countElement) {
        countElement.innerHTML = `Showing <strong>${visibleCount}</strong> employees`;
    }
}

// Switch birds tab
function switchBirdsTab(tab) {
    const tabEarly = document.getElementById('tabEarly');
    const tabLate = document.getElementById('tabLate');
    const panelEarly = document.getElementById('panelEarly');
    const panelLate = document.getElementById('panelLate');
    const title = document.getElementById('birdsTabTitle');
    
    if (tab === 'early') {
        tabEarly.style.background = '#0b044d';
        tabEarly.style.color = '#fff';
        tabLate.style.background = '#fff';
        tabLate.style.color = '#5a5888';
        panelEarly.style.display = 'grid';
        panelLate.style.display = 'none';
        title.textContent = '🌅 Top 10 Early Birds Today';
    } else {
        tabLate.style.background = '#0b044d';
        tabLate.style.color = '#fff';
        tabEarly.style.background = '#fff';
        tabEarly.style.color = '#5a5888';
        panelEarly.style.display = 'none';
        panelLate.style.display = 'grid';
        title.textContent = '🐦 Top 10 Late Birds Today';
    }
}

// Switch performance tab
function switchPerfTab(period) {
    const monthBtn = document.getElementById('perfTabMonth');
    const weekBtn = document.getElementById('perfTabWeek');
    const monthPanel = document.getElementById('perfPanelMonth');
    const weekPanel = document.getElementById('perfPanelWeek');
    
    const bottomMonth = document.querySelector('.perf-bottom-month');
    const bottomWeek = document.querySelector('.perf-bottom-week');
    
    if (period === 'month') {
        monthBtn.style.background = '#0b044d';
        monthBtn.style.color = '#fff';
        weekBtn.style.background = '#fff';
        weekBtn.style.color = '#5a5888';
        monthPanel.style.display = 'block';
        weekPanel.style.display = 'none';
        if (bottomMonth) bottomMonth.style.display = 'block';
        if (bottomWeek) bottomWeek.style.display = 'none';
    } else {
        weekBtn.style.background = '#0b044d';
        weekBtn.style.color = '#fff';
        monthBtn.style.background = '#fff';
        monthBtn.style.color = '#5a5888';
        monthPanel.style.display = 'none';
        weekPanel.style.display = 'block';
        if (bottomMonth) bottomMonth.style.display = 'none';
        if (bottomWeek) bottomWeek.style.display = 'block';
    }
}

// Switch employee chart
function switchEmployeeChart(period) {
    const buttons = document.querySelectorAll('.chart-tabs .chart-tab');
    buttons.forEach(btn => {
        if (btn.textContent.toLowerCase() === period.toLowerCase()) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    if (window.chartData && window.chartData.employees && window.chartData.employees[period]) {
        updateChart('employeeChart', window.chartData.employees[period]);
    }
}

// Switch attendance chart
function switchAttendanceChart(period) {
    const buttons = document.querySelectorAll('.chart-tabs .chart-tab');
    buttons.forEach(btn => {
        if (btn.textContent.toLowerCase() === period.toLowerCase()) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    if (window.chartData && window.chartData.attendance && window.chartData.attendance[period]) {
        updateChart('attendanceChart', window.chartData.attendance[period]);
    }
}

// Update chart
function updateChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;
    
    const isEmployeeChart = canvasId === 'employeeChart';
    
    if (isEmployeeChart && employeeChartInstance) {
        employeeChartInstance.destroy();
    } else if (!isEmployeeChart && attendanceChartInstance) {
        attendanceChartInstance.destroy();
    }
    
    const config = {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: isEmployeeChart ? 'Total Employees' : 'Attendance Rate (%)',
                data: data.data,
                borderColor: isEmployeeChart ? '#0b044d' : '#15803d',
                backgroundColor: isEmployeeChart ? 'rgba(11,4,77,0.1)' : 'rgba(21,128,61,0.1)',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0b044d',
                    padding: 12,
                    cornerRadius: 8,
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 13, weight: '700' },
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f7f6ff' },
                    ticks: { color: '#9999bb', font: { size: 11 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: '#9999bb', font: { size: 11 } }
                }
            }
        }
    };
    
    if (isEmployeeChart) {
        employeeChartInstance = new Chart(ctx, config);
    } else {
        attendanceChartInstance = new Chart(ctx, config);
    }
}

// View employee dashboard
function viewEmployeeDashboard(id) {
    window.location.href = `/admin/employees/${id}`;
}

// Open add employee modal
function openAddEmployee() {
    document.getElementById('addEmployeeModal').style.display = 'flex';
}

// Close add employee modal
function closeAddEmployee() {
    document.getElementById('addEmployeeModal').style.display = 'none';
}

// Submit add employee form
function submitAddEmployee(event) {
    event.preventDefault();
    console.log('Add employee form submitted');
    alert('Employee registration feature coming soon!');
}

// View dashboard modal
function closeViewDashboardModal() {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'none';
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set dynamic colors
    document.querySelectorAll('.emp-avatar-dynamic').forEach(el => {
        const bg = el.getAttribute('data-bg');
        if (bg) el.style.background = bg;
    });
    
    document.querySelectorAll('.dept-fill').forEach(el => {
        const w = el.getAttribute('data-w');
        const bg = el.getAttribute('data-bg');
        if (w) el.style.width = w;
        if (bg) el.style.background = bg;
    });
    
    document.querySelectorAll('.event-icon-dynamic').forEach(el => {
        const bg = el.getAttribute('data-bg');
        if (bg) el.style.background = bg;
    });
    
    // Initialize charts
    if (window.chartData) {
        updateChart('employeeChart', window.chartData.employees.month);
        updateChart('attendanceChart', window.chartData.attendance.month);
    }
});
