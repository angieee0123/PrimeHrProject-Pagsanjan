// Monetization Requests Tab — approve / disapprove over fetch, a status
// filter over the rendered rows, and a detail modal grouping the request
// into Employee / Monetization / Request sections around the office's own
// TLB = S × D × CF working.
//
// Two rules hold this file together:
//
//   • Nothing here computes money. The peso figure is
//     `monetization_requests.computed_amount` — what
//     MonetizationRequest::computeAmount() wrote when the request was filed
//     — and the working printed beside it is that same arithmetic spelled
//     out, never a second evaluation of it. Same rule as
//     MonetizationFormDataService: a screen that recomputes is a screen that
//     can disagree with the sheet the office signs.
//
//   • A confirmation names the person. "Are you sure you want to approve?"
//     over a table of eleven rows is a question the admin cannot answer,
//     because nothing on screen says which row the menu was opened from.

import { confirmAction, notify } from '../../shared/confirmDialog.js';

function monetEsc(value) {
    return String(value ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
}

function monetMoney(value) {
    return '₱' + Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function monetDays(value) {
    const n = Number(value || 0);
    return n.toFixed(1) + ' ' + (n === 1 ? 'day' : 'days');
}

function monetStatusLabel(status) {
    return String(status || '').charAt(0).toUpperCase() + String(status || '').slice(1);
}

// The badge vocabulary in resources/css/shared/statusBadge.css — the app's
// one definition — rather than a per-status colour written here.
const MONET_BADGE_CLASS = {
    approved: 'approved',
    pending: 'pending',
    disapproved: 'rejected',
    cancelled: 'cancelled',
};

const MONET_ICONS = {
    employee: '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a7 7 0 0 1 14 0v1"/>',
    money: '<circle cx="12" cy="12" r="9"/><path d="M12 7v10M9.5 9.5h4a1.8 1.8 0 0 1 0 3.6h-4M9.5 14.5h5"/>',
    request: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
};

function monetGroup(title, iconKey, fieldsHtml, extraHtml = '') {
    return `
        <section class="monet-group">
            <h4 class="monet-group-head">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${MONET_ICONS[iconKey]}</svg>
                ${monetEsc(title)}
            </h4>
            <dl class="monet-fields">${fieldsHtml}</dl>
            ${extraHtml}
        </section>`;
}

function monetField(label, value, { wide = false, cls = '', danger = false } = {}) {
    return `
        <div class="monet-field${wide ? ' is-wide' : ''}${danger ? ' is-danger' : ''}">
            <dt>${monetEsc(label)}</dt>
            <dd class="${cls}">${monetEsc(value ?? '—') || '—'}</dd>
        </div>`;
}

/**
 * The status banner. It says what the request is *and* what happens next,
 * because an admin opening a pending request is being asked for a decision
 * and a bare "PENDING" does not tell them what approving costs.
 */
function monetBanner(r) {
    const banners = {
        pending: {
            cls: 'is-pending',
            icon: '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
            html: `<span><strong>Awaiting your decision.</strong> Approving deducts ${monetEsc(monetDays(r.total_days))} from ${monetEsc(r.employee_name)}’s VL/SL balances and records a debit in Transaction History.</span>`,
        },
        approved: {
            cls: 'is-approved',
            icon: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
            html: `<span><strong>Approved${r.decided_at ? ' on ' + monetEsc(r.decided_at) : ''}.</strong> The monetized days have been deducted and the office sheet can be printed.</span>`,
        },
        disapproved: {
            cls: 'is-danger',
            icon: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
            html: `<span><strong>Disapproved${r.decided_at ? ' on ' + monetEsc(r.decided_at) : ''}.</strong> No leave credits were deducted.</span>`,
        },
        cancelled: {
            cls: 'is-neutral',
            icon: '<circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>',
            html: '<span><strong>Cancelled by the employee.</strong> No leave credits were deducted.</span>',
        },
    };

    const b = banners[r.status] || banners.cancelled;

    return `<div class="monet-banner ${b.cls}">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${b.icon}</svg>
        ${b.html}
    </div>`;
}

/**
 * The office's computation, spelled out.
 *
 * The two multiplication lines are the *same* arithmetic the stored amount
 * came from, shown because the office's sheet shows its working — not a
 * recomputation. `D` is the days being monetized, not the credits above it,
 * matching the template's own worked example.
 */
function monetWorking(r) {
    return `
        <div class="monet-working">
            <div><span class="monet-working-key">S</span> — Monthly salary = ${monetMoney(r.monthly_salary)}</div>
            <div><span class="monet-working-key">D</span> — Days monetized (${monetEsc(monetDays(r.vl_days))} VL + ${monetEsc(monetDays(r.sl_days))} SL) = ${monetEsc(monetDays(r.total_days))}</div>
            <div><span class="monet-working-key">CF</span> — Constant factor = ${monetEsc(r.constant_factor)}</div>
            <div class="monet-working-total">TLB = ${monetMoney(r.monthly_salary)} × ${monetEsc(Number(r.total_days).toFixed(1))} × ${monetEsc(r.constant_factor)} = <span>${monetMoney(r.computed_amount)}</span></div>
        </div>`;
}

/**
 * One layout for every status — grouped Employee / Monetization / Request
 * panels. The tab used to render two entirely different bodies, a
 * computation sheet for an approved request and a flat list of label/value
 * rows for every other one, so the same request changed shape the moment it
 * was decided and a pending one never showed the admin the arithmetic they
 * were being asked to approve.
 */
function adminMonetDetailsHtml(r) {
    const employee = monetGroup('Employee Information', 'employee',
        monetField('Employee Name', r.employee_name) +
        monetField('Employee ID', r.employee_id_no) +
        monetField('Position', r.position) +
        monetField('Department / Office', r.department));

    const monetization = monetGroup('Monetization Information', 'money',
        monetField('Vacation Leave Monetized', monetDays(r.vl_days)) +
        monetField('Sick Leave Monetized', monetDays(r.sl_days)) +
        monetField('Total Days Monetized', monetDays(r.total_days)) +
        monetField('Monthly Salary', monetMoney(r.monthly_salary)) +
        // The line says "as of <filed date>" for the same reason the printed
        // sheet does: an approval has since taken the monetized days out of
        // the live balance, so today's figures would contradict the
        // computation under them.
        monetField(`VL Credits as of ${r.filed_at || 'filing'}`, monetDays(r.vl_balance), { cls: 'is-muted' }) +
        monetField(`SL Credits as of ${r.filed_at || 'filing'}`, monetDays(r.sl_balance), { cls: 'is-muted' }) +
        monetField('Computed Amount', monetMoney(r.computed_amount), { wide: true, cls: 'is-money' }),
        monetWorking(r));

    let requestFields =
        monetField('Request Number', r.request_number) +
        monetField('Date Filed', r.filed_at) +
        monetField('Reason for Request', r.reason, { wide: true, cls: 'is-muted' });

    if (r.status === 'disapproved') {
        requestFields += monetField('Disapproval Reason', r.approver_remarks, { wide: true, danger: true });
    }

    if (r.decided_by) {
        requestFields +=
            monetField(r.status === 'approved' ? 'Approved By' : 'Decided By', r.decided_by) +
            monetField(r.status === 'approved' ? 'Date Approved' : 'Date Decided', r.decided_at);
    }

    const request = monetGroup('Request Information', 'request', requestFields);

    return `<div class="monet-detail">${monetBanner(r)}${employee}${monetization}${request}</div>`;
}

// ── Detail modal ──────────────────────────────────────────────────────

window.openAdminMonetDetailModal = function (id) {
    closeMonetActionMenu();

    fetch(`/admin/monetization/${id}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                notify({ title: 'Could not open the request', message: data.message || 'The monetization request could not be loaded.', tone: 'danger' });
                return;
            }

            const r = data.request;
            window.currentMonetRequest = r;

            document.getElementById('adminMonetRequestNumber').textContent = 'MONETIZATION REQUEST · ' + r.request_number;
            document.getElementById('adminMonetEmployeeName').textContent = r.employee_name;
            document.getElementById('adminMonetEmployeeId').textContent = r.employee_id_no || 'N/A';

            const statusBadge = document.getElementById('adminMonetStatus');
            statusBadge.textContent = monetStatusLabel(r.status);
            statusBadge.className = 'badge-status ' + (MONET_BADGE_CLASS[r.status] || 'cancelled');

            document.getElementById('adminMonetDetailBody').innerHTML = adminMonetDetailsHtml(r);

            const printBtns = document.getElementById('adminMonetPrintBtns');
            if (r.status === 'approved') {
                printBtns.style.display = 'flex';
                document.getElementById('adminMonetPrintBtn').onclick = () => printMonetizationSheet(r.id);
                document.getElementById('adminMonetDownloadBtn').onclick = () => downloadMonetizationSheet(r.id);
            } else {
                printBtns.style.display = 'none';
            }

            const decisionBtns = document.getElementById('adminMonetDecisionBtns');
            if (r.status === 'pending') {
                decisionBtns.style.display = 'flex';
                document.getElementById('adminMonetApproveBtn').onclick = () => approveMonetizationRequest(r.id);
                document.getElementById('adminMonetDisapproveBtn').onclick = () => {
                    closeAdminMonetDetailModal();
                    openMonetDisapproveModal(r.id);
                };
            } else {
                decisionBtns.style.display = 'none';
            }

            document.getElementById('adminMonetDetailModal').style.display = 'flex';
        })
        .catch(() => notify({ title: 'Could not open the request', message: 'The monetization request could not be loaded. Please try again.', tone: 'danger' }));
};

window.closeAdminMonetDetailModal = function () {
    document.getElementById('adminMonetDetailModal').style.display = 'none';
};

// The office's Monetization form, rendered server-side onto the template's
// 8.5 x 14 sheet. Print streams it into the browser's own PDF viewer at the
// real page size — no dashboard chrome, no print CSS fighting the admin
// layout; Download sends the identical document as a file. Same two routes,
// same pair as the Travel Order, the Pass Slip and the printed DTR.
window.printMonetizationSheet = function (id) {
    window.open(`/admin/monetization/${id}/print-form`, '_blank');
};

window.downloadMonetizationSheet = function (id) {
    window.location.href = `/admin/monetization/${id}/download-form`;
};

// ── Reading a row ─────────────────────────────────────────────────────
//
// The confirmations need the employee's name, the amount and the days.
// They come off the row the menu was opened from rather than being passed
// through every onclick as a widening list of arguments — the row already
// renders all of them, and a second copy in the handler is a second thing
// to keep in step. Falling back to the detail payload covers the modal's
// own Approve/Disapprove buttons.

function monetRow(id) {
    return document.querySelector(`.monet-row[data-monet-id="${id}"]`);
}

function monetContext(id) {
    const row = monetRow(id);
    const current = window.currentMonetRequest;

    if (row) {
        return {
            name: row.dataset.employeeName || 'this employee',
            requestNumber: row.dataset.requestNumber || '',
            amount: row.dataset.amount ? '₱' + row.dataset.amount : '',
            days: row.dataset.days || '',
            status: row.dataset.status || '',
        };
    }

    if (current && String(current.id) === String(id)) {
        return {
            name: current.employee_name || 'this employee',
            requestNumber: current.request_number || '',
            amount: monetMoney(current.computed_amount),
            days: Number(current.total_days || 0).toFixed(1),
            status: monetStatusLabel(current.status),
        };
    }

    return { name: 'this employee', requestNumber: '', amount: '', days: '', status: '' };
}

/**
 * Move a decided request to its new status without reloading the page.
 *
 * Only the two things the decision actually changed are rewritten — the
 * status badge and which actions the row offers. Everything else on the row
 * (the name, the days, the amount) is unchanged by a decision, so re-rendering
 * it from JavaScript would only create a second place for it to be wrong.
 */
function monetApplyStatus(id, status) {
    const row = monetRow(id);
    if (!row) return;

    const label = monetStatusLabel(status);
    row.dataset.status = label;

    const badge = row.querySelector('[data-monet-badge]');
    if (badge) {
        badge.textContent = status === 'disapproved' ? 'Disapproved' : label;
        badge.className = 'badge-status ' + (MONET_BADGE_CLASS[status] || 'cancelled');
    }

    const menu = row.querySelector('.monet-action-menu');
    if (menu) menu.dataset.status = label;

    applyMonetizationAdminFilters();
}

// ── Approve ───────────────────────────────────────────────────────────

window.approveMonetizationRequest = async function (id) {
    closeMonetActionMenu();

    const ctx = monetContext(id);
    const detail = [
        ctx.requestNumber && `Request ${ctx.requestNumber}`,
        ctx.days && `${ctx.days} days`,
        ctx.amount,
    ].filter(Boolean).join(' · ');

    const ok = await confirmAction({
        title: 'Approve Monetization Request?',
        message:
            `You are about to approve the monetization request of ${ctx.name}.` +
            (detail ? `\n${detail}` : '') +
            `\n\nThe request is currently ${(ctx.status || 'Pending').toLowerCase()}. Approving it deducts the monetized days from their VL/SL balances and records a debit in Transaction History.`,
        confirmLabel: 'Approve Request',
        cancelLabel: 'Cancel',
        tone: 'success',
    });

    if (!ok) return;

    fetch(`/admin/monetization/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            'X-Requested-With': 'XMLHttpRequest',
        },
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                notify({ title: 'Could not approve', message: data.message || 'The monetization request could not be approved.', tone: 'danger' });
                return;
            }

            closeAdminMonetDetailModal();
            monetApplyStatus(id, 'approved');
            notify({
                title: 'Monetization Request Approved',
                message: `The monetization request of ${ctx.name}${ctx.requestNumber ? ` (${ctx.requestNumber})` : ''} has been approved successfully. The monetized days have been deducted from their leave balances.`,
                tone: 'success',
                label: 'Done',
            });
        })
        .catch(() => notify({ title: 'Could not approve', message: 'Something went wrong while approving the request. Please try again.', tone: 'danger' }));
};

