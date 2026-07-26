<!-- Employee Records Tab -->
<section class="table-section tab-content active personnel-table-section" id="employees">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Records</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $employees->count() }} of {{ $employees->count() }} records</p>
        </div>
        <div class="table-actions">
            <button class="btn-export" onclick="openBulkImportModal()" style="background:#15803d; color:#fff; border-color:#15803d;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Bulk Import
            </button>
            {{-- Same navy pill as "File Travel Order". The inline padding/size
                 that used to sit here was reproducing .btn-export's metrics by
                 hand; the class supplies them. --}}
            <button class="btn-export adm-btn-primary-solid" onclick="openEmployeeWizard()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Employee
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table personnel-records-table" id="personnelTable">
            <thead>
                <tr>
                    <th onclick="sortTable(0)" style="cursor: pointer; width: 25%;">
                        Employee
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(1)" style="cursor: pointer; width: 18%;">
                        Position
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(2)" style="cursor: pointer; width: 17%;">
                        Department
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(3)" style="cursor: pointer; width: 10%; text-align: center;">
                        Type
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(4)" style="cursor: pointer; width: 10%; text-align: center;">
                        Appointed
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th onclick="sortTable(5)" style="cursor: pointer; width: 8%; text-align: center;">
                        Status
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;">
                            <polyline points="18 15 12 9 6 15"></polyline>
                        </svg>
                    </th>
                    <th style="width: 12%;">Actions</th>
                </tr>
            </thead>
            <tbody id="personnelTableBody">
                {{-- $avatarColors and getInitials() are declared in adminPersonnel.blade.php (the parent), since this partial's sibling schedule-tab.blade.php needs them too. --}}
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
                    $dateHiredIso = $employee->employmentDetail && $employee->employmentDetail->appointment_date
                        ? \Carbon\Carbon::parse($employee->employmentDetail->appointment_date)->format('Y-m-d')
                        : '';
                @endphp
                <tr data-hired="{{ $dateHiredIso }}">
                    <td>
                        <div class="emp-cell">
                            @if($employee->photo)
                                <img src="{{ $employee->photo }}" alt="{{ $fullName }}" class="emp-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ecebf6;">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border:2px solid #ecebf6;">
                                    {{ getInitials($fullName) }}
                                </div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $fullName }}</p>
                                <p class="emp-id">{{ $employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="position-cell">{{ $position }}</td>
                    <td><span class="dept-tag">{{ $department }}</span></td>
                    <td><span class="badge-emptype">{{ $empType }}</span></td>
                    <td style="font-size: 12px; color: #56547a; white-space: nowrap; text-align: center;">{{ $dateHired }}</td>
                    <td style="text-align: center;"><span class="badge-status {{ $status === 'Active' ? 'processed' : 'on-hold' }}">{{ $status }}</span></td>
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
                    <td colspan="7" style="text-align: center; padding: 40px; color: #56547a;">
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
            <select id="rowsPerPageSelect" onchange="changeRowsPerPage(this.value)" style="padding: 6px 12px; border: 1.5px solid #ecebf6; border-radius: 6px; font-size: 12px; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Helvetica Neue", Arial, sans-serif; color: #0b044d; background: #fff; cursor: pointer;">
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
