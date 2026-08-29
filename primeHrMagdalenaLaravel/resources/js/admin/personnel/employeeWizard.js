// Employee Wizard JavaScript
let currentStep = 1;
const totalSteps = 7;

// Password / Confirm Password show-hide toggle (step 2).
window.toggleWizardPassword = function(btn) {
    const input = btn.parentElement.querySelector('input');
    if (!input) return;
    const showIcon = btn.querySelector('.wizard-eye-show');
    const hideIcon = btn.querySelector('.wizard-eye-hide');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    showIcon.style.display = isHidden ? 'none' : '';
    hideIcon.style.display = isHidden ? '' : 'none';
    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
}

// Re-masks both password fields and resets their toggle icons — called
// whenever the wizard opens or closes so a revealed password never carries
// over into the next session.
function resetWizardPasswordVisibility() {
    document.querySelectorAll('#employeeWizardModal .wizard-pw-wrap').forEach(function(wrap) {
        const input = wrap.querySelector('input');
        const showIcon = wrap.querySelector('.wizard-eye-show');
        const hideIcon = wrap.querySelector('.wizard-eye-hide');
        const btn = wrap.querySelector('.wizard-pw-toggle');
        if (input) input.type = 'password';
        if (showIcon) showIcon.style.display = '';
        if (hideIcon) hideIcon.style.display = 'none';
        if (btn) btn.setAttribute('aria-label', 'Show password');
    });
}
window.resetWizardPasswordVisibility = resetWizardPasswordVisibility;

// ── Draft autosave (Add Employee flow only — never for editEmployee) ──
// Lets an admin who accidentally closes the wizard mid-entry pick up where
// they left off next time they click "Add Employee", instead of losing
// everything they typed.
const WIZARD_DRAFT_KEY = 'employeeWizardDraft';
// Never persisted: passwords (plaintext in localStorage is unsafe), the
// photo file (can't be serialized), and request/edit bookkeeping fields.
const WIZARD_DRAFT_SKIP_FIELDS = ['password', 'password_confirm', 'photo', '_token', '_edit_id'];

function collectWizardDraftData() {
    const form = document.getElementById('employeeWizardForm');
    const data = {};
    form.querySelectorAll('input, select').forEach(function(field) {
        if (!field.name || WIZARD_DRAFT_SKIP_FIELDS.includes(field.name) || field.type === 'file') return;
        if (field.type === 'checkbox') {
            if (field.name === 'roles[]' && field.closest('#register-roles')) {
                data.roles = data.roles || [];
                if (field.checked) data.roles.push(field.value);
            }
            return;
        }
        data[field.name] = field.value;
    });
    return data;
}

function isWizardDraftEmpty(data) {
    return Object.keys(data).every(function(key) {
        // "Employee" is checked by default on a brand-new form, so roles
        // alone don't mean the admin has actually started entering anything —
        // counting it would make a genuinely untouched form look "non-empty"
        // and overwrite a real saved draft the moment it's closed.
        if (key === 'roles') return true;
        const val = data[key];
        return Array.isArray(val) ? val.length === 0 : !val || !String(val).trim();
    });
}

function saveWizardDraft() {
    if (window.wizardIsEditMode) return;
    const data = collectWizardDraftData();
    // Nothing typed in the current session — leave any existing saved draft
    // alone rather than wiping it (e.g. right after "Start Over", before the
    // admin has typed anything new, the earlier draft must stay recoverable).
    if (isWizardDraftEmpty(data)) return;
    try {
        localStorage.setItem(WIZARD_DRAFT_KEY, JSON.stringify({ step: currentStep, data: data }));
    } catch (e) { /* storage unavailable/full — draft just won't persist */ }
}

