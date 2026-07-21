const mayorDashboardData = window.mayorDashboardData;
const perfPeriodMonth = mayorDashboardData.perfPeriodMonth;
const payrollAnchorLabel = mayorDashboardData.payrollAnchorLabel;
const deptData = mayorDashboardData.departments;
const attendanceToday = mayorDashboardData.attendanceToday;
const payrollTrend = mayorDashboardData.payrollTrend;
const leaveBreakdown = mayorDashboardData.leaveBreakdown;

window.switchHighlights = function (tab) {
    const panels = { performers: 'panelHlPerformers', earners: 'panelHlEarners', leave: 'panelHlLeave' };
    const titles = { performers: 'Top Attendance Performers', earners: 'Top 5 Highest Earners', leave: 'Recent Leave Activity' };
    Object.keys(panels).forEach(key => {
        document.getElementById(panels[key]).style.display = key === tab ? 'block' : 'none';
    });
    document.getElementById('tabHlPerformers').classList.toggle('active', tab === 'performers');
    document.getElementById('tabHlEarners').classList.toggle('active', tab === 'earners');
    document.getElementById('tabHlLeave').classList.toggle('active', tab === 'leave');
    document.getElementById('highlightsTitle').textContent = titles[tab];
    document.getElementById('highlightsSub').textContent = tab === 'performers' ? perfPeriodMonth : (tab === 'earners' ? payrollAnchorLabel : 'Latest 5 applications');
};

function donutOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#fff', titleColor: '#0b044d', bodyColor: '#56547a', borderColor: '#ecebf6', borderWidth: 1.5, padding: 10 } }
    };
}

window.addEventListener('load', () => {
    if (deptData.length) {
        new Chart(document.getElementById('deptDonut').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: deptData.map(d => d.name),
                datasets: [{ data: deptData.map(d => d.count), backgroundColor: deptData.map(d => d.color), borderWidth: 2, borderColor: '#fff' }]
            },
            options: donutOptions()
        });
    }

    new Chart(document.getElementById('attendanceDonut').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['On Time', 'Late', 'Absent'],
            datasets: [{ data: [attendanceToday.on_time, attendanceToday.late, attendanceToday.absent], backgroundColor: ['#15803d', '#c9a227', '#8e1e18'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: donutOptions()
    });

    new Chart(document.getElementById('payrollBar').getContext('2d'), {
        type: 'bar',
        data: {
            labels: payrollTrend.labels,
            datasets: [{ data: payrollTrend.data, backgroundColor: '#0b044d', borderRadius: 6, maxBarThickness: 28 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#fff', titleColor: '#0b044d', bodyColor: '#56547a', borderColor: '#ecebf6', borderWidth: 1.5, padding: 10,
                    callbacks: { label: (ctx) => '₱' + Number(ctx.raw).toLocaleString() }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f7f6fc', drawBorder: false },
                    ticks: {
                        color: '#8f8daf', font: { size: 10, family: 'Poppins' },
                        callback: function(value) {
                            if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return '₱' + (value / 1000).toFixed(0) + 'K';
                            return '₱' + value;
                        }
                    }
                },
                x: { grid: { display: false, drawBorder: false }, ticks: { color: '#8f8daf', font: { size: 10, family: 'Poppins' } } }
            }
        }
    });

    const leaveTotal = leaveBreakdown.approved + leaveBreakdown.pending + leaveBreakdown.rejected;
    if (leaveTotal > 0) {
        new Chart(document.getElementById('leaveDonut').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Pending', 'Rejected'],
                datasets: [{ data: [leaveBreakdown.approved, leaveBreakdown.pending, leaveBreakdown.rejected], backgroundColor: ['#15803d', '#c9a227', '#8e1e18'], borderWidth: 2, borderColor: '#fff' }]
            },
            options: donutOptions()
        });
    }
});
