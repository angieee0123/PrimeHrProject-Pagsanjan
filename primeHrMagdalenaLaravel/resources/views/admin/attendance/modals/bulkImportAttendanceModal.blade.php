<!-- Bulk Import Attendance Modal — same shell as Personnel's bulkImportModal -->
<div id="bulkImportAttendanceModal" style="display:none; position:fixed; inset:0; background:rgba(15,12,40,0.42); -webkit-backdrop-filter:blur(8px) saturate(150%); backdrop-filter:blur(8px) saturate(150%); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:20px; width:100%; max-width:600px; box-shadow:0 20px 50px rgba(15,23,42,.16), 0 2px 10px rgba(15,23,42,.05);">
        <div style="padding:24px; border-bottom:1.5px solid var(--gp-bg-tint-2);">
            <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div>
                    <h3 style="margin:0 0 4px; font-size:18px; font-weight:700; color:var(--gp-pri);">Bulk Import Attendance</h3>
                    <p style="margin:0; font-size:13px; color:var(--gp-text-mid);">Upload a CSV file to add many attendance records at once</p>
                </div>
                <button onclick="closeBulkImportAttendanceModal()" style="background:none; border:none; border-radius:9px; font-size:28px; color:var(--gp-text-mid); cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; transition:background .2s cubic-bezier(.4,0,.2,1);">&times;</button>
            </div>
        </div>

        <div style="padding:24px;">
            <div style="background:var(--gp-bg-tint-2); border:1.5px solid var(--theme-neutral-300); border-radius:14px; padding:16px; margin-bottom:20px;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                    <div style="width:40px; height:40px; background:var(--gp-pri); border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                            <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                            <polyline points="13 2 13 9 20 9"/>
                        </svg>
                    </div>
                    <div>
                        <h4 style="margin:0 0 4px; font-size:14px; font-weight:700; color:var(--gp-pri);">Download Template First</h4>
                        <p style="margin:0; font-size:12px; color:var(--gp-text-mid);">Use our template to ensure proper formatting</p>
                    </div>
                </div>
                <button onclick="downloadAttendanceTemplate()" style="width:100%; padding:10px; background:var(--gp-pri); color:#fff; border:none; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display','SF Pro Text','Helvetica Neue',Arial,sans-serif; display:flex; align-items:center; justify-content:center; gap:8px; transition:background .2s cubic-bezier(.4,0,.2,1);">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download CSV Template
                </button>
            </div>

            <form id="bulkImportAttendanceForm" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:20px;">
                    <label style="display:block; font-size:12px; font-weight:600; color:var(--gp-pri); margin-bottom:8px;">Upload CSV File <span style="color:#d5433c;">*</span></label>
                    <div id="attendanceDropZone" style="border:2px dashed var(--theme-neutral-300); border-radius:14px; padding:32px; text-align:center; cursor:pointer; transition:border-color .2s cubic-bezier(.4,0,.2,1), background .2s cubic-bezier(.4,0,.2,1); background:var(--gp-bg-tint);">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#8f8daf" stroke-width="1.5" style="margin:0 auto 12px;">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <p style="margin:0 0 8px; font-size:14px; font-weight:600; color:var(--gp-pri);">Drop your CSV file here or click to browse</p>
                        <p style="margin:0; font-size:12px; color:var(--gp-text-soft);">Maximum file size: 5MB</p>
                        <input type="file" id="attendanceCsvFile" name="csv_file" accept=".csv" style="display:none;" onchange="handleAttendanceFileSelect(event)">
                    </div>
                    <div id="attendanceFileInfo" style="display:none; margin-top:12px; padding:12px; background:#e9f9ef; border:1.5px solid #c8f0d8; border-radius:9px;">
                        <div style="display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/>
                                    <polyline points="13 2 13 9 20 9"/>
                                </svg>
                                <div>
                                    <p id="attendanceFileName" style="margin:0; font-size:13px; font-weight:600; color:var(--theme-success);"></p>
                                    <p id="attendanceFileSize" style="margin:0; font-size:11px; color:var(--theme-success);"></p>
                                </div>
                            </div>
                            <button type="button" onclick="removeAttendanceFile()" style="background:none; border:none; border-radius:8px; color:var(--theme-danger); cursor:pointer; padding:4px; transition:background .2s cubic-bezier(.4,0,.2,1);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"/>
                                    <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div style="background:var(--theme-warning-subtle); border:1.5px solid #ecdca4; border-radius:14px; padding:12px; margin-bottom:20px;">
                    <div style="display:flex; gap:10px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#c9a227" stroke-width="2" style="flex-shrink:0;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>
                            <p style="margin:0 0 4px; font-size:12px; font-weight:600; color:#c9a227;">Important Notes:</p>
                            <ul style="margin:0; padding-left:16px; font-size:11px; color:#c9a227; line-height:1.6;">
                                <li>Use the provided template for correct column headers</li>
                                <li>Required columns: <strong>employee_id</strong>, <strong>date</strong> (YYYY-MM-DD)</li>
                                <li>Time columns (HH:MM, 24h): am_in, am_out, pm_in, pm_out, ot_in, ot_out</li>
                                <li>Leave blank any slot with no punch</li>
                                <li>Duplicate employee + date rows update the existing record</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:10px; padding:16px 24px; border-top:1.5px solid var(--gp-bg-tint-2);">
            <button onclick="closeBulkImportAttendanceModal()" style="padding:10px 20px; border:1.5px solid var(--theme-neutral-300); border-radius:9px; background:#fff; font-size:13px; font-weight:600; color:var(--gp-text-mid); cursor:pointer; font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display','SF Pro Text','Helvetica Neue',Arial,sans-serif; transition:background .2s cubic-bezier(.4,0,.2,1);">Cancel</button>
            <button onclick="submitBulkImportAttendance()" style="padding:10px 20px; border:none; border-radius:9px; background:linear-gradient(135deg,var(--gp-pri),var(--gp-pri-2)); color:#fff; font-size:13px; font-weight:600; cursor:pointer; font-family:-apple-system,BlinkMacSystemFont,'SF Pro Display','SF Pro Text','Helvetica Neue',Arial,sans-serif; display:flex; align-items:center; gap:6px; transition:transform .15s ease;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Upload & Import
            </button>
        </div>
    </div>
</div>
