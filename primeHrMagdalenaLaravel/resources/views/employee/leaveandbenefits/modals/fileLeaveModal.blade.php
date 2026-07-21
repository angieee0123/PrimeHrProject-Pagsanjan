{{-- File Leave Modal --}}
<x-modal id="fileModal" close="closeFileModal" max-width="700px"
          eyebrow="NEW LEAVE REQUEST" title="File a Leave Application">
    <x-slot:subtitle>{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</x-slot:subtitle>
    <form id="leaveApplicationForm" method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body lb-modal-body-scroll">

            {{-- Leave Type Selection --}}
            <div class="form-field lb-mb-20">
                <label class="lb-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Leave Type <span class="lb-required">*</span>
                </label>
                <select name="leave_code" id="leaveType" required onchange="updateLeaveInfo()" class="lb-select-lg">
                    <option value="">Select leave type...</option>
                    @foreach($leaveTypes ?? [] as $type)
                        @php
                            $balance = $type->leaveBalances->first();
                            $availableCredits = $balance ? $balance->available_credits : 0;
                        @endphp
                        <option value="{{ $type->leave_code }}"
                                data-requires-attachment="{{ $type->requires_attachment }}"
                                data-attachment-info="{{ $type->attachment_info }}"
                                data-available="{{ $availableCredits }}"
                                data-is-accrued="{{ $type->is_accrued }}">
                            {{ $type->leave_name }} ({{ $type->leave_code }}) - {{ number_format($availableCredits, 1) }} days available
                        </option>
                    @endforeach
                </select>
                <div id="leaveTypeInfo" class="lb-info-box lb-hidden">
                    <div class="lb-flex-start-8">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" class="lb-icon-top">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div>
                            <p id="leaveTypeInfoText" class="lb-info-text"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date Range --}}
            <div class="lb-panel">
                <label class="lb-panel-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="lb-icon-inline">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Leave Period
                </label>
                <div class="form-grid lb-gap-12">
                    <div class="form-field">
                        <label class="lb-sublabel">Date From <span class="lb-required">*</span></label>
                        <input type="date" name="start_date" id="leaveFrom" required onchange="calculateDays()" class="lb-input-md">
                    </div>
                    <div class="form-field">
                        <label class="lb-sublabel">Date To <span class="lb-required">*</span></label>
                        <input type="date" name="end_date" id="leaveTo" required onchange="calculateDays()" class="lb-input-md">
                    </div>
                </div>

                {{-- Days Display --}}
                <div class="lb-days-box">
                    <div class="lb-flex-between">
                        <span class="lb-text-muted-12">Total Business Days</span>
                        <div class="lb-flex-gap-8">
                            <input type="number" name="number_of_days" id="leaveDays" min="0.5" step="0.5" value="0" readonly class="lb-days-input">
                            <span class="lb-days-label">days</span>
                        </div>
                    </div>
                    <p class="lb-hint">Weekends are automatically excluded</p>
                </div>
            </div>

            {{-- CS Form 6.B — Leave Details (conditional) --}}
            <div id="leaveDetailsSection" class="lb-panel-bordered lb-hidden">
                <label class="lb-panel-label">
                    Leave Details <span class="lb-label-note">(CS Form 6.B)</span>
                </label>

                <div id="vlSplDetails" class="lb-mb-20 lb-hidden">
                    <p class="lb-subtext">For Vacation / Special Privilege Leave:</p>
                    <div class="lb-flex-gap-16">
                        <label class="lb-radio-label">
                            <input type="radio" name="leave_location" value="ph" onchange="toggleAbroadSpecify()"> Within the Philippines
                        </label>
                        <label class="lb-radio-label">
                            <input type="radio" name="leave_location" value="abroad" onchange="toggleAbroadSpecify()"> Abroad
                        </label>
                    </div>
                    <input type="text" name="leave_location_specify" id="leaveLocationSpecify" placeholder="Specify destination (if abroad)" class="lb-input-md lb-hidden">
                </div>

                <div id="sickDetails" class="lb-mb-20 lb-hidden">
                    <p class="lb-subtext">For Sick Leave:</p>
                    <div class="lb-flex-gap-16">
                        <label class="lb-radio-label">
                            <input type="radio" name="sick_leave_type" value="in_hospital"> In Hospital
                        </label>
                        <label class="lb-radio-label">
                            <input type="radio" name="sick_leave_type" value="out_patient"> Out Patient
                        </label>
                    </div>
                    <input type="text" name="illness_specify" id="sickIllnessSpecify" placeholder="Specify illness" class="lb-input-md">
                </div>

                <div id="slbwDetails" class="lb-mb-20 lb-hidden">
                    <p class="lb-subtext">For Special Leave Benefits for Women:</p>
                    <input type="text" name="illness_specify" id="slbwIllnessSpecify" placeholder="Specify illness" class="lb-input-md">
                </div>

                <div id="studyDetails" class="lb-hidden">
                    <p class="lb-subtext">For Study Leave:</p>
                    <select name="study_leave_purpose" class="lb-select-md">
                        <option value="">Select purpose...</option>
                        <option value="masters">Completion of Master's Degree</option>
                        <option value="bar_review">BAR/Board Examination Review</option>
                        <option value="other">Other purpose</option>
                    </select>
                </div>
            </div>

            {{-- Reason --}}
            <div class="form-field lb-mb-20">
                <label class="lb-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Reason / Other Purpose <span class="lb-required">*</span>
                </label>
                <textarea name="reason" id="leaveReason" rows="4" placeholder="Please provide a brief reason for your leave request..." required class="lb-textarea"></textarea>
                <div class="lb-flex-between-mt4">
                    <small class="lb-text-muted-11">Be specific and concise</small>
                    <small id="reasonCounter" class="lb-text-muted-11">0 / 500</small>
                </div>
            </div>

            {{-- CS Form 6.D — Commutation --}}
            <div class="form-field lb-mb-20">
                <label class="lb-label-block">
                    Commutation <span class="lb-label-note">(CS Form 6.D)</span>
                </label>
                <div class="lb-flex-gap-20">
                    <label class="lb-radio-label">
                        <input type="radio" name="commutation_requested" value="0" checked> Not Requested
                    </label>
                    <label class="lb-radio-label">
                        <input type="radio" name="commutation_requested" value="1"> Requested
                    </label>
                </div>
            </div>

            {{-- Attachment --}}
            <div class="form-field lb-mb-20 lb-hidden" id="attachmentField">
                <label class="lb-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    Supporting Document <span class="lb-required">*</span>
                </label>
                <div class="lb-dropzone" id="attachmentDropZone">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" class="lb-icon-center">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <input type="file" name="attachment" id="leaveAttachment" accept=".pdf,.jpg,.jpeg,.png" class="lb-hidden" onchange="handleFileSelect(this)">
                    <label for="leaveAttachment" class="lb-cursor-pointer">
                        <p class="lb-upload-title">Click to upload or drag and drop</p>
                        <p class="lb-hint-tight">PDF, JPG, PNG (Max 5MB)</p>
                    </label>
                    <div id="fileNameDisplay" class="lb-file-name-box lb-hidden"></div>
                </div>
                <div id="attachmentInfo" class="lb-warn-box">
                    <div class="lb-flex-start-8">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" class="lb-icon-top">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <p id="attachmentInfoText" class="lb-warn-text">Required document for this leave type</p>
                    </div>
                </div>
            </div>

            {{-- Error Message --}}
            <div id="errorMessage" class="lb-error-box lb-hidden">
                <div class="lb-flex-start-8">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" class="lb-icon-top">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p id="errorMessageText" class="lb-error-text"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer lb-modal-footer">
            <button type="button" class="modal-btn-ghost lb-btn-pad-sm" onclick="closeFileModal()">
                Cancel
            </button>
            <button type="submit" class="modal-btn-primary lb-btn-submit" id="submitBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Submit Leave Request
            </button>
        </div>
    </form>
</x-modal>
