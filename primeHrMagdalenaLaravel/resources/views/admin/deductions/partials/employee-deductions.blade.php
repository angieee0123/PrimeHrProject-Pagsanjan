<div class="table-header" style="margin-bottom: 16px;">
    <div>
        <h3 class="table-title" style="font-size: 16px; margin-bottom: 4px;">Employee Deductions</h3>
        <p class="table-sub">Assign and manage deductions for employees</p>
    </div>
    <div class="table-actions">
        <input type="text" class="filter-select" placeholder="Search employee..." style="width: 200px;">
        <select class="filter-select">
            <option value="">All Types</option>
            <option value="GSIS">GSIS</option>
            <option value="PHILHEALTH">PhilHealth</option>
            <option value="PAGIBIG">Pag-IBIG</option>
        </select>
        <select class="filter-select">
            <option value="">All Status</option>
            <option value="ACTIVE">Active</option>
            <option value="COMPLETED">Completed</option>
            <option value="SUSPENDED">Suspended</option>
        </select>
        <button class="btn-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </button>
        <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAssignDeductionModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Assign Deduction
        </button>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Deduction Type</th>
                <th>Amount/Rate</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="7" style="text-align: center; padding: 40px; color: #9999bb;">
                    No employee deductions found. Click "Assign Deduction" to add.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="table-footer">
    <p>Showing <strong>0</strong> of <strong>0</strong> records</p>
</div>

@include('admin.deductions.modals.assignDeductionModal')
@include('admin.deductions.modals.editEmployeeDeductionModal')
