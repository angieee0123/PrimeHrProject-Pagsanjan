import { themeColor, themeRgba, chartChrome } from '../shared/themeColors.js';

// Chart.js takes colour values, not CSS declarations, so the chart
// chrome is resolved from the active theme rather than written as a
// literal that would stay navy under every other palette.
const chrome = chartChrome();

/* ══════════ EMPLOYEE DASHBOARD ══════════
   Server data arrives on window.employeeDashboardData, set by an inline script
   in employeeDashboard.blade.php. This file is an ES module, so anything the
   markup calls from an inline onclick must be hung on window explicitly. */

const { deductions: deductionsData = [], attendance: attendanceData, salary: salaryData } =
    window.employeeDashboardData ?? {};

let attendanceChart, salaryChart;
let currentDeductionView = 'monthly';

const peso = n => '₱' + n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

// Laravel serializes decimal casts as strings, so coerce before doing math.
const num = v => parseFloat(v) || 0;

/* The amount a deduction takes per cutoff (twice a month). Mirrors the same
   resolution the Blade partial does, so table, modal and CSV always agree. */
function perCutoffOf(d) {
    const type = d.deduction_type;
    let perCutoff = num(d.calculated_amount ?? d.installment_amount ?? d.amount);
    if (perCutoff === 0 && type && String(type.computation_type).toUpperCase() === 'FIXED') {
        perCutoff = num(type.percentage_rate) / 2;
    }
    return perCutoff;
}

function initCharts() {
    const canvasAtt = document.getElementById('attendanceChart');
    const canvasSalary = document.getElementById('salaryChart');
    if (!canvasAtt || !canvasSalary) return;

    const ctx1 = canvasAtt.getContext('2d');
    const ctx2 = canvasSalary.getContext('2d');

    const gradientAtt = ctx1.createLinearGradient(0, 0, 0, 400);
    gradientAtt.addColorStop(0, themeRgba('--theme-accent', 0.3));
    gradientAtt.addColorStop(1, themeRgba('--theme-accent', 0.01));

    const gradientSalary = ctx2.createLinearGradient(0, 0, 0, 300);
    gradientSalary.addColorStop(0, themeRgba('--theme-primary', 0.25));
    gradientSalary.addColorStop(1, themeRgba('--theme-primary', 0.01));

    attendanceChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: attendanceData.month.labels,
            datasets: [
                {
                    label: 'Attendance Rate (%)',
                    data: attendanceData.month.data,
                    borderColor: themeColor('--theme-accent', '#3121ca'),
                    backgroundColor: gradientAtt,
                    borderWidth: 2.5,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: themeColor('--theme-accent', '#3121ca'),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 3
                },
                {
                    label: 'Late Arrivals (%)',
                    data: attendanceData.month.lateData,
                    borderColor: themeColor('--theme-danger', '#c33228'),
                    backgroundColor: 'rgba(142, 30, 24, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: themeColor('--theme-danger', '#c33228'),
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    order: 2
                },
                {
                    label: 'Absent (%)',
                    data: attendanceData.month.absentData,
                    borderColor: themeColor('--theme-warning', '#916e18'),
                    backgroundColor: 'rgba(109, 40, 217, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                    pointBackgroundColor: themeColor('--theme-warning', '#916e18'),
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
                        color: chrome.tick,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: chrome.ink,
                    bodyColor: '#5a5888',
                    borderColor: chrome.border,
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: true
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 120,
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: { color: 'var(--gp-text-soft)', font: { size: 11, family: 'Poppins' }, padding: 8 }
                },
                x: {
                    offset: false,
                    grid: { display: false, drawBorder: false, offset: false },
                    ticks: { color: 'var(--gp-text-soft)', font: { size: 11, family: 'Poppins' }, padding: 2, autoSkip: true, maxRotation: 0, minRotation: 0 }
                }
            }
        }
    });

    salaryChart = new Chart(ctx2, {
        type: 'line',
        data: {
            labels: salaryData.month.labels,
            datasets: [{
                label: 'Net Pay (₱)',
                data: salaryData.month.data,
                borderColor: chrome.ink,
                backgroundColor: gradientSalary,
                borderWidth: 2.5,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointBackgroundColor: chrome.ink,
                pointBorderColor: '#fff',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#fff',
                    titleColor: chrome.ink,
                    bodyColor: '#5a5888',
                    borderColor: chrome.border,
                    borderWidth: 1.5,
                    padding: 12,
                    displayColors: false,
                    callbacks: { label: ctx => peso(ctx.parsed.y) }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f7f6ff', drawBorder: false },
                    ticks: {
                        color: 'var(--gp-text-soft)',
                        font: { size: 11, family: 'Poppins' },
                        callback: v => v >= 1000 ? '₱' + (v / 1000).toFixed(1) + 'k' : '₱' + v
                    }
                },
                x: {
                    grid: { display: false, drawBorder: false },
                    ticks: { color: 'var(--gp-text-soft)', font: { size: 11, family: 'Poppins' } }
                }
            }
        }
    });
}