function getWizardDraft() {
    try {
        const raw = localStorage.getItem(WIZARD_DRAFT_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch (e) {
        return null;
    }
}

function clearWizardDraft() {
    try { localStorage.removeItem(WIZARD_DRAFT_KEY); } catch (e) {}
}
window.clearWizardDraft = clearWizardDraft;

function applyWizardDraft(draft) {
    const data = draft.data || {};
    Object.keys(data).forEach(function(name) {
        if (name === 'roles') return;
        const field = document.querySelector('#employeeWizardForm [name="' + name + '"]');
        if (field && field.type !== 'checkbox') field.value = data[name] || '';
    });

    const roles = Array.isArray(data.roles) ? data.roles : [];
    document.querySelectorAll('#register-roles input[type="checkbox"]').forEach(function(cb) {
        cb.checked = roles.includes(cb.value);
    });

    if (data.department && window.loadDesignations) {
        window.loadDesignations(data.department, function() {
            const pos = document.getElementById('wizard-position');
            pos.value = data.designation_id || '';
            if (pos.value && window.fillFromDesignation) window.fillFromDesignation(pos);
        });
    }

    // A restored username (typed or previously auto-filled) shouldn't be
    // silently clobbered if the admin tweaks the name fields afterward.
    const restoredUsernameField = document.querySelector('#employeeWizardForm [name="username"]');
    if (restoredUsernameField && data.username) restoredUsernameField.dataset.userEdited = 'true';

    currentStep = (draft.step >= 1 && draft.step <= totalSteps) ? draft.step : 1;
    updateWizardUI();
    if (currentStep === totalSteps) generateReview();
    if (window.resetWizardFieldValidation) window.resetWizardFieldValidation();
}

// ── Username auto-fill (Step 2) — derived from Last Name + First Name ──
// e.g. Last "Santos" + First "Juan" -> "santosjuan". Stops auto-updating the
// moment the admin types into the username field directly, so a deliberate
// choice never gets overwritten by a later name edit.
let wizardUsernameAutoFilling = false;
// Unicode combining diacritical marks (U+0300–U+036F), built from char codes
// rather than a literal range so accented source files can't mangle it.
const DIACRITIC_MARKS_RE = new RegExp('[' + String.fromCharCode(0x0300) + '-' + String.fromCharCode(0x036f) + ']', 'g');

function usernameSlug(value) {
    return String(value || '')
        .toLowerCase()
        .normalize('NFD').replace(DIACRITIC_MARKS_RE, '')
        .replace(/[^a-z0-9]/g, '');
}

function autoFillWizardUsername() {
    const usernameField = document.querySelector('#employeeWizardForm [name="username"]');
    if (!usernameField || usernameField.dataset.userEdited === 'true') return;
    const firstName = document.querySelector('#employeeWizardForm [name="first_name"]');
    const lastName  = document.querySelector('#employeeWizardForm [name="last_name"]');
    const generated = usernameSlug(lastName && lastName.value) + usernameSlug(firstName && firstName.value);
    if (usernameField.value === generated) return;
    wizardUsernameAutoFilling = true;
    usernameField.value = generated;
    usernameField.dispatchEvent(new Event('input', { bubbles: true }));
    wizardUsernameAutoFilling = false;
}

function openFreshEmployeeWizard() {
    document.getElementById('employeeWizardModal').style.display = 'flex';
    currentStep = 1;
    updateWizardUI();
    const usernameField = document.querySelector('#employeeWizardForm [name="username"]');
    if (usernameField) delete usernameField.dataset.userEdited;
    if (window.resetWizardFieldValidation) window.resetWizardFieldValidation();
    if (window.clearGovIdCurrentFiles) window.clearGovIdCurrentFiles();
    clearGovIdOcrStatuses();
    resetWizardPasswordVisibility();
}

// Opens a blank wizard for this attempt WITHOUT deleting the saved draft —
// the admin may still want it back via "Continue Draft" on a later visit,
// so only a successful submission (see adminPersonnel.js) clears it.
window.startOverEmployeeWizard = function() {
    document.getElementById('employeeWizardForm').reset();
    document.getElementById('wizardDraftPrompt').style.display = 'none';
    openFreshEmployeeWizard();
}

window.continueEmployeeWizardDraft = function() {
    const draft = getWizardDraft();
    document.getElementById('wizardDraftPrompt').style.display = 'none';
    document.getElementById('employeeWizardModal').style.display = 'flex';
    resetWizardPasswordVisibility();
    if (draft) applyWizardDraft(draft);
}

window.openEmployeeWizard = function() {
    const draft = getWizardDraft();
    if (draft && !isWizardDraftEmpty(draft.data || {})) {
        document.getElementById('wizardDraftPrompt').style.display = 'flex';
        return;
    }
    openFreshEmployeeWizard();
}

window.closeEmployeeWizard = function() {
    saveWizardDraft();
    document.getElementById('employeeWizardModal').style.display = 'none';
    currentStep = 1;
    document.getElementById('employeeWizardForm').reset();
    if (window.resetWizardFieldValidation) window.resetWizardFieldValidation();
    resetWizardPasswordVisibility();
    // delegate to blade-defined closeEmployeeWizard for edit-mode reset if present
    if (window.wizardIsEditMode) {
        window.wizardIsEditMode = false;
        const form = document.getElementById('employeeWizardForm');
        form.action = form.dataset.storeAction || form.action;
        document.getElementById('wizardEditId').value = '';
        document.getElementById('wizardTitle').textContent    = 'Employee Registration Wizard';
        document.getElementById('wizardSubtitle').textContent = 'Complete all steps to register';
        document.getElementById('step2-register').style.display = 'block';
        document.getElementById('step2-edit').style.display     = 'none';
        const empStatusEl = document.getElementById('wizard-employment-status');
        const salaryEl    = document.getElementById('wizard-salary-grade');
        empStatusEl.setAttribute('readonly',''); empStatusEl.style.cssText = 'background:#f7f6ff;color:var(--theme-neutral-700);cursor:not-allowed;';
        salaryEl.setAttribute('readonly','');    salaryEl.style.cssText    = 'background:#f7f6ff;color:var(--theme-neutral-700);cursor:not-allowed;';
    }
}

window.goToWizardStep = function(step) {
    if (step < 1 || step > totalSteps) return;
    currentStep = step;
    updateWizardUI();
    // Landing on the Review step directly (via a header click) still needs
    // its summary populated, just like reaching it through Next.
    if (currentStep === totalSteps) {
        generateReview();
    }
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
    // 'inline-flex', not 'block': .wizard-btn lays its icon and label out as
    // a flex row, and an inline style here beats the class. Showing the
    // button as a block turned it back into normal flow, where Tailwind's
    // `svg { display: block }` put the tick on its own line above "Submit".
    const isEdit = !!window.wizardIsEditMode;
    const show = (id, visible) => {
        document.getElementById(id).style.display = visible ? 'inline-flex' : 'none';
    };

    show('prevBtn', currentStep > 1);
    show('nextBtn', currentStep < totalSteps);
    show('submitBtn', !isEdit && currentStep === totalSteps);
    show('updateBtn', isEdit && currentStep === totalSteps);
}

function validateCurrentStep() {
    if (currentStep === 2) {
        const rolesContainer = document.querySelector('#step2-register:not([style*="display: none"]) .wizard-role-checkboxes, #step2-edit:not([style*="display: none"]) .wizard-role-checkboxes');
        if (rolesContainer && !rolesContainer.querySelector('input[type="checkbox"]:checked')) {
            alert('Please select at least one role / access level.');
            return false;
        }
    }
    return true;
}

function escapeWizardHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
}

function formatWizardDate(value) {
    if (!value) return '';
    const d = new Date(value + 'T00:00:00');
    if (isNaN(d.getTime())) return value;
    return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

// Selects (department, designation) store an id as their value — the review
// should show what the admin actually picked, not the raw id.
function selectedOptionText(form, name) {
    const el = form.querySelector('select[name="' + name + '"]');
    if (!el || el.selectedIndex < 0) return '';
    const opt = el.options[el.selectedIndex];
    return opt ? opt.textContent.trim() : '';
}

const WIZARD_ROLE_LABELS = { employee: 'Employee', hr: 'HR', admin: 'Admin', mayor: 'Mayor' };

function selectedWizardRoles() {
    const container = document.querySelector('#step2-register:not([style*="display: none"]) .wizard-role-checkboxes, #step2-edit:not([style*="display: none"]) .wizard-role-checkboxes');
    if (!container) return [];
    return Array.from(container.querySelectorAll('input[type="checkbox"]:checked'))
        .map(function(cb) { return WIZARD_ROLE_LABELS[cb.value] || cb.value; });
}

function reviewRow(items) {
    const spanFull = items.length === 1;
    const cells = items.map(function(item) {
        const style = spanFull ? ' style="grid-column:1 / -1;"' : '';
        const value = item[1] ? escapeWizardHtml(item[1]) : '<span style="color:var(--gp-text-mid);font-weight:500;">N/A</span>';
        return '<div class="review-item"' + style + '><div class="review-label">' + escapeWizardHtml(item[0]) + '</div><div class="review-value">' + value + '</div></div>';
    }).join('');
    return '<div class="review-row">' + cells + '</div>';
}

function reviewSection(title, rows) {
    return '<div class="review-section"><div class="review-section-title">' + title + '</div>' + rows.join('') + '</div>';
}

function generateReview() {
    const form = document.getElementById('employeeWizardForm');
    const formData = new FormData(form);
    const isEdit = !!window.wizardIsEditMode;
    const get = function(name) { return (formData.get(name) || '').toString().trim(); };

    const fullName = [get('first_name'), get('middle_name'), get('last_name'), get('suffix')].filter(Boolean).join(' ');
    const photoFile = formData.get('photo');
    const roles = selectedWizardRoles().join(', ');

    let html = '';

    html += reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> Personal Information', [
        reviewRow([['Employee ID', get('employee_id')], ['Full Name', fullName]]),
        reviewRow([['Date of Birth', formatWizardDate(get('birth_date'))], ['Place of Birth', get('place_of_birth')]]),
        reviewRow([['Sex', get('sex')], ['Civil Status', get('civil_status')]]),
        reviewRow([['Height', get('height') ? get('height') + ' cm' : ''], ['Weight', get('weight') ? get('weight') + ' kg' : '']]),
        reviewRow([['Blood Type', get('blood_type')], ['Citizenship', get('citizenship')]]),
        reviewRow([['Photo', (photoFile && photoFile.name) ? photoFile.name : '']]),
    ]);

    html += isEdit
        ? reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Role / Access Level', [reviewRow([['Roles', roles]])])
        : reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Account Setup', [
            reviewRow([['Username', get('username')], ['Email', get('user_email')]]),
            reviewRow([['Password', get('password') ? 'Set' : ''], ['Roles', roles]]),
        ]);

    html += reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Employment Details', [
        reviewRow([['Department', selectedOptionText(form, 'department')], ['Position', selectedOptionText(form, 'designation_id')]]),
        reviewRow([['Employment Type/Status', get('employment_status')], ['Appointment Date', formatWizardDate(get('appointment_date'))]]),
        reviewRow([['Salary Grade', get('salary_grade')], ['Step Increment', get('step_increment')]]),
    ]);

    const addressLine = [get('house_no'), get('street'), get('barangay'), get('city'), get('province')].filter(Boolean).join(', ');
    const address = [addressLine, get('zip_code')].filter(Boolean).join(' ');
    html += reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg> Contact Information', [
        reviewRow([['Mobile Number', get('mobile_number')], ['Landline Number', get('landline_number')]]),
        reviewRow([['Emergency Contact Person', get('emergency_contact_person')], ['Emergency Contact Number', get('emergency_contact_number')]]),
        reviewRow([['Residential Address', address]]),
    ]);

    const govIdFileName = function(name) {
        const f = formData.get(name);
        return (f && f.name) ? f.name : '';
    };

    html += reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="12" r="2.2"/><line x1="14" y1="10" x2="19" y2="10"/><line x1="14" y1="14" x2="18" y2="14"/></svg> Government IDs', [
        reviewRow([['GSIS Number', get('gsis_no')], ['GSIS Scan', govIdFileName('gsis_file')]]),
        reviewRow([['PhilHealth Number', get('philhealth_no')], ['PhilHealth Scan', govIdFileName('philhealth_file')]]),
        reviewRow([['PAG-IBIG Number', get('pagibig_no')], ['PAG-IBIG Scan', govIdFileName('pagibig_file')]]),
        reviewRow([['TIN Number', get('tin_no')], ['TIN Scan', govIdFileName('tin_file')]]),
        reviewRow([['License Number', get('license_no')], ['License Scan', govIdFileName('license_file')]]),
    ]);

    const supportingFileName = function(name) {
        const f = formData.get(name);
        return (f && f.name) ? f.name : '';
    };

    html += reviewSection('<svg class="wizard-review-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg> Supporting Documents <span style="font-weight:400;font-size:11px;color:var(--gp-text-mid);">(PNG/JPG, 5 MB)</span>', [
        reviewRow([['CS Form 212 — PDS', supportingFileName('pds_file')], ['CS Form 33 — Appointment Form', supportingFileName('appointment_form_file')]]),
        reviewRow([['Position Description Form', supportingFileName('position_description_file')], ['Medical Certificate', supportingFileName('medical_certificate_file')]]),
        reviewRow([['NBI Clearance', supportingFileName('nbi_clearance_file')], ['Financial / Property Clearance', supportingFileName('financial_clearance_file')]]),
        reviewRow([['Neuro-psychiatric Examination', supportingFileName('neuro_exam_file')], ['Licenses', supportingFileName('supporting_licenses_file')]]),
        reviewRow([['Performance Evaluation', supportingFileName('performance_eval_file')], ['Commendation / Award', supportingFileName('commendation_file')]]),
        reviewRow([['Disciplinary / Action Docs', supportingFileName('disciplinary_file')], ['Other Records', supportingFileName('other_records_file')]]),
    ]);

    document.getElementById('wizardReviewContent').innerHTML = html;
}

