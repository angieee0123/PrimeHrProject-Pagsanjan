<x-adm-modal id="bulk-import-modal" close="closeBulkImportModal" eyebrow="DEPARTMENTS · BULK IMPORT" title="Import Departments">
    <x-slot:icon>
        <div class="adm-header-icon adm-accent-green">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
        </div>
    </x-slot:icon>

    <div class="adm-body">

        {{-- Step 1: Download Template --}}
        <div class="bim-step1-box">
            <div class="bim-step1-left">
                <div class="bim-step1-icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <p class="bim-step1-title">Step 1 — Download Template</p>
                    <p class="bim-step1-desc">Use the CSV template to fill in your department data correctly.</p>
                </div>
            </div>
            <a href="{{ route('admin.departments.template') }}" class="adm-btn-ghost bim-download-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Template
            </a>
        </div>

        {{-- Step 2: Upload File --}}
        <p class="bim-step2-label">STEP 2 — UPLOAD YOUR FILE</p>

        <form method="POST" action="{{ route('admin.departments.import') }}" enctype="multipart/form-data" id="bulk-import-form">
            @csrf
            <div class="adm-field">
                <label>CSV File <span class="adm-req">*</span></label>
                <div id="drop-zone" class="bim-drop-zone" onclick="document.getElementById('csv-file-input').click()" ondragover="event.preventDefault();this.style.borderColor='#0b044d'" ondragleave="this.style.borderColor='#c7c5e8'" ondrop="handleDrop(event)">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8f8daf" stroke-width="2" class="bim-drop-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p class="bim-drop-label" id="drop-label">Drag & drop your CSV here</p>
                    <p class="bim-drop-sub">or click to browse · .csv files only</p>
                </div>
                <input type="file" id="csv-file-input" name="csv_file" accept=".csv" class="dep-hidden" onchange="handleFileSelect(this)">
            </div>

            {{-- Column Guide --}}
            <div class="bim-columns-box">
                <p class="bim-columns-label">EXPECTED COLUMNS</p>
                <div class="bim-columns-grid">
                    @foreach(['code','name','head','personnel_count','status','description'] as $col)
                    <div class="bim-column-chip">{{ $col }}</div>
                    @endforeach
                </div>
                <p class="bim-columns-note">status must be <strong>Active</strong> or <strong>Inactive</strong> · description is optional</p>
            </div>

            <div class="adm-footer bim-footer">
                <button type="button" class="adm-btn-ghost" onclick="closeBulkImportModal()">Cancel</button>
                <button type="submit" class="adm-btn-primary adm-accent-green" id="import-submit-btn" disabled>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import Departments
                </button>
            </div>
        </form>
    </div>
</x-adm-modal>

@push('scripts')
    @vite('resources/js/admin/departments/bulkImportDepartment.js')
@endpush
