import { initBusyDateRange } from '../../shared/busyDatesCalendar.js';

// Busy-date calendar on the deduction effectivity period. INFORMATIONAL only:
// the period legitimately spans the employee's leave and travel days, so
// nothing is blocked. minDate null because assignments can be backdated.
let deductionBusyCal = null;
document.addEventListener('DOMContentLoaded', function () {
    deductionBusyCal = initBusyDateRange({
        fromId: 'start_date',
        toId: 'end_date',
        scope: 'admin',
        minDate: null,
    });

    // Repaint the marks whenever a different employee is picked. The select
    // already has an inline onchange (checkExistingDeductions), which this
    // listener runs alongside rather than replacing.
    document.getElementById('assignEmployee')?.addEventListener('change', function () {
        if (deductionBusyCal) deductionBusyCal.setEmployee(this.value);
    });
});

function openAssignDeductionModal() {
    document.getElementById('assignDeductionModal').classList.add('active');
    setTimeout(() => handleCheckboxChange(), 100);
}

function closeAssignDeductionModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('assignDeductionModal').classList.remove('active');
    document.getElementById('assignDeductionForm').reset();
    document.getElementById('existingDeductionsWarning').style.display = 'none';
    deselectAllDeductions();
}

function handleCheckboxChange() {
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]:checked');
    const submitBtn = document.querySelector('#assignDeductionForm .btn-submit');
    const selectedCount = document.getElementById('selectedCount');

    // Update selected count
    selectedCount.textContent = `(${checkboxes.length} selected)`;

    // Enable/disable submit button based on selection
    if (checkboxes.length > 0) {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
    } else {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
    }
}

function selectAllDeductions() {
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = true);
    handleCheckboxChange();
}

function deselectAllDeductions() {
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]');
    checkboxes.forEach(cb => cb.checked = false);
    handleCheckboxChange();
}

function selectMandatoryOnly() {
    deselectAllDeductions();
    const checkboxes = document.querySelectorAll('input[name="deduction_types[]"]');
    checkboxes.forEach(cb => {
        if (cb.dataset.category === 'MANDATORY') {
            cb.checked = true;
        }
    });
    handleCheckboxChange();
}

function checkExistingDeductions() {
    const employeeId = document.getElementById('assignEmployee').value;
    const warningBox = document.getElementById('existingDeductionsWarning');
    const warningList = document.getElementById('existingDeductionsList');

    if (!employeeId) {
        warningBox.style.display = 'none';
        // Enable all checkboxes
        document.querySelectorAll('input[name="deduction_types[]"]').forEach(cb => {
            cb.disabled = false;
            cb.parentElement.style.opacity = '1';
        });
        return;
    }

    // Fetch existing deductions via AJAX
    fetch(`/admin/deductions/employee/${employeeId}/active`)
        .then(response => response.json())
        .then(data => {
            if (data.deductions && data.deductions.length > 0) {
                // Show warning
                warningBox.style.display = 'flex';
                const deductionNames = data.deductions.map(d => d.name).join(', ');
                warningList.textContent = `This employee already has: ${deductionNames}`;

                // Disable checkboxes for existing deductions
                document.querySelectorAll('input[name="deduction_types[]"]').forEach(cb => {
                    const deductionTypeId = parseInt(cb.value);
                    const hasDeduction = data.deductions.some(d => d.id === deductionTypeId);

                    if (hasDeduction) {
                        cb.disabled = true;
                        cb.checked = false;
                        cb.parentElement.style.opacity = '0.5';
                        cb.parentElement.title = 'Already assigned to this employee';
                    } else {
                        cb.disabled = false;
                        cb.parentElement.style.opacity = '1';
                        cb.parentElement.title = '';
                    }
                });
            } else {
                warningBox.style.display = 'none';
                // Enable all checkboxes
                document.querySelectorAll('input[name="deduction_types[]"]').forEach(cb => {
                    cb.disabled = false;
                    cb.parentElement.style.opacity = '1';
                });
            }
            handleCheckboxChange();
        })
        .catch(error => {
            console.error('Error fetching existing deductions:', error);
            warningBox.style.display = 'none';
        });
}

// Initialize button state on modal open
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('assignDeductionModal');
    if (modal) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class' && modal.classList.contains('active')) {
                    handleCheckboxChange();
                }
            });
        });
        observer.observe(modal, { attributes: true });
    }
});

window.openAssignDeductionModal = openAssignDeductionModal;
window.closeAssignDeductionModal = closeAssignDeductionModal;
window.handleCheckboxChange = handleCheckboxChange;
window.selectAllDeductions = selectAllDeductions;
window.deselectAllDeductions = deselectAllDeductions;
window.selectMandatoryOnly = selectMandatoryOnly;
window.checkExistingDeductions = checkExistingDeductions;
