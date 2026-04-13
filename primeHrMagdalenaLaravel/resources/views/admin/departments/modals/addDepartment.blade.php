<div id="add-dept-modal" class="adm-overlay" onclick="closeAddModal()">
    <div class="adm-box" onclick="event.stopPropagation()">

        <div class="adm-header">
            <div class="adm-header-left">
                <div class="adm-header-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <span class="adm-eyebrow">DEPARTMENTS · NEW</span>
                    <h3 class="adm-title">Register Department</h3>
                </div>
            </div>
            <button class="adm-close" onclick="closeAddModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <div class="adm-body">

                <div class="adm-row-2">
                    <div class="adm-field">
                        <label>Department Code <span class="adm-req">*</span></label>
                        <input type="text" name="code" placeholder="e.g. MHO" value="{{ old('code') }}" required style="text-transform:uppercase">
                        @error('code')<span class="adm-field-err">{{ $message }}</span>@enderror
                    </div>
                    <div class="adm-field">
                        <label>Status <span class="adm-req">*</span></label>
                        <select name="status" required>
                            <option value="Active"   {{ old('status','Active') == 'Active'   ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="adm-field">
                    <label>Department / Office Name <span class="adm-req">*</span></label>
                    <input type="text" name="name" placeholder="e.g. Municipal Health Office" value="{{ old('name') }}" required>
                    @error('name')<span class="adm-field-err">{{ $message }}</span>@enderror
                </div>

                <div class="adm-field">
                    <label>Department Head <span class="adm-req">*</span></label>
                    <input type="text" name="head" placeholder="e.g. Municipal Health Officer" value="{{ old('head') }}" required>
                    @error('head')<span class="adm-field-err">{{ $message }}</span>@enderror
                </div>

                <div class="adm-field">
                    <label>Personnel Count</label>
                    <input type="number" name="personnel_count" placeholder="e.g. 38" value="{{ old('personnel_count', 0) }}" min="0">
                </div>

                <div class="adm-field">
                    <label>Description <span class="adm-opt">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Brief description of this department...">{{ old('description') }}</textarea>
                </div>

            </div>

            <div class="adm-footer">
                <button type="button" class="adm-btn-ghost" onclick="closeAddModal()">Cancel</button>
                <button type="submit" class="adm-btn-primary">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Save Department
                </button>
            </div>
        </form>
    </div>
</div>
