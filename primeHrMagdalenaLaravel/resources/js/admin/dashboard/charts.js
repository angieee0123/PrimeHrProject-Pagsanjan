// Drives both the main chart card (#dynamicChart) and the attendance trend
// chart card (#attendanceChart) — the two share initCharts() and are wired
// together in the dashboard's original markup, so they stay in one module.

const employeeData = window.dashboardChartData.employees;
const salaryData = window.dashboardChartData.salaryTrends;
const attendanceData = window.dashboardChartData.attendance;

let currentChartType = 'salary';
let currentPeriod = 'week';
let dynamicChart;
let attendanceChart;
let gradientPayroll;

function initCharts() {
    const ctx1 = document.getElementById('dynamicChart').getContext('2d');
    const ctx2 = document.getElementById('attendanceChart').getContext('2d');

    gradientPayroll = ctx1.createLinearGradient(0, 0, 0, 300);
    gradientPayroll.addColorStop(0, 'rgba(30, 64, 175, 0.25)');
    gradientPayroll.addColorStop(1, 'rgba(30, 64, 175, 0.01)');

    // Create gradient for Attendance Chart
    const gradientAtt = ctx2.createLinearGradient(0, 0, 0, 400);
    gradientAtt.addColorStop(0, 'rgba(30, 64, 175, 0.3)');
    gradientAtt.addColorStop(1, 'rgba(30, 64, 175, 0.01)');

    // Initialize with payroll by designation (week view)
    dynamicChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: salaryData.week.labels,
            datasets: salaryData.week.datasets.map((ds, index) => ({
                label: ds.label,
                data: ds.data,
                borderColor: ds.color,
                backgroundColor: index === 0 ? gradientPayroll : ds.color + '20',
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: ds.color,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }))
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 12,
                        font: { size: 11, family: 'Poppins', weight: '600' },
                        color: '#64748b',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0b044d',
                    bodyColor: '#5a5888',
                    borderColor: '#eceaf8',
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f7f6fc', drawBorder: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        callback: function (value) {
                            if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return '₱' + (value / 1000).toFixed(1) + 'K';
                            return '₱' + value.toLocaleString();
                        }
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        callback: function (value, index) {
                            const labels = this.getLabelForValue(value);
                            return labels;
                        }
                    }
                }
            }
        }
    });

    attendanceChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: attendanceData.week.labels,
            datasets: [
                {
                    label: 'Attendance Rate (%)',
                    data: attendanceData.week.data,
                    borderColor: '#1e40af',
                    backgroundColor: gradientAtt,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#1e40af',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 3
                },
                {
                    label: 'Late Arrivals (%)',
                    data: attendanceData.week.lateData,
                    borderColor: '#8e1e18',
                    backgroundColor: 'rgba(142, 30, 24, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#8e1e18',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 2
                },
                {
                    label: 'Absent (%)',
                    data: attendanceData.week.absentData,
                    borderColor: '#6d28d9',
                    backgroundColor: 'rgba(109, 40, 217, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: '#6d28d9',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: {
                        boxWidth: 12,
                        boxHeight: 12,
                        padding: 12,
                        font: { size: 11, family: 'Poppins', weight: '600' },
                        color: '#64748b',
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: '#0b044d',
                    bodyColor: '#5a5888',
                    borderColor: '#eceaf8',
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 120,
                    grid: { color: '#f7f6fc', drawBorder: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        padding: 8
                    }
                },
                x: {
                    offset: false,
                    grid: { display: false, drawBorder: false, offset: false },
                    ticks: {
                        color: '#8f8daf',
                        font: { size: 11, family: 'Poppins' },
                        padding: 2,
                        autoSkip: true,
                        maxRotation: 0,
                        minRotation: 0
                    }
                }
            }
        }
    });
}

window.switchMainChart = function (type) {
    currentChartType = type;
    document.getElementById('tabEmployees').classList.toggle('active', type === 'employees');
    document.getElementById('tabSalary').classList.toggle('active', type === 'salary');

    if (type === 'employees') {
        document.getElementById('dynamicChartTitle').textContent = 'Employee Growth';
        document.getElementById('dynamicChartSub').textContent = 'Total employees over time';
        // Keep current period for employees
        switchPeriodChart(currentPeriod);
    } else {
        document.getElementById('dynamicChartTitle').textContent = 'Payroll by Designation';
        document.getElementById('dynamicChartSub').textContent = 'Total payroll amounts per designation';
        // Default to week for payroll
        switchPeriodChart('week');
    }
};

