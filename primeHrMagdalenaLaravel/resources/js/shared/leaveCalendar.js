// Leave & Travel Calendar — read-only availability monitor.
//
// Markup:  resources/views/partials/leaveCalendar/calendar.blade.php, drawn by
//          the admin's page and the mayor's alike.
// Each avatar marker carries:
//   data-payload  → what to open on click (leave → CS Form No. 6, travel → travel order)
//   data-summary  → what to show on hover (name, type, dates, status)
//
// This file never opens a modal itself; it routes a click to whichever detail
// modals the page around it shipped. The admin page has the approve-capable
// pair (openAdminLeaveDetailModal from leaveDetailModal.js, viewOrder from
// viewTravelOrderModal.js) and is the default; a surface with its own — the
// mayor's read-only mirrors — names them on window.leaveCalendarOpeners rather
// than forcing a second copy of everything below.

import { initCalendarFit } from './calendarFit.js';

function calParse(el, attr) {
    try {
        return JSON.parse(el.getAttribute(attr) || '{}');
    } catch (e) {
        return {};
    }
}

// Which detail modals this page shipped. Read at click time, not at load, so
// the page can register them in any order relative to this module.
function calOpener(kind) {
    const names = window.leaveCalendarOpeners || {};
    const fallback = { leave: 'openAdminLeaveDetailModal', travel: 'viewOrder' };
    const fn = window[names[kind] || fallback[kind]];
    return typeof fn === 'function' ? fn : null;
}

// Route a marker's payload to the correct detail modal.
function calOpenDetail(payload) {
    if (!payload || !payload.kind) return;

    const open = calOpener(payload.kind);
    if (!open) return;

    if (payload.kind === 'leave') {
        open(
            payload.id,
            payload.name,
            payload.employee_code,
            payload.leave_type,
            payload.detail_start,
            payload.detail_end,
            payload.days,
            payload.reason,
            payload.status_label,
            payload.application_number,
            payload.attachment_url,
            payload.approver_remarks
        );
    } else if (payload.kind === 'travel') {
        open(payload.id);
    }
}

// ---------- Hover summary tooltip ----------
const calTooltip = () => document.getElementById('calTooltip');

function calShowTooltip(marker) {
    const s = calParse(marker, 'data-summary');
    const tip = calTooltip();
    if (!tip || !s.name) return;

    const statusClass = (s.status_label || '').toLowerCase() === 'approved' ? 'is-approved' : 'is-pending';
    tip.innerHTML = `
        <p class="cal-tip-name">${s.name}</p>
        <p class="cal-tip-line"><span class="cal-tip-tag type-${s.type}">${s.type_label}</span> ${s.sub || ''}</p>
        <p class="cal-tip-line cal-tip-dates">${s.range_label || ''}</p>
        <span class="cal-tip-status ${statusClass}">${s.status_label}</span>
    `;
    tip.style.display = 'block';
}

function calMoveTooltip(e) {
    const tip = calTooltip();
    if (!tip || tip.style.display === 'none') return;
    const pad = 14;
    let x = e.clientX + pad;
    let y = e.clientY + pad;
    const rect = tip.getBoundingClientRect();
    if (x + rect.width > window.innerWidth) x = e.clientX - rect.width - pad;
    if (y + rect.height > window.innerHeight) y = e.clientY - rect.height - pad;
    tip.style.left = x + 'px';
    tip.style.top = y + 'px';
}

function calHideTooltip() {
    const tip = calTooltip();
    if (tip) tip.style.display = 'none';
}

// ---------- Filter bar ----------
// Travel orders carry no leave type, so "Travel orders only" makes the leave-type
// select meaningless. Disabling it says so immediately and keeps it out of the
// submitted query — the controller drops the pair server-side either way, so a
// URL typed by hand lands in the same place.
function calSyncLeaveTypeField() {
    const type = document.getElementById('lcType');
    const leaveCode = document.getElementById('lcLeaveCode');
    if (!type || !leaveCode) return;

    const travelOnly = type.value === 'travel';
    if (travelOnly) leaveCode.value = '';
    leaveCode.disabled = travelOnly;
    leaveCode.closest('.fld')?.classList.toggle('is-disabled', travelOnly);
}

// ---------- Wire up ----------
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('lcType')?.addEventListener('change', calSyncLeaveTypeField);
    calSyncLeaveTypeField();

    // How many faces a month cell shows is a measurement, not a constant: on
    // the full page every one of them, and inside the modal as many as every
    // week row can afford before the month would need a scrollbar. Markers it
    // hides stay in the DOM for the tooltip and the day count above.
    initCalendarFit({
        grid: '.lc-days.is-month',
        cell: '.lc-day',
        head: '.lc-day-head',
        list: '.cal-markers',
        item: '.cal-marker',
        more: '.cal-more',
        layout: 'wrap',
    });

    // A marker is a record, and the cell behind it is a date. Clicking the
    // marker must not do both, so it stops there.
    document.querySelectorAll('.cal-marker').forEach(marker => {
        marker.addEventListener('click', e => {
            e.stopPropagation();
            calOpenDetail(calParse(marker, 'data-payload'));
        });
        marker.addEventListener('mouseenter', () => calShowTooltip(marker));
        marker.addEventListener('mousemove', calMoveTooltip);
        marker.addEventListener('mouseleave', calHideTooltip);
    });

    // Day view lists each record as a full row rather than an avatar, but a
    // click means the same thing: open that record.
    document.querySelectorAll('.lc-dayview-row').forEach(row => {
        row.addEventListener('click', () => calOpenDetail(calParse(row, 'data-payload')));
    });

    // Clicking anywhere on a date opens that date in day view. The date
    // number and "+X more" are real links inside the cell and navigate on
    // their own, so this only has to cover the empty space around them.
    document.querySelectorAll('.lc-day[data-day-url]').forEach(cell => {
        cell.addEventListener('click', e => {
            if (e.target.closest('a, button')) return;
            window.location.href = cell.getAttribute('data-day-url');
        });
    });
});
