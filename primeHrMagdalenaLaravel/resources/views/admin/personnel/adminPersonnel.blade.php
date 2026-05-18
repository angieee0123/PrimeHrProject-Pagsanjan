@extends('layouts.app')

@push('styles')
    @vite('resources/css/adminPersonnel.css')
@endpush

@push('scripts')
    @vite('resources/js/employeeWizard.js')
    @vite('resources/js/adminPersonnel.js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush

@section('content')
@include('admin.topbar.personnelTopbar')
@include('admin.notification.adminNotification')

<!-- Stats Grid -->
<div class="stats-grid stats-grid-4" style="margin-bottom:20px;">
    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background:#f0effe">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['total'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">All records</p>
        </div>
    </div>

    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Active</p>
            <div class="stat-icon-wrap" style="background:#e8f9ef">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['active'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d"></span>
            <p class="stat-sub">Currently active</p>
        </div>
    </div>

    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Inactive</p>
            <div class="stat-icon-wrap" style="background:#fdf0ef">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['inactive'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#8e1e18"></span>
            <p class="stat-sub">Deactivated accounts</p>
        </div>
    </div>

    <div class="stat-card personnel-stat-card">
        <div class="stat-top">
            <p class="stat-label">Permanent</p>
            <div class="stat-icon-wrap" style="background:#fefce8">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $stats['permanent'] }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#d9bb00"></span>
            <p class="stat-sub">Permanent employees</p>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="tab-nav">
    <button class="tab-btn active" data-tab="employees">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Employee Records
    </button>
    <button class="tab-btn" data-tab="schedules">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Work Schedules
    </button>
</div>

<!-- Employee Records Tab -->
<section class="table-section tab-content active personnel-table-section" id="employees">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Records</h3>
            <p class="table-sub">Municipal Government of Pagsanjan Â· {{ $employees->count() }} of {{ $employees->count() }} records</p>
        </div>
        <div class="table-actions">
                    <div class="search-wrap">
                        <svg width="13" height="13" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="personnelSearchInput" placeholder="Search employees..." class="search-input" oninput="applyFilters()">
                    </div>
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

    <div class="table-wrapper">
        <table class="payroll-table" id="personnelTable">
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
                    $empType  = $employee->employmentDetail ? $employee->employmentDetail->employment_status : 'N/A';
                    $position = $employee->employmentDetail && $employee->employmentDetail->designationRelation
                        ? $employee->employmentDetail->designationRelation->title
                        : 'N/A';
                    $department = $employee->employmentDetail && $employee->employmentDetail->departmentRelation
                        ? $employee->employmentDetail->departmentRelation->name
                        : 'N/A';
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
                            <!-- Desktop: Individual Buttons -->
                            <div class="action-buttons-desktop">
                                <button class="btn-view" onclick="viewEmployee({{ $employee->id }})" title="View">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <span>View</span>
                                </button>
                                <button class="btn-edit" onclick="editEmployee({{ $employee->id }})" title="Edit">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    <span>Edit</span>
                                </button>
                                <button class="btn-qr" onclick="generateQRCode({{ $employee->id }}, '{{ $fullName }}')" title="QR Code">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                    <span>QR</span>
                                </button>
                                @if($status === 'Active')
                                <button class="btn-deactivate" onclick="confirmStatusChange({{ $employee->id }}, 'Inactive')" title="Deactivate">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                    <span>Deactivate</span>
                                </button>
                                @else
                                <button class="btn-activate" onclick="confirmStatusChange({{ $employee->id }}, 'Active')" title="Activate">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
                                    <span>Activate</span>
                                </button>
                                @endif
                            </div>
                            
                            <!-- Mobile/Tablet: 3-Dot Menu -->
                            <div class="action-menu-wrapper">
                                <button class="action-menu-btn" onclick="toggleActionMenu(event, {{ $employee->id }})" title="Actions">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/>
                                        <circle cx="12" cy="5" r="1"/>
                                        <circle cx="12" cy="19" r="1"/>
                                    </svg>
                                </button>
                                <div class="action-menu-dropdown" id="actionMenu{{ $employee->id }}">
                                    <button class="action-menu-item" onclick="viewEmployee({{ $employee->id }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <span>View Details</span>
                                    </button>
                                    <button class="action-menu-item" onclick="editEmployee({{ $employee->id }})">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        <span>Edit Record</span>
                                    </button>
                                    <button class="action-menu-item" onclick="generateQRCode({{ $employee->id }}, '{{ $fullName }}')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="3" width="7" height="7"/>
                                            <rect x="14" y="3" width="7" height="7"/>
                                            <rect x="14" y="14" width="7" height="7"/>
                                            <rect x="3" y="14" width="7" height="7"/>
                                        </svg>
                                        <span>Generate QR Code</span>
                                    </button>
                                    <div class="action-menu-divider"></div>
                                    @if($status === 'Active')
                                    <button class="action-menu-item danger" onclick="confirmStatusChange({{ $employee->id }}, 'Inactive')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"/>
                                            <line x1="15" y1="9" x2="9" y2="15"/>
                                            <line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                        <span>Deactivate Account</span>
                                    </button>
                                    @else
                                    <button class="action-menu-item success" onclick="confirmStatusChange({{ $employee->id }}, 'Active')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                            <polyline points="22 4 12 14.01 9 11.01"/>
                                        </svg>
                                        <span>Activate Account</span>
                                    </button>
                                    @endif
                                </div>
                            </div>
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

<!-- Work Schedules Tab -->
<section class="table-section tab-content personnel-table-section" id="schedules">
    <div class="table-header">
        <div>
            <h3 class="table-title">Work Schedules</h3>
            <p class="table-sub">Manage employee work schedules · {{ $employees->count() }} employees</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="schedDepartmentFilter" onchange="applyScheduleFilters()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <button class="btn-export" onclick="exportSchedules()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="openBulkScheduleModal()" style="padding: 8px 18px; font-size: 12.5px; display: flex; align-items: center; gap: 6px; background: #1a0f6e;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Bulk Assign
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table" id="scheduleTable">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>AM In</th>
                    <th>AM Out</th>
                    <th>PM In</th>
                    <th>PM Out</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="scheduleTableBody">
                @forelse($employees as $index => $employee)
                @php
                    $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name . ($employee->suffix ? ' ' . $employee->suffix : ''));
                    $department = $employee->employmentDetail && $employee->employmentDetail->departmentRelation
                        ? $employee->employmentDetail->departmentRelation->name
                        : 'N/A';
                    // Get current active schedule
                    $currentSchedule = $employee->schedule->where('start_date', '<=', now()->format('Y-m-d'))
                        ->where('end_date', '>=', now()->format('Y-m-d'))
                        ->first();
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
                    <td><span class="dept-tag">{{ $department }}</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $currentSchedule->am_in ?? '--:--' }}</td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $currentSchedule->am_out ?? '--:--' }}</td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $currentSchedule->pm_in ?? '--:--' }}</td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $currentSchedule->pm_out ?? '--:--' }}</td>
                    <td>
                        @if($currentSchedule)
                            <span class="badge-status processed">Active</span>
                        @elseif($employee->schedule->count() > 0)
                            <span class="badge-status pending">Scheduled</span>
                        @else
                            <span class="badge-status on-hold">Not Set</span>
                        @endif
                    </td>
                    <td>
                        <div class="row-actions">
                            <!-- Desktop: Individual Buttons -->
                            <div class="action-buttons-desktop">
                                <button class="btn-view" onclick="viewEmployeeSchedules({{ $employee->id }}, '{{ $fullName }}')" title="View All">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span>View All</span>
                                </button>
                                <button class="btn-edit" onclick="openAssignScheduleModal({{ $employee->id }}, '{{ $fullName }}', null)" title="Add New">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"/>
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                    </svg>
                                    <span>Add New</span>
                                </button>
                            </div>
                            
                            <!-- Mobile/Tablet: 3-Dot Menu -->
                            <div class="action-menu-wrapper">
                                <button class="action-menu-btn" onclick="toggleActionMenu(event, 'schedule{{ $employee->id }}')" title="Actions">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="1"/>
                                        <circle cx="12" cy="5" r="1"/>
                                        <circle cx="12" cy="19" r="1"/>
                                    </svg>
                                </button>
                                <div class="action-menu-dropdown" id="actionMenuschedule{{ $employee->id }}">
                                    <button class="action-menu-item" onclick="viewEmployeeSchedules({{ $employee->id }}, '{{ $fullName }}')">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                            <line x1="16" y1="2" x2="16" y2="6"/>
                                            <line x1="8" y1="2" x2="8" y2="6"/>
                                            <line x1="3" y1="10" x2="21" y2="10"/>
                                        </svg>
                                        <span>View All Schedules</span>
                                    </button>
                                    <button class="action-menu-item" onclick="openAssignScheduleModal({{ $employee->id }}, '{{ $fullName }}', null)">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="12" y1="5" x2="12" y2="19"/>
                                            <line x1="5" y1="12" x2="19" y2="12"/>
                                        </svg>
                                        <span>Add New Schedule</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b6a8a;">
                        No employees found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p>Showing <strong id="schedShowingStart">1</strong>-<strong id="schedShowingEnd">10</strong> of <strong id="schedTotalRecords">{{ $employees->count() }}</strong> records</p>
            <select id="schedRowsPerPageSelect" onchange="changeScheduleRowsPerPage(this.value)" style="padding: 6px 12px; border: 1.5px solid #e8e7f5; border-radius: 6px; font-size: 12px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff; cursor: pointer;">
                <option value="10" selected>10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
                <option value="all">Show all</option>
            </select>
        </div>
        <div class="pagination" id="schedulePaginationControls">
            <!-- Pagination buttons will be generated by JavaScript -->
        </div>
    </div>
