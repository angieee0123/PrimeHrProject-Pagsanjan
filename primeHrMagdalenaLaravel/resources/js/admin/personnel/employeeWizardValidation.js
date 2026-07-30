// Real-time field validation for the Employee Registration Wizard.
//
// As the admin types, each field below is checked against its rule on every
// keystroke. While invalid, the error message replaces the text of that
// field's .wizard-hint (the instruction line already sitting under the
// input); once the value becomes valid again, whatever text was there right
// before the error is restored. Fields with no existing hint just get an
// empty one created on demand, so the error still has somewhere to appear.
//
// Department / Position are intentionally NOT covered here — their hint
// (#wizard-position-hint) is already rewritten live by loadDesignations()/
// fillFromDesignation() in employeeWizardComplete.js, and layering a second,
// static error message on top of that dynamic one would fight it.
const WIZARD_VALIDATORS = {
    employee_id: (value) => {
        if (!value.trim()) return 'Employee ID is required.';
        if (!/^[A-Za-z0-9-]+$/.test(value.trim())) return 'Use letters, numbers, and dashes only — no spaces.';
        return null;
    },
    first_name: (value) => {
        if (!value.trim()) return 'First name is required.';
        if (!/^[A-Za-zÀ-ſ' -]+$/.test(value.trim())) return 'Only letters, spaces, hyphens, and apostrophes are allowed.';
        return null;
    },
    last_name: (value) => {
        if (!value.trim()) return 'Last name is required.';
        if (!/^[A-Za-zÀ-ſ' -]+$/.test(value.trim())) return 'Only letters, spaces, hyphens, and apostrophes are allowed.';
        return null;
    },
    birth_date: (value) => {
        if (!value) return 'Date of birth is required.';
        const dob = new Date(value + 'T00:00:00');
        if (isNaN(dob.getTime())) return 'Enter a valid date.';
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        if (dob > today) return 'Date of birth cannot be in the future.';
        const ageYears = (today - dob) / (1000 * 60 * 60 * 24 * 365.25);
        if (ageYears < 18) return 'Employee must be at least 18 years old.';
        return null;
    },
    sex: (value) => (value ? null : 'Please select a sex.'),
    civil_status: (value) => (value ? null : 'Please select a civil status.'),
    height: (value) => {
        if (!value.trim()) return null; // optional
        const n = parseFloat(value);
        if (isNaN(n) || n <= 0) return 'Enter a valid height in centimeters.';
        return null;
    },
    weight: (value) => {
        if (!value.trim()) return null; // optional
        const n = parseFloat(value);
        if (isNaN(n) || n <= 0) return 'Enter a valid weight in kilograms.';
        return null;
    },
    username: (value) => {
        if (!value.trim()) return 'Username is required.';
        if (/\s/.test(value)) return 'Username cannot contain spaces.';
        if (!/^[a-z0-9._]+$/.test(value)) return 'Use lowercase letters, numbers, dots, and underscores only.';
        return null;
    },
    user_email: (value) => {
        if (!value.trim()) return 'Email is required.';
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value.trim())) return 'Enter a valid email address.';
        return null;
    },
    password: (value) => {
        if (!value) return 'Password is required.';
        if (value.length < 8) return 'Password must be at least 8 characters long.';
        if (!/[A-Z]/.test(value)) return 'Password must contain an uppercase letter.';
        if (!/[a-z]/.test(value)) return 'Password must contain a lowercase letter.';
        if (!/[0-9]/.test(value)) return 'Password must contain a number.';
        if (!/[!@#$%^&*]/.test(value)) return 'Password must contain a special character (!@#$%^&*).';
        return null;
    },
    password_confirm: (value, modal) => {
        const pw = modal.querySelector('[name="password"]')?.value || '';
        if (!value) return 'Please confirm your password.';
        if (value !== pw) return 'Passwords do not match.';
        return null;
    },
    appointment_date: (value) => (value ? null : 'Appointment date is required.'),
    mobile_number: (value) => {
        if (!value.trim()) return null; // optional
        if (!/^[0-9+()\-\s]{7,20}$/.test(value.trim())) return 'Enter a valid phone number.';
        return null;
    },
    landline_number: (value) => {
        if (!value.trim()) return null; // optional
        if (!/^[0-9+()\-\s]{7,20}$/.test(value.trim())) return 'Enter a valid phone number.';
        return null;
    },
    emergency_contact_number: (value) => {
        if (!value.trim()) return null; // optional
        if (!/^[0-9+()\-\s]{7,20}$/.test(value.trim())) return 'Enter a valid phone number.';
        return null;
    },
    zip_code: (value) => {
        if (!value.trim()) return null; // optional
        if (!/^[0-9]{4}$/.test(value.trim())) return 'Zip code must be 4 digits.';
        return null;
    },
};

// Normally the .wizard-hint lives as a direct sibling of the field. Password
// / Confirm Password are the exception — they're wrapped in .wizard-pw-wrap
// (for the show/hide toggle button), so the hint actually sits one level up,
// as a sibling of that wrapper instead of the input itself.
function getFieldWrapper(field) {
    const pwWrap = field.closest('.wizard-pw-wrap');
    return pwWrap ? pwWrap.parentElement : field.parentElement;
}

function findHint(field) {
    const wrapper = getFieldWrapper(field);
    return Array.from(wrapper.children).find(el => el.classList.contains('wizard-hint')) || null;
}

function getOrCreateHint(field) {
    let hint = findHint(field);
    if (!hint) {
        hint = document.createElement('p');
        hint.className = 'wizard-hint';
        getFieldWrapper(field).appendChild(hint);
    }
    return hint;
}

function setFieldError(field, message) {
    const hint = getOrCreateHint(field);
    const isSelect = field.tagName === 'SELECT';
    const invalidClass = isSelect ? 'wizard-select-invalid' : 'wizard-input-invalid';
    const validClass = isSelect ? 'wizard-select-valid' : 'wizard-input-valid';

    if (message) {
        if (!hint.classList.contains('wizard-hint-error')) {
            // First time going invalid — remember what was here so it can
            // come back once the field is corrected.
            hint.dataset.preErrorText = hint.textContent;
        }
        hint.textContent = message;
        hint.classList.remove('wizard-hint-valid');
        hint.classList.add('wizard-hint-error');
        field.classList.remove(validClass);
        field.classList.add(invalidClass);
        return;
    }

    if (hint.classList.contains('wizard-hint-error')) {
        hint.textContent = hint.dataset.preErrorText || '';
        hint.classList.remove('wizard-hint-error');
    }
    field.classList.remove(invalidClass);

    // Green only once there's actually something correct to confirm — an
    // empty, untouched optional field isn't "correct", it's just blank.
    if (field.value.trim() !== '') {
        hint.classList.add('wizard-hint-valid');
        field.classList.add(validClass);
    } else {
        hint.classList.remove('wizard-hint-valid');
        field.classList.remove(validClass);
    }
}

function wireField(field, modal) {
    const validator = WIZARD_VALIDATORS[field.name];
    if (!validator) return;
    const run = () => setFieldError(field, validator(field.value, modal));
    field.addEventListener('input', run);
    if (field.tagName === 'SELECT') field.addEventListener('change', run);
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('employeeWizardModal');
    if (!modal) return;

    Object.keys(WIZARD_VALIDATORS).forEach((name) => {
        modal.querySelectorAll(`[name="${name}"]`).forEach((field) => wireField(field, modal));
    });

    // Confirm Password depends on Password's current value, so re-check it
    // live whenever Password itself changes (not just when Confirm is typed in).
    const pwField = modal.querySelector('[name="password"]');
    const pwConfirmField = modal.querySelector('[name="password_confirm"]');
    if (pwField && pwConfirmField) {
        pwField.addEventListener('input', () => {
            if (pwConfirmField.value) {
                setFieldError(pwConfirmField, WIZARD_VALIDATORS.password_confirm(pwConfirmField.value, modal));
            }
        });
    }
});

// Clears every field's error/valid state back to its pre-error hint text —
// called when the wizard opens fresh or closes, so stale red/green states
// from a previous session never linger into the next one.
window.resetWizardFieldValidation = function () {
    const modal = document.getElementById('employeeWizardModal');
    if (!modal) return;
    modal.querySelectorAll('.wizard-hint-error').forEach((hint) => {
        hint.textContent = hint.dataset.preErrorText || '';
        hint.classList.remove('wizard-hint-error');
    });
    modal.querySelectorAll('.wizard-hint-valid').forEach((hint) => {
        hint.classList.remove('wizard-hint-valid');
    });
    modal.querySelectorAll('.wizard-input-invalid, .wizard-select-invalid, .wizard-input-valid, .wizard-select-valid').forEach((field) => {
        field.classList.remove('wizard-input-invalid', 'wizard-select-invalid', 'wizard-input-valid', 'wizard-select-valid');
    });
};
