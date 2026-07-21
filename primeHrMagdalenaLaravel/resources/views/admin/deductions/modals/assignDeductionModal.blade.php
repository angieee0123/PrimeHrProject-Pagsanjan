<x-modal-container id="assignDeductionModal" close="closeAssignDeductionModal"
                     title="Assign Deductions" subtitle="Assign multiple deduction types to an employee">
    <form id="assignDeductionForm" action="{{ route('admin.deductions.employee.bulk-assign') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Employee <span class="ded-required">*</span></label>
            <select name="employee_id" id="assignEmployee" class="form-input" required onchange="checkExistingDeductions()">
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

        <div id="existingDeductionsWarning" class="warning-box ded-hidden">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ded-min-w-16">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
                <strong>Existing Deductions:</strong>
                <p id="existingDeductionsList" class="ded-existing-list"></p>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">
                Deduction Types <span class="ded-required">*</span>
                <span id="selectedCount" class="ded-selected-count">(0 selected)</span>
            </label>
            <div class="checkbox-actions">
                <button type="button" class="btn-link" onclick="selectAllDeductions()">Select All</button>
                <button type="button" class="btn-link" onclick="deselectAllDeductions()">Deselect All</button>
                <button type="button" class="btn-link" onclick="selectMandatoryOnly()">Mandatory Only</button>
            </div>
            <div class="checkbox-group">
                @php
                    $deductionTypes = \App\Models\DeductionType::where('is_active', true)->orderBy('category')->orderBy('name')->get()->groupBy('category');
                @endphp
                @foreach($deductionTypes as $category => $types)
                    <div class="checkbox-category">
                        <p class="category-label">{{ $category }}</p>
                        @foreach($types as $type)
                            <label class="checkbox-label">
                                <input type="checkbox" name="deduction_types[]" value="{{ $type->id }}"
                                       data-category="{{ $type->category }}"
                                       data-computation="{{ $type->computation_type }}"
                                       data-code="{{ $type->code }}"
                                       onchange="handleCheckboxChange()">
                                <span class="checkbox-text">
                                    {{ $type->name }}
                                    <span class="ded-checkbox-value">({{ $type->code }})</span>
                                    @if($type->computation_type === 'PERCENTAGE')
                                        <span class="ded-checkbox-rate"> - {{ $type->percentage_rate }}%</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

        <div class="form-row">
            <div class="form-group ded-col">
                <label class="form-label">Start Date <span class="ded-required">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-input" required value="{{ date('Y-m-d') }}">
            </div>
            <div class="form-group ded-col">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" id="end_date" class="form-input">
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
            <button type="button" class="btn-cancel" onclick="closeAssignDeductionModal()">Cancel</button>
            <button type="submit" class="btn-submit">Assign Deductions</button>
        </div>
    </form>
</x-modal-container>

@push('scripts')
    @vite('resources/js/admin/deductions/assignDeductionModal.js')
@endpush
