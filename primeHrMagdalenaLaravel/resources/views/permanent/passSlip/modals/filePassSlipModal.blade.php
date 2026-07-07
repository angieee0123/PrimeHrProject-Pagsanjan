{{-- File Pass Slip Modal --}}
<div class="modal-overlay" id="filePassSlipModal" onclick="closePassSlipModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 700px;">
        <form id="passSlipForm" method="POST" action="{{ route('passslip.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <div>
                    <span class="modal-eyebrow">NEW PASS SLIP</span>
                    <h3 class="modal-title">File a Pass Slip Request</h3>
                    <p class="modal-sub">{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</p>
                </div>
                <button type="button" class="modal-close" onclick="closePassSlipModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                {{-- Reason --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Reason for Leaving Premises <span style="color: #8e1e18;">*</span>
                    </label>
                    <textarea name="reason" id="reason" rows="3" placeholder="Briefly describe the reason for your pass slip..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical; line-height: 1.6;"></textarea>
                    <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                        <small style="color: #9ca3af; font-size: 11px;">Be specific about the reason</small>
                        <small id="reasonCounter" style="color: #9ca3af; font-size: 11px;">0 / 300</small>
                    </div>
                </div>

                {{-- Destination --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Destination (Optional)
                    </label>
                    <input type="text" name="destination" id="destination" placeholder="e.g., City Hall, Bank, Clinic" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit;">
                </div>

                {{-- Date & Time --}}
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 12px; font-size: 13px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Date &amp; Time
                    </label>
                    <div class="form-field" style="margin-bottom: 12px;">
                        <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Date <span style="color: #8e1e18;">*</span></label>
                        <input type="date" name="date" id="passSlipDate" required style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                    </div>
                    <div class="form-grid" style="gap: 12px;">
                        <div class="form-field">
                            <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Time Out <span style="color: #8e1e18;">*</span></label>
                            <input type="time" name="time_out" id="timeOut" required style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                        </div>
                        <div class="form-field">
                            <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Time In (Expected)</label>
                            <input type="time" name="time_in" id="timeIn" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                        </div>
                    </div>
                </div>

                {{-- Supporting Document --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                        </svg>
                        Supporting Document (Optional)
                    </label>
                    <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; background: #fafafa; transition: all 0.2s;" id="passSlipAttachmentDropZone">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin: 0 auto 12px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <input type="file" name="attachment" id="passSlipAttachment" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="handlePassSlipFileSelect(this)">
                        <label for="passSlipAttachment" style="cursor: pointer;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #374151; font-weight: 500;">Click to upload or drag and drop</p>
                            <p style="margin: 0; font-size: 11px; color: #9ca3af;">PDF, JPG, PNG (Max 5MB)</p>
                        </label>
                        <div id="passSlipFileNameDisplay" style="display: none; margin-top: 12px; padding: 8px 12px; background: #f0f9ff; border-radius: 4px; font-size: 12px; color: #0369a1;"></div>
                    </div>
                    <p style="margin: 8px 0 0 0; font-size: 11px; color: #9ca3af;">Attach approval note or other relevant documents</p>
                </div>

                {{-- Error Message --}}
                <div id="passSlipErrorMessage" style="display: none; padding: 12px; background: #fee2e2; border-left: 3px solid #ef4444; border-radius: 6px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p id="passSlipErrorMessageText" style="margin: 0; color: #991b1b; font-size: 13px; line-height: 1.5;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px; background: #f9fafb;">
                <button type="button" class="modal-btn-ghost" onclick="closePassSlipModal()" style="padding: 10px 20px;">
                    Cancel
                </button>
                <button type="submit" class="modal-btn-primary" id="passSlipSubmitBtn" style="padding: 10px 24px; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Submit Pass Slip
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closePassSlipModal() {
    document.getElementById('filePassSlipModal').style.display = 'none';
    document.getElementById('passSlipForm').reset();
    document.getElementById('passSlipFileNameDisplay').style.display = 'none';
    document.getElementById('passSlipErrorMessage').style.display = 'none';
    document.body.style.overflow = '';
}

function openPassSlipModal() {
    document.getElementById('filePassSlipModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('passSlipDate').value = today;
}

function handlePassSlipFileSelect(input) {
    const file = input.files[0];
    const display = document.getElementById('passSlipFileNameDisplay');

    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showPassSlipError('File size must not exceed 5MB');
            input.value = '';
            display.style.display = 'none';
            return;
        }

        display.textContent = `📎 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        display.style.display = 'block';
    } else {
        display.style.display = 'none';
    }
}

function showPassSlipError(message) {
    const errorDiv = document.getElementById('passSlipErrorMessage');
    const errorText = document.getElementById('passSlipErrorMessageText');
    errorText.textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Character counter for reason
document.addEventListener('DOMContentLoaded', function() {
    const reasonField = document.getElementById('reason');
    const reasonCounter = document.getElementById('reasonCounter');

    if (reasonField && reasonCounter) {
        reasonField.addEventListener('input', function() {
            const length = this.value.length;
            reasonCounter.textContent = `${length} / 300`;
            if (length > 300) {
                reasonCounter.style.color = '#dc2626';
            } else {
                reasonCounter.style.color = '#9ca3af';
            }
        });
    }
});
</script>
