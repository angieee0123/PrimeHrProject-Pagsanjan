{{-- File Travel Order Modal --}}
<div class="modal-overlay" id="fileTravelOrderModal" onclick="closeTravelOrderModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 700px;">
        <form id="travelOrderForm" method="POST" action="{{ route('travelorder.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <div>
                    <span class="modal-eyebrow">NEW TRAVEL ORDER</span>
                    <h3 class="modal-title">File a Travel Order Request</h3>
                    <p class="modal-sub">{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</p>
                </div>
                <button type="button" class="modal-close" onclick="closeTravelOrderModal()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">

                {{-- Destination --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        Destination <span style="color: #8e1e18;">*</span>
                    </label>
                    <input type="text" name="destination" id="destination" required placeholder="e.g., Manila City Hall, Quezon City" style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px; font-family: inherit;">
                </div>

                {{-- Purpose --}}
                <div class="form-field" style="margin-bottom: 20px;">
                    <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Purpose of Travel <span style="color: #8e1e18;">*</span>
                    </label>
                    <textarea name="purpose" id="purpose" rows="3" placeholder="Briefly describe the purpose of your travel..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical; line-height: 1.6;"></textarea>
                    <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                        <small style="color: #9ca3af; font-size: 11px;">Be specific about the purpose</small>
                        <small id="purposeCounter" style="color: #9ca3af; font-size: 11px;">0 / 300</small>
                    </div>
                </div>

                {{-- Travel Date Range --}}
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 12px; font-size: 13px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Travel Period
                    </label>
                    <div class="form-grid" style="gap: 12px;">
                        <div class="form-field">
                            <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Departure Date <span style="color: #8e1e18;">*</span></label>
                            <input type="date" name="travel_date" id="travelDateFrom" required onchange="calculateTravelDuration()" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                        </div>
                        <div class="form-field">
                            <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Return Date <span style="color: #8e1e18;">*</span></label>
                            <input type="date" name="return_date" id="travelDateTo" required onchange="calculateTravelDuration()" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                        </div>
                    </div>

                    {{-- Duration Display --}}
                    <div style="margin-top: 12px; padding: 12px; background: white; border-radius: 6px; border: 2px dashed #d1d5db;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-size: 12px; color: #6b7280;">Total Duration</span>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <input type="number" name="duration" id="travelDuration" min="1" value="1" readonly style="width: 70px; padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 4px; font-size: 14px; font-weight: 700; color: #0b044d; text-align: center; background: #f9fafb;">
                                <span style="font-size: 13px; color: #6b7280; font-weight: 500;">days</span>
                            </div>
                        </div>
                        <p style="margin: 8px 0 0 0; font-size: 11px; color: #9ca3af;">Includes all calendar days</p>
                    </div>
                </div>

                {{-- Travel Details --}}
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                    <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 12px; font-size: 13px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-right: 4px;">
                            <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                            <path d="M2 17l10 5 10-5"/>
                            <path d="M2 12l10 5 10-5"/>
                        </svg>
                        Travel Details
                    </label>
                    
                    <div class="form-field" style="margin-bottom: 12px;">
                        <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Mode of Transportation</label>
                        <select name="transportation_mode" id="transportationMode" style="width: 100%; padding: 10px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit; background: white; cursor: pointer;">
                            <option value="">Select mode...</option>
                            <option value="Private Vehicle">Private Vehicle</option>
                            <option value="Government Vehicle">Government Vehicle</option>
                            <option value="Public Transportation">Public Transportation</option>
                            <option value="Air Travel">Air Travel</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-field">
                        <label style="font-size: 12px; color: #6b7280; margin-bottom: 6px; display: block;">Estimated Budget (Optional)</label>
                        <div style="position: relative;">
                            <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: 13px; font-weight: 600;">₱</span>
                            <input type="number" name="estimated_budget" id="estimatedBudget" min="0" step="0.01" placeholder="0.00" style="width: 100%; padding: 10px 10px 10px 28px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
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
                    <div style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 20px; text-align: center; background: #fafafa; transition: all 0.2s;" id="travelAttachmentDropZone">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" style="margin: 0 auto 12px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <input type="file" name="attachment" id="travelAttachment" accept=".pdf,.jpg,.jpeg,.png" style="display: none;" onchange="handleTravelFileSelect(this)">
                        <label for="travelAttachment" style="cursor: pointer;">
                            <p style="margin: 0 0 4px 0; font-size: 13px; color: #374151; font-weight: 500;">Click to upload or drag and drop</p>
                            <p style="margin: 0; font-size: 11px; color: #9ca3af;">PDF, JPG, PNG (Max 5MB)</p>
                        </label>
                        <div id="travelFileNameDisplay" style="display: none; margin-top: 12px; padding: 8px 12px; background: #f0f9ff; border-radius: 4px; font-size: 12px; color: #0369a1;"></div>
                    </div>
                    <p style="margin: 8px 0 0 0; font-size: 11px; color: #9ca3af;">Attach invitation letter, agenda, or other relevant documents</p>
                </div>

                {{-- Error Message --}}
                <div id="travelErrorMessage" style="display: none; padding: 12px; background: #fee2e2; border-left: 3px solid #ef4444; border-radius: 6px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: start; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="15" y1="9" x2="9" y2="15"/>
                            <line x1="9" y1="9" x2="15" y2="15"/>
                        </svg>
                        <p id="travelErrorMessageText" style="margin: 0; color: #991b1b; font-size: 13px; line-height: 1.5;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #e5e7eb; padding: 16px 24px; background: #f9fafb;">
                <button type="button" class="modal-btn-ghost" onclick="closeTravelOrderModal()" style="padding: 10px 20px;">
                    Cancel
                </button>
                <button type="submit" class="modal-btn-primary" id="travelSubmitBtn" style="padding: 10px 24px; display: flex; align-items: center; gap: 8px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                    </svg>
                    Submit Travel Order
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function closeTravelOrderModal() {
    document.getElementById('fileTravelOrderModal').style.display = 'none';
    document.getElementById('travelOrderForm').reset();
    document.getElementById('travelFileNameDisplay').style.display = 'none';
    document.getElementById('travelErrorMessage').style.display = 'none';
    document.body.style.overflow = '';
}

function openTravelOrderModal() {
    document.getElementById('fileTravelOrderModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('travelDateFrom').min = today;
    document.getElementById('travelDateTo').min = today;
}

function calculateTravelDuration() {
    const fromDate = document.getElementById('travelDateFrom').value;
    const toDate = document.getElementById('travelDateTo').value;
    
    if (fromDate && toDate) {
        const from = new Date(fromDate);
        const to = new Date(toDate);
        
        if (to >= from) {
            const diffTime = Math.abs(to - from);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('travelDuration').value = diffDays;
        } else {
            document.getElementById('travelDuration').value = 1;
            showTravelError('Return date must be after departure date');
        }
    }
}

function handleTravelFileSelect(input) {
    const file = input.files[0];
    const display = document.getElementById('travelFileNameDisplay');
    
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showTravelError('File size must not exceed 5MB');
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

function showTravelError(message) {
    const errorDiv = document.getElementById('travelErrorMessage');
    const errorText = document.getElementById('travelErrorMessageText');
    errorText.textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Character counter for purpose
document.addEventListener('DOMContentLoaded', function() {
    const purposeField = document.getElementById('purpose');
    const purposeCounter = document.getElementById('purposeCounter');
    
    if (purposeField && purposeCounter) {
        purposeField.addEventListener('input', function() {
            const length = this.value.length;
            purposeCounter.textContent = `${length} / 300`;
            if (length > 300) {
                purposeCounter.style.color = '#dc2626';
            } else {
                purposeCounter.style.color = '#9ca3af';
            }
        });
    }
});
</script>
