{{-- File Travel Order Modal --}}
<x-modal id="fileTravelOrderModal" close="closeTravelOrderModal" max-width="700px"
          eyebrow="NEW TRAVEL ORDER" title="File a Travel Order Request">
    <x-slot:subtitle>{{ auth()->user()->employee->first_name ?? 'Employee' }} {{ auth()->user()->employee->last_name ?? '' }} · {{ auth()->user()->employee->employee_id ?? '' }}</x-slot:subtitle>
    <form id="travelOrderForm" method="POST" action="{{ route('travelorder.store') }}" enctype="multipart/form-data">
        @csrf
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

            {{-- Travel Companions --}}
            <div class="form-field" style="margin-bottom: 20px; position: relative;">
                <label style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #0b044d; margin-bottom: 8px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                    </svg>
                    Travel Companions (Optional)
                </label>
                <div id="companionSelectBox" onclick="toggleCompanionDropdown(event)" style="min-height: 48px; width: 100%; border: 2px solid #e5e7eb; border-radius: 8px; padding: 6px 36px 6px 8px; display: flex; flex-wrap: wrap; gap: 6px; align-items: center; cursor: pointer; background: white; position: relative;">
                    <span id="companionPlaceholder" style="color: #9ca3af; font-size: 13px; padding: 4px 6px;">Select employees joining this travel...</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                        <polyline points="6 9 12 15 18 9"/>
                    </svg>
                </div>
                <div id="companionDropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 50; background: white; border: 2px solid #e5e7eb; border-radius: 8px; margin-top: 4px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); overflow: hidden;">
                    <div style="padding: 8px; border-bottom: 1px solid #f3f4f6;">
                        <input type="text" id="companionSearch" oninput="filterCompanionOptions()" placeholder="Search by name or employee ID..." autocomplete="off" style="width: 100%; padding: 8px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; font-family: inherit;">
                    </div>
                    <div id="companionOptionsList" style="max-height: 210px; overflow-y: auto;">
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
                            onclick="toggleCompanionSelection(this)"
                            style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; cursor: pointer; transition: background 0.15s;"
                            onmouseover="this.style.background='#f7f6ff'" onmouseout="this.style.background=this.classList.contains('selected') ? '#f0f9ff' : 'white'">
                            @if($companionPhotoUrl)
                                <img src="{{ $companionPhotoUrl }}" alt="{{ $companionOption->first_name }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; border: 1px solid #e5e7eb;">
                            @else
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #0b044d, #4338ca); color: white; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">{{ strtoupper(substr($companionOption->first_name, 0, 1) . substr($companionOption->last_name, 0, 1)) }}</div>
                            @endif
                            <div style="flex: 1; min-width: 0;">
                                <p style="margin: 0; font-size: 13px; font-weight: 600; color: #0b044d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $companionOption->first_name }} {{ $companionOption->last_name }}</p>
                                <p style="margin: 0; font-size: 11px; color: #9ca3af;">{{ $companionOption->employee_id }}</p>
                            </div>
                            <span class="companion-check" style="display: none; color: #0369a1; flex-shrink: 0;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            </span>
                        </div>
                        @empty
                        <div style="padding: 16px; text-align: center; color: #9ca3af; font-size: 13px;">No other employees available</div>
                        @endforelse
                    </div>
                </div>
                <div id="companionHiddenInputs"></div>
                <p style="margin: 8px 0 0 0; font-size: 11px; color: #9ca3af;">Selected employees will be notified and must accept your request before this travel order can be forwarded to HR.</p>
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
</x-modal>

<script>
function closeTravelOrderModal() {
    document.getElementById('fileTravelOrderModal').style.display = 'none';
    document.getElementById('travelOrderForm').reset();
    document.getElementById('travelFileNameDisplay').style.display = 'none';
    document.getElementById('travelErrorMessage').style.display = 'none';
    document.body.style.overflow = '';
    clearCompanionSelection();
    closeCompanionDropdown();
}

// ===== Travel companions multi-select =====
let selectedCompanions = {}; // id -> { name, empid, photo }

