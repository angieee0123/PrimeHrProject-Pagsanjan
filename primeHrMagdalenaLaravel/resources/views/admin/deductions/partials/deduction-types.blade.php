<div class="table-header" style="margin-bottom: 16px;">
    <div>
        <h3 class="table-title" style="font-size: 16px; margin-bottom: 4px;">Deduction Types</h3>
        <p class="table-sub">Manage mandatory contributions, loans, and other deduction types</p>
    </div>
    <div class="table-actions">
        <select class="filter-select">
            <option value="">All Categories</option>
            <option value="MANDATORY">Mandatory</option>
            <option value="LOAN">Loan</option>
            <option value="OTHER">Other</option>
        </select>
        <select class="filter-select">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        <button class="btn-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </button>
        <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAddDeductionTypeModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Deduction Type
        </button>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Category</th>
                <th>Computation Type</th>
                <th>Rate/Amount</th>
                <th>Base</th>
                <th>Max Amount</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">GSIS</strong></td>
                <td>GSIS Contribution</td>
                <td><span class="badge-status processed">MANDATORY</span></td>
                <td>PERCENTAGE</td>
                <td class="pay-cell">9.00%</td>
                <td>BASIC</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('GSIS')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">PHILHEALTH</strong></td>
                <td>PhilHealth Contribution</td>
                <td><span class="badge-status processed">MANDATORY</span></td>
                <td>PERCENTAGE</td>
                <td class="pay-cell">2.50%</td>
                <td>BASIC</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('PHILHEALTH')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">PAGIBIG</strong></td>
                <td>Pag-IBIG Contribution</td>
                <td><span class="badge-status processed">MANDATORY</span></td>
                <td>PERCENTAGE</td>
                <td class="pay-cell">2.00%</td>
                <td>BASIC</td>
                <td class="net-pay">₱100.00</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('PAGIBIG')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">WTAX</strong></td>
                <td>Withholding Tax</td>
                <td><span class="badge-status processed">MANDATORY</span></td>
                <td>CUSTOM</td>
                <td>—</td>
                <td>CUSTOM</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('WTAX')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">LOAN_GSIS_SALARY</strong></td>
                <td>GSIS Salary Loan</td>
                <td><span class="badge-emptype">LOAN</span></td>
                <td>FIXED</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('LOAN_GSIS_SALARY')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">LOAN_GSIS_POLICY</strong></td>
                <td>GSIS Policy Loan</td>
                <td><span class="badge-emptype">LOAN</span></td>
                <td>FIXED</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('LOAN_GSIS_POLICY')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">LOAN_PAGIBIG_MPL</strong></td>
                <td>Pag-IBIG Multi-Purpose Loan</td>
                <td><span class="badge-emptype">LOAN</span></td>
                <td>FIXED</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('LOAN_PAGIBIG_MPL')">Edit</button>
                    </div>
                </td>
            </tr>
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">LOAN_PAGIBIG_HOUSING</strong></td>
                <td>Pag-IBIG Housing Loan</td>
                <td><span class="badge-emptype">LOAN</span></td>
                <td>FIXED</td>
                <td>—</td>
                <td>—</td>
                <td>—</td>
                <td><span class="badge-status processed">Active</span></td>
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('LOAN_PAGIBIG_HOUSING')">Edit</button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div class="table-footer">
    <p>Showing <strong>8</strong> of <strong>8</strong> deduction types</p>
</div>

@include('admin.deductions.modals.addDeductionTypeModal')
@include('admin.deductions.modals.editDeductionTypeModal')
