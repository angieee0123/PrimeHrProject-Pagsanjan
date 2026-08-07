// Admin Leave & Travel Calendar — read-only availability monitor.
//
// Markup:  resources/views/admin/leaveCalendar/leaveCalendar.blade.php
// Each avatar marker carries:
//   data-payload  → what to open on click (leave → CS Form No. 6, travel → travel order)
//   data-summary  → what to show on hover (name, type, dates, status)
//
// The leave modal (openAdminLeaveDetailModal) ships from leaveDetailModal.js and
// the travel modal (viewOrder) from viewTravelOrderModal.js — both included on the
// page, so this file only routes clicks to them.

function calParse(el, attr) {
    try {
        return JSON.parse(el.getAttribute(attr) || '{}');
    } catch (e) {
        return {};
    }
}

// Route a marker's payload to the correct detail modal.
function calOpenDetail(payload) {
    if (!payload || !payload.kind) return;

    if (payload.kind === 'leave' && typeof window.openAdminLeaveDetailModal === 'function') {
        window.openAdminLeaveDetailModal(
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
    } else if (payload.kind === 'travel' && typeof window.viewOrder === 'function') {
        window.viewOrder(payload.id);
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

// ---------- Day-detail popover ("+X more") ----------
window.closeCalDayModal = function (event) {
    if (event && event.target !== event.currentTarget) return;
    const modal = document.getElementById('calDayModal');
    if (modal) modal.style.display = 'none';
};

function calOpenDayModal(cell, label) {
    const modal = document.getElementById('calDayModal');
    const list = document.getElementById('calDayModalList');
    const title = document.getElementById('calDayModalTitle');
    if (!modal || !list) return;

    title.textContent = label || 'Day';
    list.innerHTML = '';

    cell.querySelectorAll('.cal-marker').forEach(marker => {
        const s = calParse(marker, 'data-summary');
        const payload = calParse(marker, 'data-payload');

        const row = document.createElement('button');
        row.type = 'button';
        row.className = 'cal-day-row type-' + s.type + ' status-' + (s.status_label || '').toLowerCase();
        row.innerHTML = `
            <span class="cal-day-row-avatar">${marker.innerHTML}</span>
            <span class="cal-day-row-info">
                <span class="cal-day-row-name">${s.name}</span>
                <span class="cal-day-row-sub"><span class="cal-tip-tag type-${s.type}">${s.type_label}</span> ${s.sub || ''} · ${s.range_label || ''}</span>
            </span>
            <span class="cal-day-row-status ${(s.status_label || '').toLowerCase() === 'approved' ? 'is-approved' : 'is-pending'}">${s.status_label}</span>
        `;
        row.addEventListener('click', () => {
            window.closeCalDayModal();
            calOpenDetail(payload);
        });
        list.appendChild(row);
    });

    modal.style.display = 'flex';
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

    document.querySelectorAll('.cal-marker').forEach(marker => {
        marker.addEventListener('click', () => calOpenDetail(calParse(marker, 'data-payload')));
        marker.addEventListener('mouseenter', () => calShowTooltip(marker));
        marker.addEventListener('mousemove', calMoveTooltip);
        marker.addEventListener('mouseleave', calHideTooltip);
    });

    document.querySelectorAll('.cal-more').forEach(more => {
        more.addEventListener('click', () => {
            const cell = more.closest('.cal-day');
            calOpenDayModal(cell, more.getAttribute('data-day-label'));
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') window.closeCalDayModal();
    });
});