// ── Disapprove ────────────────────────────────────────────────────────
//
// This one keeps its own modal rather than going through confirmAction():
// the controller requires a reason, and a confirmation that asks for one has
// to carry a field. The request being refused is named above that field so
// the reason is written about a request the admin can see.

window.openMonetDisapproveModal = function (id) {
    closeMonetActionMenu();

    const ctx = monetContext(id);
    window.currentMonetDisapproveId = id;
    window.currentMonetDisapproveName = ctx.name;

    document.getElementById('monetRejectModalTitle').textContent = 'Disapprove Monetization Request?';
    document.getElementById('monetRejectModalSub').textContent =
        `You are about to disapprove the monetization request of ${ctx.name}. No leave credits will be deducted.`;

    const rows = [
        ['Employee', ctx.name],
        ctx.requestNumber ? ['Request No.', ctx.requestNumber] : null,
        ctx.days ? ['Days Monetized', `${ctx.days} days`] : null,
        ctx.amount ? ['Computed Amount', ctx.amount] : null,
        ['Current Status', ctx.status || 'Pending'],
    ].filter(Boolean);

    document.getElementById('monetRejectSummary').innerHTML = rows
        .map(([label, value]) => `<div><span>${monetEsc(label)}</span><strong>${monetEsc(value)}</strong></div>`)
        .join('');

    const reason = document.getElementById('monetRejectionReason');
    reason.value = '';
    document.getElementById('monetRejectionError').style.display = 'none';

    document.getElementById('monetRejectModal').style.display = 'flex';
    reason.focus();
};

