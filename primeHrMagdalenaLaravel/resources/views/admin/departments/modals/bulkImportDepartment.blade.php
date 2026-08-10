{{--
    Import Departments.

    Built on the same shell and vocabulary as addDepartment.blade.php: the form
    wraps `.adm-body` and `.adm-footer` as siblings, fields use `.adm-field`,
    and the accent is the brand rather than a hard-coded green. The footer used
    to sit *inside* .adm-body, which is why it needed a `.bim-footer` override
    to cancel the padding and border it should have had in the first place.
--}}
<x-adm-modal id="bulk-import-modal" close="closeBulkImportModal" eyebrow="DEPARTMENTS · BULK IMPORT" title="Import Departments">
    <x-slot:icon>
        <div class="adm-header-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
    </x-slot:icon>

    <form method="POST" action="{{ route('admin.departments.import') }}" enctype="multipart/form-data" id="bulk-import-form">
        @csrf
        <div class="adm-body">

            {{-- Step 1 — template --}}
            <div class="bim-step">
                <span class="bim-step-num">1</span>
                <div class="bim-step-text">
                    <p class="bim-step-title">Download the template</p>
                    <p class="bim-step-desc">Fill in your departments using the CSV template so the columns match.</p>
                </div>
                <a href="{{ route('admin.departments.template') }}" class="adm-btn-ghost bim-download-link">
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
                <div id="drop-zone" class="bim-drop-zone" role="button" tabindex="0"
                     onclick="document.getElementById('csv-file-input').click()"
                     onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();document.getElementById('csv-file-input').click();}"
                     ondragover="event.preventDefault(); this.classList.add('is-dragging')"
                     ondragleave="this.classList.remove('is-dragging')"
                     ondrop="handleDrop(event)">
                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="bim-drop-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p class="bim-drop-label" id="drop-label">Drag &amp; drop your CSV here</p>
                    <p class="bim-drop-sub">or click to browse &middot; .csv only</p>
                </div>
                <input type="file" id="csv-file-input" name="csv_file" accept=".csv" class="dep-hidden" onchange="handleFileSelect(this)">
                @error('csv_file')<span class="adm-field-err">{{ $message }}</span>@enderror
            </div>

            {{-- Column guide --}}
            <div class="bim-columns-box">
                <p class="bim-columns-label">Expected columns</p>
                <div class="bim-columns-grid">
                    @foreach(['code','name','head','personnel_count','status','description'] as $col)
                    <code class="bim-column-chip">{{ $col }}</code>
                    @endforeach
                </div>
                <p class="bim-columns-note">
                    <strong>status</strong> must be Active or Inactive &middot; <strong>description</strong> is optional
                </p>
            </div>

        </div>

        <div class="adm-footer">
            <button type="button" class="adm-btn-ghost" onclick="closeBulkImportModal()">Cancel</button>
            <button type="submit" class="adm-btn-primary" id="import-submit-btn" disabled>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Import Departments
            </button>
        </div>
    </form>
</x-adm-modal>

@push('scripts')
    @vite('resources/js/admin/departments/bulkImportDepartment.js')
@endpush
