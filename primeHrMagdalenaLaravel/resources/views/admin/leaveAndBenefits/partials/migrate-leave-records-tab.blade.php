<section class="table-section" id="migrate-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Migrate Leave Records</h3>
            <p class="table-sub">Import historical leave data from Excel files for employees · Batch migration support</p>
        </div>
        <div class="table-actions">
            <button class="btn-export" style="background: #4b5563; color: #fff; border-color: #4b5563;" onclick="downloadLeaveTemplate()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Template
            </button>
            <button class="btn-export" style="background: var(--gp-pri); color: #fff; border-color: var(--gp-pri);" onclick="openMigrateLeaveRecordsModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Migrate Records
            </button>
        </div>
    </div>

    <!-- Info Box -->
    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
        <div style="display: flex; gap: 12px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <div style="flex: 1;">
                <h4 style="margin: 0 0 8px 0; color: #0369a1; font-size: 14px; font-weight: 600;">Leave Records Migration Guide</h4>
                <div style="font-size: 13px; color: #075985; line-height: 1.7;">
                    <p style="margin: 0 0 10px 0;"><strong>Steps to migrate leave records:</strong></p>
                    <ol style="margin: 0 0 10px 0; padding-left: 20px;">
                        <li>Click the "Migrate Records" button above</li>
                        <li>Select an employee from the dropdown</li>
                        <li>Upload their leave ledger Excel file (.xlsx or .xls)</li>
                        <li>The system will parse and validate the data</li>
                        <li>Records are added to Transaction History automatically</li>
                    </ol>
                    <p style="margin: 0 0 8px 0;"><strong>Expected Excel Format:</strong></p>
                    <ul style="margin: 0 0 10px 0; padding-left: 20px;">
                        <li><strong>Rows 1-5:</strong> Header information (Name, Position, etc.)</li>
                        <li><strong>Row 6+:</strong> Monthly data rows</li>
                        <li><strong>Column A:</strong> Month/Year (e.g., "2012", "August")</li>
                        <li><strong>Column B:</strong> Notes (VL1, FL1, T(0-2-10), etc.)</li>
                        <li><strong>Leave Columns:</strong> D=VL Earned, F=VL Used, M=VL Balance, H=SL Earned, J=SL Used, N=SL Balance</li>
                    </ul>
                    <p style="margin: 0; color: #0284c7;"><strong>Tip:</strong> Maximum file size is 5MB. Supported formats: .xlsx, .xls</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Migration Status -->
    <div style="background: white; border: 1px solid var(--gp-border); border-radius: 8px; padding: 20px; text-align: center;">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        <p style="margin: 0 0 8px 0; font-size: 15px; color: var(--theme-neutral-700); font-weight: 500;">Ready to migrate leave records</p>
        <p style="margin: 0; font-size: 13px; color: var(--theme-neutral-600);">Click the "Migrate Records" button to import historical leave data from an Excel file</p>
    </div>
</section>
