@extends('layouts.app')

@section('content')

@php
    $adminName = optional(Auth::user())->name ?? 'Admin';
    $adminInitials = collect(explode(' ', trim($adminName)))->filter()->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') ?: 'AD';
@endphp

<main class="enterprise-hr-dashboard glass-shell">

@include('admin.topbar.adminTopbar')
@include('admin.notification.adminNotification')

@include('admin.dashboard.partials.stats-grid')

@include('admin.dashboard.partials.quick-actions-bar')

{{-- Main reference layout: wide chart + right requests --}}
<div class="enterprise-overview-grid">
    @include('admin.dashboard.partials.main-chart-card')
    @include('admin.dashboard.partials.pending-requests-card')
</div>

<div class="enterprise-secondary-grid">
    @include('admin.dashboard.partials.top-performers-card')
    @include('admin.dashboard.partials.attendance-trend-chart-card')
    @include('admin.dashboard.partials.top-earners-card')
</div>

<div class="enterprise-compact-grid">
    @include('admin.dashboard.partials.early-birds-card')
    @include('admin.dashboard.partials.recent-leave-filers-card')
    @include('admin.dashboard.partials.department-distribution-card')
</div>

@include('admin.dashboard.partials.employee-directory')

</main>

@include('admin.dashboard.modals.performerDetailsModal')
@include('admin.dashboard.modals.viewEmployeeDashboardModal')
@include('admin.dashboard.modals.addEmployeeModal')

@include('admin.dashboard.partials.bird-schedule-tooltip')

@push('scripts')
<script>
function switchPendingRequestsTab(tab) {
    const isLeave = tab === 'leave';
    document.getElementById('pendingLeaveTabPanel').style.display = isLeave ? 'block' : 'none';
    document.getElementById('pendingPassSlipTabPanel').style.display = isLeave ? 'none' : 'block';
    document.getElementById('pendingTabLeaveBtn').classList.toggle('active', isLeave);
    document.getElementById('pendingTabPassSlipBtn').classList.toggle('active', !isLeave);
    document.getElementById('pendingRequestsViewAllBtn').onclick = () => window.location.href = isLeave ? '/admin/leave' : '/admin/passslip';
    document.querySelectorAll('.leave-action-menu').forEach(m => m.style.display = 'none');
}

