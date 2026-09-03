{{-- File Monetization Modal. Same x-modal shell and lb-* field styles as the
     File Leave modal, so both forms read as one system. --}}
<x-modal id="fileMonetizationModal" close="closeMonetizationModal" max-width="640px" class="permanent-leavebenefits"
          eyebrow="NEW MONETIZATION REQUEST" title="Request Monetization">
    <x-slot:subtitle>{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</x-slot:subtitle>
    <form id="monetizationForm" method="POST" action="{{ route('monetization.store') }}">
        @csrf
        <div class="modal-body lb-modal-body-scroll">

            {{-- Live balances + salary the request is validated against --}}
            <div class="lb-panel">
                <label class="lb-panel-label">Your Leave Credits &amp; Salary</label>
                <div class="modal-row"><span>Vacation Leave available</span><strong>{{ number_format((float) ($monetVlAvailable ?? 0), 1) }} days</strong></div>
                <div class="modal-row"><span>Sick Leave available</span><strong>{{ number_format((float) ($monetSlAvailable ?? 0), 1) }} days</strong></div>
                <div class="modal-row"><span>Monthly salary</span><strong>{{ isset($monetMonthlySalary) ? '₱' . number_format((float) $monetMonthlySalary, 2) : 'Not on record' }}</strong></div>
            </div>

            {{-- Days to monetize --}}
            <div class="lb-panel">
                <label class="lb-panel-label">Days to Monetize</label>
                <div class="form-grid lb-gap-12">
                    <div class="form-field">
                        <label class="lb-sublabel">VL Days</label>
                        <input type="number" name="vl_days" id="monetVlDays" min="0" max="999" step="any" value="0"
                               class="lb-input-md" oninput="updateMonetEstimate()"
                               data-available="{{ (float) ($monetVlAvailable ?? 0) }}">
                    </div>
                    <div class="form-field">
                        <label class="lb-sublabel">SL Days</label>
                        <input type="number" name="sl_days" id="monetSlDays" min="0" max="999" step="any" value="0"
                               class="lb-input-md" oninput="updateMonetEstimate()"
                               data-available="{{ (float) ($monetSlAvailable ?? 0) }}">
                    </div>
                </div>

                {{-- Live estimate: Salary × Days × 0.0481927 --}}
                <div class="lb-days-box">
                    <div class="lb-flex-between">
                        <span class="lb-text-muted-12">Total Days</span>
                        <span class="lb-days-label" id="monetTotalDays">0 days</span>
                    </div>
                    <div class="lb-flex-between">
                        <span class="lb-text-muted-12">Estimated Amount (S × D × 0.0481927)</span>
                        <strong id="monetEstimate" data-salary="{{ (float) ($monetMonthlySalary ?? 0) }}">₱0.00</strong>
                    </div>
                </div>
            </div>

            {{-- Reason --}}
            <div class="form-field lb-mb-20">
                <label class="lb-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Reason <span class="lb-required">*</span>
                </label>
                <textarea name="reason" id="monetReason" rows="4" maxlength="500" placeholder="Why are you monetizing these leave credits?..." required class="lb-textarea"></textarea>
            </div>

            {{-- Error Message --}}
            <div id="monetErrorMessage" class="lb-error-box lb-hidden">
                <div class="lb-flex-start-8">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" class="lb-icon-top">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p id="monetErrorMessageText" class="lb-error-text"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer lb-modal-footer">
            <button type="button" class="modal-btn-ghost lb-btn-pad-sm" onclick="closeMonetizationModal()">
                Cancel
            </button>
            <button type="submit" class="modal-btn-primary lb-btn-submit" id="monetSubmitBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Submit Request
            </button>
        </div>
    </form>
</x-modal>
