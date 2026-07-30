{{-- File Pass Slip Modal --}}
<x-modal id="filePassSlipModal" close="closePassSlipModal" max-width="700px"
          eyebrow="NEW PASS SLIP" title="File a Pass Slip Request">
    <x-slot:subtitle>{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</x-slot:subtitle>
    <form id="passSlipForm" method="POST" action="{{ route('passslip.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body ps-modal-body-scroll">

            {{-- Issued For --}}
            <div class="form-field ps-mb-20">
                <label class="ps-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                    Issued For <span class="ps-required">*</span>
                </label>
                <div class="ps-radio-row">
                    <label class="ps-radio-label">
                        <input type="radio" name="type" value="official_activity" checked onchange="togglePassSlipPurposeOptions()"> Official Activity
                    </label>
                    <label class="ps-radio-label">
                        <input type="radio" name="type" value="personal_reason" onchange="togglePassSlipPurposeOptions()"> Personal Reasons
                    </label>
                </div>
            </div>

            {{-- Purpose --}}
            <div class="form-field ps-mb-20">
                <label class="ps-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                        <line x1="7" y1="7" x2="7.01" y2="7"/>
                    </svg>
                    Purpose <span class="ps-required">*</span>
                </label>
                <select name="purpose_category" id="purposeCategory" required class="ps-select-lg">
                    <optgroup label="Official Activity" id="officialPurposeGroup">
                        <option value="coordinate_with">To coordinate with</option>
                        <option value="meeting_conference">To attend meeting/conference</option>
                        <option value="secure_documents">To secure documents &amp; others</option>
                        <option value="follow_up">To follow up</option>
                    </optgroup>
                    {{-- Deliberately kept as an inline style: togglePassSlipPurposeOptions() toggles this
                         between '' and 'none' directly on .style.display, so a CSS class here would be
                         permanently masked once JS clears the inline value back to ''. --}}
                    <optgroup label="Personal Reason" id="personalPurposeGroup" style="display: none;">
                        <option value="personal_matter">To attend personal matter</option>
                    </optgroup>
                </select>
            </div>

            {{-- Reason --}}
            <div class="form-field ps-mb-20">
                <label class="ps-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Details <span class="ps-required">*</span>
                </label>
                <textarea name="reason" id="reason" rows="3" placeholder="Briefly describe the reason for your pass slip..." required class="ps-textarea"></textarea>
                <div class="ps-flex-between-mt4">
                    <small class="ps-text-muted-11">Be specific about the reason</small>
                    <small id="reasonCounter" class="ps-text-muted-11">0 / 300</small>
                </div>
            </div>

            {{-- Destination --}}
            <div class="form-field ps-mb-20">
                <label class="ps-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Destination (Optional)
                </label>
                <input type="text" name="destination" id="destination" placeholder="e.g., City Hall, Bank, Clinic" class="ps-input-lg">
            </div>

            {{-- Date & Time --}}
            <div class="ps-panel">
                <label class="ps-panel-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ps-icon-inline">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Date &amp; Time
                </label>
                {{-- type=text: flatpickr calendar (busyDatesCalendar.js) that marks
                     dates already used by leaves or travel orders --}}
                <div class="form-field ps-mb-12">
                    <label class="ps-sublabel">Date <span class="ps-required">*</span></label>
                    <input type="text" name="date" id="passSlipDate" required placeholder="Select date..." autocomplete="off" class="ps-input-md">
                    <div class="busy-cal-legend">
                        <span><i class="dot-pending"></i> Pending leave</span>
                        <span><i class="dot-approved"></i> Approved leave</span>
                        <span><i class="dot-travel"></i> Travel order</span>
                    </div>
                </div>
                <div class="form-grid ps-gap-12">
                    <div class="form-field">
                        <label class="ps-sublabel">Time Out <span class="ps-required">*</span></label>
                        <input type="time" name="time_out" id="timeOut" required class="ps-input-md">
                    </div>
                    <div class="form-field">
                        <label class="ps-sublabel">Time In (Expected)</label>
                        <input type="time" name="time_in" id="timeIn" class="ps-input-md">
                    </div>
                </div>
            </div>

            {{-- Supporting Document --}}
            <div class="form-field ps-mb-20">
                <label class="ps-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    Supporting Document (Optional)
                </label>
                <div class="ps-dropzone" id="passSlipAttachmentDropZone">
                    <div class="ps-upload-icon-wrap">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                    </div>
                    <input type="file" name="attachment" id="passSlipAttachment" accept=".pdf,.jpg,.jpeg,.png" class="ps-hidden" onchange="handlePassSlipFileSelect(this)">
                    <label for="passSlipAttachment" class="ps-cursor-pointer">
                        <p class="ps-upload-title">Click to upload or drag and drop</p>
                        <p class="ps-hint-tight">PDF, JPG, PNG (Max 5MB)</p>
                    </label>
                    <div id="passSlipFileNameDisplay" class="ps-file-name-box ps-hidden"></div>
                </div>
                <p class="ps-hint">Attach approval note or other relevant documents</p>
            </div>

            {{-- Error Message --}}
            <div id="passSlipErrorMessage" class="ps-error-box ps-hidden">
                <div class="ps-flex-start-8">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" class="ps-icon-top">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p id="passSlipErrorMessageText" class="ps-error-text"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer ps-modal-footer">
            <button type="button" class="modal-btn-ghost ps-btn-pad-sm" onclick="closePassSlipModal()">
                Cancel
            </button>
            <button type="submit" class="modal-btn-primary ps-btn-submit" id="passSlipSubmitBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Submit Pass Slip
            </button>
        </div>
    </form>
</x-modal>
