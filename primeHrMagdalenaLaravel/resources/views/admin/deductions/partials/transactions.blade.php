{{--
    Deduction Transactions.

    The markup was malformed — a `</tbody>` with no opening `<tbody>`, so the
    empty row landed in a tbody the parser had to invent.

    The table is also not wired to anything: `deduction_transactions` exists
    with the right columns but has no model, and DeductionController hard-codes
    `transactions_this_month => 0` with a note that nothing populates it. So
    the row below is not a placeholder standing in for data that failed to
    load — there is genuinely nothing to read yet, and it says so rather than
    implying a filter or a fault.
--}}
<div id="transactions-tab" class="ded-hidden">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Transactions</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · View complete history of all deduction transactions</p>
        </div>
    </div>

<div class="table-wrapper">
    <table class="payroll-table ded-txn-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Employee</th>
                <th>Deduction</th>
                <th>Cutoff</th>
                <th class="ded-num-col">Amount</th>
                <th class="row-menu-head">Actions</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="ded-empty-cell">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="ded-empty-icon"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    <p class="ded-empty-title">No transactions recorded yet</p>
                    <p class="ded-empty-sub">Deductions are written here when a payroll run is processed.</p>
                </td>
            </tr>
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <p>Showing <strong>0</strong> of <strong>0</strong> transactions</p>
    </div>
</section>
</div>
