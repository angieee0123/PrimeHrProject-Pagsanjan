// Employee personal Leave & Travel calendar — read-only view of the logged-in
// employee's OWN leaves and travel orders.
//
// Markup: resources/views/employee/leaveCalendar/leaveCalendar.blade.php
// Each pill carries data-summary (label, type, dates, status) for its hover
// tooltip; clicking a day lists everything on that day in a small popover.

import { initCalendarFit } from '../../shared/calendarFit.js';

function ecParse(el, attr) {
    try { return JSON.parse(el.getAttribute(attr) || '{}'); }
    catch (e) { return {}; }
}

function ecKindClass(kind) {
    if (kind === 'leave-approved') return 'is-approved';
    if (kind === 'leave-pending')  return 'is-pending';
    return 'is-travel';
}

// ---------- Hover tooltip ----------
function ecShowTooltip(pill) {
    const s = ecParse(pill, 'data-summary');
    const tip = document.getElementById('ecTooltip');
    if (!tip || !s.label) return;

    const statusClass = (s.status_label || '').toLowerCase() === 'approved' ? 'is-approved' : 'is-pending';
    tip.innerHTML = `
        <p class="ec-tip-name">${s.label}</p>
        <p class="ec-tip-line"><span class="ec-tip-tag kind-${s.kind}">${s.type_label}</span></p>
        <p class="ec-tip-line ec-tip-dates">${s.range_label || ''}</p>
        <span class="ec-tip-status ${statusClass}">${s.status_label}</span>
    `;
    tip.style.display = 'block';
}

function ecMoveTooltip(e) {
    const tip = document.getElementById('ecTooltip');
    if (!tip || tip.style.display === 'none') return;
    const pad = 14;
    let x = e.clientX + pad, y = e.clientY + pad;
    const r = tip.getBoundingClientRect();
    if (x + r.width > window.innerWidth) x = e.clientX - r.width - pad;
    if (y + r.height > window.innerHeight) y = e.clientY - r.height - pad;
    tip.style.left = x + 'px';
    tip.style.top = y + 'px';
}

function ecHideTooltip() {
    const tip = document.getElementById('ecTooltip');
    if (tip) tip.style.display = 'none';
}

// ---------- Day popover ----------
window.closeEcDayModal = function (event) {
    if (event && event.target !== event.currentTarget) return;
    const m = document.getElementById('ecDayModal');
    if (m) m.style.display = 'none';
};

function ecOpenDayModal(cell, label) {
    const modal = document.getElementById('ecDayModal');
    const list = document.getElementById('ecDayModalList');
    const title = document.getElementById('ecDayModalTitle');
    if (!modal || !list) return;

    // The pointer leaves the pill without a mouseleave when the overlay covers
    // it, so a tooltip opened on the way here would stay pinned over the panel.
    ecHideTooltip();

    title.textContent = label || 'Day';
    list.innerHTML = '';

    cell.querySelectorAll('.ec-pill').forEach(pill => {
        const s = ecParse(pill, 'data-summary');
        const row = document.createElement('div');
        row.className = 'ec-day-row kind-' + s.kind;
        row.innerHTML = `
            <span class="ec-day-row-dot"></span>
            <span class="ec-day-row-info">
                <span class="ec-day-row-name">${s.label}</span>
                <span class="ec-day-row-sub">${s.type_label} · ${s.range_label || ''}</span>
            </span>
            <span class="ec-day-row-status ${(s.status_label || '').toLowerCase() === 'approved' ? 'is-approved' : 'is-pending'}">${s.status_label}</span>
        `;
        list.appendChild(row);
    });

    modal.style.display = 'flex';
}

// ---------- Wire up ----------
document.addEventListener('DOMContentLoaded', () => {
    // How many records a month cell shows is a measurement, not a constant: on
    // the full page every one of them, and inside the modal as many as every
    // week row can afford before the month would need a scrollbar. Pills it
    // hides stay in the DOM — the popover below is built from them.
    initCalendarFit({
        grid: '.ec-days.is-month',
        cell: '.ec-day',
        head: '.ec-day-head',
        list: '.ec-events',
        item: '.ec-pill',
        more: '.ec-more',
        layout: 'stack',
    });

    document.querySelectorAll('.ec-pill').forEach(pill => {
        pill.addEventListener('mouseenter', () => ecShowTooltip(pill));
        pill.addEventListener('mousemove', ecMoveTooltip);
        pill.addEventListener('mouseleave', ecHideTooltip);
    });

    // Click anywhere on a day that has events → open that day's list.
    document.querySelectorAll('.ec-day').forEach(cell => {
        if (!cell.querySelector('.ec-pill')) return;
        cell.style.cursor = 'pointer';
        cell.addEventListener('click', () => {
            const events = cell.querySelector('.ec-events');
            ecOpenDayModal(cell, events ? events.getAttribute('data-day-label') : 'Day');
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') window.closeEcDayModal();
    });
});
