<x-adm-modal id="bulk-import-designation-modal" close="closeBulkImportDesignationModal" eyebrow="DESIGNATIONS · BULK IMPORT" title="Import Designations">
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
                    <p class="bim-step1-desc">Use the CSV template to fill in your designation data correctly.</p>
                </div>
            </div>
            <a href="{{ route('admin.designations.template') }}" class="adm-btn-ghost bim-download-link">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Template
            </a>
        </div>

        {{-- Step 2: Upload File --}}
        <p class="bim-step2-label">STEP 2 — UPLOAD YOUR FILE</p>

        <form method="POST" action="{{ route('admin.designations.import') }}" enctype="multipart/form-data" id="bulk-import-designation-form">
            @csrf
            <div class="adm-field">
                <label>CSV File <span class="adm-req">*</span></label>
                <div id="desig-drop-zone" class="bim-drop-zone" onclick="document.getElementById('desig-csv-input').click()" ondragover="event.preventDefault();this.style.borderColor='#0b044d'" ondragleave="this.style.borderColor='#c7c5e8'" ondrop="handleDesigDrop(event)">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8f8daf" stroke-width="2" class="bim-drop-icon"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <p class="bim-drop-label" id="desig-drop-label">Drag & drop your CSV here</p>
                    <p class="bim-drop-sub">or click to browse · .csv files only</p>
                </div>
                <input type="file" id="desig-csv-input" name="csv_file" accept=".csv" class="dep-hidden" onchange="handleDesigFileSelect(this)">
            </div>

            {{-- Column Guide --}}
            <div class="bim-columns-box">
                <p class="bim-columns-label">EXPECTED COLUMNS</p>
                <div class="bim-columns-grid">
                    @foreach(['title','department_code','salary_grade','monthly_rate','employment_type','description'] as $col)
                    <div class="bim-column-chip">{{ $col }}</div>
                    @endforeach
                </div>
                <p class="bim-columns-note"><strong>department_code</strong> must match an existing department · employment_type: Permanent, Casual, Contractual, Job Order · monthly_rate and description are optional</p>
            </div>

            <div class="adm-footer bim-footer">
                <button type="button" class="adm-btn-ghost" onclick="closeBulkImportDesignationModal()">Cancel</button>
                <button type="submit" class="adm-btn-primary adm-accent-green" id="desig-import-submit-btn" disabled>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Import Designations
                </button>
            </div>
        </form>
    </div>
</x-adm-modal>

@push('scripts')
    @vite('resources/js/admin/departments/bulkImportDesignation.js')
@endpush