/* ── Dashboard tabs ──
   Both panels stay in the DOM and are toggled with a class, never removed —
   switchDeductionView() and exportDeductions() query for their elements
   globally and would break if tab 2's markup were detached. */
const TABS = ['overview', 'payroll'];
const TAB_KEY = 'employeeDashboardTab';

function switchDashboardTab(name) {
    if (!TABS.includes(name)) name = TABS[0];

    document.querySelectorAll('.perm-tab').forEach(btn => {
        const on = btn.dataset.tab === name;
        btn.classList.toggle('active', on);
        btn.setAttribute('aria-selected', String(on));
    });

    document.querySelectorAll('.perm-tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.dataset.panel === name);
    });

    try { sessionStorage.setItem(TAB_KEY, name); } catch { /* private mode */ }

    // A hidden panel has no height, so Chart.js will have sized the canvases to
    // zero. Re-measure now that the panel is back on screen.
    if (name === 'overview') {
        attendanceChart?.resize();
        salaryChart?.resize();
    }
}

function restoreDashboardTab() {
    let saved = null;
    try { saved = sessionStorage.getItem(TAB_KEY); } catch { /* private mode */ }
    if (saved && saved !== 'overview') switchDashboardTab(saved);
}

function switchAttendanceChart(period) {
    const evt = window.event;
    const chartCard = document.getElementById('attendanceChart').closest('.chart-card');
    chartCard.querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    evt.target.classList.add('active');

    attendanceChart.data.labels = attendanceData[period].labels;
    attendanceChart.data.datasets[0].data = attendanceData[period].data;
    attendanceChart.data.datasets[1].data = attendanceData[period].lateData;
    attendanceChart.data.datasets[2].data = attendanceData[period].absentData;
    attendanceChart.update();
}

function switchSalaryChart(period) {
    const evt = window.event;
    document.getElementById('salaryChart').closest('.chart-card')
        .querySelectorAll('.chart-tab').forEach(t => t.classList.remove('active'));
    evt.target.classList.add('active');

    salaryChart.data.labels = salaryData[period].labels;
    salaryChart.data.datasets[0].data = salaryData[period].data;
    salaryChart.update();
}

function switchDeductionView(view) {
    const evt = window.event;
    evt.stopPropagation();
    currentDeductionView = view;
    document.querySelectorAll('#deductionsSection .chart-tab').forEach(t => t.classList.remove('active'));
    evt.target.classList.add('active');

    const amountHeader = document.getElementById('deductionAmountHeader');
    const dateHeader   = document.getElementById('deductionDateHeader');
    const mobileAmountLabels = document.querySelectorAll('.mobile-deduction-amount-label');
    const mobileDateLabels   = document.querySelectorAll('.mobile-deduction-date-label');
    const amountCells = document.querySelectorAll('.deduction-amount-cell');
    const dateCells   = document.querySelectorAll('.deduction-date-cell');
    const today = new Date();

    // Amounts are stored per cutoff (twice a month), so each view scales off that.
    const views = {
        daily:   { amountLabel: 'Daily Amount',   dateLabel: 'Today',         period: 'per day',   scale: pc => pc / 15 },
        weekly:  { amountLabel: 'Weekly Amount',  dateLabel: 'Current Week',  period: 'per week',  scale: pc => pc / 2 },
        monthly: { amountLabel: 'Monthly Amount', dateLabel: 'Current Month', period: 'per month', scale: pc => pc * 2 },
    };
    const cfg = views[view] ?? views.monthly;

    amountHeader.textContent = cfg.amountLabel;
    dateHeader.textContent   = cfg.dateLabel;
    mobileAmountLabels.forEach(l => l.textContent = cfg.amountLabel);
    mobileDateLabels.forEach(l => l.textContent = cfg.dateLabel);

    amountCells.forEach(cell => {
        const pc = parseFloat(cell.dataset.perCutoff || 0);
        if (pc <= 0) return;
        const amount = cell.querySelector('.deduction-amount');
        const period = cell.querySelector('.deduction-period');
        if (amount && period) {
            amount.textContent = peso(cfg.scale(pc));
            period.textContent = cfg.period;
        }
    });

    dateCells.forEach(cell => {
        const startDate = cell.dataset.startDate;
        if (!startDate) {
            if (view === 'monthly') cell.textContent = 'N/A';
            return;
        }
        if (today < new Date(startDate)) {
            cell.textContent = 'Not yet started';
            return;
        }
        cell.textContent = formatRange(view, today);
    });
}

