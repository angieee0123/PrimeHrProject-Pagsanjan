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
    // No `employee_id` rule: the wizard has no field for it any more. The
    // number is assigned by Employee::generateEmployeeId() on save, so there is
    // nothing here for the admin to mistype.
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

// The .wizard-hint lives as a direct sibling of the field it describes, which
// is where setFieldError() writes the message. (The Password fields that were
// the one exception — wrapped in .wizard-pw-wrap for their show/hide button —
// are gone: the server generates the password now, so there is no field.)
function getFieldWrapper(field) {
    return field.parentElement;
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

// ── Username / email availability ────────────────────────────────────────
// Both account fields must be unique in `users`, and the only way the admin
// used to learn otherwise was a failed submit six steps later. Each is asked
// about on blur instead, against the same table the `unique:` rule checks, and
// the answer is kept on the element so Next can refuse a value already known to
// be taken without another round trip.
//
// The endpoint changes how the conflict is *reported*, never whether it is
// enforced: the submit-time validation is still the authority, and the check
// writes nothing.
const AVAILABILITY_TIMERS = new WeakMap();   // field -> pending debounce id
const AVAILABILITY_INFLIGHT = new WeakMap(); // field -> promise for the last request

function clearAvailability(field) {
    if (AVAILABILITY_TIMERS.has(field)) {
        clearTimeout(AVAILABILITY_TIMERS.get(field));
        AVAILABILITY_TIMERS.delete(field);
    }
    AVAILABILITY_INFLIGHT.delete(field);
    delete field.dataset.availabilityError;
    delete field.dataset.availabilityChecked;
}

// Asks the server whether this value is already taken, and shows its answer
// under the field. Returns a promise that always resolves — a failed request
// must leave the form usable, with the submit-time rule still guarding it.
function requestAvailability(field) {
    const url = field.form && field.form.dataset.checkAvailabilityUrl;
    if (!url) return Promise.resolve();

    // Checked at request time, not read later: the admin may keep typing while
    // this is in flight, and a late answer about an older value is not an
    // answer about what is on the screen.
    const value = field.value.trim();

    const request = fetch(`${url}?field=${encodeURIComponent(field.name)}&value=${encodeURIComponent(value)}`, {
        headers: { 'Accept': 'application/json' },
    })
        .then((response) => (response.ok ? response.json() : null))
        .then((data) => {
            if (!data || field.value.trim() !== value) return;
            field.dataset.availabilityChecked = value;
            if (data.available) {
                delete field.dataset.availabilityError;
                setFieldError(field, null);
                return;
            }
            field.dataset.availabilityError = data.message || 'That value is already in use.';
            setFieldError(field, field.dataset.availabilityError);
        })
        .catch(() => {
            // Offline / server error: no claim either way. Clearing the error
            // rather than leaving it would be wrong, and so would inventing
            // one, so the field is simply left as the local rule found it.
        });

    AVAILABILITY_INFLIGHT.set(field, request);
    return request;
}

function checkWizardFieldAvailable(field) {
    clearAvailability(field);

    // The field's own rule runs first: "Email is required." is the useful
    // message for an empty box, and there is nothing to ask the server about.
    const validator = WIZARD_VALIDATORS[field.name];
    if (!validator || validator(field.value) !== null) return Promise.resolve();
    if (!field.value.trim()) return Promise.resolve();

    // Short debounce so tabbing straight through the field on the way to Role
    // doesn't fire a request for a value the admin has not settled on.
    return new Promise((resolve) => {
        const timer = setTimeout(() => {
            AVAILABILITY_TIMERS.delete(field);
            requestAvailability(field).then(resolve);
        }, 250);
        AVAILABILITY_TIMERS.set(field, timer);
    });
}
window.checkWizardFieldAvailable = checkWizardFieldAvailable;

function wireField(field, modal) {
    const validator = WIZARD_VALIDATORS[field.name];
    if (!validator) return;
    const run = () => {
        // A verdict from the server is about the value that was sent; once the
        // admin edits it, only the local rule may speak until the next check.
        delete field.dataset.availabilityError;
        AVAILABILITY_INFLIGHT.delete(field);
        setFieldError(field, validator(field.value, modal));
    };
    field.addEventListener('input', run);
    if (field.tagName === 'SELECT') field.addEventListener('change', run);
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('employeeWizardModal');
    if (!modal) return;

    Object.keys(WIZARD_VALIDATORS).forEach((name) => {
        modal.querySelectorAll(`[name="${name}"]`).forEach((field) => wireField(field, modal));
    });
});

// Lets the wizard wait for the check before it decides about the step: the
// admin can click Next in the same moment they leave the field, before the
// request has answered, and walking past a conflict the server is about to
// report is the exact round trip this check exists to remove.
window.awaitWizardFieldAvailability = function () {
    const modal = document.getElementById('employeeWizardModal');
    if (!modal) return Promise.resolve();
    const inFlight = [];
    ['username', 'user_email'].forEach((name) => {
        modal.querySelectorAll(`[name="${name}"]`).forEach((field) => {
            const promise = AVAILABILITY_INFLIGHT.get(field);
            if (promise) inFlight.push(promise);
        });
    });
    return Promise.all(inFlight);
};

// Clears every field's error/valid state back to its pre-error hint text —
// called when the wizard opens fresh or closes, so stale red/green states
// from a previous session never linger into the next one. Any availability
// verdict goes with them: it belongs to the value that was on screen at the
// time, and the next record opened in this modal is somebody else.
window.resetWizardFieldValidation = function () {
    const modal = document.getElementById('employeeWizardModal');
    if (!modal) return;
    ['username', 'user_email'].forEach((name) => {
        modal.querySelectorAll(`[name="${name}"]`).forEach(clearAvailability);
    });
    modal.querySelectorAll('.wizard-hint-error').forEach((hint) => {
        hint.textContent = hint.dataset.preErrorText || '';
        hint.classList.remove('wizard-hint-error');
        delete hint.dataset.preErrorText;
    });
    modal.querySelectorAll('.wizard-hint-valid').forEach((hint) => {
        hint.classList.remove('wizard-hint-valid');
    });
    modal.querySelectorAll('.wizard-input-invalid, .wizard-select-invalid, .wizard-input-valid, .wizard-select-valid').forEach((field) => {
        field.classList.remove('wizard-input-invalid', 'wizard-select-invalid', 'wizard-input-valid', 'wizard-select-valid');
    });
};