window.closeMonetDisapproveModal = function () {
    document.getElementById('monetRejectModal').style.display = 'none';
    window.currentMonetDisapproveId = null;
    window.currentMonetDisapproveName = null;
};

// ── The row action menu ───────────────────────────────────────────────
//
// An open menu is moved to <body> and positioned against the viewport.
// `.table-wrapper` scrolls, and `.glass-shell .table-section` carries a
// backdrop-filter — which makes it the containing block for any fixed
// descendant — so neither `position: absolute` nor `position: fixed` alone
// escaped the clip. Leaving the subtree does. It is put back where it came
// from on close, so the Blade stays the one copy of this markup.

let openMonetMenu = null;
let openMonetMenuHome = null;
let openMonetMenuButton = null;

function closeMonetActionMenu() {
    if (!openMonetMenu) return;

    openMonetMenu.classList.remove('is-open', 'is-floating');
    openMonetMenu.style.left = '';
    openMonetMenu.style.top = '';
    openMonetMenuHome?.appendChild(openMonetMenu);
    openMonetMenuButton?.setAttribute('aria-expanded', 'false');

    openMonetMenu = null;
    openMonetMenuHome = null;
    openMonetMenuButton = null;
}

window.toggleMonetActionMenu = function (event, btn) {
    event.stopPropagation();

    // A second click on the same button closes it. Checked before the
    // lookup below, because this button's menu is not under it while open.
    if (openMonetMenuButton === btn) {
        closeMonetActionMenu();
        return;
    }

    closeMonetActionMenu();

    const menu = btn.parentElement.querySelector('.monet-action-menu');
    if (!menu) return;

    openMonetMenu = menu;
    openMonetMenuHome = btn.parentElement;
    openMonetMenuButton = btn;

    document.body.appendChild(menu);
    menu.classList.add('is-open', 'is-floating');
    btn.setAttribute('aria-expanded', 'true');
    positionMonetMenu(btn, menu);
};