// ── Government ID scan upload -> OCR auto-fill (Step 5) ──
// Best-effort only: a misread or an unsupported scan just leaves the number
// field for the admin to type by hand, which is why the request never blocks
// the wizard and every failure path still leaves the field editable.
function govIdStatusEl(idType) {
    return document.querySelector('.govid-ocr-status[data-status-for="' + idType + '"]');
}

function setGovIdStatus(idType, message, tone) {
    const el = govIdStatusEl(idType);
    if (!el) return;
    el.textContent = message;
    el.style.color = tone === 'error' ? 'var(--theme-danger)' : (tone === 'success' ? 'var(--theme-success)' : 'var(--gp-text-mid)');
}

function clearGovIdOcrStatuses() {
    document.querySelectorAll('.govid-ocr-status').forEach(function(el) {
        el.textContent = '';
        el.style.color = '';
    });
}

function wireGovIdOcrUploads() {
    document.querySelectorAll('.govid-file-input').forEach(function(input) {
        input.addEventListener('change', function() {
            const idType = input.dataset.idType;
            const targetField = document.querySelector('#employeeWizardForm [name="' + input.dataset.target + '"]');
            const file = input.files[0];
            if (!file || !idType) return;

            setGovIdStatus(idType, 'Reading ID scan…');

            const ocrData = new FormData();
            ocrData.append('file', file);
            ocrData.append('id_type', idType);
            const tokenField = document.querySelector('#employeeWizardForm [name="_token"]');

            fetch('/admin/personnel/government-ids/extract', {
                method: 'POST',
                headers: tokenField ? { 'X-CSRF-TOKEN': tokenField.value } : {},
                body: ocrData,
            })
                .then(function(r) { return r.json(); })
                .then(function(result) {
                    if (result.number && targetField) {
                        targetField.value = result.number;
                        setGovIdStatus(idType, result.message || 'Auto-filled from scan — please verify.', 'success');
                    } else {
                        setGovIdStatus(idType, result.message || 'Could not read the number automatically — please type it in.', 'error');
                    }
                })
                .catch(function() {
                    setGovIdStatus(idType, 'Could not read the scan automatically — please type the number in.', 'error');
                });
        });
    });
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    wireGovIdOcrUploads();
    // Keyboard access for the clickable header steps (Enter / Space).
    document.querySelectorAll('#progressBar .wizard-step').forEach(function(stepEl) {
        stepEl.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const step = parseInt(stepEl.getAttribute('data-step'), 10);
                if (step) goToWizardStep(step);
            }
        });
    });

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

    // Autosave the draft as the admin types, so the wizard can be restored
    // even if the tab is closed outright (not just via the Cancel/X button).
    if (form) {
        let draftSaveTimer = null;
        ['input', 'change'].forEach(function(evt) {
            form.addEventListener(evt, function() {
                clearTimeout(draftSaveTimer);
                draftSaveTimer = setTimeout(saveWizardDraft, 400);
            });
        });
    }

    // Username auto-fill: keep it in sync with Last Name + First Name until
    // the admin edits it directly, at which point their choice takes over.
    const firstNameField = document.querySelector('#employeeWizardForm [name="first_name"]');
    const lastNameField  = document.querySelector('#employeeWizardForm [name="last_name"]');
    const usernameField  = document.querySelector('#employeeWizardForm [name="username"]');
    [firstNameField, lastNameField].forEach(function(field) {
        if (field) field.addEventListener('input', autoFillWizardUsername);
    });
    if (usernameField) {
        usernameField.addEventListener('input', function() {
            if (!wizardUsernameAutoFilling) usernameField.dataset.userEdited = 'true';
        });
    }
});
