// Mayor dashboard.
//
// The department, attendance and leave figures render server-side as ranked
// rows and stacked status meters — the right forms for magnitude comparison and
// status part-to-whole — so they need no canvas and no JS.
//
// Payroll is the one chart. It is a trend over time for a single measure, which
// is a line/area, not columns: columns ask you to compare six discrete heights,
// while a line draws the direction the money is moving, which is the question
// the panel's title actually poses.

const mayorDashboardData = window.mayorDashboardData;
const perfPeriodMonth = mayorDashboardData.perfPeriodMonth;
const payrollAnchorLabel = mayorDashboardData.payrollAnchorLabel;
const chartSeries = mayorDashboardData.chartSeries;

const INK = '#0b044d';
const SURFACE = '#ffffff';
const MUTED = '#8f8daf';

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

function peso(value) {
    if (value >= 1000000) return '₱' + (value / 1000000).toFixed(2) + 'M';
    if (value >= 1000) return '₱' + (value / 1000).toFixed(0) + 'K';
    return '₱' + Math.round(value).toLocaleString();
}

/* The crosshair finds the X: a hairline tracks the pointer and snaps to the
   nearest month, so you read a value without having to hit a 4px dot. */
const crosshair = {
    id: 'payrollCrosshair',
    afterDatasetsDraw(chart) {
        const active = chart.tooltip?.getActiveElements?.() ?? [];
        if (!active.length) return;
        const { ctx, chartArea } = chart;
        const x = active[0].element.x;
        ctx.save();
        ctx.beginPath();
        ctx.moveTo(x, chartArea.top);
        ctx.lineTo(x, chartArea.bottom);
        ctx.lineWidth = 1;
        ctx.strokeStyle = '#d9d7ea';
        ctx.stroke();
        ctx.restore();
    }
};

/* Label selectively — the newest month carries its value at the end of the
   line; a number on every point would be chaos and goes unread. */
const endLabel = {
    id: 'payrollEndLabel',
    afterDatasetsDraw(chart) {
        // Only for a single series — five labels stacked at the right edge would
        // collide, and the legend plus tooltip already carry identity there.
        if (chart.data.datasets.length !== 1) return;

        const meta = chart.getDatasetMeta(0);
        const last = meta.data[meta.data.length - 1];
        if (!last) return;
        const value = chart.data.datasets[0].data[meta.data.length - 1];
        const { ctx, chartArea } = chart;
        const text = formatValue(value, CHART_META[currentChart].unit);

        ctx.save();
        ctx.font = '700 11px Poppins, sans-serif';
        const width = ctx.measureText(text).width;

        /* The newest point always sits on the plot's right edge, so a label
           placed after it would need ~56px of right padding to clear the canvas
           — width stolen from the plot on every render. Instead it rides just
           inside, above-left of the point, and is clamped to the plot box so it
           can never reach the edge and be cut. A 2px surface backing keeps it
           legible where it crosses the line. */
        const x = Math.max(chartArea.left + width + 4, last.x - 8);
        const y = Math.max(chartArea.top + 8, last.y - 14);

        ctx.textAlign = 'right';
        ctx.textBaseline = 'middle';
        ctx.lineWidth = 4;
        ctx.strokeStyle = SURFACE;
        ctx.strokeText(text, x, y);
        ctx.fillStyle = INK;
        ctx.fillText(text, x, y);
        ctx.restore();
    }
};

/* Two-level switch, mirroring the admin dashboard: what to plot, then over what
   range. Every series ships with the page, so switching is a data swap rather
   than a round trip. */
const CHART_META = {
    payroll:     { title: 'Payroll Trend',          unit: 'peso',  vs: { week: 'vs previous day', month: 'vs previous day', year: 'vs previous month' } },
    designation: { title: 'Payroll by Designation', unit: 'peso',  vs: { week: 'vs previous day', month: 'vs previous day', year: 'vs previous month' } },
    employees:   { title: 'Employee Growth',        unit: 'count', vs: { week: 'vs previous day', month: 'vs previous day', year: 'vs previous month' } },
};
const PERIOD_RANGE = { week: '7 days through ', month: '30 days through ', year: '12 months through ' };