function togglePassSlipMenuDash(e) {
    e.stopPropagation();
    const menu = e.target.closest('button').nextElementSibling;
    const allMenus = document.querySelectorAll('.leave-action-menu');
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function toggleLeaveMenu(e) {
    e.stopPropagation();
    const menu = e.target.closest('button').nextElementSibling;
    const allMenus = document.querySelectorAll('.leave-action-menu');
    allMenus.forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function approveLeave(e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been approved!');
    listItem.querySelector('.leave-action-menu').style.display = 'none';
}

function disapproveLeave(e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    alert('Leave request for ' + name + ' has been disapproved.');
    listItem.querySelector('.leave-action-menu').style.display = 'none';
}

function viewLeaveDetails(e) {
    e.stopPropagation();
    const listItem = e.target.closest('.enterprise-list-item');
    const name = listItem.querySelector('.enterprise-person strong').textContent;
    const details = listItem.querySelector('.enterprise-person span').textContent;
    alert('Leave details for ' + name + ':\n' + details);
    listItem.querySelector('.leave-action-menu').style.display = 'none';
}

document.addEventListener('click', function() {
    document.querySelectorAll('.leave-action-menu').forEach(m => m.style.display = 'none');
});

const perfPeriods = { month: '{{ $perfPeriodMonth }}', week: '{{ $perfPeriodWeek }}' };

function switchPerfTab(tab) {
    const isMonth = tab === 'month';
    document.getElementById('perfPanelMonth').style.display = isMonth ? 'flex' : 'none';
    document.getElementById('perfPanelWeek').style.display  = isMonth ? 'none'  : 'flex';
    document.getElementById('perfTabMonth').classList.toggle('active', isMonth);
    document.getElementById('perfTabWeek').classList.toggle('active', !isMonth);
    document.getElementById('perfPeriodSub').textContent = perfPeriods[tab];
}

// Initialize with week view by default
document.addEventListener('DOMContentLoaded', function() {
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


function switchBirdsTab(tab) {
    const isEarly = tab === 'early';
    document.getElementById('panelEarly').style.display = isEarly ? 'grid' : 'none';
    document.getElementById('panelLate').style.display = isEarly ? 'none' : 'grid';
    document.getElementById('birdsTabTitle').textContent = isEarly ? 'Earliest Time-ins Today' : 'Late Arrivals Today';
    document.getElementById('tabEarly').classList.toggle('active', isEarly);
    document.getElementById('tabLate').classList.toggle('active', !isEarly);
}

const employeeData = @json($chartData['employees']);
const salaryData = @json($chartData['salaryTrends']);
const attendanceData = @json($chartData['attendance']);

let currentChartType = 'salary';
let currentPeriod = 'week';
let dynamicChart;
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

    const colors = ['#1e40af', '#6d28d9', '#9d174d', '#c9a227', '#065f46'];

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
                        callback: function(value) {
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
                        callback: function(value, index) {
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

function switchMainChart(type) {
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
}

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
        dynamicChart.options.scales.y.ticks.callback = function(value) {
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
        dynamicChart.options.scales.y.ticks.callback = function(value) {
            if (value >= 1000000) return '₱' + (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return '₱' + (value / 1000).toFixed(1) + 'K';
            return '₱' + value.toLocaleString();
        };
    }

    dynamicChart.update();
}

function switchAttendanceChart(period) {
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
}

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

window.addEventListener('load', () => {
    initCharts();
    adjustDeptDistribution();
});
window.addEventListener('resize', adjustDeptDistribution);

function viewEmployeeDashboard(employeeId) {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'flex';
    document.getElementById('viewEmployeeDashboardContent').innerHTML = '<p style="text-align:center; color:#56547a;">Loading...</p>';

    fetch(`/admin/personnel/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewEmployeeDashboardId').textContent = data.employee_id;
            document.getElementById('viewEmployeeDashboardContent').innerHTML = generateEmployeeViewDashboard(data);
        })
        .catch(error => {
            document.getElementById('viewEmployeeDashboardContent').innerHTML = '<p style="text-align:center; color:#8e1e18;">Error loading employee details.</p>';
        });
}

function closeViewDashboardModal() {
    document.getElementById('viewEmployeeDashboardModal').style.display = 'none';
}

function generateEmployeeViewDashboard(data) {
    return `
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">👤 Personal Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Full Name</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.first_name} ${data.middle_name || ''} ${data.last_name} ${data.suffix || ''}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Date of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.birth_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Place of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.place_of_birth || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Sex</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.sex || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Civil Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.civil_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Citizenship</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.citizenship || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Blood Type</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.blood_type || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">💼 Employment Details</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Designation</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.designation_relation?.title || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Department</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.department_relation?.name || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Employment Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.employment_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Appointment Date</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.appointment_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Salary Grade</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.salary_grade || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Step Increment</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.step_increment || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">📞 Contact Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Email</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.email || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Mobile Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.mobile_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Landline</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.landline_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Emergency Contact</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_person || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Emergency Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_number || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">🪪 Government IDs</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">GSIS Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.gsis_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">PhilHealth Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.philhealth_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">PAG-IBIG Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.pagibig_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">TIN Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.tin_no || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">License Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.license_no || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f2f1fb;">📍 Address</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">House No.</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.house_no || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Street</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.street || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Barangay</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.barangay || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">City/Municipality</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.city || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Province</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.province || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#8f8daf; display:block; margin-bottom:4px;">Zip Code</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.zip_code || 'N/A'}</span></div>
            </div>
        </div>
    `;
}

function applyFilters() {
    const dept = document.getElementById('filterDept').value;
    const type = document.getElementById('filterType').value;
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const matchDept = !dept || row.dataset.dept === dept;
        const matchType = !type || row.dataset.type === type;
        const show = matchDept && matchType;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees';
}

function searchEmployees(query) {
    const searchTerm = query.toLowerCase();
    const rows = document.querySelectorAll('.payroll-table tbody tr[data-dept]');
    let visible = 0;
    rows.forEach(row => {
        const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const position = row.querySelector('.position-cell')?.textContent.toLowerCase() || '';
        const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
        const show = name.includes(searchTerm) || id.includes(searchTerm) || position.includes(searchTerm) || dept.includes(searchTerm);
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const total = rows.length;
    document.getElementById('filterCount').innerHTML =
        visible === total
            ? 'Showing <strong>1–' + total + '</strong> of <strong>' + total + '</strong> employees'
            : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> employees (filtered)';
}

function openAddEmployee() {
    document.getElementById('addEmployeeModal').classList.add('show');
}

function closeAddEmployee() {
    document.getElementById('addEmployeeModal').classList.remove('show');
    document.getElementById('addEmployeeForm').reset();
}

function submitAddEmployee(e) {
    e.preventDefault();
    const form = document.getElementById('addEmployeeForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    const data = Object.fromEntries(new FormData(form));
    alert('Employee added successfully!\\n\\n' + data.first_name + ' ' + data.last_name + ' (' + data.emp_type + ')\\n' + data.position + ' · ' + data.department);
    closeAddEmployee();
}

function viewEmployee(emp) {
    document.getElementById('viewEmpName').textContent = emp.name;
    document.getElementById('viewEmpId').textContent = emp.id;
    document.getElementById('viewPosition').textContent = emp.position;
    document.getElementById('viewDept').textContent = emp.dept;
    document.getElementById('viewType').textContent = emp.type;
    document.getElementById('viewStatus').textContent = emp.status.charAt(0).toUpperCase() + emp.status.slice(1);
    document.getElementById('viewEmployeeModal').classList.add('show');
}

function closeViewEmployee() {
    document.getElementById('viewEmployeeModal').classList.remove('show');
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeAddEmployee();
        closeViewEmployee();
    }
});

// Apply dynamic colors (avoid Blade-in-style lint issues)
document.querySelectorAll('.emp-avatar-dynamic, .event-icon-dynamic, .dept-fill').forEach(el => {
    const bg = el.dataset.bg;
    if (bg) el.style.backgroundColor = bg;
    const w = el.dataset.w;
    if (w) el.style.width = w;
});

function showPerformerDetails(emp, period, rank) {
    const modal = document.getElementById('performerDetailsModal');
    const rankEmojis = ['🥇', '🥈', '🥉', '4', '5'];
    const periodLabels = {
        'month': 'Previous Month · ' + perfPeriods.month,
        'week':  'Previous Week · '  + perfPeriods.week
    };

    document.getElementById('modalPerformerName').textContent = emp.name;
    document.getElementById('modalPerformerRank').textContent = rankEmojis[rank - 1] || rank;
    document.getElementById('modalPerformerPosition').textContent = emp.position;
    document.getElementById('modalPerformerDept').textContent = emp.department;
    document.getElementById('modalPeriodLabel').textContent = periodLabels[period];
    document.getElementById('modalAttendanceRate').textContent = emp.rate + '%';
    document.getElementById('modalPresentDays').textContent = emp.present_days;
    document.getElementById('modalAbsentDays').textContent = emp.absent_days;
    document.getElementById('modalLateDays').textContent = emp.late_days;
    document.getElementById('modalWorkingDays').textContent = emp.working_days;
    document.getElementById('modalPresentDays2').textContent = emp.present_days;
    document.getElementById('modalAbsentDays2').textContent = emp.absent_days;
    document.getElementById('modalLateDays2').textContent = emp.late_days;
    document.getElementById('modalRate2').textContent = emp.rate + '%';

    const tierEl = document.getElementById('modalTier');
    const tierLabels = {
        'excellent': 'Excellent',
        'good': 'Good',
        'needs_improvement': 'Needs Improvement',
        'poor': 'Poor'
    };
    const tierColors = {
        'excellent': 'background:#e8f9ef;color:#15803d',
        'good': 'background:#e8f9ef;color:#15803d',
        'needs_improvement': 'background:#fbf6e3;color:#c9a227',
        'poor': 'background:#fde8e8;color:#8e1e18'
    };
    tierEl.textContent = tierLabels[emp.tier] || emp.tier;
    tierEl.style.cssText = 'font-size:12px;padding:4px 10px;border-radius:999px;font-weight:700;' + tierColors[emp.tier];

    const avatar = document.getElementById('modalPerformerAvatar');
    if (emp.photo) {
        avatar.innerHTML = '<img src="' + emp.photo + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover">';
    } else {
        avatar.innerHTML = '<span style="color:#fff;font-weight:700;font-size:24px">' + emp.initials + '</span>';
        avatar.style.backgroundColor = emp.color;
    }

    let reason = '<ul style="margin:0;padding-left:20px">';

    if (emp.rate >= 95) {
        reason += '<li style="margin-bottom:8px"><strong>Outstanding attendance rate of ' + emp.rate + '%</strong> - Near perfect attendance record!</li>';
    } else if (emp.rate >= 80) {
        reason += '<li style="margin-bottom:8px"><strong>Excellent attendance rate of ' + emp.rate + '%</strong> - Consistently present at work.</li>';
    }

    if (emp.absent_days === 0) {
        reason += '<li style="margin-bottom:8px"><strong>Zero absences</strong> during the evaluation period.</li>';
    } else if (emp.absent_days <= 2) {
        reason += '<li style="margin-bottom:8px">Only <strong>' + emp.absent_days + ' day(s) absent</strong> - Minimal absenteeism.</li>';
    }

    if (emp.late_days === 0) {
        reason += '<li style="margin-bottom:8px"><strong>Always on time</strong> - Zero late arrivals recorded.</li>';
    } else if (emp.late_days <= 2) {
        reason += '<li style="margin-bottom:8px">Punctual with only <strong>' + emp.late_days + ' late instance(s)</strong>.</li>';
    } else {
        reason += '<li style="margin-bottom:8px">Recorded <strong>' + emp.late_days + ' late arrivals</strong> but maintained excellent overall attendance.</li>';
    }

    reason += '<li style="margin-bottom:8px">Present for <strong>' + emp.present_days + ' out of ' + emp.working_days + ' working days</strong> in this period.</li>';

    if (rank === 1) {
        reason += '<li><strong>🏆 #1 Top Performer</strong> - Leading by example with exceptional dedication!</li>';
    } else if (rank === 2) {
        reason += '<li><strong>🥈 Silver Medal</strong> - Outstanding performance and reliability!</li>';
    } else if (rank === 3) {
        reason += '<li><strong>🥉 Bronze Medal</strong> - Excellent work ethic and consistency!</li>';
    } else {
        reason += '<li>Among the <strong>Top 5 Performers</strong> - Recognized for excellent attendance!</li>';
    }

    reason += '</ul>';
    document.getElementById('modalReason').innerHTML = reason;

    modal.style.display = 'flex';
}

function closePerformerModal() {
    document.getElementById('performerDetailsModal').style.display = 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closePerformerModal();
    }
});
</script>
@endpush

@endsection
