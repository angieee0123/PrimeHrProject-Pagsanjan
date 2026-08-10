/*
    Deduction breakdown modal for the payroll register.

    Rebuilt from a string of inline styles carrying literal hexes (#fef8f8,
    #fdd, #d0c9ff) — colours that could never follow the theme — into classed
    markup. Three behaviour changes came with it:

      · the total now includes late and undertime, so it equals the "Total Ded."
        badge the modal was opened from. It previously summed only the
        contribution columns while wearing the same "Total Deductions" label;
      · zero lines are dropped rather than listed, so a clean payroll period
        shows the empty state instead of six ₱0.00 rows;
      · the header names the employee and the period, which the modal never did
        even though it is opened from one specific row.
*/

// The sign belongs outside the symbol: "₱-145.45" reads as a currency code,
// "−₱145.45" reads as money owed.
const peso = (n) => {
    const abs = Math.abs(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return (n < 0 ? '−₱' : '₱') + abs;
};

const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
}[c]));

// A time-based charge (late, undertime) and a contribution (GSIS, Pag-IBIG)
// are different kinds of money leaving the pay, so they carry different marks.
const ICONS = {
    time: '<path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/>',
    contribution: '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>',
};

function showDeductionsModal(index) {
    const dataContainer = document.querySelector(`.deductions-data[data-index="${index}"]`);
    const modal = document.getElementById('deductionsModal');
    const modalBody = document.getElementById('deductionsModalBody');
    const who = document.getElementById('deductionsModalWho');

    if (!dataContainer) return;

    const name = dataContainer.dataset.employee || '';
    const empId = dataContainer.dataset.employeeId || '';
    const period = dataContainer.dataset.period || '';
    const gross = parseFloat(dataContainer.dataset.gross) || 0;
    const net = parseFloat(dataContainer.dataset.net) || 0;

    if (who) {
        who.textContent = [name, empId && `· ${empId}`, period && `· ${period}`]
            .filter(Boolean).join(' ');
    }

    const lines = [...dataContainer.querySelectorAll('span[data-type]')]
        .map((el) => ({
            type: el.dataset.type,
            kind: el.dataset.kind || 'contribution',
            amount: parseFloat(el.dataset.amount) || 0,
        }))
        .filter((l) => l.amount > 0);

    const total = lines.reduce((sum, l) => sum + l.amount, 0);

    let html = `
        <div class="pr-ded-summary">
            <div class="pr-ded-summary-item">
                <span>Gross Pay</span>
                <strong>${peso(gross)}</strong>
            </div>
            <div class="pr-ded-summary-item is-deduction">
                <span>Deductions</span>
                <strong>${total > 0 ? '−' : ''}${peso(total)}</strong>
            </div>
            <!-- A net pay below zero is not good news, so it does not wear the
                 success colour. It happens when the period's deductions exceed
                 what was earned in it. -->
            <div class="pr-ded-summary-item is-net${net < 0 ? ' is-negative' : ''}">
                <span>Net Pay</span>
                <strong>${peso(net)}</strong>
            </div>
        </div>
    `;

    if (lines.length === 0) {
        html += `
            <div class="pr-ded-empty">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="pr-ded-empty-icon">
                    <circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/>
                </svg>
                <p class="pr-ded-empty-title">No deductions this period</p>
                <p class="pr-ded-empty-sub">Gross pay was released in full.</p>
            </div>
        `;
    } else {
        html += '<p class="vdm-section-label">BREAKDOWN</p>';

        lines.forEach((line) => {
            // How much of the total this one line accounts for — the reason a
            // breakdown is opened is usually "which of these is the big one".
            const share = total > 0 ? Math.round((line.amount / total) * 100) : 0;
            html += `
                <div class="vdm-row">
                    <span class="vdm-row-label">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="pr-ded-icon is-${esc(line.kind)}">${ICONS[line.kind] || ICONS.contribution}</svg>
                        ${esc(line.type)}
                    </span>
                    <span class="pr-ded-figures">
                        <span class="pr-ded-share" title="${share}% of total deductions">${share}%</span>
                        <strong class="vdm-row-amount">${peso(line.amount)}</strong>
                    </span>
                </div>
            `;
        });

        html += `
            <div class="pr-ded-total">
                <span class="pr-ded-total-label">Total Deductions</span>
                <strong class="pr-ded-total-amount">${peso(total)}</strong>
            </div>
        `;
    }

    modalBody.innerHTML = html;
    modal.classList.add('active');
}

function closeDeductionsModal() {
    const modal = document.getElementById('deductionsModal');
    modal.classList.remove('active');
}

// Close modal on ESC key
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeDeductionsModal();
    }
});

window.showDeductionsModal = showDeductionsModal;
window.closeDeductionsModal = closeDeductionsModal;