function positionMonetMenu(btn, menu) {
    const anchor = btn.getBoundingClientRect();
    const box = menu.getBoundingClientRect();
    const gap = 6;
    const margin = 8;

    // Right-aligned to the button, the way it sat when it was absolutely
    // positioned — then pulled back inside the viewport rather than left to
    // hang off the edge on a narrow screen.
    let left = anchor.right - box.width;
    left = Math.min(left, window.innerWidth - box.width - margin);
    left = Math.max(margin, left);

    // Below the button unless the last rows leave no room, in which case it
    // opens upward. This is the case that was simply cut off before.
    let top = anchor.bottom + gap;
    if (top + box.height > window.innerHeight - margin) {
        const above = anchor.top - box.height - gap;
        top = above >= margin ? above : Math.max(margin, window.innerHeight - box.height - margin);
    }

    menu.style.left = `${Math.round(left)}px`;
    menu.style.top = `${Math.round(top)}px`;
}

document.addEventListener('click', closeMonetActionMenu);
// A menu placed against the viewport does not travel with the row it belongs
// to, so any scroll or resize dismisses it rather than leaving it stranded
// over an unrelated row.
window.addEventListener('scroll', closeMonetActionMenu, true);
window.addEventListener('resize', closeMonetActionMenu);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMonetActionMenu();
});

