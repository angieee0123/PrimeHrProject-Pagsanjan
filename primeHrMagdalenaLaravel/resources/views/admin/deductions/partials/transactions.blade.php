<div id="transactions-tab" style="display: none;">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Transactions</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · View complete history of all deduction transactions</p>
        </div>
        <div class="table-actions">
            <input type="date" class="filter-select" placeholder="From">
            <span style="font-size: 12px; color: #9999bb;">to</span>
            <input type="date" class="filter-select" placeholder="To">
            <input type="text" class="filter-select" placeholder="Search employee..." style="width: 180px;">
            <select class="filter-select">
                <option value="">All Types</option>
                <option value="GSIS">GSIS</option>
                <option value="PHILHEALTH">PhilHealth</option>
                <option value="PAGIBIG">Pag-IBIG</option>
                <option value="WTAX">Withholding Tax</option>
            </select>
            <select class="filter-select">
                <option value="">All Cutoffs</option>
                <option value="1ST">1st Cutoff</option>
                <option value="2ND">2nd Cutoff</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
        </div>
    </div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee</th>
                <th>Deduction Type</th>
                <th>Cutoff Period</th>
                <th>Amount Deducted</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="text-align: center; padding: 40px; color: #9999bb;">
                    No transactions found. Transactions will appear here after processing payroll.
                </td>
            </tr>
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <p>Showing <strong>0</strong> of <strong>0</strong> transactions</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
        </div>
    </div>
</section>
</div>
