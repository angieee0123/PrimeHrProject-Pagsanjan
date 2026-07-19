// ── Sidebar / mobile nav ──
const sidebar       = document.getElementById('sidebar');
const toggleBtn      = document.getElementById('toggle-btn');
const logoText       = document.getElementById('logo-text');
const navLabel       = document.getElementById('nav-label');
const userInfo       = document.getElementById('user-info');
const sidebarFooter  = document.getElementById('sidebar-footer');
const mobileBtn      = document.getElementById('mobile-menu-btn');
const overlay        = document.getElementById('mobile-overlay');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText) logoText.style.display = collapsed ? 'none' : '';
        if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
        if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
        if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });
}

if (mobileBtn) {
    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
}

// ── Topbar search: filter visible DTR rows ──
function filterPermanentAttendanceTable(query) {
    const q = query.toLowerCase();
    document.querySelectorAll('#detailedDTRBody tr:not(.week-sep)').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    syncWeekSeparators();
}

// ── Detailed DTR ──
// window.attendancePageData is set by an inline script in employeeAttendance.blade.php
// (@json-ed default date range, the detailed-attendance route, and the employee ID).
const DEFAULT_START = window.attendancePageData.defaultStart;
const DEFAULT_END   = window.attendancePageData.defaultEnd;
let detailedRecords = [];

document.addEventListener('DOMContentLoaded', loadDetailedDTR);

function loadDetailedDTR() {
    const startDate = document.getElementById('detailedStartDate').value;
    const endDate   = document.getElementById('detailedEndDate').value;

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before or equal to end date');
        return;
    }

    document.getElementById('detailedDTRLoading').style.display = 'block';
    document.getElementById('detailedDTRTable').style.display = 'none';

    fetch(`${window.attendancePageData.detailedRoute}?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            detailedRecords = data.records || [];
            renderDetailedDTR(detailedRecords);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailedDTRLoading').innerHTML =
                '<p style="color:#d5433c;">Failed to load attendance records. Please refresh the page.</p>';
        });
}

function resetDetailedDTR() {
    document.getElementById('detailedStartDate').value = DEFAULT_START;
    document.getElementById('detailedEndDate').value = DEFAULT_END;
    loadDetailedDTR();
}

function getWeekNumber(date) {
    const d = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
    d.setUTCDate(d.getUTCDate() + 4 - (d.getUTCDay() || 7));
    const yearStart = new Date(Date.UTC(d.getUTCFullYear(), 0, 1));
    return Math.ceil((((d - yearStart) / 86400000) + 1) / 7);
}

function fmt12(t) {
    if (!t || !/^\d{1,2}:\d{2}/.test(t)) return null;
    const [h, m] = t.split(':').map(Number);
    const suffix = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${String(m).padStart(2, '0')} ${suffix}`;
}

function renderTimePair(inTime, outTime) {
    const i = fmt12(inTime), o = fmt12(outTime);
    if (!i && !o) return '';
    return `<span class="time-val${i ? '' : ' time-missing'}">${i || '—'}</span>` +
           `<span class="time-sep">–</span>` +
           `<span class="time-val${o ? '' : ' time-missing'}">${o || '—'}</span>`;
}

function formatTotalMinutes(minutes) {
    if (minutes <= 0) return '0 min';
    const hours = Math.floor(minutes / 60);
    const mins = Math.round(minutes % 60);
    if (hours > 0 && mins > 0) return `${hours} hr${hours > 1 ? 's' : ''} ${mins} min`;
    if (hours > 0) return `${hours} hr${hours > 1 ? 's' : ''}`;
    return `${mins} min`;
}

function formatHours(minutes) {
    if (minutes <= 0) return '0h';
    const hours = Math.floor(minutes / 60);
    const mins = Math.round(minutes % 60);
    return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
}

function otMinutes(inT, outT) {
    if (!fmt12(inT) || !fmt12(outT)) return 0;
    const toMin = t => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
    return Math.max(0, toMin(outT) - toMin(inT));
}

