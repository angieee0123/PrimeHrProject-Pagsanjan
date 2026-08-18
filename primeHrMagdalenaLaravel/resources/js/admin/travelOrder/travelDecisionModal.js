// ── Travel order: approve / disapprove confirmation ─────────────────────────
//
// This used to be `confirm('Approve this travel order?')` on one button and
// `prompt('Reason for disapproval:')` on the other. Neither named the order, so
// an approver working down a table of pending rows answered from memory about
// whichever row they believed they had clicked; and prompt() could not enforce
// the controller's `required|string|max:500` on the reason.
//
// The dialog is now built from the menu item that was pressed — both items carry
// the same data attributes, so the two decisions cannot drift on which order
// they describe — and it submits the same POST the buttons always did.

/** The order currently open in the modal — set by openTravelDecision(). */
let travelDecisionContext = null;

const TDM_ICONS = {
    approve: '<polyline points="20 6 9 17 4 12"/>',
    disapprove: '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
};

const el = (id) => document.getElementById(id);
const set = (id, text) => { const node = el(id); if (node) node.textContent = text; };

/**
 * Reads the travel order off the button that was pressed.
 *
 * @param {HTMLElement} button a menu item carrying the data-* attributes
 *                            rendered by pending-orders-tab.blade.php
 */
function openTravelDecision(button) {
    const data = button.dataset;
    const approving = data.decision === 'approve';

    travelDecisionContext = {
        action: data.action,
        decision: data.decision,
    };

    // Who filed it. The photo goes in as an <img> with `src` set as a property
    // rather than interpolated into a CSS `url(...)` string — a filename holding
    // a quote would break out of the latter.
    const avatar = el('tdmAvatar');
    avatar.textContent = '';
    if (data.photo) {
        const img = document.createElement('img');
        img.src = data.photo;
        img.alt = '';
        avatar.appendChild(img);
    } else {
        avatar.textContent = data.initials || '--';
    }
    set('tdmEmployee', data.employee);
    set('tdmEmployeeMeta', data.department ? `${data.employeeId} · ${data.department}` : data.employeeId);
    set('tdmOrderNumber', data.orderNumber);

    // The trip
    set('tdmDates', data.dates);
    set('tdmDestination', data.destination);
    const days = parseInt(data.duration, 10) || 0;
    const party = parseInt(data.party, 10) || 1;
    set('tdmParty', `${party} ${party === 1 ? 'traveller' : 'travellers'}${days ? ` · ${days} ${days === 1 ? 'day' : 'days'}` : ''}`);
    set('tdmTransport', [data.transport, data.budget].filter(Boolean).join(' · ') || 'Not specified');
    set('tdmPurpose', data.purpose || 'Not specified');

    // The question, in this order's own words.
    const modal = el('travelDecisionModal');
    modal.classList.toggle('is-approve', approving);
    modal.classList.toggle('is-disapprove', !approving);

    el('tdmIcon').innerHTML =
        `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">${TDM_ICONS[data.decision]}</svg>`;

    if (approving) {
        set('tdmEyebrow', 'APPROVE TRAVEL ORDER');
        set('tdmTitle', `Approve ${data.firstName}'s travel to ${data.destination}?`);
        set('tdmLede', `${data.dates} — the trip is authorised as official business.`);
        // Stated because it is the part an approver cannot see from the table:
        // the approval writes attendance, which the DTR and payroll then read.
        set('tdmConsequence', `Every weekday of the trip is written onto ${data.firstName}'s daily time record as TRAVEL ORDER and credited a full 8 hours, so payroll reads the days as worked. ${data.firstName} is notified straight away.`);
        set('tdmConfirmLabel', 'Yes, approve');
        set('tdmCancel', 'Go back');
    } else {
        set('tdmEyebrow', 'DISAPPROVE TRAVEL ORDER');
        set('tdmTitle', `Disapprove ${data.firstName}'s travel to ${data.destination}?`);
        set('tdmLede', `The trip on ${data.dates} will not be authorised.`);
        set('tdmConsequence', `${data.firstName} is notified with your reason, and no attendance is credited for those dates. Reversing this means filing a new travel order.`);
        set('tdmConfirmLabel', 'Disapprove order');
        set('tdmCancel', 'Keep pending');
    }

    // The reason belongs to the disapproval only; the controller nulls
    // `remarks` on approve, so a note attached to one has nowhere to be read.
    const noteBlock = el('tdmNoteBlock');
    noteBlock.hidden = approving;
    el('tdmNote').value = '';
    set('tdmNoteCount', '0 / 500');

    // The server rejects an empty reason with a 422 this page has nowhere to
    // show, so the button stays out of reach until there is one to send.
    syncConfirmState();

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    (approving ? el('tdmConfirm') : el('tdmNote')).focus();
}

function closeTravelDecision() {
    const modal = document.getElementById('travelDecisionModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    travelDecisionContext = null;
}

/** Disapproval needs a reason; approval needs nothing. */
function syncConfirmState() {
    if (!travelDecisionContext) return;
    const confirmBtn = el('tdmConfirm');
    if (travelDecisionContext.decision === 'approve') {
        confirmBtn.disabled = false;
        return;
    }
    confirmBtn.disabled = el('tdmNote').value.trim() === '';
}

function submitTravelDecision() {
    if (!travelDecisionContext) return;

    const { action, decision } = travelDecisionContext;
    const reason = decision === 'approve' ? '' : el('tdmNote').value.trim().substring(0, 500);
    if (decision === 'disapprove' && !reason) return;

    // Double submission on a slow connection would post the decision twice.
    const confirmBtn = el('tdmConfirm');
    confirmBtn.disabled = true;
    set('tdmConfirmLabel', decision === 'approve' ? 'Approving…' : 'Disapproving…');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = action;

    const field = (name, value) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    };

    field('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
    if (reason) field('reason', reason);

    document.body.appendChild(form);
    form.submit();
}

document.addEventListener('DOMContentLoaded', function () {
    const note = el('tdmNote');
    const count = el('tdmNoteCount');
    if (note && count) {
        // Live counter, so the 500-character ceiling is visible rather than a
        // silent truncation at submit time.
        note.addEventListener('input', () => {
            count.textContent = `${note.value.length} / 500`;
            count.classList.toggle('is-full', note.value.length >= 500);
            syncConfirmState();
        });
    }
});

// Escape is a "no" — the safe answer for both decisions.
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    const modal = document.getElementById('travelDecisionModal');
    if (modal && modal.style.display === 'flex') closeTravelDecision();
});

// Invoked from inline onclick attributes in the pending tab and the modal.
window.openTravelDecision = openTravelDecision;
window.closeTravelDecision = closeTravelDecision;
window.submitTravelDecision = submitTravelDecision;
