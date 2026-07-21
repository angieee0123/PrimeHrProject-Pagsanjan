function showDeductionsModal(index) {
    const dataContainer = document.querySelector(`.deductions-data[data-index="${index}"]`);
    const modal = document.getElementById('deductionsModal');
    const modalBody = document.getElementById('deductionsModalBody');

    if (!dataContainer) return;

    const deductions = dataContainer.querySelectorAll('span[data-type]');
    let html = '<p class="vdm-section-label">BREAKDOWN</p>';

    let totalAmount = 0;
    deductions.forEach(deduction => {
        const type = deduction.getAttribute('data-type');
        const amountStr = deduction.getAttribute('data-amount');
        const amount = parseFloat(amountStr.replace(/[₱,]/g, '')) || 0;
        totalAmount += amount;

        html += `
            <div class="vdm-row">
                <span class="vdm-row-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    ${type}
                </span>
                <strong class="vdm-row-amount">${amountStr}</strong>
            </div>
        `;
    });

    if (deductions.length === 0) {
        html = '<div style="text-align: center; padding: 40px 20px;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d0c9ff" stroke-width="1.5" style="margin-bottom: 12px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><p style="color: #8f8daf; font-size: 14px; margin: 0;">No deductions found</p></div>';
    } else {
        // Add total row
        html += `
            <div style="margin-top: 16px; padding-top: 16px; border-top: 2px solid #f2f1fb;">
                <div class="vdm-row" style="background: linear-gradient(135deg, #fef8f8, #fff); border: 2px solid #fdd;">
                    <span class="vdm-row-label" style="font-weight: 700; color: #0b044d !important;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                        </svg>
                        Total Deductions
                    </span>
                    <strong class="vdm-row-amount" style="font-size: 16px;">₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                </div>
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
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeductionsModal();
    }
});

window.showDeductionsModal = showDeductionsModal;
window.closeDeductionsModal = closeDeductionsModal;
