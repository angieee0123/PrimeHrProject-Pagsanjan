<div class="table-header" style="margin-bottom: 16px;">
    <div>
        <h3 class="table-title" style="font-size: 16px; margin-bottom: 4px;">Employee Loans</h3>
        <p class="table-sub">Manage GSIS and Pag-IBIG loans with automatic balance tracking</p>
    </div>
    <div class="table-actions">
        <input type="text" class="filter-select" placeholder="Search employee..." style="width: 200px;">
        <select class="filter-select">
            <option value="">All Loan Types</option>
            <option value="GSIS_SALARY">GSIS Salary Loan</option>
            <option value="GSIS_POLICY">GSIS Policy Loan</option>
            <option value="PAGIBIG_MPL">Pag-IBIG MPL</option>
            <option value="PAGIBIG_HOUSING">Pag-IBIG Housing</option>
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
        <button class="btn-secondary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px; background: #fff; border: 1.5px solid #e8e7f5; color: #0b044d;" onclick="openAddLoanTypeModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M12 2v20M2 12h20"/>
            </svg>
            Manage Loan Types
        </button>
        <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAddLoanModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Loan
        </button>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Loan Type</th>
                <th>Total Amount</th>
                <th>Remaining Balance</th>
                <th>Monthly Installment</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #9999bb;">
                    No loans found. Click "Add Loan" to create a new loan.
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="table-footer">
    <p>Showing <strong>0</strong> of <strong>0</strong> loans</p>
</div>

@include('admin.deductions.modals.addLoanModal')
@include('admin.deductions.modals.addLoanTypeModal')