// ── Filtering ─────────────────────────────────────────────────────────

window.applyMonetizationAdminFilters = function () {
    const status = document.getElementById('filterMonetStatus')?.value || '';
    const rows = document.querySelectorAll('#monetRequestsTableBody .monet-row');
    let visible = 0;

    rows.forEach(row => {
        const show = !status || row.dataset.status === status;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    // "No requests at all" and "no requests matching this filter" are
    // different statements, so they are different rows. Showing the first
    // for the second would report the register as empty when it is not.
    const noMatches = document.getElementById('monetNoMatchesRow');
    if (noMatches) noMatches.style.display = (rows.length > 0 && visible === 0) ? '' : 'none';

    const totalEl = document.getElementById('monetRequestRowTotal');
    if (totalEl) totalEl.textContent = visible;
    const countEl = document.getElementById('monetRequestCount');
    if (countEl) countEl.textContent = rows.length;

    const clearBtn = document.getElementById('monetClearFilters');
    if (clearBtn) clearBtn.style.display = status ? '' : 'none';
};

window.clearMonetizationAdminFilters = function () {
    const select = document.getElementById('filterMonetStatus');
    if (select) select.value = '';
    applyMonetizationAdminFilters();
};

document.addEventListener('DOMContentLoaded', function () {
    applyMonetizationAdminFilters();

    const confirmBtn = document.getElementById('monetConfirmRejectBtn');
    if (!confirmBtn) return;

    confirmBtn.addEventListener('click', function () {
        const reasonField = document.getElementById('monetRejectionReason');
        const errorEl = document.getElementById('monetRejectionError');
        const reason = reasonField.value.trim();
        const id = window.currentMonetDisapproveId;
        const name = window.currentMonetDisapproveName || 'this employee';

        if (!reason) {
            errorEl.style.display = 'block';
            reasonField.focus();
            return;
        }

        errorEl.style.display = 'none';

        const btn = this;
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.textContent = 'Disapproving…';

        fetch(`/admin/monetization/${id}/disapprove`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ remarks: reason }),
        })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalContent;

                if (!data.success) {
                    notify({ title: 'Could not disapprove', message: data.message || 'The monetization request could not be disapproved.', tone: 'danger' });
                    return;
                }

                closeMonetDisapproveModal();
                monetApplyStatus(id, 'disapproved');
                notify({
                    title: 'Monetization Request Disapproved',
                    message: `The monetization request of ${name} has been disapproved. No leave credits were deducted, and the reason you gave is shown on their own copy of the request.`,
                    tone: 'info',
                    label: 'Done',
                });
            })
            .catch(() => {
                btn.disabled = false;
                btn.innerHTML = originalContent;
                notify({ title: 'Could not disapprove', message: 'Something went wrong while disapproving the request. Please try again.', tone: 'danger' });
            });
    });
});
