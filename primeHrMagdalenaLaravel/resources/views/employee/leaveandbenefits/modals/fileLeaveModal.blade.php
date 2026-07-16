{{-- File Leave Modal --}}
<x-modal id="fileModal" close="closeFileModal" max-width="700px"
          eyebrow="NEW LEAVE REQUEST" title="File a Leave Application">
    <x-slot:subtitle>{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</x-slot:subtitle>
    <form id="leaveApplicationForm" method="POST" action="{{ route('leave.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

            {{-- Leave Type Selection --}}
            <div class="form-field" style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Leave Type <span style="color: #8e1e18;">*</span>
                </label>
                <select name="leave_code" id="leaveType" required onchange="updateLeaveInfo()" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit; background: white; cursor: pointer; transition: all 0.2s;">
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
                <div id="leaveTypeInfo" style="display: none; margin-top: 8px; padding: 10px; background: #f0f9ff; border-left: 3px solid #0ea5e9; border-radius: 4px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <div>
                            <p id="leaveTypeInfoText" style="margin: 0; font-size: 12px; color: #0369a1; line-height: 1.5;"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Date Range --}}
            <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 12px; font-size: 13px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Leave Period
                </label>
                <div class="form-grid" style="gap: 12px;">
                    <div class="form-field">
                        <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Date From <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="start_date" id="leaveFrom" required onchange="calculateDays()" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                    </div>
                    <div class="form-field">
                        <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Date To <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="end_date" id="leaveTo" required onchange="calculateDays()" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                    </div>
                </div>

                {{-- Days Display --}}
                <div style="margin-top: 12px; padding: 12px; background: white; border-radius: 6px; border: 2px dashed #d1d5db;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 12px; color: #6b7280;">Total Business Days</span>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="number" name="number_of_days" id="leaveDays" min="0.5" step="0.5" value="0" readonly style="width: 70px; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; font-weight: 700; color: #0b044d; text-align: center; background: #f9fafb;">
                            <span style="font-size: 13px; color: #6b7280; font-weight: 500;">days</span>
                        </div>
                    </div>
                    <p style="margin: 8px 0 0 0; font-size: 11px; color: #9ca3af;">Weekends are automatically excluded</p>
                </div>
            </div>

            {{-- CS Form 6.B — Leave Details (conditional) --}}
            <div id="leaveDetailsSection" style="display: none; background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e5e7eb;">
                <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 12px; font-size: 13px;">
                    Leave Details <span style="font-weight: 400; color: #6b7280;">(CS Form 6.B)</span>
                </label>

                <div id="vlSplDetails" style="display: none; margin-bottom: 14px;">
                    <p style="font-size: 12px; color: #374151; margin-bottom: 8px;">For Vacation / Special Privilege Leave:</p>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 8px;">
                        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="radio" name="leave_location" value="ph" onchange="toggleAbroadSpecify()"> Within the Philippines
                        </label>
                        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="radio" name="leave_location" value="abroad" onchange="toggleAbroadSpecify()"> Abroad
                        </label>
                    </div>
                    <input type="text" name="leave_location_specify" id="leaveLocationSpecify" placeholder="Specify destination (if abroad)" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; display: none;">
                </div>

                <div id="sickDetails" style="display: none; margin-bottom: 14px;">
                    <p style="font-size: 12px; color: #374151; margin-bottom: 8px;">For Sick Leave:</p>
                    <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 8px;">
                        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="radio" name="sick_leave_type" value="in_hospital"> In Hospital
                        </label>
                        <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                            <input type="radio" name="sick_leave_type" value="out_patient"> Out Patient
                        </label>
                    </div>
                    <input type="text" name="illness_specify" id="sickIllnessSpecify" placeholder="Specify illness" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px;">
                </div>

                <div id="slbwDetails" style="display: none; margin-bottom: 14px;">
                    <p style="font-size: 12px; color: #374151; margin-bottom: 8px;">For Special Leave Benefits for Women:</p>
                    <input type="text" name="illness_specify" id="slbwIllnessSpecify" placeholder="Specify illness" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px;">
                </div>

                <div id="studyDetails" style="display: none;">
                    <p style="font-size: 12px; color: #374151; margin-bottom: 8px;">For Study Leave:</p>
                    <select name="study_leave_purpose" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px;">
                        <option value="">Select purpose...</option>
                        <option value="masters">Completion of Master's Degree</option>
                        <option value="bar_review">BAR/Board Examination Review</option>
                        <option value="other">Other purpose</option>
                    </select>
                </div>
            </div>

            {{-- Reason --}}
            <div class="form-field" style="margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                    Reason / Other Purpose <span style="color: #8e1e18;">*</span>
                </label>
                <textarea name="reason" id="leaveReason" rows="4" placeholder="Please provide a brief reason for your leave request..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical; line-height: 1.6;"></textarea>
                <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                    <small style="color: #9ca3af; font-size: 11px;">Be specific and concise</small>
                    <small id="reasonCounter" style="color: #9ca3af; font-size: 11px;">0 / 500</small>
                </div>
            </div>

            {{-- CS Form 6.D — Commutation --}}
            <div class="form-field" style="margin-bottom: 20px;">
                <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 8px; font-size: 13px;">
                    Commutation <span style="font-weight: 400; color: #6b7280;">(CS Form 6.D)</span>
                </label>
                <div style="display: flex; gap: 20px;">
                    <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="commutation_requested" value="0" checked> Not Requested
                    </label>
                    <label style="font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="commutation_requested" value="1"> Requested
                    </label>
                </div>
            </div>

            {{-- Attachment --}}
            <div class="form-field" id="attachmentField" style="display: none; margin-bottom: 20px;">
                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    Supporting Document <span style="color: #8e1e18;">*</span>
                </label>
                <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; background: #fafafa; transition: all 0.2s;" id="attachmentDropZone">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin: 0 auto 12px;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <input type="file" name="attachment" id="leaveAttachment" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="handleFileSelect(this)">
                    <label for="leaveAttachment" style="cursor: pointer;">
                        <p style="margin: 0 0 4px 0; font-size: 13px; color: #374151; font-weight: 500;">Click to upload or drag and drop</p>
                        <p style="margin: 0; font-size: 11px; color: #9ca3af;">PDF, JPG, PNG (Max 5MB)</p>
                    </label>
                    <div id="fileNameDisplay" style="display: none; margin-top: 12px; padding: 8px 12px; background: #f0f9ff; border-radius: 4px; font-size: 12px; color: #0369a1;"></div>
                </div>
                <div id="attachmentInfo" style="margin-top: 8px; padding: 10px; background: #fef3c7; border-left: 3px solid #f59e0b; border-radius: 4px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                            <line x1="12" y1="9" x2="12" y2="13"/>
                            <line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        <p id="attachmentInfoText" style="margin: 0; font-size: 12px; color: #92400e; line-height: 1.5;">Required document for this leave type</p>
                    </div>
                </div>
            </div>

            {{-- Error Message --}}
            <div id="errorMessage" style="display: none; padding: 12px; background: #fee2e2; border-left: 3px solid #ef4444; border-radius: 6px; margin-bottom: 16px;">
                <div style="display: flex; align-items: start; gap: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p id="errorMessageText" style="margin: 0; color: #991b1b; font-size: 13px; line-height: 1.5;"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px; background: #f9fafb;">
            <button type="button" class="modal-btn-ghost" onclick="closeFileModal()" style="padding: 10px 20px;">
                Cancel
            </button>
            <button type="submit" class="modal-btn-primary" id="submitBtn" style="padding: 10px 24px; display: flex; align-items: center; gap: 8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Submit Leave Request
            </button>
        </div>
    </form>
</x-modal>
