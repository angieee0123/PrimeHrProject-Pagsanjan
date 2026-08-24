<!-- Employee Records Tab -->
<section class="table-section tab-content active personnel-table-section" id="employees">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Records</h3>
            {{-- "14 of 14" was the same total printed twice, so filtering or
                 searching left the heading claiming the whole roster was still
                 on screen. The first figure is now written by the same code
                 that writes the footer count. --}}
            <p class="table-sub">Municipal Government of Pagsanjan · <strong id="recordsShown">{{ $employees->count() }}</strong> of <strong>{{ $employees->count() }}</strong> records</p>
        </div>
        <div class="table-actions">
            {{-- Same green outline pill as Departments' Bulk Import, off the one
                 shared .btn-export-green rule. The colour used to be three inline
                 declarations here, one of them a hard-coded white no theme could
                 reach. --}}
            <button class="btn-export btn-export-green" onclick="openBulkImportModal()">
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
        {{--
            Same layout rule as the Work Schedules tab: widths are declared on
            the <colgroup>, the table is `table-layout: fixed`, and each
            column's alignment is set on `th` and `td` together in
            adminPersonnel.css -- a heading cannot end up over content aligned
            the other way. The widths used to be inline `style="width:%"` on the
            `th` with `text-align: center` on three of the headings and on only
            two of the matching cells, so a centred "Type" heading sat over a
            left-aligned badge, and every column re-measured itself per page:
            the headings shifted depending on who happened to be listed.
        --}}
        <table class="payroll-table personnel-records-table" id="personnelTable">
            <colgroup>
                <col class="pcol-employee">
                <col class="pcol-position">
                <col class="pcol-dept">
                <col class="pcol-type">
                <col class="pcol-date">
                <col class="pcol-status">
                <col class="pcol-actions">
            </colgroup>
            <thead>
                <tr>
                    {{-- The heading is a real <button> rather than an onclick on
                         the <th>: sorting is this table's primary control and a
                         bare cell cannot be reached from the keyboard. --}}
                    <th scope="col">
                        <button type="button" class="th-sort" onclick="sortTable(0)">
                            <span>Employee</span>
                            <svg class="th-sort-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="th-sort" onclick="sortTable(1)">
                            <span>Position</span>
                            <svg class="th-sort-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="th-sort" onclick="sortTable(2)">
                            <span>Department</span>
                            <svg class="th-sort-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="th-sort" onclick="sortTable(3)">
                            <span>Type</span>
                            <svg class="th-sort-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="th-sort" onclick="sortTable(4)">
                            <span>Appointed</span>
                            <svg class="th-sort-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </button>
                    </th>
                    <th scope="col">
                        <button type="button" class="th-sort" onclick="sortTable(5)">
                            <span>Status</span>
                            <svg class="th-sort-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="18 15 12 9 6 15"></polyline></svg>
                        </button>
                    </th>
                    <th scope="col" class="row-menu-head">Actions</th>
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
                <tr class="prs-row" data-hired="{{ $dateHiredIso }}">
                    <td>
                        <div class="emp-cell">
                            @if($employee->photo)
                                <img src="{{ $employee->photo }}" alt="" class="emp-avatar" loading="lazy">
                            @else
                                {{-- Only the hue is inline; it is per-row data.
                                     The size, the ring and the type used to be
                                     eight more declarations repeated on both
                                     branches of this @if. --}}
                                <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                    {{ getInitials($fullName) }}
                                </div>
                            @endif
                            <div class="emp-cell-text">
                                <p class="emp-name">{{ $fullName }}</p>
                                <p class="emp-id">{{ $employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="position-cell">{{ $position }}</span></td>
                    <td><span class="dept-tag">{{ $department }}</span></td>
                    <td><span class="badge-emptype">{{ $empType }}</span></td>
                    <td class="prs-date">{{ $dateHired }}</td>
                    <td><span class="badge-status {{ $status === 'Active' ? 'processed' : 'on-hold' }}">{{ $status }}</span></td>
                    <td class="row-menu-cell">
                        <div class="row-actions">
                            {{-- Desktop: one icon per action. These carried their
                                 labels inline, which needed a ~280px column and
                                 made the buttons the widest thing on the row
                                 rather than the person. Each label survives as
                                 the tooltip and as the accessible name, and the
                                 tablet menu below still spells every one out. --}}
                            <div class="action-buttons-desktop">
                                <button class="btn-view" onclick="viewEmployee({{ $employee->id }})" title="View details" aria-label="View {{ $fullName }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                </button>
                                <button class="btn-edit" onclick="editEmployee({{ $employee->id }})" title="Edit record" aria-label="Edit {{ $fullName }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>
                                <button class="btn-qr" onclick="generateQRCode({{ $employee->id }}, '{{ $fullName }}', '{{ $employee->qr_payload }}')" title="Generate QR code" aria-label="Generate QR code for {{ $fullName }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="3" width="7" height="7"/>
                                        <rect x="14" y="3" width="7" height="7"/>
                                        <rect x="14" y="14" width="7" height="7"/>
                                        <rect x="3" y="14" width="7" height="7"/>
                                    </svg>
                                </button>
                                @if($status === 'Active')
                                <button class="btn-deactivate" onclick="confirmStatusChange({{ $employee->id }}, 'Inactive')" title="Deactivate account" aria-label="Deactivate {{ $fullName }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"/>
                                        <line x1="15" y1="9" x2="9" y2="15"/>
                                        <line x1="9" y1="9" x2="15" y2="15"/>
                                    </svg>
                                </button>
                                @else
                                <button class="btn-activate" onclick="confirmStatusChange({{ $employee->id }}, 'Active')" title="Activate account" aria-label="Activate {{ $fullName }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                        <polyline points="22 4 12 14.01 9 11.01"/>
                                    </svg>
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
                                    <button class="action-menu-item" onclick="generateQRCode({{ $employee->id }}, '{{ $fullName }}', '{{ $employee->qr_payload }}')">
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
                <tr class="prs-empty-row">
                    <td colspan="7">
                        <div class="prs-empty">
                            <span class="prs-empty-icon">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                    <circle cx="9" cy="7" r="4"/>
                                    <line x1="19" y1="8" x2="19" y2="14"/>
                                    <line x1="22" y1="11" x2="16" y2="11"/>
                                </svg>
                            </span>
                            <p class="prs-empty-title">No employees on record</p>
                            <p class="prs-empty-text">Use “Add Employee” to register new personnel, or Bulk Import to bring in an existing roster.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="prs-footer-left">
            <p>Showing <strong id="showingStart">1</strong>-<strong id="showingEnd">10</strong> of <strong id="totalRecords">{{ $employees->count() }}</strong> records</p>
            {{-- A whole font stack, a border and a hard-coded white used to sit
                 inline on this control, so it could not follow the theme. --}}
            <select id="rowsPerPageSelect" class="prs-perpage" onchange="changeRowsPerPage(this.value)" aria-label="Rows per page">
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