function toggleCompanionDropdown(event) {
    // Ignore clicks on chip remove buttons
    if (event.target.closest('.companion-chip-remove')) return;
    const dropdown = document.getElementById('companionDropdown');
    const isOpen = dropdown.style.display === 'block';
    dropdown.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) {
        const search = document.getElementById('companionSearch');
        search.value = '';
        filterCompanionOptions();
        search.focus();
    }
}

function closeCompanionDropdown() {
    document.getElementById('companionDropdown').style.display = 'none';
}

function filterCompanionOptions() {
    const query = document.getElementById('companionSearch').value.toLowerCase().trim();
    document.querySelectorAll('.companion-option').forEach(option => {
        const haystack = (option.dataset.name + ' ' + option.dataset.empid).toLowerCase();
        option.style.display = haystack.includes(query) ? 'flex' : 'none';
    });
}

function toggleCompanionSelection(option) {
    const id = option.dataset.id;
    if (selectedCompanions[id]) {
        delete selectedCompanions[id];
        option.classList.remove('selected');
        option.style.background = 'white';
        option.querySelector('.companion-check').style.display = 'none';
    } else {
        selectedCompanions[id] = {
            name: option.dataset.name,
            empid: option.dataset.empid,
            photo: option.dataset.photo
        };
        option.classList.add('selected');
        option.style.background = '#f0f9ff';
        option.querySelector('.companion-check').style.display = 'inline-flex';
    }
    renderCompanionChips();
}

function removeCompanion(id) {
    delete selectedCompanions[id];
    const option = document.querySelector(`.companion-option[data-id="${id}"]`);
    if (option) {
        option.classList.remove('selected');
        option.style.background = 'white';
        option.querySelector('.companion-check').style.display = 'none';
    }
    renderCompanionChips();
}

function clearCompanionSelection() {
    selectedCompanions = {};
    document.querySelectorAll('.companion-option').forEach(option => {
        option.classList.remove('selected');
        option.style.background = 'white';
        option.querySelector('.companion-check').style.display = 'none';
    });
    renderCompanionChips();
}

function companionAvatarHtml(companion) {
    if (companion.photo) {
        return `<img src="${companion.photo}" alt="" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;">`;
    }
    const initials = companion.name.split(' ').filter(Boolean).map(part => part[0]).slice(0, 2).join('').toUpperCase();
    return `<span style="width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg, #0b044d, #4338ca); color: white; display: inline-flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 700;">${initials}</span>`;
}

function renderCompanionChips() {
    const box = document.getElementById('companionSelectBox');
    const placeholder = document.getElementById('companionPlaceholder');
    const hiddenInputs = document.getElementById('companionHiddenInputs');

    box.querySelectorAll('.companion-chip').forEach(chip => chip.remove());
    hiddenInputs.innerHTML = '';

    const ids = Object.keys(selectedCompanions);
    placeholder.style.display = ids.length ? 'none' : 'inline';

    ids.forEach(id => {
        const companion = selectedCompanions[id];

        const chip = document.createElement('span');
        chip.className = 'companion-chip';
        chip.style.cssText = 'display: inline-flex; align-items: center; gap: 6px; background: #eef2ff; border: 1px solid #d9d9ee; border-radius: 999px; padding: 3px 8px 3px 4px; font-size: 12px; font-weight: 600; color: #0b044d;';
        chip.innerHTML = companionAvatarHtml(companion) +
            `<span>${companion.name}</span>` +
            `<button type="button" class="companion-chip-remove" onclick="removeCompanion('${id}')" style="border: none; background: none; cursor: pointer; color: #6b7280; display: inline-flex; padding: 0;" title="Remove">` +
            `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        box.insertBefore(chip, box.lastElementChild);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'companions[]';
        input.value = id;
        hiddenInputs.appendChild(input);
    });
}

// Close the companion dropdown when clicking anywhere else in the modal
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('companionDropdown');
    if (!dropdown || dropdown.style.display !== 'block') return;
    if (!e.target.closest('#companionDropdown') && !e.target.closest('#companionSelectBox')) {
        closeCompanionDropdown();
    }
});

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
