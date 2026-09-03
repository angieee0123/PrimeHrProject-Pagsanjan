// ══════════════════════════════════════════════════════════════════════
//  Confirm dialog — the app's own, in place of window.confirm().
//
//  A browser confirm() cannot be themed, cannot say which record it is
//  about in anything but plain text, and on some platforms offers to
//  "prevent this page from creating additional dialogs" — which silently
//  disables every later confirmation on the page. Cancelling a leave
//  request is exactly the action that must not be confirmable by accident
//  or unconfirmable by a browser setting.
//
//  Two ways in:
//    • markup    — <form data-confirm="…"> or <button data-confirm="…">,
//                  intercepted here, so a Blade view needs no inline JS.
//    • promise   — await confirmAction({ title, message, … }) from a module.
//
//  The dialog is a single element appended once and reused; it is built
//  from the theme variables, so it follows whatever palette and surfaces
//  the viewer has chosen.
// ══════════════════════════════════════════════════════════════════════

let dialog = null;
let resolvePending = null;
let lastFocused = null;

const ICONS = {
    danger: '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    question: '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
    info: '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
    // An approval is a confirmation like any other, but it is not a warning:
    // shown in the danger tone it reads as "you are about to break
    // something", and shown in the primary tone it is indistinguishable
    // from the disapprove dialog beside it. Green is the only thing that
    // tells the two decisions apart at a glance.
    success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
};

function build() {
    if (dialog) return dialog;

    dialog = document.createElement('div');
    dialog.className = 'confirm-overlay';
    dialog.hidden = true;
    dialog.innerHTML = `
        <div class="confirm-card" role="alertdialog" aria-modal="true"
             aria-labelledby="confirmDialogTitle" aria-describedby="confirmDialogText">
            <span class="confirm-icon" data-role="icon" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
            </span>
            <h3 class="confirm-title" id="confirmDialogTitle"></h3>
            <p class="confirm-text" id="confirmDialogText"></p>
            <div class="confirm-actions">
                <button type="button" class="confirm-btn is-keep" data-role="cancel"></button>
                <button type="button" class="confirm-btn is-go" data-role="ok"></button>
            </div>
        </div>`;
    document.body.appendChild(dialog);

    // Clicking the backdrop, or Escape, is a "no" — the safe answer for
    // every dialog this opens.
    dialog.addEventListener('click', e => { if (e.target === dialog) settle(false); });
    dialog.querySelector('[data-role="cancel"]').addEventListener('click', () => settle(false));
    dialog.querySelector('[data-role="ok"]').addEventListener('click', () => settle(true));
    document.addEventListener('keydown', e => {
        if (dialog.hidden) return;
        if (e.key === 'Escape') settle(false);
        if (e.key === 'Tab') trapFocus(e);
    });

    return dialog;
}

/** Keep tab focus inside the dialog while it is open. */
function trapFocus(e) {
    const focusable = dialog.querySelectorAll('button');
    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
    } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
    }
}

function settle(answer) {
    if (!dialog || dialog.hidden) return;
    dialog.hidden = true;
    document.body.style.overflow = '';
    lastFocused?.focus?.();

    const resolve = resolvePending;
    resolvePending = null;
    resolve?.(answer);
}

/**
 * Ask the question. Resolves true if confirmed, false otherwise.
 *
 * @returns {Promise<boolean>}
 */
export function confirmAction({
    title = 'Are you sure?',
    message = '',
    confirmLabel = 'Confirm',
    cancelLabel = 'Keep it',
    tone = 'danger',
} = {}) {
    build();

    // A second question while one is open would strand the first promise.
    settle(false);

    dialog.querySelector('[data-role="icon"]').className = `confirm-icon is-${tone}`;
    dialog.querySelector('[data-role="icon"] svg').innerHTML = ICONS[tone] || ICONS.question;
    dialog.querySelector('#confirmDialogTitle').textContent = title;

    const text = dialog.querySelector('#confirmDialogText');
    text.textContent = message;
    text.hidden = !message;

    const ok = dialog.querySelector('[data-role="ok"]');
    ok.textContent = confirmLabel;
    ok.className = `confirm-btn is-go tone-${tone}`;

    // No cancel label means there is nothing to decline — notify() uses this
    // for a one-button message, and an empty button would just be a gap.
    const cancel = dialog.querySelector('[data-role="cancel"]');
    cancel.textContent = cancelLabel;
    cancel.hidden = !cancelLabel;

    lastFocused = document.activeElement;
    dialog.hidden = false;
    document.body.style.overflow = 'hidden';
    // Focus lands on the safe button, so a stray Enter cannot confirm.
    (cancelLabel ? cancel : ok).focus();

    return new Promise(resolve => { resolvePending = resolve; });
}

/** Message-only variant, for reporting an outcome rather than asking. */
export function notify({ title = 'Notice', message = '', tone = 'info', label = 'Got it' } = {}) {
    return confirmAction({ title, message, tone, confirmLabel: label, cancelLabel: '' })
        .then(() => {
            // A one-button dialog has nothing to decline.
        });
}

// ── Markup route ──────────────────────────────────────────────────────
//
// <form data-confirm="…" data-confirm-title="…" data-confirm-action="…">
// <button data-confirm="…"> (links and plain buttons too)
//
// Replaces `onsubmit="return confirm('…')"`, which cannot be themed and
// runs before any of this file's code gets a say.

function wire() {
    document.addEventListener('submit', async e => {
        const form = e.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === 'yes') return;

        e.preventDefault();
        const ok = await confirmAction(optionsFrom(form));
        if (!ok) return;

        // Mark and re-submit, so this listener lets it through the second time.
        form.dataset.confirmed = 'yes';
        form.submit();
    });

    document.addEventListener('click', async e => {
        const el = e.target.closest('[data-confirm]:not(form)');
        if (!el || el.closest('form[data-confirm]') || el.dataset.confirmed === 'yes') return;

        e.preventDefault();
        e.stopPropagation();
        if (!await confirmAction(optionsFrom(el))) return;

        el.dataset.confirmed = 'yes';
        el.click();
        delete el.dataset.confirmed;
    }, true);
}

function optionsFrom(el) {
    return {
        title: el.dataset.confirmTitle || 'Are you sure?',
        message: el.dataset.confirm || '',
        confirmLabel: el.dataset.confirmAction || 'Confirm',
        cancelLabel: el.dataset.confirmCancel || 'Keep it',
        tone: el.dataset.confirmTone || 'danger',
    };
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', wire);
}

// Available to inline handlers and to non-module scripts (the employee
// settings page and several modals still use plain <script> blocks).
if (typeof window !== 'undefined') {
    window.confirmAction = confirmAction;
    window.notifyDialog = notify;
}