function switchPeriodChart(period) {
    currentPeriod = period;
    const chartCard = document.getElementById('dynamicChart').closest('.chart-card');
    chartCard.querySelectorAll('#periodTabs .chart-tab').forEach(t => {
        t.classList.remove('active');
        if (t.textContent.toLowerCase() === period) {
            t.classList.add('active');
        }
    });

    if (currentChartType === 'employees') {
        dynamicChart.config.type = 'line';
        dynamicChart.data.labels = employeeData[period].labels;
        dynamicChart.data.datasets = [{
            label: 'Total Employees',
            data: employeeData[period].data,
            borderColor: '#0b044d',
            backgroundColor: 'rgba(11, 4, 77, 0.1)',
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#0b044d',
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }];
        dynamicChart.options.plugins.legend.display = false;
        dynamicChart.options.scales.y.ticks.callback = function (value) {
            return value;
        };
    } else {
        // Payroll by designation
        dynamicChart.config.type = 'line';
        dynamicChart.data.labels = salaryData[period].labels;
        dynamicChart.data.datasets = salaryData[period].datasets.map((ds, index) => ({
            label: ds.label,
            data: ds.data,
            borderColor: ds.color,
            backgroundColor: index === 0 ? gradientPayroll : ds.color + '20',
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: ds.color,
            pointBorderColor: '#fff',
            pointBorderWidth: 2
        }));
        if (dynamicChart.data.datasets.length > 0) {
            dynamicChart.data.datasets[0].backgroundColor = gradientPayroll;
        }
        dynamicChart.options.plugins.legend.display = true;
        dynamicChart.options.plugins.legend.position = 'top';
        dynamicChart.options.plugins.legend.align = 'end';
        dynamicChart.options.plugins.legend.labels = {
            boxWidth: 12,
            boxHeight: 12,
            padding: 12,
            font: { size: 11, family: 'Poppins', weight: '600' },
            color: '#64748b',
            usePointStyle: true,
            pointStyle: 'circle'
        };
        dynamicChart.options.scales.y.ticks.callback = function (value) {
            if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return '₱' + (value / 1000).toFixed(1) + 'K';
            return '₱' + value.toLocaleString();
        };
    }

    dynamicChart.update();
}
window.switchPeriodChart = switchPeriodChart;

window.switchAttendanceChart = function (period) {
    const chartCard = document.getElementById('attendanceChart').closest('.chart-card');
    const buttons = chartCard.querySelectorAll('.chart-tab');
    buttons.forEach(t => t.classList.remove('active'));

    // Find and activate the correct button
    buttons.forEach((btn, idx) => {
        if ((period === 'week' && idx === 0) || (period === 'month' && idx === 1) || (period === 'year' && idx === 2)) {
            btn.classList.add('active');
        }
    });

    attendanceChart.data.labels = attendanceData[period].labels;
    attendanceChart.data.datasets[0].data = attendanceData[period].data;
    attendanceChart.data.datasets[1].data = attendanceData[period].lateData;
    attendanceChart.data.datasets[2].data = attendanceData[period].absentData;
    attendanceChart.update();
};

// Initialize with week view by default
document.addEventListener('DOMContentLoaded', function () {
    switchPeriodChart('week');
    // Set week as active for attendance chart
    const attendanceChartCard = document.getElementById('attendanceChart').closest('.chart-card');
    attendanceChartCard.querySelectorAll('.chart-tab').forEach((t, idx) => {
        t.classList.toggle('active', idx === 0);
    });
    attendanceChart.data.labels = attendanceData['week'].labels;
    attendanceChart.data.datasets[0].data = attendanceData['week'].data;
    attendanceChart.data.datasets[1].data = attendanceData['week'].lateData;
    attendanceChart.update();
});

window.addEventListener('load', () => {
    initCharts();
});
