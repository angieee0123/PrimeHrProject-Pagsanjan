{{--
    Import Designations.

    Same shell and vocabulary as addDesignation / addDepartment: the form wraps
    `.adm-body` and `.adm-footer` as siblings, fields use `.adm-field`, and the
    accent is the brand. Kept deliberately identical to
    bulkImportDepartment.blade.php — the two differ only in their route, their
    column list and their wording, so any visual difference between them would
    be accidental.
--}}
<x-adm-modal id="bulk-import-designation-modal" close="closeBulkImportDesignationModal" eyebrow="DESIGNATIONS · BULK IMPORT" title="Import Designations">
    <x-slot:icon>
        <div class="adm-header-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
    </x-slot:icon>

    <form method="POST" action="{{ route('admin.designations.import') }}" enctype="multipart/form-data" id="bulk-import-designation-form">
        @csrf
        <div class="adm-body">

            {{-- Step 1 — template --}}
            <div class="bim-step">
                <span class="bim-step-num">1</span>
                <div class="bim-step-text">
                    <p class="bim-step-title">Download the template</p>
                    <p class="bim-step-desc">Fill in your designations using the CSV template so the columns match.</p>
                </div>
                <a href="{{ route('admin.designations.template') }}" class="adm-btn-ghost bim-download-link">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Template
                </a>
            </div>

            {{-- Step 2 — file --}}
            <div class="adm-field">
                <label>
                    <span class="bim-step-num bim-step-num-inline">2</span>
                    Upload your CSV <span class="adm-req">*</span>
                </label>
                <div id="desig-drop-zone" class="bim-drop-zone" role="button" tabindex="0"
                     onclick="document.getElementById('desig-csv-input').click()"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('desig-csv-input').click();}"
                     ondragover="event.preventDefault(); this.classList.add('is-dragging')"
                     ondragleave="this.classList.remove('is-dragging')"
                     ondrop="handleDesigDrop(event)">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="bim-drop-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p class="bim-drop-label" id="desig-drop-label">Drag &amp; drop your CSV here</p>
                    <p class="bim-drop-sub">or click to browse &middot; .csv only</p>
                </div>
                <input type="file" id="desig-csv-input" name="csv_file" accept=".csv" class="dep-hidden" onchange="handleDesigFileSelect(this)">
                @error('csv_file')<span class="adm-field-err">{{ $message }}</span>@enderror
            </div>

            {{-- Column guide --}}
            <div class="bim-columns-box">
                <p class="bim-columns-label">Expected columns</p>
                <div class="bim-columns-grid">
                    @foreach(['title','department_code','salary_grade','monthly_rate','employment_type','description'] as $col)
                    <code class="bim-column-chip">{{ $col }}</code>
                    @endforeach
                </div>
                <p class="bim-columns-note">
                    <strong>department_code</strong> must match an existing department &middot;
                    <strong>employment_type</strong> one of Permanent, Temporary, Coterminous, Casual, Contractual, Job Order &middot;
                    <strong>monthly_rate</strong> and <strong>description</strong> are optional
                </p>
            </div>

        </div>

        <div class="adm-footer">
            <button type="button" class="adm-btn-ghost" onclick="closeBulkImportDesignationModal()">Cancel</button>
            <button type="submit" class="adm-btn-primary" id="desig-import-submit-btn" disabled>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Designations
            </button>
        </div>
    </form>
</x-adm-modal>

@push('scripts')
    @vite('resources/js/admin/departments/bulkImportDesignation.js')
@endpush
