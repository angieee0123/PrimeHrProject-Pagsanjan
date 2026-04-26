@extends('layouts.app')

@push('styles')
    @vite('resources/css/employeeWizard.css')
@endpush

@push('scripts')
    @vite('resources/js/employeeWizard.js')
    @vite('resources/js/adminPersonnel.js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush

@section('content')
@include('admin.topbar.personnelTopbar')

<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="stat-card" style="cursor: pointer; transition: all 0.3s ease; border-top: 3px solid transparent; border-radius: 12px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 28px rgba(11,4,77,0.09)'; this.style.borderTopLeftRadius='12px'; this.style.borderTopRightRadius='12px'; this.style.borderTop='3px solid'; this.style.borderImage='linear-gradient(90deg, transparent, #0b044d 30%, #ffffff 70%, transparent) 1'; this.style.borderImageSlice='1 0 0 0'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''; this.style.borderTop='3px solid transparent'; this.style.borderImage='none'">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.08); box-shadow: 0 4px 12px rgba(11, 4, 77, 0.15);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['total'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All records</p>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer; transition: all 0.3s ease; border-top: 3px solid transparent; border-radius: 12px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 28px rgba(11,4,77,0.09)'; this.style.borderTopLeftRadius='12px'; this.style.borderTopRightRadius='12px'; this.style.borderTop='3px solid'; this.style.borderImage='linear-gradient(90deg, transparent, #0b044d 30%, #ffffff 70%, transparent) 1'; this.style.borderImageSlice='1 0 0 0'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''; this.style.borderTop='3px solid transparent'; this.style.borderImage='none'">
        <div class="stat-top">
            <p class="stat-label">Active</p>
            <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.08); box-shadow: 0 4px 12px rgba(21, 128, 61, 0.15);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['active'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">Currently active</p>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer; transition: all 0.3s ease; border-top: 3px solid transparent; border-radius: 12px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 28px rgba(11,4,77,0.09)'; this.style.borderTopLeftRadius='12px'; this.style.borderTopRightRadius='12px'; this.style.borderTop='3px solid'; this.style.borderImage='linear-gradient(90deg, transparent, #0b044d 30%, #ffffff 70%, transparent) 1'; this.style.borderImageSlice='1 0 0 0'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''; this.style.borderTop='3px solid transparent'; this.style.borderImage='none'">
        <div class="stat-top">
            <p class="stat-label">Inactive</p>
            <div class="stat-icon-wrap" style="background: rgba(142, 30, 24, 0.08); box-shadow: 0 4px 12px rgba(142, 30, 24, 0.15);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['inactive'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Deactivated accounts</p>
        </div>
    </div>

    <div class="stat-card" style="cursor: pointer; transition: all 0.3s ease; border-top: 3px solid transparent; border-radius: 12px;" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 28px rgba(11,4,77,0.09)'; this.style.borderTopLeftRadius='12px'; this.style.borderTopRightRadius='12px'; this.style.borderTop='3px solid'; this.style.borderImage='linear-gradient(90deg, transparent, #0b044d 30%, #ffffff 70%, transparent) 1'; this.style.borderImageSlice='1 0 0 0'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow=''; this.style.borderTop='3px solid transparent'; this.style.borderImage='none'">
        <div class="stat-top">
            <p class="stat-label">Permanent</p>
            <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.08); box-shadow: 0 4px 12px rgba(217, 187, 0, 0.15);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['permanent'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Permanent employees</p>
        </div>
    </div>
</div>

<!-- Employee Table -->
<section class="table-section" style="box-shadow: 0 4px 16px rgba(11, 4, 77, 0.08);">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Records</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $employees->count() }} of {{ $employees->count() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="departmentFilter" onchange="applyFilters()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
            <button class="btn-export" onclick="exportTableData()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="openEmployeeWizard()" style="padding: 8px 18px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Employee
            </button>
        </div>
    </div>

    <div class="table-wrapper" style="overflow-x: auto;">
        <table class="payroll-table" id="personnelTable" style="min-width: 1200px;">
            <thead>
                <tr>
                    <th onclick="sortTable(0)" style="cursor: pointer;">
                        Employee
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(1)" style="cursor: pointer;">
                        Position
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(2)" style="cursor: pointer;">
                        Department / Office
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(3)" style="cursor: pointer;">
                        Type
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(4)" style="cursor: pointer;">
                        Date Appointement
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(5)" style="cursor: pointer;">
                        Status
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="personnelTableBody">
                @php
                $avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
                function getInitials($name) {
                    $parts = explode(' ', $name);
                    $initials = '';
                    foreach ($parts as $part) {
                        if (preg_match('/^[A-Z]/', $part)) {
                            $initials .= $part[0];
                        }
                    }
                    return strtoupper(substr($initials, 0, 2));
                }
                @endphp

                @forelse($employees as $index => $employee)
                @php
                    $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name . ($employee->suffix ? ' ' . $employee->suffix : ''));
                    $status = $employee->user ? $employee->user->status : 'Inactive';
                    $empType = $employee->employmentDetail ? $employee->employmentDetail->employment_status : 'N/A';
                    $position = $employee->employmentDetail ? $employee->employmentDetail->position : 'N/A';

                    // Fetch department name from departments table
                    $department = 'N/A';
                    if ($employee->employmentDetail && $employee->employmentDetail->department) {
                        $deptId = $employee->employmentDetail->department;
                        $dept = \App\Models\Department::find($deptId);
                        $department = $dept ? $dept->name : $deptId;
                    }

                    $dateHired = $employee->employmentDetail && $employee->employmentDetail->appointment_date
                        ? \Carbon\Carbon::parse($employee->employmentDetail->appointment_date)->format('M d, Y')
                        : 'N/A';
                @endphp
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($fullName) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $fullName }}</p>
                                <p class="emp-id">{{ $employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="position-cell">{{ $position }}</td>
                    <td><span class="dept-tag">{{ $department }}</span></td>
                    <td><span class="badge-emptype">{{ $empType }}</span></td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $dateHired }}</td>
                    <td><span class="badge-status {{ $status === 'Active' ? 'processed' : 'on-hold' }}">{{ $status }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewEmployee({{ $employee->id }})">View</button>
                            <button class="btn-edit">Edit</button>
                            <button class="btn-qr" onclick="generateQRCode({{ $employee->id }}, '{{ $fullName }}')">QR Code</button>
                            @if($status === 'Active')
                            <button class="btn-deactivate" onclick="confirmStatusChange({{ $employee->id }}, 'Inactive')">Deactivate</button>
                            @else
                            <button class="btn-activate" onclick="confirmStatusChange({{ $employee->id }}, 'Active')">Activate</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">
                        No employees found. Click "Add Employee" to register new personnel.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p>Showing <strong id="showingStart">1</strong>-<strong id="showingEnd">10</strong> of <strong id="totalRecords">{{ $employees->count() }}</strong> records</p>
            <select id="rowsPerPageSelect" onchange="changeRowsPerPage(this.value)" style="padding: 6px 12px; border: 1.5px solid #e8e7f5; border-radius: 6px; font-size: 12px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff; cursor: pointer;">
                <option value="10" selected>10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
                <option value="all">Show all</option>
            </select>
        </div>
        <div class="pagination" id="paginationControls">
            <!-- Pagination buttons will be generated by JavaScript -->
        </div>
    </div>
</section>

@include('admin.personnel.modals.employeeWizardComplete')

<!-- Success Modal -->
<div id="successModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; background:#e8f9ef; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Registration Successful!</h3>
        <p id="successMessage" style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6;"></p>
        <button onclick="closeSuccessModal()" style="padding:12px 32px; background:#15803d; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
            Done
        </button>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; background:#fee8e8; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Registration Failed</h3>
        <p id="errorMessage" style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6;"></p>
        <button onclick="closeErrorModal()" style="padding:12px 32px; background:#8e1e18; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
            Close
        </button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:480px; padding:32px; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;" id="confirmIconWrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke-width="2.5" id="confirmIcon">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d; text-align:center;" id="confirmTitle">Confirm Action</h3>
        <p style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6; text-align:center;" id="confirmMessage"></p>
        <div style="margin-bottom:24px;">
            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:8px; text-align:left;">Type "Yes I confirm" to proceed:</label>
            <input type="text" id="confirmInput" placeholder="Yes I confirm" style="width:100%; padding:12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:14px; font-family:'Poppins',sans-serif; box-sizing:border-box;" />
            <p style="margin:8px 0 0; font-size:11px; color:#8e1e18; display:none;" id="confirmError">Please type exactly "Yes I confirm"</p>
        </div>
        <div style="display:flex; gap:10px;">
            <button onclick="closeConfirmModal()" style="flex:1; padding:12px; background:#f7f6ff; color:#6b6a8a; border:1px solid #e8e7f5; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
                Cancel
            </button>
            <button onclick="submitConfirmation()" style="flex:1; padding:12px; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;" id="confirmSubmitBtn">
                Confirm
            </button>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div id="viewEmployeeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; overflow-y:auto;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:900px; margin:20px; box-shadow:0 8px 32px rgba(11,4,77,0.2); max-height:90vh; display:flex; flex-direction:column;">
        <div style="background:linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding:24px; border-radius:12px 12px 0 0; display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0 0 4px; font-size:20px; font-weight:700; color:#fff;">Employee Details</h3>
                <p style="margin:0; font-size:13px; color:rgba(255,255,255,0.7);" id="viewEmployeeId"></p>
            </div>
            <button onclick="closeViewModal()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">&times;</button>
        </div>
        <div style="padding:24px; overflow-y:auto; flex:1;" id="viewEmployeeContent">
            <p style="text-align:center; color:#6b6a8a;">Loading...</p>
        </div>
    </div>
</div>

<!-- Export Success Modal -->
<div id="exportSuccessModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; background:#e8f9ef; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Export Successful!</h3>
        <p id="exportSuccessMessage" style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6;"></p>
        <button onclick="closeExportSuccessModal()" style="padding:12px 32px; background:#15803d; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
            Done
        </button>
    </div>
</div>

<!-- Export Error Modal -->
<div id="exportErrorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; background:#fee8e8; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Export Failed</h3>
        <p id="exportErrorMessage" style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6;"></p>
        <button onclick="closeExportErrorModal()" style="padding:12px 32px; background:#8e1e18; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
            Close
        </button>
    </div>
</div>

<!-- QR Code Modal -->
<div id="qrCodeModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:500px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:20px; font-weight:700; color:#0b044d;">Employee QR Code</h3>
            <button onclick="closeQRModal()" style="background:transparent; border:none; color:#6b6a8a; font-size:24px; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center;">&times;</button>
        </div>

        <div style="background:#f7f6ff; border:2px solid #e8e7f5; border-radius:12px; padding:24px; margin-bottom:20px;">
            <p style="margin:0 0 8px; font-size:14px; font-weight:600; color:#0b044d;" id="qrEmployeeName"></p>
            <p style="margin:0 0 16px; font-size:12px; color:#6b6a8a;" id="qrEmployeeId"></p>
            <div id="qrCodeContainer" style="display:flex; justify-content:center; align-items:center; min-height:300px;">
                <p style="color:#6b6a8a;">Generating QR Code...</p>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button onclick="downloadQRCode()" style="flex:1; padding:12px; background:#0b044d; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; justify-content:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download
            </button>
            <button onclick="printQRCode()" style="flex:1; padding:12px; background:#15803d; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; justify-content:center; gap:8px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print
            </button>
        </div>
    </div>
</div>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('successMessage').textContent = "{{ session('success') }}";
    document.getElementById('successModal').style.display = 'flex';
    if (document.getElementById('employeeWizardModal')) {
        document.getElementById('employeeWizardModal').style.display = 'none';
    }
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('errorMessage').textContent = "{{ session('error') }}";
    document.getElementById('errorModal').style.display = 'flex';
});
</script>
@endif
@endsection
