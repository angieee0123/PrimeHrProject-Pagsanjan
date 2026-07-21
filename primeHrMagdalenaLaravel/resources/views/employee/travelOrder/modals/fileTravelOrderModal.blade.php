{{-- File Travel Order Modal --}}
<x-modal id="fileTravelOrderModal" close="closeTravelOrderModal" max-width="700px"
          eyebrow="NEW TRAVEL ORDER" title="File a Travel Order Request">
    <x-slot:subtitle>{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</x-slot:subtitle>
    <form id="travelOrderForm" method="POST" action="{{ route('travelorder.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="modal-body to-modal-body-scroll">

            {{-- Destination --}}
            <div class="form-field to-mb-20">
                <label class="to-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                        <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Destination <span class="to-required">*</span>
                </label>
                <input type="text" name="destination" id="destination" required placeholder="e.g., Manila City Hall, Quezon City" class="to-input-lg">
            </div>

            {{-- Purpose --}}
            <div class="form-field to-mb-20">
                <label class="to-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Purpose of Travel <span class="to-required">*</span>
                </label>
                <textarea name="purpose" id="purpose" rows="3" placeholder="Briefly describe the purpose of your travel..." required class="to-textarea"></textarea>
                <div class="to-flex-between-mt4">
                    <small class="to-text-muted-11">Be specific about the purpose</small>
                    <small id="purposeCounter" class="to-text-muted-11">0 / 300</small>
                </div>
            </div>

            {{-- Travel Date Range --}}
            <div class="to-panel">
                <label class="to-panel-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="to-icon-inline">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Travel Period
                </label>
                <div class="form-grid to-gap-12">
                    <div class="form-field">
                        <label class="to-sublabel">Departure Date <span class="to-required">*</span></label>
                        <input type="date" name="travel_date" id="travelDateFrom" required onchange="calculateTravelDuration()" class="to-input-md">
                    </div>
                    <div class="form-field">
                        <label class="to-sublabel">Return Date <span class="to-required">*</span></label>
                        <input type="date" name="return_date" id="travelDateTo" required onchange="calculateTravelDuration()" class="to-input-md">
                    </div>
                </div>

                {{-- Duration Display --}}
                <div class="to-days-box">
                    <div class="to-flex-between">
                        <span class="to-text-muted-12">Total Duration</span>
                        <div class="to-flex-gap-8">
                            <input type="number" name="duration" id="travelDuration" min="1" value="1" readonly class="to-days-input">
                            <span class="to-days-label">days</span>
                        </div>
                    </div>
                    <p class="to-hint">Includes all calendar days</p>
                </div>
            </div>

            {{-- Travel Details --}}
            <div class="to-panel">
                <label class="to-panel-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="to-icon-inline">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    Travel Details
                </label>

                <div class="form-field to-mb-12">
                    <label class="to-sublabel">Mode of Transportation</label>
                    <select name="transportation_mode" id="transportationMode" class="to-select-md">
                        <option value="">Select mode...</option>
                        <option value="Private Vehicle">Private Vehicle</option>
                        <option value="Government Vehicle">Government Vehicle</option>
                        <option value="Public Transportation">Public Transportation</option>
                        <option value="Air Travel">Air Travel</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-field">
                    <label class="to-sublabel">Estimated Budget (Optional)</label>
                    <div class="to-relative">
                        <span class="to-currency-symbol">₱</span>
                        <input type="number" name="estimated_budget" id="estimatedBudget" min="0" step="0.01" placeholder="0.00" class="to-input-currency">
                    </div>
                </div>
            </div>

            {{-- Travel Companions --}}
            <div class="form-field to-mb-20 to-relative">
                <label class="to-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Travel Companions (Optional)
                </label>
                <div id="companionSelectBox" onclick="toggleCompanionDropdown(event)" class="to-select-box">
                    <span id="companionPlaceholder" class="to-placeholder">Select employees joining this travel...</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" class="to-select-arrow">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                <div id="companionDropdown" class="to-dropdown to-hidden">
                    <div class="to-dropdown-search-wrap">
                        <input type="text" id="companionSearch" oninput="filterCompanionOptions()" placeholder="Search by name or employee ID..." autocomplete="off" class="to-input-md">
                    </div>
                    <div id="companionOptionsList" class="to-options-list">
                        @forelse($companionOptions ?? [] as $companionOption)
                        @php
                            // employees.photo may hold a ready URL path ("/storage/...") or a bare storage path
                            $companionPhotoUrl = $companionOption->photo
                                ? (\Illuminate\Support\Str::startsWith($companionOption->photo, ['/', 'http']) ? $companionOption->photo : asset('storage/' . $companionOption->photo))
                                : '';
                        @endphp
                        <div class="companion-option"
                            data-id="{{ $companionOption->id }}"
                            data-name="{{ $companionOption->first_name }} {{ $companionOption->last_name }}"
                            data-empid="{{ $companionOption->employee_id }}"
                            data-photo="{{ $companionPhotoUrl }}"
                            onclick="toggleCompanionSelection(this)">
                            @if($companionPhotoUrl)
                                <img src="{{ $companionPhotoUrl }}" alt="{{ $companionOption->first_name }}" class="to-avatar-sm">
                            @else
                                <div class="to-avatar-initials-sm">{{ strtoupper(substr($companionOption->first_name, 0, 1) . substr($companionOption->last_name, 0, 1)) }}</div>
                            @endif
                            <div class="to-flex-1-minw0">
                                <p class="to-option-name">{{ $companionOption->first_name }} {{ $companionOption->last_name }}</p>
                                <p class="to-option-id">{{ $companionOption->employee_id }}</p>
                            </div>
                            <span class="companion-check to-hidden">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                        </div>
                        @empty
                        <div class="to-empty-cell">No other employees available</div>
                        @endforelse
                    </div>
                </div>
                <div id="companionHiddenInputs"></div>
                <p class="to-hint">Selected employees will be notified and must accept your request before this travel order can be forwarded to HR.</p>
            </div>

            {{-- Supporting Document --}}
            <div class="form-field to-mb-20">
                <label class="to-field-label">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/>
                    </svg>
                    Supporting Document (Optional)
                </label>
                <div class="to-dropzone" id="travelAttachmentDropZone">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" class="to-icon-center">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <input type="file" name="attachment" id="travelAttachment" accept=".pdf,.jpg,.jpeg,.png" class="to-hidden" onchange="handleTravelFileSelect(this)">
                    <label for="travelAttachment" class="to-cursor-pointer">
                        <p class="to-upload-title">Click to upload or drag and drop</p>
                        <p class="to-hint-tight">PDF, JPG, PNG (Max 5MB)</p>
                    </label>
                    <div id="travelFileNameDisplay" class="to-file-name-box to-hidden"></div>
                </div>
                <p class="to-hint">Attach invitation letter, agenda, or other relevant documents</p>
            </div>

            {{-- Error Message --}}
            <div id="travelErrorMessage" class="to-error-box to-hidden">
                <div class="to-flex-start-8">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" class="to-icon-top">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="15" y1="9" x2="9" y2="15"/>
                        <line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                    <p id="travelErrorMessageText" class="to-error-text"></p>
                </div>
            </div>
        </div>
        <div class="modal-footer to-modal-footer">
            <button type="button" class="modal-btn-ghost to-btn-pad-sm" onclick="closeTravelOrderModal()">
                Cancel
            </button>
            <button type="submit" class="modal-btn-primary to-btn-submit" id="travelSubmitBtn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Submit Travel Order
            </button>
        </div>
    </form>
</x-modal>
