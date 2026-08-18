// ── Pass slip: approve / disapprove confirmation ────────────────────────────
//
// This used to be `confirm('Approve this pass slip?')` on one button and
// `prompt('Reason for disapproval:')` on the other. Neither named the slip, so
// an approver working down a table of pending rows answered from memory about
// whichever row they believed they had clicked; and prompt() could not enforce
// the controller's `required|string|max:500` on the reason.
//
// The dialog is built from the menu item that was pressed — both items carry
// the same data attributes, so the two decisions cannot drift on which slip
// they describe — and it submits the same POST the buttons always did.

/** The slip currently open in the modal — set by openPassSlipDecision(). */
let passSlipDecisionContext = null;

const PSD_ICONS = {
    approve: '<polyline points="20 6 9 17 4 12"/>',
    disapprove: '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
};

const psdEl = (id) => document.getElementById(id);
const psdSet = (id, text) => { const node = psdEl(id); if (node) node.textContent = text; };

/** "1 hr 30 min" from a minute count; '' when the slip states no return time. */
function psdDuration(minutes) {
    const mins = parseInt(minutes, 10) || 0;
    if (mins <= 0) return '';
    const h = Math.floor(mins / 60);
    const m = mins % 60;
    if (!h) return `${m} min`;
    return m ? `${h} hr ${m} min` : `${h} hr`;
}

/**
 * Reads the pass slip off the button that was pressed.
 *
 * @param {HTMLElement} button a menu item carrying the data-* attributes
 *                            rendered by passSlip.blade.php
 */
function openPassSlipDecision(button) {
    const data = button.dataset;
    const approving = data.decision === 'approve';
    const official = data.type === 'official_activity';

    passSlipDecisionContext = {
        action: data.action,
        decision: data.decision,
    };

    // Who filed it. The photo goes in as an <img> with `src` set as a property
    // rather than interpolated into a CSS `url(...)` string — a filename holding
    // a quote would break out of the latter.
    const avatar = psdEl('psdAvatar');
    avatar.textContent = '';
    if (data.photo) {
        const img = document.createElement('img');
        img.src = data.photo;
        img.alt = '';
        avatar.appendChild(img);
    } else {
        avatar.textContent = data.initials || '--';
    }
    psdSet('psdEmployee', data.employee);
    psdSet('psdEmployeeMeta', data.department ? `${data.employeeId} · ${data.department}` : data.employeeId);
    psdSet('psdSlipNumber', data.slipNumber);

    // The slip
    psdSet('psdDate', data.date);
    psdSet('psdWindow', data.window);
    const away = psdDuration(data.minutes);
    psdSet('psdType', away ? `${data.typeLabel} · ${away}` : data.typeLabel);
    psdSet('psdDestination', data.destination || 'Not specified');
    psdSet('psdReason', [data.purposeLabel, data.reason].filter(Boolean).join(' — ') || 'Not specified');

    // The question, in this slip's own words.
    const modal = psdEl('passSlipDecisionModal');
    modal.classList.toggle('is-approve', approving);
    modal.classList.toggle('is-disapprove', !approving);

    psdEl('psdIcon').innerHTML =
        `<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">${PSD_ICONS[data.decision]}</svg>`;

    if (approving) {
        psdSet('psdEyebrow', official ? 'APPROVE OFFICIAL ACTIVITY' : 'APPROVE PERSONAL REASON');
        psdSet('psdTitle', `Approve ${data.firstName}'s pass slip for ${data.date}?`);
        psdSet('psdLede', official
            ? `${data.window} — the time out of the office is authorised as official business.`
            : `${data.window} — the time out of the office is allowed as a personal errand.`);
        // The two types do opposite things to the DTR, so the consequence is
        // written from the type rather than shared. See PassSlipComplianceService:
        // isExcused() subtracts the gap from undertime, isChargeable() adds it.
        // "the 1 hr 30 min away" when both times are stated; "the time away"
        // when the slip names no return time and the figure would be a guess.
        const awayPhrase = away ? `the ${away} away` : 'the time away';
        psdSet('psdConsequence', official
            ? `Official business counts as time rendered, so ${awayPhrase} is credited on ${data.firstName}'s daily time record instead of counting as undertime for ${data.date}.`
            : `A personal-reason slip is chargeable: ${awayPhrase} is added to ${data.firstName}'s undertime for ${data.date}, which payroll deducts from vacation leave first, then sick leave.`);
        psdSet('psdConfirmLabel', 'Yes, approve');
        psdSet('psdCancel', 'Go back');
    } else {
        psdSet('psdEyebrow', 'DISAPPROVE PASS SLIP');
        psdSet('psdTitle', `Disapprove ${data.firstName}'s pass slip for ${data.date}?`);
        psdSet('psdLede', `The time out of the office on ${data.date} will not be authorised.`);
        psdSet('psdConsequence', `${data.firstName} is not excused for ${data.window}, so that day is computed from their punches alone. Your reason is saved on the slip and is what ${data.firstName} sees when they open it. Reversing this means filing a new pass slip.`);
        psdSet('psdConfirmLabel', 'Disapprove slip');
        psdSet('psdCancel', 'Keep pending');
    }

    // The reason belongs to the disapproval only; the controller nulls
    // `remarks` on approve, so a note attached to one has nowhere to be read.
    const noteBlock = psdEl('psdNoteBlock');
    noteBlock.hidden = approving;
    psdEl('psdNote').value = '';
    psdSet('psdNoteCount', '0 / 500');

    // The server rejects an empty reason with a 422 this page has nowhere to
    // show, so the button stays out of reach until there is one to send.
    syncPassSlipConfirmState();

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    (approving ? psdEl('psdConfirm') : psdEl('psdNote')).focus();
}

