// Employee Wizard JavaScript
let currentStep = 1;
const totalSteps = 6;

window.openEmployeeWizard = function() {
    document.getElementById('employeeWizardModal').style.display = 'flex';
    currentStep = 1;
    updateWizardUI();
}

window.closeEmployeeWizard = function() {
    document.getElementById('employeeWizardModal').style.display = 'none';
    currentStep = 1;
    document.getElementById('employeeWizardForm').reset();
    // delegate to blade-defined closeEmployeeWizard for edit-mode reset if present
    if (typeof wizardIsEditMode !== 'undefined' && wizardIsEditMode) {
        wizardIsEditMode = false;
        const form = document.getElementById('employeeWizardForm');
        form.action = form.dataset.storeAction || form.action;
        document.getElementById('wizardEditId').value = '';
        document.getElementById('wizardTitle').textContent    = 'Employee Registration Wizard';
        document.getElementById('wizardSubtitle').textContent = 'Complete all steps to register';
        document.getElementById('step2-register').style.display = 'block';
        document.getElementById('step2-edit').style.display     = 'none';
        const empStatusEl = document.getElementById('wizard-employment-status');
        const salaryEl    = document.getElementById('wizard-salary-grade');
        empStatusEl.setAttribute('readonly',''); empStatusEl.style.cssText = 'background:#f7f6ff;color:#6b6a8a;cursor:not-allowed;';
        salaryEl.setAttribute('readonly','');    salaryEl.style.cssText    = 'background:#f7f6ff;color:#6b6a8a;cursor:not-allowed;';
    }
}

window.goToWizardStep = function(step) {
    currentStep = step;
    updateWizardUI();
}

window.nextStep = function() {
    if (validateCurrentStep()) {
        if (currentStep < totalSteps) {
            currentStep++;
            updateWizardUI();
            if (currentStep === totalSteps) {
                generateReview();
            }
        }
    }
}

window.previousStep = function() {
    if (currentStep > 1) {
        currentStep--;
        updateWizardUI();
    }
}

function updateWizardUI() {
    document.querySelectorAll('.wizard-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));

    document.querySelector(`.wizard-content[data-step="${currentStep}"]`).style.display = 'block';
    document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');

    document.getElementById('stepIndicator').textContent = `Step ${currentStep} of ${totalSteps}`;
    document.getElementById('prevBtn').style.display = currentStep > 1 ? 'block' : 'none';
    document.getElementById('nextBtn').style.display   = currentStep < totalSteps ? 'block' : 'none';
    const isEdit = typeof wizardIsEditMode !== 'undefined' && wizardIsEditMode;
    document.getElementById('submitBtn').style.display  = (!isEdit && currentStep === totalSteps) ? 'block' : 'none';
    document.getElementById('updateBtn').style.display  = (isEdit  && currentStep === totalSteps) ? 'block' : 'none';
}

function validateCurrentStep() {
    // Validation can be added here if needed
    return true;
}

function generateReview() {
    const formData = new FormData(document.getElementById('employeeWizardForm'));
    let html = '<div class="review-section"><div class="review-section-title">👤 Personal Info</div>';
    html += `<div class="review-row"><div class="review-item"><div class="review-label">Employee ID</div><div class="review-value">${formData.get('employee_id') || 'N/A'}</div></div>`;
    html += `<div class="review-item"><div class="review-label">Full Name</div><div class="review-value">${formData.get('first_name') || ''} ${formData.get('last_name') || ''}</div></div></div></div>`;

    html += '<div class="review-section"><div class="review-section-title">💼 Employment</div>';
    html += `<div class="review-row"><div class="review-item"><div class="review-label">Position</div><div class="review-value">${formData.get('position') || 'N/A'}</div></div>`;
    html += `<div class="review-item"><div class="review-label">Department</div><div class="review-value">${formData.get('department') || 'N/A'}</div></div></div></div>`;

    document.getElementById('wizardReviewContent').innerHTML = html;
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('employeeWizardForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (currentStep !== totalSteps) {
                e.preventDefault();
                return false;
            }
            return true;
        });
    }

    const submitBtn = document.getElementById('submitBtn');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentStep === totalSteps) {
                document.getElementById('employeeWizardForm').submit();
            } else {
                alert('Please complete all steps before submitting.');
            }
        });
    }
});
