{{--
    Loan Type details.

    Replaces a window.alert() that drew a box out of ╔═╗ characters and
    padEnd() — which ignored the theme, could not be styled, truncated any
    name longer than the hard-coded 37-character pad, and on some platforms
    offers to suppress every later dialog on the page.

    Same shell as editLoanTypeModal so the two read as one pair, and the
    footer offers the edit action directly: reading the details is usually
    the step before changing them.
--}}
<x-modal-container id="viewLoanTypeModal" close="closeViewLoanTypeModal"
                   title="Loan Type Details" subtitle="Registered loan type and how it is used">

    <div class="vlt-head">
        <span class="vlt-icon" aria-hidden="true">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
        </span>
        <div class="vlt-head-text">
            <p class="vlt-name" id="viewLoanTypeName">—</p>
            <p class="vlt-code"><span class="ded-code ded-code-lead" id="viewLoanTypeCode">—</span></p>
        </div>
        <span class="badge-status" id="viewLoanTypeStatus">—</span>
    </div>

    <dl class="vlt-grid">
        <div class="vlt-row">
            <dt>Provider</dt>
            <dd><span class="ded-chip is-other" id="viewLoanTypeProvider">—</span></dd>
        </div>
        <div class="vlt-row">
            <dt>Category</dt>
            <dd><span class="ded-chip is-loan" id="viewLoanTypeCategory">—</span></dd>
        </div>
        <div class="vlt-row">
            <dt>Max loanable</dt>
            <dd id="viewLoanTypeMax">—</dd>
        </div>
        <div class="vlt-row">
            <dt>Interest rate</dt>
            <dd id="viewLoanTypeRate">—</dd>
        </div>
        <div class="vlt-row">
            <dt>Computation</dt>
            <dd id="viewLoanTypeComputation">—</dd>
        </div>
        <div class="vlt-row">
            <dt>Currently in use</dt>
            <dd id="viewLoanTypeUsage">—</dd>
        </div>
    </dl>

    <div class="form-actions">
        <button type="button" class="btn-cancel" onclick="closeViewLoanTypeModal()">Close</button>
        <button type="button" class="btn-submit" id="viewLoanTypeEditBtn">Edit Loan Type</button>
    </div>
</x-modal-container>
