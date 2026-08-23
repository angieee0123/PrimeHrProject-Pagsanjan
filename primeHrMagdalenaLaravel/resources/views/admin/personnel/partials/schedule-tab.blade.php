<!-- Work Schedules Tab -->
<section class="table-section tab-content personnel-table-section" id="schedules">
    <div class="table-header">
        <div>
            <h3 class="table-title">Work Schedules</h3>
            <p class="table-sub">Manage employee work schedules · <strong>{{ $employees->count() }}</strong> employees</p>
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
        {{--
            Widths are declared here and the table is `table-layout: fixed`,
            so the four time columns keep the same width whether they hold
            "10:00 AM" or "--:--" -- they used to re-measure per page, which
            left the AM/PM headings sitting over different columns depending
            on who happened to be listed. Each column's alignment is set on
            `th` and `td` together in adminPersonnel.css, which is what keeps
            a centred time under a centred heading.
        --}}
        <table class="payroll-table sched-table" id="scheduleTable">
            <colgroup>
                <col class="scol-employee">
                <col class="scol-dept">
                <col class="scol-time">
                <col class="scol-time">
                <col class="scol-time">
                <col class="scol-time">
                <col class="scol-status">
                <col class="scol-actions">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Employee</th>
                    <th scope="col">Department</th>
                    <th scope="col">AM In</th>
                    <th scope="col">AM Out</th>
                    <th scope="col">PM In</th>
                    <th scope="col">PM Out</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="row-menu-head">Actions</th>
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
                    // Rendered four times per row; formatting it here keeps the
                    // Carbon call and the "never set" fallback in one place
                    // rather than repeated down the row with the styling.
                    $slot = fn ($value) => $value
                        ? \Carbon\Carbon::parse($value)->format('g:i A')
                        : null;

                    // Four states, resolved on the model so the CSV export
                    // reports the same ones -- see Employee::scheduleStatus().
                    $scheduleStatus = $employee->scheduleStatus();
                    $statusBadge = [
                        'active'   => 'processed',
                        'upcoming' => 'is-info',
                        'expired'  => 'is-warning',
                        'none'     => 'is-neutral',
                    ];
                    // A schedule lapsing inside a month is the row an HR
                    // officer needs to act on before it does.
                    $endsSoon = $scheduleStatus['state'] === 'active'
                        && $scheduleStatus['date']
                        && \Carbon\Carbon::parse($scheduleStatus['date'])->lessThanOrEqualTo(now()->addDays(30));
                @endphp
                <tr class="sched-row @if(in_array($scheduleStatus['state'], ['expired', 'none'])) is-unscheduled @endif">
                    <td>
                        <div class="emp-cell">
                            @if($employee->photo)
                                <img src="{{ $employee->photo }}" alt="" class="emp-avatar sched-avatar" loading="lazy">
                            @else
                                <div class="emp-avatar sched-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                    {{ getInitials($fullName) }}
                                </div>
                            @endif
                            <div class="emp-cell-text">
                                <p class="emp-name">{{ $fullName }}</p>
                                <p class="emp-id">{{ $employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag" title="{{ $department }}">{{ $department }}</span></td>
                    @foreach(['am_in', 'am_out', 'pm_in', 'pm_out'] as $field)
                        @php $time = $currentSchedule ? $slot($currentSchedule->{$field}) : null; @endphp
                        <td class="sched-time">
                            @if($time)
                                <span class="sched-time-value">{{ $time }}</span>
                            @else
                                {{-- Muted, so a row with no schedule reads as
                                     empty at a glance instead of as four
                                     equally-weighted values. --}}
                                <span class="sched-time-empty" title="No schedule set">--:--</span>
                            @endif
                        </td>
                    @endforeach
                    <td>
                        {{-- The badge alone said whether a schedule was in
                             force, never until when -- so an active row gave
                             no warning that it lapses on Friday. The date the
                             state turns over sits under it: when an active
                             schedule ends, when an upcoming one starts, when
                             a lapsed one ended. --}}
                        <div class="sched-status">
                            <span class="badge-status {{ $statusBadge[$scheduleStatus['state']] }}">{{ $scheduleStatus['label'] }}</span>
                            @if($scheduleStatus['date'])
                                <span class="sched-status-when @if($endsSoon) is-soon @endif"
                                      title="{{ $scheduleStatus['note'] }} {{ \Carbon\Carbon::parse($scheduleStatus['date'])->format('l, j F Y') }}">
                                    {{ $scheduleStatus['note'] }} {{ \Carbon\Carbon::parse($scheduleStatus['date'])->format('M j, Y') }}
                                </span>
                            @endif
                        </div>
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
                <tr class="sched-empty-row">
                    <td colspan="8">
                        <div class="sched-empty">
                            <span class="sched-empty-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            </span>
                            <p class="sched-empty-title">No employees found</p>
                            <p class="sched-empty-text">Employees added under the Employee Records tab appear here for scheduling.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="sched-footer-left">
            <p>Showing <strong id="schedShowingStart">0</strong>-<strong id="schedShowingEnd">0</strong> of <strong id="schedTotalRecords">{{ $employees->count() }}</strong> records</p>
            <select id="schedRowsPerPageSelect" class="filter-select sched-perpage" onchange="changeScheduleRowsPerPage(this.value)" aria-label="Rows per page">
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