function renderDetailedDTR(records) {
    const tbody = document.getElementById('detailedDTRBody');
    tbody.innerHTML = '';

    let totalPresent = 0, totalAbsent = 0, totalLate = 0;
    let totalLateMinutes = 0, totalUndertimeMinutes = 0;
    let totalLeave = 0, totalOvertimeMinutes = 0;
    let lastWeekNum = null;

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate   = document.getElementById('detailedEndDate').value;
    const fmtLong   = d => new Date(d + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('detailedPeriod').textContent = `${fmtLong(startDate)} – ${fmtLong(endDate)}`;

    const todayKey = new Date().toLocaleDateString('en-CA');

    records.forEach(record => {
        const tr = document.createElement('tr');

        const isWeekend  = record.day === 'Saturday' || record.day === 'Sunday';
        const isOnLeave  = !!record.is_on_leave;
        const isOnTravel = !!record.is_on_travel_order;

        // ON LEAVE / ON TRAVEL rows carry sentinel strings instead of times
        const amIn  = fmt12(record.am_in)  ? record.am_in  : null;
        const amOut = fmt12(record.am_out) ? record.am_out : null;
        const pmIn  = fmt12(record.pm_in)  ? record.pm_in  : null;
        const pmOut = fmt12(record.pm_out) ? record.pm_out : null;

        const hasAnyLog  = amIn || amOut || pmIn || pmOut;
        const isComplete = amIn && amOut && pmIn && pmOut;
        const isAbsent   = !isWeekend && !hasAnyLog && !isOnLeave && !isOnTravel;
        const isLate     = record.late_minutes > 0;

        if (isWeekend) tr.className = 'day-weekend';
        if (isAbsent) { tr.className = 'day-absent'; totalAbsent++; }
        else if (hasAnyLog) totalPresent++;

        if (isLate) { totalLate++; totalLateMinutes += record.late_minutes; }
        if (record.undertime > 0) totalUndertimeMinutes += record.undertime;
        if (isOnLeave) totalLeave++;
        totalOvertimeMinutes += otMinutes(record.ot_in, record.ot_out);

        if (record.date_key === todayKey) tr.classList.add('day-today');

        // ── Status badge ──
        let statusBadge = '';
        if (isOnTravel)      statusBadge = ' <span class="badge-travel">ON TRAVEL</span>';
        else if (isOnLeave)  statusBadge = ' <span class="badge-leave">ON LEAVE</span>';
        else if (isAbsent)   statusBadge = ' <span class="badge-absent">ABSENT</span>';
        else if (hasAnyLog && !isComplete) statusBadge = ' <span class="badge-incomplete">Incomplete</span>';

        // ── Accredited hours pill ──
        let accreditedDisplay;
        if (isOnLeave || isOnTravel) {
            accreditedDisplay = `<span class="acc-pill acc-leave">8 hrs</span>`;
        } else if (isAbsent) {
            accreditedDisplay = `<span class="acc-pill acc-absent">0 hrs</span>`;
        } else if (record.accredited_minutes > 0) {
            const hrs   = Math.floor(record.accredited_minutes / 60);
            const mins  = record.accredited_minutes % 60;
            const label = hrs > 0 && mins > 0 ? `${hrs}h ${mins}m` : hrs > 0 ? `${hrs} hrs` : `${mins} min`;
            const cls   = record.accredited_minutes >= 480 ? 'acc-full' : 'acc-partial';
            accreditedDisplay = `<span class="acc-pill ${cls}">${label}</span>`;
        } else {
            accreditedDisplay = `<span class="acc-pill acc-incomplete">Incomplete</span>`;
        }

        // ── Leave deduction ──
        let leaveDeductionDisplay = '—';
        if (isOnTravel && record.travel_order_info) {
            const t = record.travel_order_info;
            leaveDeductionDisplay = `<span style="color:#0b044d;font-weight:600;">${t.order_number}</span><br><small style="color:#6b6a8a;font-size:10px;">${t.destination} (${t.duration} day${t.duration > 1 ? 's' : ''})</small>`;
        } else if (isOnLeave && record.leave_info) {
            const l = record.leave_info;
            leaveDeductionDisplay = `<span style="color:#0b044d;font-weight:600;">${l.leave_code}</span><br><small style="color:#6b6a8a;font-size:10px;">${l.leave_type} (${l.days} day${l.days > 1 ? 's' : ''})</small>`;
        } else if (record.leave_deduction && record.leave_deduction !== '-') {
            leaveDeductionDisplay = `<span style="color:#0b044d;font-weight:600;">${record.leave_deduction}</span>`;
        }

        // ── Timeline date cell ──
        const dotState = isAbsent ? 'absent'
            : isOnLeave || isOnTravel ? 'leave'
            : isWeekend ? 'weekend'
            : isLate ? 'late'
            : hasAnyLog && !isComplete ? 'review'
            : 'present';

        const dateObj  = new Date(record.date_key + 'T00:00:00');
        const dayNum   = dateObj.getDate();
        const monthStr = dateObj.toLocaleDateString('en-US', { month: 'short' });
        const dayStr   = record.day.substring(0, 3).toUpperCase();

        // ── Week separator ──
        const weekNum = getWeekNumber(dateObj);
        if (weekNum !== lastWeekNum) {
            const sepTr = document.createElement('tr');
            sepTr.className = 'week-sep';
            const weekStart = new Date(dateObj);
            weekStart.setDate(dateObj.getDate() - ((dateObj.getDay() + 6) % 7)); // Monday
            const weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            const fmt = d => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            sepTr.innerHTML = `<td colspan="9">Week ${weekNum} &nbsp;·&nbsp; ${fmt(weekStart)} – ${fmt(weekEnd)}</td>`;
            tbody.appendChild(sepTr);
            lastWeekNum = weekNum;
        }

        const dateCellHtml = `
            <div class="dtr-date-cell">
                <div class="dtr-tl-track">
                    <div class="dtr-tl-line tl-line-${dotState}"></div>
                    <div class="dtr-tl-dot tl-${dotState}"></div>
                    <div class="dtr-tl-line tl-line-${dotState}"></div>
                </div>
                <div class="dtr-date-info">
                    <span class="dtr-date-num">${dayNum}</span>
                    <div class="dtr-date-meta">
                        <span class="dtr-date-sub">${monthStr} · ${dayStr}</span>
                        ${statusBadge}
                    </div>
                </div>
            </div>`;

        const noTime = isOnLeave || isOnTravel;

        tr.innerHTML = `
            <td class="ddtr-date-cell-pad">${dateCellHtml}</td>
            <td><div class="dtr-time-cell"><div class="dtr-time-row">${renderTimePair(amIn, amOut)}</div></div></td>
            <td><div class="dtr-time-cell"><div class="dtr-time-row">${renderTimePair(pmIn, pmOut)}</div></div></td>
            <td>${fmt12(record.ot_in) ? fmt12(record.ot_in) + '<br><span style="color:#9aa1b5;font-size:11px;">' + (fmt12(record.ot_out) || '—') + '</span>' : '—'}</td>
            <td>${noTime ? '' : (record.undertime > 0 ? '<span class="log-late">' + record.undertime_display + '</span>' : (pmOut ? '0 min' : '—'))}</td>
            <td>${noTime ? '' : (record.late_minutes > 0 ? '<span class="log-late">' + record.late_display + '</span>' : (amIn ? '0 min' : '—'))}</td>
            <td><strong>${record.total_hours}</strong></td>
            <td>${accreditedDisplay}</td>
            <td>${leaveDeductionDisplay}</td>
        `;

        // stamp data attrs for the view dropdown filter
        tr.dataset.day = record.day;
        tr.dataset.state = isAbsent ? 'absent'
            : isOnLeave || isOnTravel ? 'leave'
            : isWeekend ? 'weekend'
            : !isComplete ? 'incomplete'
            : isLate ? 'late'
            : 'present';

        tbody.appendChild(tr);
    });

    // ── KPI cards ──
    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
    const workingDays = totalPresent + totalAbsent;
    const presentPct = workingDays > 0 ? Math.round((totalPresent / workingDays) * 100) : 0;

    setText('detailedKpiPresent', totalPresent);
    setText('detailedKpiPresentSub', presentPct + '% of ' + workingDays + ' work days');
    setText('detailedKpiAbsent', totalAbsent);
    setText('detailedKpiLate', totalLate);
    setText('detailedKpiLateSub', formatTotalMinutes(totalLateMinutes) + ' total');
    setText('detailedKpiLeave', totalLeave);
    setText('detailedKpiUndertime', formatTotalMinutes(totalUndertimeMinutes));
    setText('detailedKpiOvertime', formatHours(totalOvertimeMinutes));

    document.getElementById('detailedDTRLoading').style.display = 'none';
    document.getElementById('detailedDTRTable').style.display = records.length ? 'table' : 'none';

    if (!records.length) {
        document.getElementById('detailedDTRLoading').style.display = 'block';
        document.getElementById('detailedDTRLoading').innerHTML =
            '<p>No attendance records found for this period.</p>';
    }

    // Reset the view dropdown on every fresh load
    document.querySelectorAll('#ddtrDropdown .ddtr-dd-item').forEach(i => i.classList.remove('active'));
    document.querySelector('#ddtrDropdown [data-chip="all"]')?.classList.add('active');
    const lbl = document.getElementById('ddtrViewLabel');
    if (lbl) lbl.textContent = 'All Records';
}

// ── View dropdown ──
function toggleDdtrDropdown() {
    const btn = document.getElementById('ddtrViewBtn');
    const dd  = document.getElementById('ddtrDropdown');
    const open = dd.classList.toggle('open');
    btn.classList.toggle('open', open);
}

document.getElementById('ddtrDropdown')?.addEventListener('click', function(e) {
    const item = e.target.closest('.ddtr-dd-item');
    if (!item) return;
    document.querySelectorAll('#ddtrDropdown .ddtr-dd-item').forEach(i => i.classList.remove('active'));
    item.classList.add('active');
    const lbl = document.getElementById('ddtrViewLabel');
    if (lbl) lbl.textContent = item.textContent.trim();
    document.getElementById('ddtrDropdown').classList.remove('open');
    document.getElementById('ddtrViewBtn').classList.remove('open');
    applyDtrChip(item.dataset.chip);
});

document.addEventListener('click', function(e) {
    if (!e.target.closest('#ddtrViewWrap')) {
        document.getElementById('ddtrDropdown')?.classList.remove('open');
        document.getElementById('ddtrViewBtn')?.classList.remove('open');
    }
});

function applyDtrChip(chip) {
    const dayMap = { mon: 'Monday', tue: 'Tuesday', wed: 'Wednesday', thu: 'Thursday', fri: 'Friday' };
    document.querySelectorAll('#detailedDTRBody tr:not(.week-sep)').forEach(tr => {
        const day   = tr.dataset.day   || '';
        const state = tr.dataset.state || '';
        let show = true;
        if      (chip === 'all')        show = true;
        else if (dayMap[chip])          show = day === dayMap[chip];
        else if (chip === 'weekdays')   show = ['Monday','Tuesday','Wednesday','Thursday','Friday'].includes(day);
        else if (chip === 'weekend')    show = ['Saturday','Sunday'].includes(day);
        else if (chip === 'present')    show = state === 'present';
        else if (chip === 'absent')     show = state === 'absent';
        else if (chip === 'late')       show = state === 'late';
        else if (chip === 'leave')      show = state === 'leave';
        else if (chip === 'incomplete') show = state === 'incomplete';
        tr.style.display = show ? '' : 'none';
    });
    syncWeekSeparators();
}

// Hide week separators whose rows are all filtered out
function syncWeekSeparators() {
    document.querySelectorAll('#detailedDTRBody tr.week-sep').forEach(sep => {
        let next = sep.nextElementSibling;
        let hasVisible = false;
        while (next && !next.classList.contains('week-sep')) {
            if (next.style.display !== 'none') { hasVisible = true; break; }
            next = next.nextElementSibling;
        }
        sep.style.display = hasVisible ? '' : 'none';
    });
}

// ── Export ──
function exportDetailedDTR() {
    if (!detailedRecords.length) {
        alert('No records to export');
        return;
    }

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate   = document.getElementById('detailedEndDate').value;
    const dateRange = startDate === endDate ? startDate : `${startDate}_to_${endDate}`;

    let csv = 'Date,Day,AM In,AM Out,PM In,PM Out,OT In,OT Out,Late,Undertime,Total Hours,Accredited Hours,Status\n';

    detailedRecords.forEach(record => {
        const isOnLeave  = !!record.is_on_leave;
        const isOnTravel = !!record.is_on_travel_order;
        const amIn  = fmt12(record.am_in)  ? record.am_in  : '';
        const amOut = fmt12(record.am_out) ? record.am_out : '';
        const pmIn  = fmt12(record.pm_in)  ? record.pm_in  : '';
        const pmOut = fmt12(record.pm_out) ? record.pm_out : '';
        const hasAnyLog  = amIn || amOut || pmIn || pmOut;
        const isComplete = amIn && amOut && pmIn && pmOut;

        let status = 'Present';
        if (isOnTravel)      status = 'On Travel';
        else if (isOnLeave)  status = 'On Leave';
        else if (!hasAnyLog) status = 'Absent';
        else if (!isComplete) status = 'Incomplete';
        else if (record.late_minutes > 0) status = 'Late';

        const accredited = ((record.accredited_minutes || 0) / 60).toFixed(1) + ' hrs';

        csv += `${record.date},${record.day},${amIn},${amOut},${pmIn},${pmOut},`;
        csv += `${fmt12(record.ot_in) ? record.ot_in : ''},${fmt12(record.ot_out) ? record.ot_out : ''},`;
        csv += `${record.late_display || '-'},${record.undertime_display || '-'},`;
        csv += `${record.total_hours},${accredited},${status}\n`;
    });

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Detailed_DTR_${window.attendancePageData.employeeId}_${dateRange}.csv`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}

window.filterPermanentAttendanceTable = filterPermanentAttendanceTable;
window.loadDetailedDTR = loadDetailedDTR;
window.resetDetailedDTR = resetDetailedDTR;
window.toggleDdtrDropdown = toggleDdtrDropdown;
window.exportDetailedDTR = exportDetailedDTR;
