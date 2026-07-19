<!-- Migrate Leave Records Modal -->
<x-modal id="migrateLeaveRecordsModal" close="closeMigrateLeaveRecordsModal" max-width="650px"
          eyebrow="MIGRATE LEAVE RECORDS" title="Migrate Historical Leave Data"
          subtitle="Import leave records from Pagsanjan legacy Excel files into the system"
          data-import-url="{{ route('admin.leave.import') }}"
          data-redirect-url="{{ route('admin.leave', ['tab' => 'transactions']) }}">
    <div class="modal-body">
        <form id="migrateLeaveRecordsForm" enctype="multipart/form-data">
            @csrf

            <!-- Employee Selection -->
            <div style="margin-bottom: 20px;">
                <label class="form-label">
                    Select Employee <span style="color: #8e1e18;">*</span>
                </label>
                <select id="migrateEmployeeId" name="employee_id" required class="form-input" style="width: 100%;">
                    <option value="">-- Choose Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">
                            {{ $emp->employee_id }} - {{ $emp->first_name }} {{ $emp->last_name }}
                            @if($emp->employmentDetail && $emp->employmentDetail->departmentRelation)
                                ({{ $emp->employmentDetail->departmentRelation->name }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <small class="form-hint">Choose the employee whose leave records you want to migrate</small>
            </div>

            <!-- File Upload -->
            <div style="margin-bottom: 20px;">
                <label class="form-label">
                    Excel File <span style="color: #8e1e18;">*</span>
                </label>
                <div style="border: 2px dashed #bae6fd; border-radius: 8px; padding: 20px; text-align: center; background: #f0f9ff; cursor: pointer;" id="migrateFileDropZone" onclick="document.getElementById('migrateExcelFile').click()">
                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" style="margin: 0 auto 8px; display: block;">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                    </svg>
                    <p style="margin: 0 0 4px 0; color: #0369a1; font-weight: 600; font-size: 14px;">Drag Excel file here or click to browse</p>
                    <p style="margin: 0; color: #0369a1; font-size: 12px; opacity: 0.7;">Supported: .xlsx, .xls (Max 5MB)</p>
                </div>
                <input type="file" id="migrateExcelFile" name="excel_file" accept=".xlsx,.xls" required class="form-control" style="display: none;" onchange="updateMigrateFileName(this)">
                <p id="migrateFileName" style="margin-top: 8px; font-size: 12px; color: #56547a; display: none;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 4px; vertical-align: -1px;">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    </svg>
                    Selected: <strong id="migrateFileNameText"></strong>
                </p>
                <small class="form-hint">Use your Pagsanjan leave ledger format (see expected format below)</small>
            </div>

            <!-- Expected Format Info -->
            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                <div style="display: flex; gap: 12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" style="flex-shrink: 0;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                    <div style="flex: 1;">
                        <h4 style="margin: 0 0 8px 0; color: #0369a1; font-size: 13px; font-weight: 600;">Expected Excel Format</h4>
                        <div style="font-size: 12px; color: #075985; line-height: 1.6;">
                            <p style="margin: 0 0 8px 0;"><strong>Columns (A-H):</strong></p>
                            <ul style="margin: 0 0 8px 0; padding-left: 20px;">
                                <li>A: Month/Year (e.g., "Jun-19", "8/1/2012")</li>
                                <li>B: Notes (e.g., "VL1", "FL1", "T(0-1-2)", "VL1/T(0-1-2)")</li>
                                <li>C: VL Earned | D: VL Used | E: SL Earned | F: SL Used</li>
                                <li>G: VL Balance | H: SL Balance</li>
                            </ul>
                            <p style="margin: 0; color: #0369a1; font-weight: 600;">
                                <a href="{{ route('admin.leave.download-template') }}" style="color: #0369a1; text-decoration: none; border-bottom: 1px solid #0369a1;">
                                    Download Template
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Migration Progress -->
            <div id="migrateProgress" style="display: none; margin-bottom: 20px;">
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; display: flex; align-items: center; gap: 10px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2" style="animation: spin 1s linear infinite;">
                        <line x1="12" y1="2" x2="12" y2="6"/>
                        <line x1="12" y1="18" x2="12" y2="22"/>
                        <line x1="4.22" y1="4.22" x2="7.34" y2="7.34"/>
                        <line x1="16.66" y1="16.66" x2="19.78" y2="19.78"/>
                        <line x1="2" y1="12" x2="6" y2="12"/>
                        <line x1="18" y1="12" x2="22" y2="12"/>
                        <line x1="4.22" y1="19.78" x2="7.34" y2="16.66"/>
                        <line x1="16.66" y1="7.34" x2="19.78" y2="4.22"/>
                    </svg>
                    <span style="color: #15803d; font-size: 13px; font-weight: 500;">Migrating records...</span>
                </div>
            </div>
        </form>
    </div>

    <div class="modal-footer">
        <button type="button" class="modal-btn-ghost" onclick="closeMigrateLeaveRecordsModal()" id="migrateCancelBtn">Cancel</button>
        <button type="button" class="modal-btn-primary" onclick="submitMigrateLeaveRecords()" id="migrateSubmitBtn" style="background: #0b044d;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 6px; vertical-align: -1px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <span class="btn-text">Migrate Records</span>
            <span class="btn-loader" style="display: none;">Migrating...</span>
        </button>
    </div>
</x-modal>

@push('scripts')
    @vite('resources/js/leaveAndBenefits/migrate-leave-records-modal.js')
@endpush