let payrollChart = null;
let currentChart = 'payroll';
let currentPeriod = 'year';

/** Format a value in the units of whichever series is showing. */
function formatValue(value, unit) {
    return unit === 'peso' ? peso(value) : Math.round(value).toLocaleString();
}

window.switchMayorChart = function (type) {
    if (!chartSeries[type]) return;
    currentChart = type;
    // Keyed off data-chart, not the label — "By Designation" is not its key.
    document.querySelectorAll('#mayorChartTabs .chart-tab').forEach(tab => {
        tab.classList.toggle('active', tab.dataset.chart === type);
    });
    renderSeries();
};

window.switchChartPeriod = function (period) {
    if (!chartSeries[currentChart]?.[period]) return;
    currentPeriod = period;
    document.querySelectorAll('#payrollPeriodTabs .chart-tab').forEach(tab => {
        tab.classList.toggle('active', tab.textContent.trim().toLowerCase() === period);
    });
    renderSeries();
};

/** One dataset, styled to the line/area mark spec. */
function lineDataset(label, data, color, fill) {
    return {
        label,
        data,
        borderColor: color,
        borderWidth: 2,                // 2px line
        borderJoinStyle: 'round',
        borderCapStyle: 'round',
        tension: 0.35,
        fill,
        backgroundColor: fill ? areaWash(color) : 'transparent',
        pointRadius: data.length > 14 ? 0 : 4,
        pointHoverRadius: 6,
        pointBackgroundColor: color,
        pointBorderColor: SURFACE,
        pointBorderWidth: 2,           // 2px surface ring
        pointHoverBorderWidth: 2,
        pointHitRadius: 18,            // hit target bigger than the mark
    };
}

function renderSeries() {
    const bundle = chartSeries[currentChart];
    const series = bundle?.[currentPeriod];
    if (!series || !payrollChart) return;

    const meta = CHART_META[currentChart];
    const multi = Array.isArray(series.datasets);

    payrollChart.data.labels = series.labels;

    if (multi) {
        // Several designations share the plot: no area fills (five washes would
        // muddy each other) and identity comes from the legend, which is the
        // dependable channel — never colour-matching alone.
        payrollChart.data.datasets = series.datasets.map(d => lineDataset(d.label, d.data, d.color, false));
    } else {
        payrollChart.data.datasets = [lineDataset(meta.title, series.data, INK, true)];
    }

    // A legend is always present for two or more series, and never for one —
    // keyed off the actual count, since a breakdown can legitimately come back
    // with a single designation.
    payrollChart.options.plugins.legend.display = payrollChart.data.datasets.length > 1;
    // Headcount is a whole number — never draw it with fractional ticks.
    payrollChart.options.scales.y.ticks.precision = meta.unit === 'count' ? 0 : undefined;
    payrollChart.update();

    const title = document.getElementById('payrollChartTitle');
    if (title) title.textContent = meta.title;

    const sub = document.getElementById('payrollChartSub');
    if (sub) {
        const anchor = currentPeriod === 'year' ? bundle.anchorMonth : bundle.anchorDate;
        sub.textContent = PERIOD_RANGE[currentPeriod] + anchor;
    }

    // With several designations on screen the headline states their combined
    // total for the newest bucket — the same measure, just summed.
    const headlineData = multi
        ? series.labels.map((_, i) => series.datasets.reduce((sum, d) => sum + Number(d.data[i] ?? 0), 0))
        : series.data;

    updateHeadline({ data: headlineData }, meta);
}

/* The headline restates the newest point of whichever series is showing, so the
   figure and the chart can never disagree. */