function formatRange(view, today) {
    if (view === 'daily') {
        return today.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    if (view === 'weekly') {
        const start = new Date(today);
        start.setDate(today.getDate() - today.getDay());
        const end = new Date(start);
        end.setDate(start.getDate() + 6);
        return start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
            + ' – ' + end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }
    // Mirrors the Blade fallback: "Jul 1 – 31, 2026". Built by hand because
    // toLocaleDateString has no day+year-only format and emits "2026 (day: 31)".
    const start = new Date(today.getFullYear(), today.getMonth(), 1);
    const end   = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    return start.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
        + ' – ' + end.getDate() + ', ' + end.getFullYear();
}

function showDeductionSummary() {
    if (deductionsData.length > 0) showDeductionModal(deductionsData[0].id);
    else alert('No deduction records available.');
}

function showDeductionModal(deductionId) {
    const d = deductionsData.find(x => x.id == deductionId);
    if (!d) { alert('Deduction not found.'); return; }

    document.getElementById('deductionCategory').textContent = (d.deduction_type?.category || 'DEDUCTION').toUpperCase() + ' DETAILS';
    document.getElementById('deductionName').textContent = d.deduction_type?.name || 'N/A';
    document.getElementById('deductionCode').textContent = d.deduction_type?.code || '';

    const totalAmount = d.total_amount ? parseFloat(d.total_amount) : (d.calculated_amount ? parseFloat(d.calculated_amount) : parseFloat(d.amount || 0));
    const installment = d.calculated_amount ? parseFloat(d.calculated_amount) : (d.installment_amount ? parseFloat(d.installment_amount) : parseFloat(d.amount || 0));
    const monthly     = installment * 2;
    const remaining   = d.remaining_balance ? parseFloat(d.remaining_balance) : 0;

    document.getElementById('deductionTotalAmount').textContent = peso(totalAmount);
    document.getElementById('deductionMonthly').textContent     = peso(monthly);
    document.getElementById('deductionInstallment').textContent = peso(installment);
    document.getElementById('deductionRemaining').textContent   = peso(remaining);
    document.getElementById('deductionStartDate').textContent   = d.start_date || 'N/A';
    document.getElementById('deductionEndDate').textContent     = d.end_date   || 'N/A';

    const badge = document.getElementById('deductionStatusBadge');
    badge.textContent = d.status ? d.status.charAt(0).toUpperCase() + d.status.slice(1) : 'Active';
    badge.className   = 'badge-status';
    if (d.status === 'active')       badge.style.cssText = 'background:var(--theme-success-subtle);color:var(--theme-success);border:1px solid #bbf7d0';
    else if (d.status === 'pending') { badge.className = 'badge-status pending'; badge.style.cssText = ''; }
    else                             { badge.className = 'badge-status on-hold'; badge.style.cssText = ''; }

    if (d.remarks) {
        document.getElementById('deductionRemarksRow').style.display = 'flex';
        document.getElementById('deductionRemarks').textContent = d.remarks;
    } else {
        document.getElementById('deductionRemarksRow').style.display = 'none';
    }

    document.getElementById('deductionModal').style.display = 'flex';
}

function exportDeductions() {
    if (deductionsData.length === 0) { alert('No deduction records to export.'); return; }

    const day = v => v ? String(v).slice(0, 10) : 'N/A';

    let csv = 'Deduction Type,Category,Monthly Amount,Per Cutoff,Remaining Balance,Total Amount,Start Date,End Date,Status\n';
    deductionsData.forEach(d => {
        const perCutoff = perCutoffOf(d);
        const monthly   = perCutoff * 2;
        csv += `"${d.deduction_type?.name || 'N/A'}","${d.deduction_type?.category || 'N/A'}","${monthly.toFixed(2)}","${perCutoff.toFixed(2)}","${num(d.remaining_balance).toFixed(2)}","${num(d.total_amount ?? d.amount).toFixed(2)}","${day(d.start_date)}","${day(d.end_date)}","${d.status || 'N/A'}"\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url  = window.URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'deductions_' + new Date().toISOString().split('T')[0] + '.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

function closeModal(id) { document.getElementById(id).style.display = 'none'; }

// <x-modal>'s close prop is always called with no arguments, so the
// generic closeModal(id) needs a zero-arg wrapper to plug into it.
function closeDeductionModal() { closeModal('deductionModal'); }

/* Sidebar toggle lives in app.js, which the employee layout loads on every
   page — binding it again here would toggle twice per click and cancel out. */

/* Inline onclick handlers in the Blade markup resolve against window, and this
   file is a module — so these have to be published explicitly. */
window.switchDashboardTab    = switchDashboardTab;
window.switchAttendanceChart = switchAttendanceChart;
window.switchSalaryChart     = switchSalaryChart;
window.switchDeductionView   = switchDeductionView;
window.showDeductionSummary  = showDeductionSummary;
window.showDeductionModal    = showDeductionModal;
window.exportDeductions      = exportDeductions;
window.closeModal            = closeModal;
window.closeDeductionModal   = closeDeductionModal;

window.addEventListener('load', () => {
    initCharts();
    // After initCharts, so the charts exist to be resized if we land on tab 2.
    restoreDashboardTab();
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
});
