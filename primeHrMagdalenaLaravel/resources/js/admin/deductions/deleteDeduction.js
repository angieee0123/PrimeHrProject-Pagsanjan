// ══════════════════════════════════════════════════════════════════════
//  Delete confirmations for the Employee Deductions and Loans tables.
//
//  Both rows post to the same endpoint, but they are not the same
//  decision, and a browser confirm() could say neither: deleting a
//  deduction stops a recurring collection, while deleting a loan throws
//  away the balance payroll is still collecting against. The amount an
//  employee still owes is exactly what an HR officer needs on screen
//  before answering, so it is named in the question.
// ══════════════════════════════════════════════════════════════════════

import { confirmAction } from '../../shared/confirmDialog.js';

const peso = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
});

/** The row action is a DELETE, so it needs a form rather than a link. */
function submitDelete(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/deductions/employee/${id}/delete`;

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;

    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';

    form.appendChild(csrfInput);
    form.appendChild(methodInput);
    document.body.appendChild(form);
    form.submit();
}

/**
 * Employee Deductions → "Delete deduction".
 *
 * @param {number} id
 * @param {string} employeeName
 * @param {string} deductionType
 * @param {number|null} remainingBalance  Loan rows appear in this table too;
 *                                        null for everything else.
 */
export async function confirmDeleteDeduction(id, employeeName, deductionType, remainingBalance = null) {
    const owed = Number(remainingBalance) || 0;
    const balanceLine = owed > 0
        ? ` ${peso.format(owed)} is still outstanding and will stop being collected.`
        : '';

    const ok = await confirmAction({
        title: 'Delete this deduction?',
        message: `${deductionType} will be removed from ${employeeName}'s record, so payroll will not deduct it on any future cutoff.${balanceLine} Payroll already processed keeps the amounts it took. This cannot be undone.`,
        confirmLabel: 'Delete deduction',
        cancelLabel: 'Keep it',
        tone: 'danger',
    });

    if (ok) submitDelete(id);
}

/**
 * Loans → "Delete loan". Same endpoint, different stakes.
 *
 * @param {number} id
 * @param {string} employeeName
 * @param {string} loanType
 * @param {number} remainingBalance
 */
export async function confirmDeleteLoan(id, employeeName, loanType, remainingBalance = 0) {
    const owed = Number(remainingBalance) || 0;

    const message = owed > 0
        ? `${employeeName} still owes ${peso.format(owed)} on this ${loanType}. Deleting it removes the loan and its balance tracking, so payroll stops collecting the rest — the remaining amount is not written off anywhere else. This cannot be undone.`
        : `${employeeName}'s ${loanType} is fully paid. Deleting it removes the loan and its repayment record from the deductions list. This cannot be undone.`;

    const ok = await confirmAction({
        title: 'Delete this loan?',
        message,
        confirmLabel: 'Delete loan',
        cancelLabel: 'Keep it',
        tone: 'danger',
    });

    if (ok) submitDelete(id);
}
