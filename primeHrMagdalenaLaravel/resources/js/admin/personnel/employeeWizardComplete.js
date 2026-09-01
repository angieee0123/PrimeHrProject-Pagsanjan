// Employee Wizard - Edit Mode (populate wizard from an existing employee record)
window.wizardIsEditMode = false;

function editEmployee(id) {
    window.wizardIsEditMode = true;
    const form = document.getElementById('employeeWizardForm');
    form.action = `/admin/personnel/${id}/update`;
    document.getElementById('wizardEditId').value    = id;
    document.getElementById('wizardTitle').textContent    = 'Edit Employee';
    document.getElementById('wizardSubtitle').textContent = 'Update employee information';
    document.getElementById('step2-register').style.display = 'none';
    document.getElementById('step2-edit').style.display     = 'block';
    document.getElementById('submitBtn').style.display = 'none';
    document.getElementById('updateBtn').style.display = 'none';

    // Reset to step 1 and open
    if (typeof goToWizardStep === 'function') goToWizardStep(1);
    document.getElementById('employeeWizardModal').style.display = 'flex';
    if (window.resetWizardFieldValidation) window.resetWizardFieldValidation();
    if (window.resetWizardPasswordVisibility) window.resetWizardPasswordVisibility();

    fetch(`/admin/personnel/${id}/edit`)
        .then(r => r.json())
        .then(d => {
            // Step 1 — Personal
            setVal('employee_id', d.employee_id);
            setVal('first_name',  d.first_name);
            setVal('middle_name', d.middle_name);
            setVal('last_name',   d.last_name);
            setVal('suffix',      d.suffix);
            setVal('birth_date',  d.birth_date);
            setVal('place_of_birth', d.place_of_birth);
            setVal('sex',         d.sex);
            setVal('civil_status',d.civil_status);
            setVal('height',      d.height);
            setVal('weight',      d.weight);
            setVal('blood_type',  d.blood_type);
            setVal('citizenship', d.citizenship);

            // Step 3 — Employment
            const emp = d.employment_detail || {};
            const deptSelect = document.getElementById('wizard-department');
            deptSelect.value = emp.department_id || '';
            // Load designations then set position
            if (emp.department_id) {
                fetch(`/admin/departments/${emp.department_id}/designations`)
                    .then(r => r.json())
                    .then(desigs => {
                        const pos = document.getElementById('wizard-position');
                        pos.innerHTML = '<option value="">Select Position</option>';
                        desigs.forEach(dg => {
                            const opt = document.createElement('option');
                            opt.value = dg.id;
                            opt.textContent = dg.title;
                            opt.dataset.employmentType = dg.employment_type || '';
                            opt.dataset.salaryGrade    = dg.salary_grade    || '';
                            pos.appendChild(opt);
                        });
                        pos.disabled = false;
                        pos.value = emp.designation_id || '';
                        fillFromDesignation(pos);
                    });
            }
            const empStatusEl = document.getElementById('wizard-employment-status');
            const salaryEl    = document.getElementById('wizard-salary-grade');
            empStatusEl.removeAttribute('readonly'); empStatusEl.style.cssText = '';
            salaryEl.removeAttribute('readonly');    salaryEl.style.cssText = '';
            setVal('employment_status', emp.employment_status);
            setVal('appointment_date',  emp.appointment_date);
            setVal('salary_grade',      emp.salary_grade);
            setVal('step_increment',    emp.step_increment);

            // Step 4 — Contact
            const contactsByType = {};
            if (d.contacts && Array.isArray(d.contacts)) {
                d.contacts.forEach(c => {
                    contactsByType[c.type] = c;
                });
            }
            const mobile    = contactsByType.mobile;
            const landline  = contactsByType.landline;
            const emergency = contactsByType.emergency;
            setVal('mobile_number',             mobile?.mobile_number || mobile?.number);
            setVal('landline_number',           landline?.landline_number || landline?.number);
            setVal('emergency_contact_person',  emergency?.emergency_contact_person || emergency?.contact_person);
            setVal('emergency_contact_number',  emergency?.emergency_contact_number || emergency?.number);
            const addr = (d.addresses || [])[0] || {};
            setVal('house_no',  addr.house_no);
            setVal('street',    addr.street);
            setVal('barangay',  addr.barangay);
            setVal('city',      addr.city);
            setVal('province',  addr.province);
            setVal('zip_code',  addr.zip_code);

            // Step 2 — Roles
            const editRolesEl = document.getElementById('edit-roles');
            if (editRolesEl) {
                const selectedRoles = Array.isArray(d.roles) ? d.roles : [];
                editRolesEl.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.checked = selectedRoles.includes(cb.value);
                });
            }

            // Steps 5 and 6 are attachment cards. Clear whatever the previous
            // employee left on them before painting this one's, or a card the
            // last record filled still reads as attached.
            if (window.resetWizardDocumentCards) window.resetWizardDocumentCards();

            // Step 5 — Gov IDs
            const gov = (d.government_ids || [])[0] || {};
            Object.keys(GOV_ID_COLUMNS).forEach(function(key) {
                setVal(key + '_no', gov[key + '_no']);
                showGovIdCurrentFile(key, gov[GOV_ID_COLUMNS[key]]);
            });

            // Step 6 — Supporting Documents (the 201 file)
            const sd = d.supporting_documents || d.supportingDocuments || null;
            const sdObj = Array.isArray(sd) ? (sd[0] || {}) : (sd || {});
            Object.keys(SUPPORTING_DOC_COLUMNS).forEach(function(key) {
                showSupportingCurrentFile(key, sdObj[SUPPORTING_DOC_COLUMNS[key]]);
            });
        });
}

