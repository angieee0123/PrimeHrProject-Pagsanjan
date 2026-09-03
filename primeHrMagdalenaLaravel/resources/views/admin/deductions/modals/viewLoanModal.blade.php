{{--
    Employee Loan details — the Loans tab's "View details" row action.

    Replaces a window.alert() that drew its own box out of ╔═╗ characters and
    padStart()/padEnd(). That dialog could not be themed, truncated any name
    or loan type longer than its hard-coded 31-character pad, mis-aligned the
    moment a peso figure ran past six digits, and on some platforms offers to
    suppress every later dialog on the page.

    Same x-modal-container shell as viewLoanTypeModal — the two "view" screens
    on this page read as one pair — and the footer offers Edit loan directly,
    because reading a loan is usually the step before correcting it.

    Grouped rather than one flat list of label/value rows: an admin opening
    this is checking a person, a debt and a payroll schedule, and those are
    three separate questions. The three figures the whole record turns on —
    loan amount, monthly amortization and remaining balance — are lifted out
    of the lists into their own cards.

    Every id below is filled by viewLoanDetails() in
    resources/js/admin/deductions/loans.js. Nothing here is hard-coded: the
    em-dashes are placeholders overwritten before the modal is shown.
--}}
<x-modal-container id="viewLoanModal" close="closeViewLoanModal" max-width="680px"
                   title="Loan Details" subtitle="Employee loan record, balance and deduction schedule">

    {{-- Identity strip: who the loan belongs to, and what state it is in. --}}
    <div class="vld-head">
        <img src="" alt="" class="vld-avatar" id="viewLoanAvatarPhoto" hidden>
        <span class="vld-avatar vld-avatar-initials" id="viewLoanAvatarInitials" aria-hidden="true">—</span>
        <div class="vld-head-text">
            <p class="vld-name" id="viewLoanEmployeeName">—</p>
            <p class="vld-head-sub" id="viewLoanEmployeeMeta">—</p>
        </div>
        <span class="badge-status" id="viewLoanStatus">—</span>
    </div>

    {{-- The three figures the record turns on. --}}
    <div class="vld-figures">
        <div class="vld-figure">
            <p class="vld-figure-label">Loan Amount</p>
            <p class="vld-figure-value" id="viewLoanTotalAmount">—</p>
        </div>
        <div class="vld-figure">
            <p class="vld-figure-label">Monthly Amortization</p>
            <p class="vld-figure-value" id="viewLoanInstallment">—</p>
        </div>
        <div class="vld-figure is-balance">
            <p class="vld-figure-label">Remaining Balance</p>
            <p class="vld-figure-value" id="viewLoanRemaining">—</p>
        </div>
    </div>

    {{-- Repayment. A balance says little on its own — this is the same
         progress reading the table row shows, so the two cannot disagree. --}}
    <section class="vld-section">
        <h4 class="vld-section-title">Repayment Progress</h4>
        <div class="vld-progress">
            <div class="vld-progress-head">
                <span class="vld-progress-pct" id="viewLoanProgressPct">—</span>
                <span class="vld-progress-note" id="viewLoanProgressNote">—</span>
            </div>
            <div class="vld-progress-bar" role="img" id="viewLoanProgressBar" aria-label="Repayment progress">
                <div class="vld-progress-fill" id="viewLoanProgressFill" style="width: 0%;"></div>
            </div>
        </div>
        <dl class="vld-grid">
            <div class="vld-row">
                <dt>Amount paid</dt>
                <dd id="viewLoanAmountPaid">—</dd>
            </div>
            <div class="vld-row">
                <dt>Months remaining</dt>
                <dd id="viewLoanMonthsRemaining">—</dd>
            </div>
        </dl>
    </section>

    <section class="vld-section">
        <h4 class="vld-section-title">Loan Information</h4>
        <dl class="vld-grid">
            <div class="vld-row">
                <dt>Loan type</dt>
                <dd id="viewLoanTypeName">—</dd>
            </div>
            <div class="vld-row">
                <dt>Provider</dt>
                <dd><span class="lt-tag is-other" id="viewLoanProvider">—</span></dd>
            </div>
            <div class="vld-row">
                <dt>Code</dt>
                <dd><span class="ded-code ded-code-lead" id="viewLoanTypeCodeValue">—</span></dd>
            </div>
            <div class="vld-row">
                <dt>Category</dt>
                <dd><span class="ded-chip is-loan" id="viewLoanCategory">—</span></dd>
            </div>
            <div class="vld-row">
                <dt>Start date</dt>
                <dd id="viewLoanStartDate">—</dd>
            </div>
            <div class="vld-row">
                <dt>End date</dt>
                <dd id="viewLoanEndDate">—</dd>
            </div>
        </dl>
    </section>

    {{-- What payroll actually takes, and per cutoff. --}}
    <section class="vld-section">
        <h4 class="vld-section-title">Deduction Schedule</h4>
        <dl class="vld-grid">
            <div class="vld-row vld-row-wide">
                <dt>Schedule</dt>
                <dd>
                    <span id="viewLoanScheduleText">—</span>
                    <span class="lt-tag is-custom" id="viewLoanScheduleSource" hidden>—</span>
                </dd>
            </div>
            <div class="vld-row">
                <dt>1st cutoff</dt>
                <dd id="viewLoanCutoff1">—</dd>
            </div>
            <div class="vld-row">
                <dt>2nd cutoff</dt>
                <dd id="viewLoanCutoff2">—</dd>
            </div>
        </dl>
    </section>

    {{-- Hidden outright when the record carries none: an empty panel headed
         "Remarks" reads as a note somebody failed to write. --}}
    <section class="vld-section" id="viewLoanRemarksSection" hidden>
        <h4 class="vld-section-title">Remarks</h4>
        <p class="vld-remarks" id="viewLoanRemarks">—</p>
    </section>

    <div class="form-actions">
        <button type="button" class="btn-cancel" onclick="closeViewLoanModal()">Close</button>
        <button type="button" class="btn-submit" id="viewLoanEditBtn">Edit Loan</button>
    </div>
</x-modal-container>
