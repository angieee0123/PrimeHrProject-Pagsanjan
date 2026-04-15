<!-- Add Employee Modal -->
<div id="addEmployeeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:1000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:560px; padding:32px; position:relative; box-shadow:0 8px 32px rgba(11,4,77,0.15);">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <h3 style="margin:0; font-size:16px; font-weight:700; color:#0b044d;">Add New Employee</h3>
                <p style="margin:4px 0 0; font-size:12px; color:#6b7280;">Fill in the employee details below.</p>
            </div>
            <button onclick="document.getElementById('addEmployeeModal').style.display='none'"
                style="background:none; border:none; cursor:pointer; color:#6b7280; font-size:20px; line-height:1;">&times;</button>
        </div>

        <form action="{{ route('admin.personnel.store') }}" method="POST">
            @csrf

            <!-- Row 1 -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">First Name</label>
                    <input type="text" name="first_name" required placeholder="e.g. Maria"
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Last Name</label>
                    <input type="text" name="last_name" required placeholder="e.g. Santos"
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box;">
                </div>
            </div>

            <!-- Row 2 -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Middle Initial</label>
                    <input type="text" name="middle_initial" maxlength="2" placeholder="e.g. B"
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Employee ID</label>
                    <input type="text" name="employee_id" required placeholder="e.g. PGS-0001"
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box;">
                </div>
            </div>

            <!-- Row 3 -->
            <div style="margin-bottom:14px;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Position</label>
                <input type="text" name="position" required placeholder="e.g. Administrative Officer IV"
                    style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box;">
            </div>

            <!-- Row 4 -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Department / Office</label>
                    <select name="department" required
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box; background:#fff;">
                        <option value="">Select Department</option>
                        <option>Office of the Mayor</option>
                        <option>Office of the Mun. Engineer</option>
                        <option>Municipal Health Office</option>
                        <option>MSWD – Pagsanjan</option>
                        <option>Office of the Mun. Treasurer</option>
                        <option>Municipal Civil Registrar</option>
                        <option>Office of the Mun. Budget</option>
                        <option>Office of the Mun. Agriculturist</option>
                    </select>
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Employment Type</label>
                    <select name="emp_type" required
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box; background:#fff;">
                        <option value="">Select Type</option>
                        <option>Permanent</option>
                        <option>Casual</option>
                        <option>Contractual</option>
                        <option>Job Order</option>
                    </select>
                </div>
            </div>

            <!-- Row 5 -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:24px;">
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Date Hired</label>
                    <input type="date" name="date_hired" required
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box;">
                </div>
                <div>
                    <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:5px;">Status</label>
                    <select name="status" required
                        style="width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; box-sizing:border-box; background:#fff;">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Footer -->
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('addEmployeeModal').style.display='none'"
                    style="padding:8px 20px; border:1px solid #d1d5db; border-radius:7px; background:#fff; font-size:13px; cursor:pointer; color:#374151;">
                    Cancel
                </button>
                <button type="submit" class="modal-btn-primary" style="padding:8px 20px; font-size:13px;">
                    Save Employee
                </button>
            </div>
        </form>
    </div>
</div>