function closePassSlipDecision() {
    const modal = document.getElementById('passSlipDecisionModal');
    if (!modal) return;
    modal.style.display = 'none';
    document.body.style.overflow = '';
    passSlipDecisionContext = null;
}

/** Disapproval needs a reason; approval needs nothing. */
function syncPassSlipConfirmState() {
    if (!passSlipDecisionContext) return;
    const confirmBtn = psdEl('psdConfirm');
    if (passSlipDecisionContext.decision === 'approve') {
        confirmBtn.disabled = false;
        return;
    }
    confirmBtn.disabled = psdEl('psdNote').value.trim() === '';
}

function submitPassSlipDecision() {
    if (!passSlipDecisionContext) return;

    const { action, decision } = passSlipDecisionContext;
    const reason = decision === 'approve' ? '' : psdEl('psdNote').value.trim().substring(0, 500);
    if (decision === 'disapprove' && !reason) return;

    // Double submission on a slow connection would post the decision twice.
    const confirmBtn = psdEl('psdConfirm');
    confirmBtn.disabled = true;
    psdSet('psdConfirmLabel', decision === 'approve' ? 'Approving…' : 'Disapproving…');

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
    const note = psdEl('psdNote');
    const count = psdEl('psdNoteCount');
    if (note && count) {
        // Live counter, so the 500-character ceiling is visible rather than a
        // silent truncation at submit time.
        note.addEventListener('input', () => {
            count.textContent = `${note.value.length} / 500`;
            count.classList.toggle('is-full', note.value.length >= 500);
            syncPassSlipConfirmState();
        });
    }
});

// Escape is a "no" — the safe answer for both decisions.
document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    const modal = document.getElementById('passSlipDecisionModal');
    if (modal && modal.style.display === 'flex') closePassSlipDecision();
});

// Invoked from inline onclick attributes in the pending tab and the modal.
window.openPassSlipDecision = openPassSlipDecision;
window.closePassSlipDecision = closePassSlipDecision;
window.submitPassSlipDecision = submitPassSlipDecision;