function updateHeadline(series, meta) {
    const data = series.data ?? [];
    const last = data.length ? Number(data[data.length - 1]) : 0;
    const prev = data.length > 1 ? Number(data[data.length - 2]) : null;

    const valueEl = document.getElementById('payrollHeadlineValue');
    if (valueEl) valueEl.textContent = formatValue(last, meta.unit);

    const chip = document.getElementById('payrollDelta');
    const text = document.getElementById('payrollDeltaText');
    const arrow = document.getElementById('payrollDeltaArrow');
    const label = document.getElementById('payrollDeltaLabel');
    if (!chip || !text || !arrow) return;

    if (prev === null || prev <= 0) {
        chip.hidden = true;
    } else {
        const delta = (last - prev) / prev * 100;
        const up = delta >= 0;
        chip.hidden = false;
        chip.classList.toggle('is-up', up);
        chip.classList.toggle('is-down', !up);
        arrow.setAttribute('points', up ? '6 15 12 9 18 15' : '6 9 12 15 18 9');
        // Direction in words as well as colour and arrow.
        text.textContent = (up ? 'Up ' : 'Down ') + Math.abs(delta).toFixed(1) + '%';
    }
    if (label) label.textContent = meta.vs[currentPeriod];
}

/* Area wash — the series hue at ~10%, fading toward the baseline so the fill
   never competes with the line it belongs to. */
let washCtx = null;
function areaWash(color) {
    if (!washCtx) return 'transparent';
    const grad = washCtx.createLinearGradient(0, 0, 0, 300);
    grad.addColorStop(0, hexToRgba(color, 0.14));
    grad.addColorStop(1, hexToRgba(color, 0.01));
    return grad;
}

function hexToRgba(hex, alpha) {
    const h = hex.replace('#', '');
    const n = parseInt(h.length === 3 ? h.split('').map(c => c + c).join('') : h, 16);
    return `rgba(${(n >> 16) & 255}, ${(n >> 8) & 255}, ${n & 255}, ${alpha})`;
}

window.addEventListener('load', () => {
    const canvas = document.getElementById('payrollTrendChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    washCtx = ctx;

    const initial = chartSeries[currentChart][currentPeriod];

    payrollChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initial.labels,
            datasets: [lineDataset(CHART_META[currentChart].title, initial.data, INK, true)]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            // The end label now rides inside the plot, so only a little headroom
            // is needed. Nothing is reserved on the right, which keeps the full
            // card width available to the line.
            layout: { padding: { top: 20 } },
            // The crosshair snaps to the nearest month without needing a direct hit.
            interaction: { mode: 'index', intersect: false },
            plugins: {
                // Toggled per series in renderSeries(): shown for the designation
                // breakdown, hidden when there is only one line to name.
                legend: {
                    display: false,
                    position: 'bottom',
                    labels: {
                        boxWidth: 10, boxHeight: 2, usePointStyle: false,
                        color: '#56547a', font: { size: 10.5, family: 'Poppins' },
                        padding: 12,
                    }
                },
                tooltip: {
                    backgroundColor: SURFACE,
                    titleColor: MUTED,
                    bodyColor: INK,
                    borderColor: '#ecebf6',
                    borderWidth: 1.5,
                    padding: 10,
                    // One tooltip listing every series at that X — keyed by a
                    // short stroke of the series colour rather than a filled box.
                    displayColors: true,
                    boxWidth: 10,
                    boxHeight: 2,
                    titleFont: { size: 10, family: 'Poppins', weight: '600' },
                    bodyFont: { size: 12.5, family: 'Poppins', weight: '700' },
                    callbacks: {
                        // Values lead, labels follow — in the active series' units.
                        title: (items) => items[0].label,
                        label: (item) => {
                            const value = Number(item.raw);
                            const amount = CHART_META[currentChart].unit === 'peso'
                                ? '₱' + value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                                : value.toLocaleString() + (value === 1 ? ' employee' : ' employees');
                            // Name the series only when more than one is plotted.
                            return item.chart.data.datasets.length > 1
                                ? amount + ' · ' + item.dataset.label
                                : amount;
                        },
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    // Hairline, solid, one step off the surface — recessive.
                    grid: { color: '#f1f0f9', drawTicks: false },
                    border: { display: false },
                    ticks: {
                        color: MUTED, font: { size: 10, family: 'Poppins' }, padding: 8, maxTicksLimit: 5,
                        callback: (v) => formatValue(v, CHART_META[currentChart].unit)
                    }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { color: MUTED, font: { size: 10, family: 'Poppins' }, padding: 6 }
                }
            }
        },
        plugins: [crosshair, endLabel]
    });
});
