<!-- Import Leave Records Modal -->
<div id="importLeaveRecordsModal"
     class="modal"
     style="display: none;"
     data-import-url="{{ route('admin.leave.import') }}"
     data-redirect-url="{{ route('admin.leave', ['tab' => 'transactions']) }}">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3>Import Leave Records</h3>
            <button type="button" class="close-btn" onclick="closeImportLeaveRecordsModal()">&times;</button>
        </div>

        <div class="modal-body">
            <form id="importLeaveRecordsForm" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="importEmployeeId">Select Employee <span style="color: #d32f2f;">*</span></label>
                    <select id="importEmployeeId" name="employee_id" required class="form-control">
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
                </div>

                <div class="form-group">
                    <label for="importExcelFile">Excel File <span style="color: #d32f2f;">*</span></label>
                    <div class="file-upload-wrapper">
                        <input type="file" id="importExcelFile" name="excel_file" accept=".xlsx,.xls" required class="form-control">
                        <small style="color: #666; margin-top: 5px; display: block;">
                            Supported formats: .xlsx, .xls (Max: 5MB). Use the Pagsanjan leave ledger format.
                        </small>
                    </div>
                </div>

                <div style="background: #f5f5f5; border-left: 3px solid #2196F3; padding: 12px; margin: 15px 0; border-radius: 3px;">
                    <p style="font-weight: 500; margin: 0 0 8px 0; color: #333;">Expected Excel Format:</p>
                    <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #666;">
                        <li>Header info in rows 1-5 (Employee name, position, etc.)</li>
                        <li>Data starts from row 6 (month names in column A)</li>
                        <li>Column B: Notes (VL1, FL1, T(0-2-10), etc.)</li>
                        <li>Column D: Vacation Leave Earned</li>
                        <li>Column F: Vacation Leave Used</li>
                        <li>Column H: Sick Leave Earned</li>
                        <li>Column J: Sick Leave Used</li>
                        <li>Column M: VL Balance</li>
                        <li>Column N: SL Balance</li>
                    </ul>
                </div>
            </form>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeImportLeaveRecordsModal()">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitImportLeaveRecords()" id="importSubmitBtn">
                <span class="btn-text">Import Records</span>
                <span class="btn-loader" style="display: none;">Importing...</span>
            </button>
        </div>
    </div>
</div>