</section>

@include('admin.personnel.modals.employeeWizardComplete')
@include('admin.personnel.modals.assignSchedule')
@include('admin.personnel.modals.bulkAssignSchedule')
@include('admin.personnel.modals.viewSchedules')

<!-- Success Modal -->
<div id="successModal" class="personnel-modal">
    <div class="personnel-modal-box">
        <div class="personnel-modal-icon success">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3 class="personnel-modal-title">Registration Successful!</h3>
        <p id="successMessage" class="personnel-modal-message"></p>
        <button onclick="closeSuccessModal()" class="personnel-modal-btn success">Done</button>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" class="personnel-modal">
    <div class="personnel-modal-box">
        <div class="personnel-modal-icon error">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 class="personnel-modal-title">Registration Failed</h3>
        <p id="errorMessage" class="personnel-modal-message"></p>
        <button onclick="closeErrorModal()" class="personnel-modal-btn error">Close</button>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmModal">
    <div class="confirm-modal-box">
        <div class="confirm-modal-icon" id="confirmIconWrap">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke-width="2.5" id="confirmIcon">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
        <h3 class="confirm-modal-title" id="confirmTitle">Confirm Action</h3>
        <p class="confirm-modal-message" id="confirmMessage"></p>
        <div class="confirm-input-wrap">
            <label class="confirm-input-label">Type "Yes I confirm" to proceed:</label>
            <input type="text" id="confirmInput" placeholder="Yes I confirm" class="confirm-input" />
            <p class="confirm-error" id="confirmError">Please type exactly "Yes I confirm"</p>
        </div>
        <div class="confirm-modal-footer">
            <button onclick="closeConfirmModal()" class="confirm-btn-cancel">Cancel</button>
            <button onclick="submitConfirmation()" class="confirm-btn-submit" id="confirmSubmitBtn">Confirm</button>
        </div>
    </div>
