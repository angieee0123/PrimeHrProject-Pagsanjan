import { themeColor, themeRgba, chartChrome } from '../../shared/themeColors.js';

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

// Module scope, not per-function: switchPeriodChart() rebuilds datasets and
// needs the same chrome initCharts() used. Resolved at import, by which point
// the theme's <style> block in <head> has already been parsed.
const chrome = chartChrome();

function initCharts() {
    const ctx1 = document.getElementById('dynamicChart').getContext('2d');
    const ctx2 = document.getElementById('attendanceChart').getContext('2d');

    gradientPayroll = ctx1.createLinearGradient(0, 0, 0, 300);
    gradientPayroll.addColorStop(0, themeRgba('--theme-accent', 0.25));
    gradientPayroll.addColorStop(1, themeRgba('--theme-accent', 0.01));

    // Create gradient for Attendance Chart
    const gradientAtt = ctx2.createLinearGradient(0, 0, 0, 400);
    gradientAtt.addColorStop(0, themeRgba('--theme-accent', 0.3));
    gradientAtt.addColorStop(1, themeRgba('--theme-accent', 0.01));

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
                    grid: { color: chrome.grid, drawBorder: false },
                    ticks: {
                        color: chrome.tick,
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
                        color: chrome.tick,
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
            labels: attendanceData.labels,
            datasets: [
                {
                    label: 'Attendance Rate (%)',
                    data: attendanceData.data,
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
                    data: attendanceData.lateData,
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
                    data: attendanceData.absentData,
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
                    grid: { color: chrome.grid, drawBorder: false },
                    ticks: {
                        color: chrome.tick,
                        font: { size: 11, family: 'Poppins' },
                        padding: 8
                    }
                },
                x: {
                    offset: false,
                    grid: { display: false, drawBorder: false, offset: false },
                    ticks: {
                        color: chrome.tick,
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
            borderColor: chrome.ink,
            backgroundColor: themeRgba('--theme-primary', 0.1),
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: chrome.ink,
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
            color: chrome.tick,
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

/* ══════════════════════════════════════════════════════════════════════
   Attendance Trend — bucket size (Week / Month / Year) plus WHICH week,
   month or year it is drawn over.

   The card used to be pinned to "now": week was the last seven days,
   month the last thirty, year the last twelve. Week/Month/Year are now
   the bucket size and the picker beside them chooses the period, served
   by AttendanceTrendService through #attendanceTrendCard's data-endpoint.

   The heading and the picker are only ever moved once a payload has been
   applied, so a failed request cannot leave them describing a period the
   chart is not showing.
   ══════════════════════════════════════════════════════════════════════ */

const attCard = document.getElementById('attendanceTrendCard');
const attCanvasWrap = document.getElementById('attendanceCanvasWrap');
const attNote = document.getElementById('attendanceChartNote');
const attSub = document.getElementById('attendanceChartSub');
const attPeriodTabs = document.getElementById('attendancePeriodTabs');
const attAnchorInput = document.getElementById('attendanceAnchor');
const attPrev = document.getElementById('attendancePrev');
const attNext = document.getElementById('attendanceNext');

// One input, three shapes: a week is picked by a day, a month by a month, a
// year by a year. `format` turns the ISO anchor the server returns into the
// value that input expects.
const ATT_INPUT = {
    week:  { type: 'date',   format: (iso) => iso,           min: '2000-01-01', max: '2100-12-31' },
    month: { type: 'month',  format: (iso) => iso.slice(0, 7), min: '2000-01',   max: '2100-12' },
    year:  { type: 'number', format: (iso) => iso.slice(0, 4), min: '1900',      max: '2100' },
};

// What is on screen right now — seeded from the week the server rendered.
let attState = {
    period: attendanceData.period,
    anchor: attendanceData.anchor,
    canStepNext: attendanceData.can_step_next,
};

// Guards against a slow early request landing after a later one.
let attRequest = 0;

function setAttendanceNote(text) {
    attNote.textContent = text || '';
    attNote.hidden = !text;
    attCanvasWrap.classList.toggle('is-dimmed', Boolean(text));
}

function syncAttendanceControls() {
    const config = ATT_INPUT[attState.period] || ATT_INPUT.week;

    // type first: changing it resets the field, so min/max/value are set after.
    attAnchorInput.type = config.type;
    attAnchorInput.min = config.min;
    attAnchorInput.max = config.max;
    attAnchorInput.value = config.format(attState.anchor);

    attNext.disabled = !attState.canStepNext;

    attPeriodTabs.querySelectorAll('.chart-tab').forEach((tab) => {
        tab.classList.toggle('active', tab.dataset.period === attState.period);
    });
}

function applyAttendanceSeries(payload) {
    attendanceChart.data.labels = payload.labels;
    attendanceChart.data.datasets[0].data = payload.data;
    attendanceChart.data.datasets[1].data = payload.lateData;
    attendanceChart.data.datasets[2].data = payload.absentData;
    attendanceChart.update();

    attState = {
        period: payload.period,
        anchor: payload.anchor,
        canStepNext: payload.can_step_next,
    };

    attSub.textContent = payload.sublabel;

    if (payload.has_data) {
        setAttendanceNote('');
    } else if (payload.in_future) {
        setAttendanceNote('Nothing has been recorded yet for ' + payload.label + '.');
    } else {
        setAttendanceNote('No attendance records for ' + payload.label + '.');
    }

    syncAttendanceControls();
}

async function loadAttendancePeriod(period, anchor) {
    if (!attCard || !attendanceChart) return;

    const token = ++attRequest;
    attCard.classList.add('is-att-loading');

    try {
        const url = new URL(attCard.dataset.endpoint, window.location.origin);
        url.searchParams.set('period', period);
        if (anchor) url.searchParams.set('date', anchor);

        const response = await fetch(url, {
            headers: { Accept: 'application/json' },
            credentials: 'same-origin',
        });
        if (!response.ok) throw new Error('HTTP ' + response.status);

        const payload = await response.json();
        if (token !== attRequest) return; // superseded by a newer request
        applyAttendanceSeries(payload);
    } catch (error) {
        if (token !== attRequest) return;
        // Keep the last good chart and say what is on screen, rather than
        // showing the old line under the new period's heading.
        syncAttendanceControls();
        setAttendanceNote('Could not load attendance data for that period. Showing ' + attSub.textContent + '.');
    } finally {
        if (token === attRequest) attCard.classList.remove('is-att-loading');
    }
}

/** The anchor moved one bucket, in the direction the stepper arrows point. */
function stepAttendanceAnchor(direction) {
    const [year, month, day] = attState.anchor.split('-').map(Number);
    const date = new Date(Date.UTC(year, month - 1, day));

    if (attState.period === 'week') {
        date.setUTCDate(date.getUTCDate() + 7 * direction);
    } else if (attState.period === 'month') {
        date.setUTCDate(1);
        date.setUTCMonth(date.getUTCMonth() + direction);
    } else {
        date.setUTCFullYear(date.getUTCFullYear() + direction);
    }

    return date.toISOString().slice(0, 10);
}

/** The picker's value as a full date, or null when it is empty or half-typed. */
function readAttendanceAnchor() {
    const raw = attAnchorInput.value;
    if (!raw) return null;

    if (attState.period === 'month') return /^\d{4}-\d{2}$/.test(raw) ? raw + '-01' : null;
    if (attState.period === 'year') return /^\d{4}$/.test(raw) ? raw + '-01-01' : null;

    return /^\d{4}-\d{2}-\d{2}$/.test(raw) ? raw : null;
}

// Switching bucket size keeps you where you are: whatever is on screen becomes
// the anchor of the new view, so "Month" opens the month you were looking at.
// The tabs are wired by listener rather than by inline onclick — the employee
// dashboard has a window.switchAttendanceChart of its own, and one page's
// handler must never be reachable from the other's markup.
attPeriodTabs.addEventListener('click', (event) => {
    const tab = event.target.closest('.chart-tab');
    if (!tab) return;

    const period = tab.dataset.period;
    if (!ATT_INPUT[period] || period === attState.period) return;

    loadAttendancePeriod(period, attState.anchor);
});

attPrev.addEventListener('click', () => loadAttendancePeriod(attState.period, stepAttendanceAnchor(-1)));
attNext.addEventListener('click', () => loadAttendancePeriod(attState.period, stepAttendanceAnchor(1)));

attAnchorInput.addEventListener('change', () => {
    const anchor = readAttendanceAnchor();
    // An emptied or half-typed field is not a period: put the last one back.
    if (!anchor) {
        syncAttendanceControls();
        return;
    }

    loadAttendancePeriod(attState.period, anchor);
});


// Initialise with the week view.
//
// This ran on DOMContentLoaded while initCharts() ran on window.load — and
// DOMContentLoaded always fires first, so switchPeriodChart() reached for
// dynamicChart before it existed and threw "Cannot read properties of
// undefined (reading 'config')" on every dashboard load. The defaults now
// run after the charts they configure.
window.addEventListener('load', () => {
    initCharts();

    switchPeriodChart('week');

    // The server already rendered the current week into window.dashboardChartData,
    // so this adopts that payload (heading, picker and empty state included)
    // instead of asking for the same week again.
    applyAttendanceSeries(attendanceData);
});
