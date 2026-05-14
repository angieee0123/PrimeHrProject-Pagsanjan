<section class="table-section" id="detailed-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Detailed Time Record — {{ $periodDisplay }}</h3>
            <p class="table-sub">Daily attendance logs with timestamps · {{ $detailedPagination['total'] }} records</p>
        </div>
        <div class="table-actions">
            <form method="GET" action="{{ route('admin.attendance') }}" id="detailedFilterForm" style="display: contents;">
                <input type="hidden" name="tab" value="detailed">
                <select class="filter-select" name="employee_name" style="min-width: 200px;">
                    <option value="">All Employees</option>
                    @foreach(\App\Models\Employee::orderBy('first_name')->get() as $emp)
                        @php
                            $fullName = trim($emp->first_name . ' ' . ($emp->middle_name ? $emp->middle_name . ' ' : '') . $emp->last_name);
                        @endphp
                        <option value="{{ $fullName }}" {{ request('employee_name') == $fullName ? 'selected' : '' }}>
                            {{ $fullName }} ({{ $emp->employee_id }})
                        </option>
                    @endforeach
                </select>
                <input type="date" class="filter-select" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" class="filter-select" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                <select class="filter-select" name="department">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-filter-main">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                @if(request('employee_name') || request('department'))
                    <a href="{{ route('admin.attendance', ['tab' => 'detailed', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                       class="btn-filter-main" 
                       style="background: #f7f6ff; color: #0b044d; border: 1px solid #e8e7f5;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Clear
                    </a>
                @endif
            </form>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export All
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Employee</th>
                    <th>Time In</th>
                    <th>Time Out</th>
                    <th>Late</th>
                    <th>Undertime</th>
                    <th>Hours Worked</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($detailedRecords as $record)
                @php
                    $rowClass = '';
                    if ($record['day'] === 'Saturday' || $record['day'] === 'Sunday') {
                        $rowClass = 'day-weekend';
                    } elseif ($record['is_absent']) {
                        $rowClass = 'day-absent';
                    } elseif (!$record['am_in'] && !$record['am_out'] && !$record['pm_in'] && !$record['pm_out'] && !$record['is_on_leave']) {
                        $rowClass = 'day-absent';
                    } elseif (($record['am_in'] && !$record['am_out']) || ($record['pm_in'] && !$record['pm_out']) || (!$record['am_in'] && $record['am_out']) || (!$record['pm_in'] && $record['pm_out'])) {
                        $rowClass = 'day-needs-review';
                    }
                @endphp
                <tr class="{{ $rowClass }}">
                    <td>
                        <strong>{{ $record['date'] }}</strong>
                        @if($record['is_absent'])
                            <span class="badge-absent" style="display: inline-block; margin-left: 6px; padding: 2px 6px; background: #fee; color: #8e1e18; font-size: 10px; font-weight: 600; border-radius: 3px;">ABSENT</span>
                        @elseif(!$record['am_in'] && !$record['am_out'] && !$record['pm_in'] && !$record['pm_out'] && !$record['is_on_leave'] && $record['day'] !== 'Saturday' && $record['day'] !== 'Sunday')
                            <span class="badge-absent" style="display: inline-block; margin-left: 6px; padding: 2px 6px; background: #fee; color: #8e1e18; font-size: 10px; font-weight: 600; border-radius: 3px;">ABSENT</span>
                        @elseif(($record['am_in'] && !$record['am_out']) || ($record['pm_in'] && !$record['pm_out']) || (!$record['am_in'] && $record['am_out']) || (!$record['pm_in'] && $record['pm_out']))
                            <span class="badge-incomplete" style="display: inline-block; margin-left: 6px; padding: 2px 6px; background: #ffe8e8; color: #8e1e18; font-size: 10px; font-weight: 600; border-radius: 3px;">Incomplete</span>
                        @endif
                    </td>
                    <td>{{ $record['day'] }}</td>
                    <td>
                        <p class="emp-name" style="font-size: 13.5px; font-weight: 600; color: #0b044d; margin: 0;">{{ $record['employee_name'] }}</p>
                    </td>
                    <td>
                        @if($record['is_on_leave'])
                            <span style="color: #0369a1; font-weight: 600; font-size: 12px;">ON LEAVE</span>
                        @elseif($record['is_absent'])
                            <span class="log-missing" style="color: #9999bb; font-size: 12px;">No Record</span>
                        @else
                            <div style="line-height: 1.6;">
                                @if($record['am_in'])
                                    <div style="font-weight: 600; color: #0b044d;">{{ $record['am_in'] }}</div>
                                @endif
                                @if($record['pm_in'])
                                    <div style="font-size: 11px; color: #6b6a8a;">{{ $record['pm_in'] }}</div>
                                @endif
                                @if(!$record['am_in'] && !$record['pm_in'])
                                    <span class="log-missing" style="color: #9999bb; font-size: 12px;">No Record</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($record['is_on_leave'])
                            <span style="color: #0369a1; font-weight: 600; font-size: 12px;">ON LEAVE</span>
                        @elseif($record['is_absent'])
                            <span class="log-missing" style="color: #9999bb; font-size: 12px;">No Record</span>
                        @else
                            <div style="line-height: 1.6;">
                                @if($record['am_out'])
                                    <div style="font-size: 11px; color: #6b6a8a;">{{ $record['am_out'] }}</div>
                                @endif
                                @if($record['pm_out'])
                                    <div style="font-weight: 600; color: #0b044d;">{{ $record['pm_out'] }}</div>
                                @endif
                                @if(!$record['am_out'] && !$record['pm_out'])
                                    <span class="log-missing" style="color: #9999bb; font-size: 12px;">No Record</span>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($record['late_minutes'] > 0)
                            @php
                                $lateHrs = floor($record['late_minutes'] / 60);
                                $lateMins = $record['late_minutes'] % 60;
                            @endphp
                            <span class="log-late" style="color: #a16207; font-weight: 600;">
                                @if($lateHrs > 0)
                                    {{ $lateHrs }}h {{ $lateMins }}m
                                @else
                                    {{ $lateMins }} min
                                @endif
                            </span>
                        @else
                            <span style="color: #9999bb;">—</span>
                        @endif
                    </td>
                    <td>
                        @if($record['undertime_minutes'] > 0)
                            @php
                                $utHrs = floor($record['undertime_minutes'] / 60);
                                $utMins = $record['undertime_minutes'] % 60;
                            @endphp
                            <span class="log-late" style="color: #8e1e18; font-weight: 600;">
                                @if($utHrs > 0)
                                    {{ $utHrs }}h {{ $utMins }}m
                                @else
                                    {{ $utMins }} min
                                @endif
                            </span>
                        @else
                            <span style="color: #9999bb;">—</span>
                        @endif
                    </td>
                    <td><strong>{{ $record['total_hours'] }} hrs</strong></td>
                    <td>
                        @if($record['is_on_leave'] && $record['leave_info'])
                            <span class="badge-status processed">On Leave</span>
                            <br>
                            <small style="color: #6b6a8a; font-size: 10px;">
                                {{ $record['leave_info']['leave_type'] }}
                            </small>
                        @elseif($record['is_absent'])
                            <span class="badge-status on-hold">Absent</span>
                        @elseif($record['late_minutes'] > 0)
                            <span class="badge-status pending">Late</span>
                        @elseif($record['accredited_hours'] >= 480)
                            <span class="badge-status processed">Present</span>
                        @else
                            <span class="badge-status on-hold">Incomplete</span>
                        @endif
                    </td>
                    <td>
                        @if($record['attendance_id'])
                            <button class="btn-edit-time" onclick="openCorrectModal({{ $record['attendance_id'] }}, '{{ $record['date'] }}')" title="Edit time records">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                                Edit
                            </button>
                        @else
                            <button class="btn-edit-time" onclick="openCorrectModal('new_{{ $record['employee_id'] }}_{{ \Carbon\Carbon::parse($record['date'])->format('Y-m-d') }}', '{{ $record['date'] }}')" title="Add time records">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="12" y1="5" x2="12" y2="19"/>
                                    <line x1="5" y1="12" x2="19" y2="12"/>
                                </svg>
                                Add
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; padding: 40px; color: #6b6a8a;">
                        No attendance records found for the selected period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ $detailedPagination['from'] }}</strong> to <strong>{{ $detailedPagination['to'] }}</strong> of <strong>{{ $detailedPagination['total'] }}</strong> daily attendance records</p>
        
        @if($detailedPagination['last_page'] > 1)
        <div class="pagination" style="display: flex; gap: 8px; align-items: center;">
            @if($detailedPagination['current_page'] > 1)
                <a href="{{ route('admin.attendance', array_merge(request()->all(), ['page' => $detailedPagination['current_page'] - 1, 'tab' => 'detailed'])) }}" class="pagination-btn">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    Previous
                </a>
            @endif
            
            @for($i = max(1, $detailedPagination['current_page'] - 2); $i <= min($detailedPagination['last_page'], $detailedPagination['current_page'] + 2); $i++)
                <a href="{{ route('admin.attendance', array_merge(request()->all(), ['page' => $i, 'tab' => 'detailed'])) }}" 
                   class="pagination-btn {{ $i == $detailedPagination['current_page'] ? 'active' : '' }}">
                    {{ $i }}
                </a>
            @endfor
            
            @if($detailedPagination['current_page'] < $detailedPagination['last_page'])
                <a href="{{ route('admin.attendance', array_merge(request()->all(), ['page' => $detailedPagination['current_page'] + 1, 'tab' => 'detailed'])) }}" class="pagination-btn">
                    Next
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @endif
        </div>
        @endif
    </div>
</section>
