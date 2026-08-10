// Detailed DTR Modal (per-employee timeline view)
let currentDetailedEmployeeName = null;
let currentDetailedEmployeeEmpId = null;

window.openDetailedDTRModal = function(employeeId, name, empId) {
    window.currentDetailedEmployeeId = employeeId;
    currentDetailedEmployeeName = name;
    currentDetailedEmployeeEmpId = empId;

    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    document.getElementById('detailedStartDate').value = firstDay.toISOString().split('T')[0];
    document.getElementById('detailedEndDate').value = lastDay.toISOString().split('T')[0];

    document.getElementById('detailedDTRModal').style.display = 'flex';

    loadDetailedDTR();
}

window.closeDetailedDTRModal = function() {
    document.getElementById('detailedDTRModal').style.display = 'none';
    window.currentDetailedEmployeeId = null;
}

window.loadDetailedDTR = function() {
    if (!window.currentDetailedEmployeeId) return;

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }

    document.getElementById('detailedDTRLoading').style.display = 'block';
    document.getElementById('detailedDTRTable').style.display = 'none';

    fetch(`/admin/attendance/detailed/${window.currentDetailedEmployeeId}?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            renderDetailedDTR(data);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailedDTRLoading').innerHTML = '<p style="color: var(--theme-danger);">Error loading attendance records</p>';
        });
}

function computeAccreditedHours(record) {
    const amIn  = record.am_in;
    const amOut = record.am_out;
    const pmIn  = record.pm_in;
    const pmOut = record.pm_out;

    if (!amIn || !amOut || !pmIn || !pmOut) {
        return { display: '<span class="badge-incomplete">Incomplete</span>', minutes: 0, incomplete: true };
    }

    const toMin = (t) => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };

    // AM window: 08:00 – 12:00 (5-min grace: if amIn <= 08:05, treat as 08:00)
    const AM_START   = 8 * 60;         // 08:00
    const AM_END     = 12 * 60;        // 12:00
    const AM_GRACE   = AM_START + 5;   // 08:05
    // PM window: 13:00 – 17:00 (5-min grace: if pmIn <= 13:05, treat as 13:00)
    const PM_START   = 13 * 60;        // 13:00
    const PM_END     = 17 * 60;        // 17:00
    const PM_GRACE   = PM_START + 5;   // 13:05

    // AM: if within grace period, count from 08:00; otherwise count from actual amIn
    const amInMin  = toMin(amIn);
    const amFrom   = amInMin <= AM_GRACE ? AM_START : amInMin;
    const amTo     = Math.min(toMin(amOut), AM_END);
    const amMins   = Math.max(0, amTo - amFrom);

    // PM: if within grace period, count from 13:00; otherwise count from actual pmIn
    const pmInMin  = toMin(pmIn);
    const pmFrom   = pmInMin <= PM_GRACE ? PM_START : pmInMin;
    const pmTo     = Math.min(toMin(pmOut), PM_END);
    const pmMins   = Math.max(0, pmTo - pmFrom);

    const totalMins = amMins + pmMins;
    const hrs  = Math.floor(totalMins / 60);
    const mins = totalMins % 60;

    let color = 'var(--theme-success)';
    if (totalMins < 480) color = 'var(--theme-warning)';
    if (totalMins <= 0)  color = 'var(--theme-danger)';

    const label = hrs > 0 && mins > 0 ? `${hrs}h ${mins}m` : hrs > 0 ? `${hrs} hrs` : `${mins} min`;
    return { display: `<strong style="color:${color};">${label}</strong>`, minutes: totalMins, incomplete: false };
}

function getWeekNumber(d) {
    const date = new Date(Date.UTC(d.getFullYear(), d.getMonth(), d.getDate()));
    date.setUTCDate(date.getUTCDate() + 4 - (date.getUTCDay() || 7));
    const yearStart = new Date(Date.UTC(date.getUTCFullYear(), 0, 1));
    return Math.ceil((((date - yearStart) / 86400000) + 1) / 7);
}

function renderDetailedDTR(data) {
    const tbody = document.getElementById('detailedDTRBody');
    tbody.innerHTML = '';

    let totalPresent = 0;
    let totalAbsent = 0;
    let totalLate = 0;
    let totalLateMinutes = 0;
    let totalUndertimeMinutes = 0;
    let totalLeave = 0;
    let totalOvertimeMinutes = 0;
    let totalWorkedMinutes = 0;
    let lastWeekNum = null;

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    const startFormatted = new Date(startDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const endFormatted = new Date(endDate + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('detailedPeriod').textContent = startFormatted + ' - ' + endFormatted;

    const todayKey = new Date().toLocaleDateString('en-CA'); // YYYY-MM-DD in local time
    const otMinutes = (inT, outT) => {
        if (!inT || !outT) return 0;
        const toMin = (t) => { const [h, m] = t.split(':').map(Number); return h * 60 + m; };
        return Math.max(0, toMin(outT) - toMin(inT));
    };

    data.records.forEach(record => {
        const tr = document.createElement('tr');

        const isWeekend = record.day === 'Saturday' || record.day === 'Sunday';
        if (isWeekend) {
            tr.className = 'day-weekend';
        }

        const hasAnyLog = record.am_in || record.am_out || record.pm_in || record.pm_out;
        const isAbsent = !isWeekend && !hasAnyLog && !record.is_on_leave;
        if (isAbsent) {
            tr.className = 'day-absent';
            totalAbsent++;
        } else if (hasAnyLog) {
            totalPresent++;
        }

        const isLate = record.late_minutes > 0;
        if (isLate) {
            totalLate++;
            totalLateMinutes += record.late_minutes;
        }

        if (record.undertime > 0) {
            totalUndertimeMinutes += record.undertime;
        }

        if (record.is_on_leave) {
            totalLeave++;
        }

        totalOvertimeMinutes += otMinutes(record.ot_in, record.ot_out);
        if (record.accredited_minutes > 0) {
            totalWorkedMinutes += record.accredited_minutes;
        }

        // Add class if needs review
        if (record.needs_review) {
            tr.className = 'day-needs-review';
        }

        // Highlight the current day
        if (record.date_key === todayKey) {
            tr.classList.add('day-today');
        }

        // Build status badge
        let statusBadge = '';
        if (record.is_abandoned) {
            statusBadge = ' <span class="badge-absent">ABANDONED</span>';
            tr.className = 'day-absent';
        } else if (record.is_absent || isAbsent) {
            statusBadge = ' <span class="badge-absent">ABSENT</span>';
            tr.className = 'day-absent';
        } else if (record.is_incomplete) {
            statusBadge = ' <span class="badge-incomplete">Incomplete</span>';
        } else if (record.needs_review) {
            statusBadge = ' <span class="badge-needs-review">Needs Review</span>';
        }
        if (record.is_on_pass_slip) {
            statusBadge += ' <span class="badge-passslip">Pass Slip</span>';
        }

        // Build accredited hours — clean pill + optional tooltip for annotations
        let accreditedDisplay;
        if (record.is_abandoned || record.is_absent || isAbsent) {
            accreditedDisplay = `<span class="acc-pill acc-absent">0 hrs</span>`;
        } else if (record.is_on_leave) {
            accreditedDisplay = `<span class="acc-pill acc-leave">8 hrs</span>`;
        } else if (record.accredited_minutes > 0) {
            const hrs   = Math.floor(record.accredited_minutes / 60);
            const mins  = record.accredited_minutes % 60;
            const label = hrs > 0 && mins > 0 ? `${hrs}h ${mins}m` : hrs > 0 ? `${hrs} hrs` : `${mins} min`;
            const cls   = record.accredited_minutes >= 480 ? 'acc-full' : 'acc-partial';

            // Build tooltip lines
            const tips = [];
            if (record.has_log)                          tips.push('From biometric log');
            if (record.am_grace_applied)                 tips.push('AM grace applied');
            if (record.pm_grace_applied)                 tips.push('PM grace applied');
            if (record.late_deducted_from_leave && record.late_deduction_leave_type) {
                const lm = record.late_minutes || 0;
                const lt = record.late_deduction_leave_type.replace(/ \((full|partial)\)/, '');
                tips.push(`Late ${lm}m → covered by ${lt}`);
            }
            if (record.undertime_deducted_from_leave && record.undertime_deduction_leave_type) {
                const um = record.undertime || 0;
                const ut = record.undertime_deduction_leave_type.replace(/ \((full|partial)\)/, '');
                tips.push(`Undertime ${um}m → covered by ${ut}`);
            }
            if (record.lwop_minutes > 0) {
                tips.push(`LWOP ${record.lwop_minutes}m → salary deduction`);
            }
            if (record.is_on_pass_slip && record.pass_slip_info) {
                record.pass_slip_info.forEach(slip => {
                    const label = slip.type === 'official_activity' ? 'Official Activity' : 'Personal Reason';
                    if (slip.gap_minutes > 0) {
                        const verb = slip.excused ? 'excused' : 'charged as undertime';
                        tips.push(`${label} Pass Slip: ${slip.gap_minutes}m ${verb} (approved)`);
                    } else {
                        tips.push(`${label} Pass Slip (approved)`);
                    }
                });
            }

            const tipAttr = tips.length ? ` data-acc-tip="${tips.join(' · ')}"` : '';
            accreditedDisplay = `<span class="acc-pill ${cls}"${tipAttr}>${label}${tips.length ? '<svg class="acc-info-ico" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>' : ''}</span>`;
        } else {
            accreditedDisplay = `<span class="acc-pill acc-incomplete">Incomplete</span>`;
        }

        // Build leave deduction display
        let leaveDeductionDisplay = '—';
        if (record.is_on_leave && record.leave_info) {
            // Full-day approved leave
            const leaveType = record.leave_info.leave_type || 'Leave';
            const leaveCode = record.leave_info.leave_code || 'N/A';
            const days = record.leave_info.days || 1;
            leaveDeductionDisplay = `<span style="color:var(--gp-pri);font-weight:600;">${leaveCode}</span><br><small style="color:var(--theme-neutral-700);font-size:10px;">${leaveType} (${days} day${days > 1 ? 's' : ''})</small>`;
        } else {
            // Late / undertime deducted from leave credits
            const parts = [];
            if (record.late_deducted_from_leave && record.late_deduction_leave_type) {
                const lt = record.late_deduction_leave_type.replace(/ \((full|partial)\)/, '');
                const lm = record.late_minutes || 0;
                const lDays = (lm / 480).toFixed(4);
                parts.push(`<span style="color:var(--gp-pri);font-weight:600;">${lt}</span><br><small style="color:var(--theme-neutral-700);font-size:10px;">Late ${lm}m (${lDays}d)</small>`);
            }
            if (record.undertime_deducted_from_leave && record.undertime_deduction_leave_type) {
                const ut = record.undertime_deduction_leave_type.replace(/ \((full|partial)\)/, '');
                const um = record.undertime || 0;
                const uDays = (um / 480).toFixed(4);
                parts.push(`<span style="color:var(--gp-pri);font-weight:600;">${ut}</span><br><small style="color:var(--theme-neutral-700);font-size:10px;">Undertime ${um}m (${uDays}d)</small>`);
            }
            if (record.lwop_minutes > 0) {
                const lwopDays = (record.lwop_minutes / 480).toFixed(4);
                parts.push(`<span style="color:var(--theme-danger);font-weight:600;">LWOP</span><br><small style="color:var(--theme-neutral-700);font-size:10px;">${record.lwop_minutes}m (${lwopDays}d)</small>`);
            }
            if (parts.length) leaveDeductionDisplay = parts.join('<br>');
        }

        const fmt12 = (t) => {
            if (!t || t === 'undefined' || t === 'null' || !/^\d{1,2}:\d{2}/.test(t)) return null;
            const [h, m] = t.split(':').map(Number);
            const suffix = h >= 12 ? 'PM' : 'AM';
            const h12 = h % 12 || 12;
            return `${h12}:${String(m).padStart(2,'0')} ${suffix}`;
        };

        // ── Timeline date cell ──
        const dotState = record.is_abandoned || record.is_absent || isAbsent ? 'absent'
            : record.is_on_leave ? 'leave'
            : isWeekend ? 'weekend'
            : isLate ? 'late'
            : record.needs_review ? 'review'
            : 'present';

        const dateObj  = new Date(record.date_key + 'T00:00:00');
        const dayNum   = dateObj.getDate();
        const monthStr = dateObj.toLocaleDateString('en-US', { month: 'short' });
        const dayStr   = record.day.substring(0, 3).toUpperCase();

        // Week separator
        const weekNum = getWeekNumber(dateObj);
        if (weekNum !== lastWeekNum) {
            const sepTr = document.createElement('tr');
            sepTr.className = 'week-sep';
            const weekStart = new Date(dateObj);
            weekStart.setDate(dateObj.getDate() - ((dateObj.getDay() + 6) % 7)); // Monday
            const weekEnd = new Date(weekStart); weekEnd.setDate(weekStart.getDate() + 6);
            const fmt = d => d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            sepTr.innerHTML = `<td colspan="10">Week ${weekNum} &nbsp;·&nbsp; ${fmt(weekStart)} – ${fmt(weekEnd)}</td>`;
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

        // Build pass slip time annotations for AM/PM cells
        const psClockIcon = '<svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
        let passSlipAmNote = '', passSlipPmNote = '';
        if (record.is_on_pass_slip && record.pass_slip_info) {
            record.pass_slip_info.forEach(slip => {
                const slipOut = fmt12(slip.time_out);
                const slipIn  = fmt12(slip.time_in);
                const label   = slip.type === 'official_activity' ? 'Official Activity' : 'Personal Reason';
                const gapNote = slip.gap_minutes > 0
                    ? (slip.excused ? `Excused ${slip.gap_minutes}m` : `+${slip.gap_minutes}m undertime`)
                    : null;
                const note = `<div class="ps-time-note" title="Approved Pass Slip${slip.slip_number ? ' #' + slip.slip_number : ''}">` +
                    psClockIcon +
                    `<span class="ps-time-badge">${label}</span>` +
                    (slipOut ? `<span class="ps-time-range">${slipOut}${slipIn ? ' – ' + slipIn : ''}</span>` : '') +
                    (gapNote ? `<span class="ps-time-gap">${gapNote}</span>` : '') +
                    `</div>`;
                // time_out is when they left — that's AM out or PM out depending on time
                if (slip.time_out) {
                    const h = parseInt(slip.time_out.split(':')[0]);
                    if (h < 13) passSlipAmNote += note;
                    else passSlipPmNote += note;
                } else {
                    passSlipPmNote += note;
                }
            });
        }

        const renderTimePair = (inTime, outTime, isOnLeave) => {
            if (isOnLeave && !inTime && !outTime) return '';
            const i = fmt12(inTime), o = fmt12(outTime);
            if (!i && !o) return '';
            return `<span class="time-val${i ? '' : ' time-missing'}">${i || '—'}</span>` +
                `<span class="time-sep">–</span>` +
                `<span class="time-val${o ? '' : ' time-missing'}">${o || '—'}</span>`;
        };

        tr.innerHTML = `
            <td style="padding:0 8px;">${dateCellHtml}</td>
            <td>
                <div class="dtr-time-cell">
                    <div class="dtr-time-row">${renderTimePair(record.am_in, record.am_out, record.is_on_leave)}</div>
                    ${passSlipAmNote}
                </div>
            </td>
            <td>
                <div class="dtr-time-cell">
                    <div class="dtr-time-row">${renderTimePair(record.pm_in, record.pm_out, record.is_on_leave)}</div>
                    ${passSlipPmNote}
                </div>
            </td>
            <td>${record.ot_in ? record.ot_in + '<br><span style="color:#9aa1b5;font-size:11px;">' + (record.ot_out || '—') + '</span>' : '—'}</td>
            <td>${record.is_on_leave && !record.pm_out ? '' : (record.undertime_display ? '<span class="log-late">' + record.undertime_display + '</span>' : (record.pm_out ? '0 min' : '—'))}</td>
            <td>${record.is_on_leave && !record.am_in ? '' : (record.late_display ? '<span class="log-late">' + record.late_display + '</span>' : (record.am_in ? '0 min' : '—'))}</td>
            <td><strong>${record.total_hours}</strong></td>
            <td>${accreditedDisplay}</td>
            <td>${leaveDeductionDisplay}</td>
            <td><button class="btn-edit-time" onclick="openCorrectModal(${record.attendance_id ? record.attendance_id : "'new_" + window.currentDetailedEmployeeId + "_" + record.date_key + "'"}, '${record.date}')" title="${record.attendance_id ? 'Edit time records' : 'Add time records'}"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg></button></td>
        `;

        // stamp data attrs for chip filtering
        tr.dataset.day = record.day;
        const chipState = record.is_abandoned || record.is_absent || isAbsent ? 'absent'
            : record.is_on_leave ? 'leave'
            : isWeekend ? 'weekend'
            : !record.am_in || !record.am_out || !record.pm_in || !record.pm_out ? 'incomplete'
            : isLate ? 'late'
            : 'present';
        tr.dataset.state = chipState;

        tbody.appendChild(tr);
    });

    const setText = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

    // Format total minutes to hrs and min
    const formatTotalMinutes = (minutes) => {
        if (minutes <= 0) return '0 min';
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        if (hours > 0 && mins > 0) {
            return hours + ' hr' + (hours > 1 ? 's' : '') + ' ' + Math.round(mins) + ' min';
        } else if (hours > 0) {
            return hours + ' hr' + (hours > 1 ? 's' : '');
        } else {
            return Math.round(mins) + ' min';
        }
    };
    const formatHours = (minutes) => {
        if (minutes <= 0) return '0h';
        const hours = Math.floor(minutes / 60);
        const mins = Math.round(minutes % 60);
        return mins > 0 ? `${hours}h ${mins}m` : `${hours}h`;
    };



    // KPI cards
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

    // Attendance score ring
    const score = workingDays > 0 ? Math.round((totalPresent / workingDays) * 100) : (totalPresent > 0 ? 100 : 0);
    const scoreCircle = document.getElementById('detailedScoreCircle');
    const scoreVal = document.getElementById('detailedScore');
    const scoreTag = document.getElementById('detailedScoreTag');
    if (scoreVal) scoreVal.textContent = score + '%';
    if (scoreCircle) {
        const C = 2 * Math.PI * 52; // r = 52
        scoreCircle.style.strokeDasharray = C.toFixed(1);
        // reset then apply so the transition animates on each load
        scoreCircle.style.strokeDashoffset = C.toFixed(1);
        let color = '#22C55E';
        if (score < 90) color = '#4F7CFF';
        if (score < 75) color = '#F59E0B';
        if (score < 50) color = '#EF4444';
        scoreCircle.setAttribute('stroke', color);
        requestAnimationFrame(() => {
            scoreCircle.style.strokeDashoffset = (C * (1 - score / 100)).toFixed(1);
        });
    }
    if (scoreTag) {
        let tag = 'Excellent', bg = '#ecfdf3', fg = '#16a34a';
        if (score < 90) { tag = 'Good';  bg = '#eef4ff'; fg = '#3a5bd9'; }
        if (score < 75) { tag = 'Fair';  bg = '#fff7ed'; fg = 'var(--theme-warning)'; }
        if (score < 50) { tag = 'Poor';  bg = '#fef2f2'; fg = 'var(--theme-danger)'; }
        scoreTag.textContent = tag;
        scoreTag.style.background = bg;
        scoreTag.style.color = fg;
    }

    document.getElementById('detailedDTRLoading').style.display = 'none';
    document.getElementById('detailedDTRTable').style.display = 'table';

    // Reset view dropdown to "All" on fresh load
    document.querySelectorAll('#ddtrDropdown .ddtr-dd-item').forEach(i => i.classList.remove('active'));
    const allItem = document.querySelector('#ddtrDropdown [data-chip="all"]');
    if (allItem) allItem.classList.add('active');
    const lbl = document.getElementById('ddtrViewLabel');
    if (lbl) lbl.textContent = 'All Records';
}

// ── Dropdown toggle ──
window.toggleDdtrDropdown = function() {
    const btn = document.getElementById('ddtrViewBtn');
    const dd  = document.getElementById('ddtrDropdown');
    const open = dd.classList.toggle('open');
    btn.classList.toggle('open', open);
};

// ── Item click: delegated on the dropdown itself so it still fires even
// though .ddtr-modal calls event.stopPropagation() on its own onclick (that
// stopPropagation happens during the bubble phase at .ddtr-modal, which is
// an ANCESTOR of #ddtrDropdown, so a listener bound here already ran) ──
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

// ── Outside click closes the dropdown. Registered in the capture phase so
// it still runs even when .ddtr-modal's stopPropagation() halts bubbling ──
document.addEventListener('click', function(e) {
    if (!e.target.closest('#ddtrViewWrap')) {
        document.getElementById('ddtrDropdown')?.classList.remove('open');
        document.getElementById('ddtrViewBtn')?.classList.remove('open');
    }
}, true);

window.applyDtrChip = function applyDtrChip(chip) {
    const dayMap = { mon: 'Monday', tue: 'Tuesday', wed: 'Wednesday', thu: 'Thursday', fri: 'Friday' };
    const rows = document.querySelectorAll('#detailedDTRBody tr:not(.week-sep)');
    rows.forEach(tr => {
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
    // hide week-sep rows that have no visible data rows
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

window.exportDetailedDTR = function() {
    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    window.location.href = `/admin/attendance/detailed/${window.currentDetailedEmployeeId}/export?start_date=${startDate}&end_date=${endDate}`;
}
