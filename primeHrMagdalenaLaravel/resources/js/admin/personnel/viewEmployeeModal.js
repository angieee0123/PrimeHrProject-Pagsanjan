// View Employee ("Employee Details") modal — shared by the personnel page and
// the admin dashboard. Loaded by the viewEmployeeModal blade partial itself
// (moved out of adminPersonnel.js, which is personnel-page-scoped).

function viewEmployee(employeeId) {
    document.getElementById('viewEmployeeModal').style.display = 'flex';
    document.getElementById('viewEmployeeContent').innerHTML = '<p style="text-align:center; color:var(--theme-neutral-700);">Loading...</p>';

    fetch(`/admin/personnel/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewEmployeeId').textContent = data.employee_id;
            document.getElementById('viewEmployeeContent').innerHTML = generateEmployeeView(data);
        })
        .catch(error => {
            document.getElementById('viewEmployeeContent').innerHTML = '<p style="text-align:center; color:var(--theme-danger);">Error loading employee details.</p>';
        });
}

function closeViewModal() {
    document.getElementById('viewEmployeeModal').style.display = 'none';
}

// Close on backdrop click and Escape (the dashboard doesn't load the personnel
// page's generic modal-close handlers, so the modal handles its own).
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('viewEmployeeModal');
    if (!modal) return;

    modal.addEventListener('click', function (e) {
        if (e.target === this) closeViewModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && window.getComputedStyle(modal).display === 'flex') {
            closeViewModal();
        }
    });
});

function escapeViewAttr(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
    });
}

// Government ID paths embed the admin's original uploaded filename, so the
// href is escaped before going into the template literal below.
function govIdScanLink(path) {
    if (!path) return '';
    return ` <a href="${escapeViewAttr(path)}" target="_blank" rel="noopener" style="font-size:11px;color:var(--gp-pri-2);font-weight:600;margin-left:8px;">View Scan</a>`;
}

// 201-file supporting documents (EmployeeSupportingDocument). Mirrors the
// GROUPS vocabulary on the model: column => on-screen label, in three groups.
const SUPPORTING_DOC_GROUPS = [
    {
        title: 'Appointment & Personnel Forms',
        items: [
            ['pds_file_path', 'CS Form 212 — Personal Data Sheet'],
            ['appointment_form_file_path', 'CS Form 33 — Appointment Form'],
            ['position_description_file_path', 'Position Description Form'],
        ],
    },
    {
        title: 'Clearances & Examinations',
        items: [
            ['medical_certificate_file_path', 'Medical Certificate'],
            ['nbi_clearance_file_path', 'NBI Clearance'],
            ['financial_clearance_file_path', 'Financial & Property Clearance'],
            ['neuro_exam_file_path', 'Neuro-psychiatric Examination'],
        ],
    },
    {
        title: 'Service Record & Credentials',
        items: [
            ['licenses_file_path', 'Professional Licenses'],
            ['performance_eval_file_path', 'Performance Evaluation Documents'],
            ['commendation_file_path', 'Commendations & Awards'],
            ['disciplinary_file_path', 'Disciplinary & Action Documents'],
            ['other_records_file_path', 'Other Employee Records'],
        ],
    },
];

function supportingDocRow(label, path) {
    if (path) {
        return `<div class="view-supporting-doc"><span class="view-supporting-doc-label">${label}</span><a class="view-supporting-doc-link" href="${escapeViewAttr(path)}" target="_blank" rel="noopener">View File</a></div>`;
    }
    return `<div class="view-supporting-doc view-supporting-doc-missing"><span class="view-supporting-doc-label">${label}</span><span class="view-supporting-doc-empty">Not uploaded</span></div>`;
}

function supportingDocsSection(data) {
    const docs = data.supportingDocuments || data.supporting_documents || null;
    if (!docs) {
        return `
        <div style="margin-top:24px;">
            <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">📁 201 File — Supporting Documents</h4>
            <p class="view-supporting-docs-none">No supporting documents on file for this employee.</p>
        </div>`;
    }
    const groups = SUPPORTING_DOC_GROUPS.map(group => `
        <div class="view-supporting-doc-group">
            <p class="view-supporting-doc-group-title">${group.title}</p>
            ${group.items.map(([column, label]) => supportingDocRow(label, docs[column])).join('')}
        </div>`).join('');
    return `
        <div style="margin-top:24px;">
            <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">📁 201 File — Supporting Documents</h4>
            ${groups}
        </div>`;
}

function generateEmployeeView(data) {
    return `
        <div style="margin-bottom:24px;">
            ${data.photo ? `<div style="margin-bottom:24px;"><img src="${data.photo}" alt="${data.first_name} ${data.last_name}" style="width:100%; max-width:300px; height:auto; border-radius:12px; border:3px solid #e8e7f5; object-fit:cover; display:block; margin:0 auto;"></div>` : ''}
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">👤 Personal Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Full Name</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.first_name} ${data.middle_name || ''} ${data.last_name} ${data.suffix || ''}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Date of Birth</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.birth_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Place of Birth</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.place_of_birth || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Sex</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.sex || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Civil Status</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.civil_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Citizenship</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.citizenship || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Blood Type</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.blood_type || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">💼 Employment Details</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Email</span><span class="view-employment-email" style="font-size:13px; font-weight:600; color:var(--gp-pri);">${escapeViewAttr(data.email) || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Designation</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.employment_detail?.designation_relation?.title || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Department</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.employment_detail?.department_relation?.name || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Employment Status</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.employment_detail?.employment_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Appointment Date</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.employment_detail?.appointment_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Salary Grade</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.employment_detail?.salary_grade || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Step Increment</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.employment_detail?.step_increment || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">📞 Contact Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Email</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.email || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Mobile Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.contacts?.[0]?.mobile_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Landline</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.contacts?.[0]?.landline_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Emergency Contact</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.contacts?.[0]?.emergency_contact_person || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Emergency Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.contacts?.[0]?.emergency_contact_number || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">🪪 Government IDs</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">GSIS Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.government_ids?.[0]?.gsis_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.gsis_file_path)}</div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">PhilHealth Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.government_ids?.[0]?.philhealth_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.philhealth_file_path)}</div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">PAG-IBIG Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.government_ids?.[0]?.pagibig_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.pagibig_file_path)}</div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">TIN Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.government_ids?.[0]?.tin_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.tin_file_path)}</div>
                    <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">License Number</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.government_ids?.[0]?.license_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.license_file_path)}</div>
                </div>
            </div>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:700; color:var(--gp-pri); margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid var(--theme-primary-light);">📍 Address</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">House No.</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.addresses?.[0]?.house_no || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Street</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.addresses?.[0]?.street || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Barangay</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.addresses?.[0]?.barangay || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">City/Municipality</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.addresses?.[0]?.city || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Province</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.addresses?.[0]?.province || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:var(--gp-text-soft); display:block; margin-bottom:4px;">Zip Code</span><span style="font-size:13px; font-weight:600; color:var(--gp-pri);">${data.addresses?.[0]?.zip_code || 'N/A'}</span></div>
            </div>
        </div>
        ${supportingDocsSection(data)}
    `;
}

window.viewEmployee = viewEmployee;
window.closeViewModal = closeViewModal;