</div>

<!-- View Employee Modal -->
<div id="viewEmployeeModal">
    <div class="view-employee-box">
        <div class="view-employee-header">
            <div>
                <h3>Employee Details</h3>
                <p id="viewEmployeeId"></p>
            </div>
            <button onclick="closeViewModal()" class="view-employee-close">&times;</button>
        </div>
        <div class="view-employee-body" id="viewEmployeeContent">
            <p style="text-align:center; color:#6b6a8a;">Loading...</p>
        </div>
    </div>
</div>

<!-- Export Success Modal -->
<div id="exportSuccessModal" class="personnel-modal">
    <div class="personnel-modal-box">
        <div class="personnel-modal-icon export">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
        </div>
        <h3 class="personnel-modal-title">Export Successful!</h3>
        <p id="exportSuccessMessage" class="personnel-modal-message"></p>
        <button onclick="closeExportSuccessModal()" class="personnel-modal-btn success">Done</button>
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

<script>
// Tab switching functionality
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

// Check if we should activate schedules tab on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('active_tab') === 'schedules')
        // Activate schedules tab
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        const schedulesTabBtn = document.querySelector('.tab-btn[data-tab="schedules"]');
        const schedulesTabContent = document.getElementById('schedules');

        if (schedulesTabBtn && schedulesTabContent) {
            schedulesTabBtn.classList.add('active');
            schedulesTabContent.classList.add('active');
        }
    @endif
});

// Schedule filters
function applyScheduleFilters() {
    const deptFilter = document.getElementById('schedDepartmentFilter').value;
    const rows = document.querySelectorAll('#scheduleTableBody tr');

    rows.forEach(row => {
        const deptCell = row.querySelector('.dept-tag');
        if (!deptCell) return;

        const deptMatch = !deptFilter || deptCell.textContent.trim() === deptFilter;
        row.style.display = deptMatch ? '' : 'none';
    });
}

function changeScheduleRowsPerPage(value) {
    // Implement pagination logic similar to main table
    console.log('Change schedule rows per page:', value);
}

function exportSchedules() {
    window.location.href = '{{ route("admin.schedules.export") }}';
}

function openBulkScheduleModal() {
    document.getElementById('bulkScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function openAssignScheduleModal(employeeId, employeeName, schedule) {
    document.getElementById('scheduleEmployeeId').value = employeeId;
    document.getElementById('scheduleEmployeeName').textContent = employeeName;

    if (schedule) {
        document.getElementById('scheduleId').value = schedule.id || '';
        document.getElementById('scheduleStartDate').value = schedule.start_date || '';
        document.getElementById('scheduleEndDate').value = schedule.end_date || '';
        document.getElementById('scheduleAmIn').value = schedule.am_in || '';
        document.getElementById('scheduleAmOut').value = schedule.am_out || '';
        document.getElementById('schedulePmIn').value = schedule.pm_in || '';
        document.getElementById('schedulePmOut').value = schedule.pm_out || '';
    } else {
        document.getElementById('scheduleId').value = '';
        document.getElementById('scheduleStartDate').value = '';
        document.getElementById('scheduleEndDate').value = '';
        document.getElementById('scheduleAmIn').value = '';
        document.getElementById('scheduleAmOut').value = '';
        document.getElementById('schedulePmIn').value = '';
        document.getElementById('schedulePmOut').value = '';
    }

    document.getElementById('assignScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function confirmRemoveSchedule(employeeId, employeeName) {
    if (confirm(`Are you sure you want to remove the schedule for ${employeeName}?`)) {
        // Submit form to remove schedule
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/schedules/${employeeId}/remove`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
