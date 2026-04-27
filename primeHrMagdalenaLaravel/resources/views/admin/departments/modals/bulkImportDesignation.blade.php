<div id="bulk-import-designation-modal" class="adm-overlay" onclick="closeBulkImportDesignationModal()">
    <div class="adm-box" onclick="event.stopPropagation()">

        <div class="adm-header">
            <div class="adm-header-left">
                <div class="adm-header-icon" style="background:#15803d;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                </div>
                <div>
                    <span class="adm-eyebrow">DESIGNATIONS · BULK IMPORT</span>
                    <h3 class="adm-title">Import Designations</h3>
                </div>
            </div>
            <button class="adm-close" onclick="closeBulkImportDesignationModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="adm-body">

            {{-- Step 1: Download Template --}}
            <div style="background:#f0fdf4;border:1.5px solid #bbf7d0;border-radius:10px;padding:16px 18px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:38px;height:38px;border-radius:9px;background:#15803d;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <div>
                        <p style="font-size:13px;font-weight:700;color:#15803d;margin-bottom:2px;">Step 1 — Download Template</p>
                        <p style="font-size:12px;color:#6b6a8a;">Use the CSV template to fill in your designation data correctly.</p>
                    </div>
                </div>
                <a href="{{ route('admin.designations.template') }}" class="adm-btn-ghost" style="white-space:nowrap;text-decoration:none;display:flex;align-items:center;gap:6px;font-size:12.5px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download Template
                </a>
            </div>

            {{-- Step 2: Upload File --}}
            <p style="font-size:12px;font-weight:700;color:#0b044d;margin-bottom:8px;letter-spacing:.4px;">STEP 2 — UPLOAD YOUR FILE</p>

            <form method="POST" action="{{ route('admin.designations.import') }}" enctype="multipart/form-data" id="bulk-import-designation-form">
                @csrf
                <div class="adm-field">
                    <label>CSV File <span class="adm-req">*</span></label>
                    <div id="desig-drop-zone" style="border:2px dashed #c7c5e8;border-radius:10px;padding:28px;text-align:center;cursor:pointer;transition:border-color .2s;" onclick="document.getElementById('desig-csv-input').click()" ondragover="event.preventDefault();this.style.borderColor='#0b044d'" ondragleave="this.style.borderColor='#c7c5e8'" ondrop="handleDesigDrop(event)">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" style="margin-bottom:8px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        <p style="font-size:13px;font-weight:600;color:#0b044d;margin-bottom:4px;" id="desig-drop-label">Drag & drop your CSV here</p>
                        <p style="font-size:12px;color:#9999bb;">or click to browse · .csv files only</p>
                    </div>
                    <input type="file" id="desig-csv-input" name="csv_file" accept=".csv" style="display:none;" onchange="handleDesigFileSelect(this)">
                </div>

                {{-- Column Guide --}}
                <div style="background:#f7f6ff;border-radius:10px;padding:14px 16px;margin-top:4px;">
                    <p style="font-size:11px;font-weight:700;color:#0b044d;margin-bottom:8px;letter-spacing:.4px;">EXPECTED COLUMNS</p>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;">
                        @foreach(['title','department_code','salary_grade','monthly_rate','employment_type','description'] as $col)
                        <div style="background:#fff;border-radius:6px;padding:6px 10px;font-size:11.5px;font-weight:600;color:#1a0f6e;border:1px solid #e8e6f8;">{{ $col }}</div>
                        @endforeach
                    </div>
                    <p style="font-size:11px;color:#9999bb;margin-top:8px;"><strong>department_code</strong> must match an existing department · employment_type: Permanent, Casual, Contractual, Job Order · monthly_rate and description are optional</p>
                </div>

                <div class="adm-footer" style="margin-top:16px;padding:0;">
                    <button type="button" class="adm-btn-ghost" onclick="closeBulkImportDesignationModal()">Cancel</button>
                    <button type="submit" class="adm-btn-primary" style="background:#15803d;" id="desig-import-submit-btn" disabled>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Import Designations
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openBulkImportDesignationModal()  { document.getElementById('bulk-import-designation-modal').classList.add('open');  document.body.style.overflow = 'hidden'; }
function closeBulkImportDesignationModal() { document.getElementById('bulk-import-designation-modal').classList.remove('open'); document.body.style.overflow = ''; }

function handleDesigFileSelect(input) {
    if (input.files.length) updateDesigDropZone(input.files[0].name);
}

function handleDesigDrop(e) {
    e.preventDefault();
    document.getElementById('desig-drop-zone').style.borderColor = '#c7c5e8';
    const file = e.dataTransfer.files[0];
    if (file && file.name.endsWith('.csv')) {
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('desig-csv-input').files = dt.files;
        updateDesigDropZone(file.name);
    }
}

function updateDesigDropZone(name) {
    document.getElementById('desig-drop-label').textContent = '✓ ' + name;
    document.getElementById('desig-drop-zone').style.borderColor = '#15803d';
    document.getElementById('desig-import-submit-btn').disabled = false;
}
</script>