// Card key -> the column holding its stored path. These mirror
// GovernmentId::columnMap() and EmployeeSupportingDocument::columnMap() on the
// server, which key the same records by input name instead.
const GOV_ID_COLUMNS = {
    gsis: 'gsis_file_path',
    philhealth: 'philhealth_file_path',
    pagibig: 'pagibig_file_path',
    tin: 'tin_file_path',
    license: 'license_file_path',
};

// Card key -> the column holding its stored path. This mirrors
// EmployeeSupportingDocument::columnMap() on the server, which keys the same
// twelve documents by input name instead.
const SUPPORTING_DOC_COLUMNS = {
    pds: 'pds_file_path',
    appointment_form: 'appointment_form_file_path',
    position_description: 'position_description_file_path',
    medical_certificate: 'medical_certificate_file_path',
    nbi_clearance: 'nbi_clearance_file_path',
    financial_clearance: 'financial_clearance_file_path',
    neuro_exam: 'neuro_exam_file_path',
    supporting_licenses: 'licenses_file_path',
    performance_eval: 'performance_eval_file_path',
    commendation: 'commendation_file_path',
    disciplinary: 'disciplinary_file_path',
    other_records: 'other_records_file_path',
};

function setVal(name, value) {
    const el = document.querySelector(`[name="${name}"]`);
    if (el) el.value = value || '';
}

// A file input can't be pre-populated with an existing upload (browsers block
// it), so edit mode shows what is already on the server as a strip inside the
// card instead. The card is marked `doc-card-onfile` too: without it, a set of
// empty-looking cards is the same screen whether the records are complete or
// untouched.
//
// Built with DOM methods rather than innerHTML — the path embeds the admin's
// original filename verbatim.
function showCurrentFile(containerClass, key, path, linkText) {
    const el = document.querySelector('.' + containerClass + '[data-current-for="' + key + '"]');
    if (!el) return;
    const card = el.closest('.doc-card');
    el.innerHTML = '';
    if (!path) {
        el.style.display = 'none';
        if (card) card.classList.remove('doc-card-onfile');
        return;
    }
    el.style.display = 'flex';
    if (card) card.classList.add('doc-card-onfile');

    const label = document.createElement('span');
    label.className = 'doc-onfile-label';
    label.textContent = 'On file';
    el.appendChild(label);

    const a = document.createElement('a');
    a.href = path;
    a.target = '_blank';
    a.rel = 'noopener';
    a.className = 'doc-onfile-link';
    a.textContent = linkText;
    el.appendChild(a);

    const note = document.createElement('span');
    note.className = 'doc-onfile-note';
    note.textContent = 'Choose a file to replace it';
    el.appendChild(note);
}

function showGovIdCurrentFile(idType, path) {
    showCurrentFile('wizard-govid-current', idType, path, 'View current scan');
}

function showSupportingCurrentFile(docKey, path) {
    showCurrentFile('wizard-supporting-current', docKey, path, 'View current document');
}
window.showSupportingCurrentFile = showSupportingCurrentFile;

function clearGovIdCurrentFiles() {
    document.querySelectorAll('.wizard-govid-current, .wizard-supporting-current').forEach(function(el) {
        el.innerHTML = '';
        el.style.display = 'none';
        const card = el.closest('.doc-card');
        if (card) card.classList.remove('doc-card-onfile');
    });
}
window.clearGovIdCurrentFiles = clearGovIdCurrentFiles;

function submitWizardUpdate() {
    if (window.blockedByAttachmentSize && window.blockedByAttachmentSize()) return;
    document.getElementById('employeeWizardForm').submit();
}

function loadDesignations(deptId, onLoaded) {
    const posSelect  = document.getElementById('wizard-position');
    const statusInput = document.getElementById('wizard-employment-status');
    const gradeInput  = document.getElementById('wizard-salary-grade');
    const hint        = document.getElementById('wizard-position-hint');

    posSelect.innerHTML = '<option value="">Loading...</option>';
    posSelect.disabled  = true;
    statusInput.value   = '';
    gradeInput.value    = '';

    if (!deptId) {
        posSelect.innerHTML = '<option value="">— Select a department first —</option>';
        hint.textContent    = 'Select a department to load available designations.';
        if (typeof onLoaded === 'function') onLoaded();
        return;
    }

    fetch(`/admin/departments/${deptId}/designations`)
        .then(r => r.json())
        .then(data => {
            if (!data.length) {
                posSelect.innerHTML = '<option value="">No designations found for this department</option>';
                hint.textContent    = 'No designations are set up for this department yet.';
                if (typeof onLoaded === 'function') onLoaded();
                return;
            }
            posSelect.innerHTML = '<option value="">Select Position</option>';
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value                   = d.id;
                opt.textContent             = d.title;
                opt.dataset.employmentType  = d.employment_type || '';
                opt.dataset.salaryGrade     = d.salary_grade    || '';
                posSelect.appendChild(opt);
            });
            posSelect.disabled   = false;
            hint.textContent     = `${data.length} designation(s) available. Select one to auto-fill employment details.`;
            if (typeof onLoaded === 'function') onLoaded();
        })
        .catch(() => {
            posSelect.innerHTML = '<option value="">Error loading designations</option>';
            hint.textContent    = 'Could not load designations. Please try again.';
            if (typeof onLoaded === 'function') onLoaded();
        });
}

function fillFromDesignation(select) {
    const opt = select.options[select.selectedIndex];
    document.getElementById('wizard-employment-status').value = opt.dataset.employmentType || '';
    document.getElementById('wizard-salary-grade').value      = opt.dataset.salaryGrade    || '';
}

window.editEmployee = editEmployee;
window.submitWizardUpdate = submitWizardUpdate;
window.loadDesignations = loadDesignations;
window.fillFromDesignation = fillFromDesignation;
