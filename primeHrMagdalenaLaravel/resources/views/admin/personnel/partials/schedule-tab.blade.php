<!-- Work Schedules Tab -->
<section class="table-section tab-content personnel-table-section" id="schedules">
    <div class="table-header">
        <div>
            <h3 class="table-title">Work Schedules</h3>
            <p class="table-sub">Manage employee work schedules � {{ $employees->count() }} employees</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="schedDepartmentFilter" onchange="applyScheduleFilters()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <button class="btn-export" data-export-url="{{ route('admin.schedules.export') }}" onclick="exportSchedules(this)">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            {{-- The same solid pill as "Add Employee" on the Employee Records
                 tab. It used to be a .modal-btn-primary carrying inline
                 padding, size and a hard-coded navy background — which both
                 reproduced .btn-export's metrics by hand and pinned the colour
                 so it could not follow the theme. The classes supply all of it. --}}
            <button class="btn-export adm-btn-primary-solid" onclick="openBulkScheduleModal()">
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
                    <th class="row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody id="scheduleTableBody">
                @forelse($employees as $index => $employee)
                @php
                    $fullName = trim($employee->first_name . ' ' . ($employee->middle_name ? substr($employee->middle_name, 0, 1) . '. ' : '') . $employee->last_name . ($employee->suffix ? ' ' . $employee->suffix : ''));
                    $department = $employee->employmentDetail && $employee->employmentDetail->departmentRelation
                        ? $employee->employmentDetail->departmentRelation->name
                        : 'N/A';
                    // One rule for "the schedule in force today", shared with
                    // the CSV export — see Employee::currentSchedule().
                    $currentSchedule = $employee->currentSchedule();
                @endphp
                <tr>
                    <td>
                        <div class="emp-cell">
                            @if($employee->photo)
                                <img src="{{ $employee->photo }}" alt="{{ $fullName }}" class="emp-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--gp-border);">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border:2px solid var(--gp-border);">
                                    {{ getInitials($fullName) }}
                                </div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $fullName }}</p>
                                <p class="emp-id">{{ $employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $department }}</span></td>
                    <td style="font-size: 13px; color: var(--gp-pri); font-weight: 600;">{{ $currentSchedule ? \Carbon\Carbon::parse($currentSchedule->am_in)->format('g:i A') : '--:--' }}</td>
                    <td style="font-size: 13px; color: var(--gp-pri); font-weight: 600;">{{ $currentSchedule ? \Carbon\Carbon::parse($currentSchedule->am_out)->format('g:i A') : '--:--' }}</td>
                    <td style="font-size: 13px; color: var(--gp-pri); font-weight: 600;">{{ $currentSchedule ? \Carbon\Carbon::parse($currentSchedule->pm_in)->format('g:i A') : '--:--' }}</td>
                    <td style="font-size: 13px; color: var(--gp-pri); font-weight: 600;">{{ $currentSchedule ? \Carbon\Carbon::parse($currentSchedule->pm_out)->format('g:i A') : '--:--' }}</td>
                    <td>
                        @if($currentSchedule)
                            <span class="badge-status processed">Active</span>
                        @elseif($employee->schedule->count() > 0)
                            <span class="badge-status pending">Scheduled</span>
                        @else
                            <span class="badge-status is-neutral">Not Set</span>
                        @endif
                    </td>
                    <td class="row-menu-cell">
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
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--gp-text-mid);">
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
            <select id="schedRowsPerPageSelect" onchange="changeScheduleRowsPerPage(this.value)" style="padding: 6px 12px; border: 1.5px solid var(--gp-border); border-radius: 6px; font-size: 12px; font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif; color: var(--gp-pri); background: #fff; cursor: pointer;">
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
