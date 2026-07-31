// View Employee ("Employee Details") modal — shared by the personnel page and
// the admin dashboard. Loaded by the viewEmployeeModal blade partial itself
// (moved out of adminPersonnel.js, which is personnel-page-scoped).

function viewEmployee(employeeId) {
    document.getElementById('viewEmployeeModal').style.display = 'flex';
    document.getElementById('viewEmployeeContent').innerHTML = '<p style="text-align:center; color:#6b6a8a;">Loading...</p>';

    fetch(`/admin/personnel/${employeeId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewEmployeeId').textContent = data.employee_id;
            document.getElementById('viewEmployeeContent').innerHTML = generateEmployeeView(data);
        })
        .catch(error => {
            document.getElementById('viewEmployeeContent').innerHTML = '<p style="text-align:center; color:#8e1e18;">Error loading employee details.</p>';
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
    return ` <a href="${escapeViewAttr(path)}" target="_blank" rel="noopener" style="font-size:11px;color:#150c63;font-weight:600;margin-left:8px;">View Scan</a>`;
}

function generateEmployeeView(data) {
    return `
        <div style="margin-bottom:24px;">
            ${data.photo ? `<div style="margin-bottom:24px;"><img src="${data.photo}" alt="${data.first_name} ${data.last_name}" style="width:100%; max-width:300px; height:auto; border-radius:12px; border:3px solid #e8e7f5; object-fit:cover; display:block; margin:0 auto;"></div>` : ''}
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">👤 Personal Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Full Name</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.first_name} ${data.middle_name || ''} ${data.last_name} ${data.suffix || ''}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Date of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.birth_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Place of Birth</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.place_of_birth || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Sex</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.sex || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Civil Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.civil_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Citizenship</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.citizenship || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Blood Type</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.blood_type || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">💼 Employment Details</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Designation</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.designation_relation?.title || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Department</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.department_relation?.name || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Employment Status</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.employment_status || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Appointment Date</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.appointment_date || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Salary Grade</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.salary_grade || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Step Increment</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.employment_detail?.step_increment || 'N/A'}</span></div>
                </div>
            </div>
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:24px;">
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">📞 Contact Information</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Email</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.email || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Mobile Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.mobile_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Landline</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.landline_number || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Emergency Contact</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_person || 'N/A'}</span></div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Emergency Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.contacts?.[0]?.emergency_contact_number || 'N/A'}</span></div>
                </div>
            </div>
            <div>
                <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">🪪 Government IDs</h4>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">GSIS Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.gsis_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.gsis_file_path)}</div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">PhilHealth Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.philhealth_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.philhealth_file_path)}</div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">PAG-IBIG Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.pagibig_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.pagibig_file_path)}</div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">TIN Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.tin_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.tin_file_path)}</div>
                    <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">License Number</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.government_ids?.[0]?.license_no || 'N/A'}</span>${govIdScanLink(data.government_ids?.[0]?.license_file_path)}</div>
                </div>
            </div>
        </div>
        <div>
            <h4 style="font-size:14px; font-weight:700; color:#0b044d; margin:0 0 16px; padding-bottom:8px; border-bottom:2px solid #f0effe;">📍 Address</h4>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">House No.</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.house_no || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Street</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.street || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Barangay</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.barangay || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">City/Municipality</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.city || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Province</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.province || 'N/A'}</span></div>
                <div><span style="font-size:11px; color:#9999bb; display:block; margin-bottom:4px;">Zip Code</span><span style="font-size:13px; font-weight:600; color:#0b044d;">${data.addresses?.[0]?.zip_code || 'N/A'}</span></div>
            </div>
        </div>
    `;
}

window.viewEmployee = viewEmployee;
window.closeViewModal = closeViewModal;
