<x-modal-container id="addLoanModal" close="closeAddLoanModal"
                     title="Add Employee Loan" subtitle="Create a new loan record with automatic balance tracking">
    <form id="addLoanForm" action="{{ route('admin.deductions.employee.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Employee <span class="ded-required">*</span></label>
            <select name="employee_id" id="loanEmployee" class="form-input" required>
                <option value="">Select Employee</option>
                @foreach(\App\Models\Employee::with('employmentDetail.departmentRelation')->orderBy('last_name')->get() as $emp)
                    <option value="{{ $emp->id }}">
                        {{ $emp->last_name }}, {{ $emp->first_name }}
                        @if($emp->employmentDetail && $emp->employmentDetail->departmentRelation)
                            - {{ $emp->employmentDetail->departmentRelation->name }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Loan Type <span class="ded-required">*</span></label>
                <select name="deduction_type_id" id="loanProvider" class="form-input" required onchange="handleLoanTypeChange()">
                    <option value="">Select Loan Type</option>
                    @php
                        $loanTypes = \App\Models\LoanType::with('deductionType')
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->get()
                            ->groupBy(function($loan) {
                                if (str_contains($loan->code, 'GSIS')) return 'GSIS';
                                if (str_contains($loan->code, 'PAGIBIG')) return 'PAG-IBIG';
                                return 'Other';
                            });
                    @endphp
                    @if($loanTypes->has('GSIS'))
                        <optgroup label="GSIS Loans">
                            @foreach($loanTypes['GSIS'] as $loan)
                                <option value="{{ $loan->deduction_type_id }}"
                                        data-provider="GSIS"
                                        data-max-amount="{{ $loan->max_loanable_amount }}"
                                        data-interest-rate="{{ $loan->interest_rate }}"
                                        data-max-terms="{{ $loan->max_terms_months }}">
                                    {{ $loan->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if($loanTypes->has('PAG-IBIG'))
                        <optgroup label="Pag-IBIG Loans">
                            @foreach($loanTypes['PAG-IBIG'] as $loan)
                                <option value="{{ $loan->deduction_type_id }}"
                                        data-provider="PAG-IBIG"
                                        data-max-amount="{{ $loan->max_loanable_amount }}"
                                        data-interest-rate="{{ $loan->interest_rate }}"
                                        data-max-terms="{{ $loan->max_terms_months }}">
                                    {{ $loan->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    @if($loanTypes->has('Other'))
                        <optgroup label="Other Loans">
                            @foreach($loanTypes['Other'] as $loan)
                                <option value="{{ $loan->deduction_type_id }}"
                                        data-provider="OTHER"
                                        data-max-amount="{{ $loan->max_loanable_amount }}"
                                        data-interest-rate="{{ $loan->interest_rate }}"
                                        data-max-terms="{{ $loan->max_terms_months }}">
                                    {{ $loan->name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endif
                    <option value="OTHER" data-provider="CUSTOM">Other (External Provider)</option>
                </select>
            </div>
            <div class="form-group ded-col" id="loanProviderDisplay">
                <label class="form-label">Provider</label>
                <input type="text" id="providerName" class="form-input ded-readonly-input" readonly placeholder="Select loan type first">
            </div>
        </div>

        <div class="form-row ded-hidden" id="otherProviderFields">
            <div class="form-group ded-col">
                <label class="form-label">Provider Name <span class="ded-required">*</span></label>
                <input type="text" name="other_provider_name" id="otherProviderName" class="form-input" placeholder="e.g., SSS, Private Bank, Cooperative">
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Loan Description <span class="ded-required">*</span></label>
                <input type="text" name="other_loan_type" id="otherLoanType" class="form-input" placeholder="e.g., Personal Loan, Emergency Loan">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Total Loan Amount <span class="ded-required">*</span></label>
                <input type="number" name="total_amount" id="loanTotalAmount" class="form-input" placeholder="e.g., 50000.00" step="0.01" min="0" required onchange="calculateLoanInstallment()">
                <p id="maxAmountHint" class="ded-field-hint-hidden"></p>
            </div>
            <div class="form-group ded-col">
                <label class="form-label">Monthly Installment <span class="ded-required">*</span></label>
                <input type="number" name="installment_amount" id="loanInstallment" class="form-input" placeholder="e.g., 2500.00" step="0.01" min="0" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Start Date <span class="ded-required">*</span></label>
                {{-- type=text: flatpickr calendar (busyDatesCalendar.js) marking the
                     selected employee's leave / travel days for context. Nothing is
                     blocked — a loan period legitimately spans them. --}}
                <input type="text" name="start_date" id="loanStartDate" class="form-input" required value="{{ date('Y-m-d') }}" autocomplete="off">
            </div>
            <div class="form-group ded-col">
                <label class="form-label">End Date <span class="ded-required">*</span></label>
                <input type="text" name="end_date" id="loanEndDate" class="form-input" required autocomplete="off">
            </div>
            <div class="busy-cal-legend" style="width:100%">
                <span><i class="dot-pending"></i> Pending leave</span>
                <span><i class="dot-approved"></i> Approved leave</span>
                <span><i class="dot-travel"></i> Travel order</span>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Status <span class="ded-required">*</span></label>
            <select name="status" class="form-input" required>
                <option value="ACTIVE">Active</option>
                <option value="SUSPENDED">Suspended</option>
                <option value="COMPLETED">Completed</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Remarks</label>
            <textarea name="remarks" class="form-input" rows="2" placeholder="Additional notes or remarks..."></textarea>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" onclick="closeAddLoanModal()">Cancel</button>
            <button type="submit" class="btn-submit">Add Loan</button>
        </div>
    </form>
</x-modal-container>

@push('scripts')
    @vite('resources/js/admin/deductions/addLoanModal.js')
@endpush
