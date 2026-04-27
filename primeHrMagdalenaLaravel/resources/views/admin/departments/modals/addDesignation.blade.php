<div id="add-designation-modal" class="adm-overlay" onclick="closeAddDesignationModal()">
    <div class="adm-box" onclick="event.stopPropagation()">

        <div class="adm-header">
            <div class="adm-header-left">
                <div class="adm-header-icon" style="background:#1a0f6e;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
                </div>
                <div>
                    <span class="adm-eyebrow">DESIGNATIONS · NEW</span>
                    <h3 class="adm-title">Add Designation</h3>
                </div>
            </div>
            <button class="adm-close" onclick="closeAddDesignationModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.designations.store') }}">
            @csrf
            <div class="adm-body">

                <div class="adm-field">
                    <label>Designation Title <span class="adm-req">*</span></label>
                    <input type="text" name="title" placeholder="e.g. Municipal Health Officer" value="{{ old('title') }}" required>
                    @error('title')<span class="adm-field-err">{{ $message }}</span>@enderror
                </div>

                <div class="adm-field">
                    <label>Department <span class="adm-req">*</span></label>
                    <select name="department_id" required>
                        <option value="">— Select Department —</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<span class="adm-field-err">{{ $message }}</span>@enderror
                </div>

                <div class="adm-row-2">
                    <div class="adm-field">
                        <label>Salary Grade <span class="adm-opt">(optional)</span></label>
                        <input type="text" name="salary_grade" placeholder="e.g. SG-24" value="{{ old('salary_grade') }}">
                    </div>
                    <div class="adm-field">
                        <label>Monthly Rate of Pay <span class="adm-opt">(optional)</span></label>
                        <input type="number" name="monthly_rate" placeholder="e.g. 35000" value="{{ old('monthly_rate') }}" min="0" step="0.01">
                    </div>
                </div>

                <div class="adm-field">
                    <label>Employment Type <span class="adm-opt">(optional)</span></label>
                    <select name="employment_type">
                        <option value="">— Select —</option>
                        <option value="Permanent"   {{ old('employment_type') == 'Permanent'   ? 'selected' : '' }}>Permanent</option>
                        <option value="Casual"      {{ old('employment_type') == 'Casual'      ? 'selected' : '' }}>Casual</option>
                        <option value="Contractual" {{ old('employment_type') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                        <option value="Job Order"   {{ old('employment_type') == 'Job Order'   ? 'selected' : '' }}>Job Order</option>
                    </select>
                </div>

                <div class="adm-field">
                    <label>Description <span class="adm-opt">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Brief description of this designation...">{{ old('description') }}</textarea>
                </div>

            </div>

            <div class="adm-footer">
                <button type="button" class="adm-btn-ghost" onclick="closeAddDesignationModal()">Cancel</button>
                <button type="submit" class="adm-btn-primary" style="background:#1a0f6e;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Save Designation
                </button>
            </div>
        </form>
    </div>
</div>
