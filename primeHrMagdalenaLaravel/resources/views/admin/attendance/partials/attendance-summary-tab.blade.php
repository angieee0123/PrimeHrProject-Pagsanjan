<section class="table-section" id="summary-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">Daily Time Record — {{ $periodDisplay }}</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ count($attendanceRecords) }} records</p>
        </div>
        <div class="table-actions">
            <form method="GET" action="{{ route('admin.attendance') }}" id="filterForm" style="display: contents;">
                <input type="date" class="filter-select" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" class="filter-select" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                <select class="filter-select" name="department">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <select class="filter-select" name="status">
                    <option value="">All Status</option>
                    <option value="Complete" {{ request('status') == 'Complete' ? 'selected' : '' }}>Complete</option>
                    <option value="Incomplete" {{ request('status') == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                </select>
                <button type="submit" class="btn-filter-main">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
            </form>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="payroll-summary-bar" style="margin-top: 0; margin-bottom: 16px;">
        <div class="psummary-item">
            <span>Total Present</span>
            <strong style="color: #15803d;">{{ $totalPresent }} days</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>On Leave</span>
            <strong style="color: #0369a1;">{{ $totalOnLeave ?? 0 }} days</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Total Absent</span>
            <strong style="color: #8e1e18;">{{ $totalAbsent }} days</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Late Arrivals</span>
            <strong style="color: #a16207;">{{ $totalLate }} times</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Overtime</span>
            <strong>{{ $totalOT }} hrs</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Records</span>
            <strong>{{ count($attendanceRecords) }}</strong>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table attendance-summary-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Employee</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 5%; text-align: center;">Present</th>
                    <th style="width: 5%; text-align: center;">Leave</th>
                    <th style="width: 5%; text-align: center;">Absent</th>
                    <th style="width: 5%; text-align: center;">Late</th>
                    <th style="width: 5%; text-align: center;">½ Day</th>
                    <th style="width: 5%; text-align: center;">OT</th>
                    <th style="width: 10%;">Rate</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 15%;">Actions</th>
                </tr>
            </thead>
            <tbody id="attendanceSummaryBody">
                @foreach($attendanceRecords as $index => $record)
                <tr data-department="{{ $record['dept'] }}" data-status="{{ $record['status'] }}" data-name="{{ $record['name'] }}" data-id="{{ $record['id'] }}">
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($record['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $record['name'] }}</p>
                                <p class="emp-id">{{ $record['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                    <td style="color: #15803d; font-weight: 600; font-size: 13px; text-align: center;">{{ $record['present'] }}</td>
                    <td style="text-align: center;">
                        @if(isset($record['on_leave']) && $record['on_leave'] > 0)
                            <span style="color: #0369a1; font-weight: 600; font-size: 13px;">{{ $record['on_leave'] }}</span>
                        @else
                            <span style="color: #9999bb; font-size: 13px;">—</span>
                        @endif
                    </td>
                    <td style="color: {{ $record['absent'] > 0 ? '#8e1e18' : '#9999bb' }}; font-weight: {{ $record['absent'] > 0 ? '600' : '400' }}; font-size: 13px; text-align: center;">{{ $record['absent'] }}</td>
                    <td style="color: {{ $record['late'] > 0 ? '#a16207' : '#9999bb' }}; font-weight: {{ $record['late'] > 0 ? '600' : '400' }}; font-size: 13px; text-align: center;">{{ $record['late'] }}</td>
                    <td style="color: {{ $record['halfday'] > 0 ? '#a16207' : '#9999bb' }}; font-size: 13px; text-align: center;">{{ $record['halfday'] }}</td>
                    <td style="color: {{ $record['overtime'] > 0 ? '#0b044d' : '#9999bb' }}; font-weight: {{ $record['overtime'] > 0 ? '600' : '400' }}; font-size: 12px; text-align: center;">{{ $record['overtime'] > 0 ? $record['overtime'] : '—' }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 40px;">
                                <div style="width: {{ $record['rate'] }}%; height: 100%; background: {{ $record['rate'] >= 90 ? '#15803d' : ($record['rate'] >= 75 ? '#d9bb00' : '#8e1e18') }}; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 11.5px; font-weight: 600; color: #0b044d; white-space: nowrap;">{{ $record['rate'] }}%</span>
                        </div>
                    </td>
                    <td><span class="badge-status {{ $record['status'] === 'Complete' ? 'processed' : 'pending' }}">{{ $record['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick='openDTRModal(@json($record), {{ $index }})'>DTR</button>
                            <button class="btn-detailed" onclick="openDetailedDTRModal({{ $record['employee_id'] }}, '{{ addslashes($record['name']) }}', '{{ $record['id'] }}')">Detailed DTR</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="attendanceSummaryFooter">Showing <strong id="attendanceRowStart">1</strong>-<strong id="attendanceRowEnd">{{ min(10, count($attendanceRecords)) }}</strong> of <strong id="attendanceRowTotal">{{ count($attendanceRecords) }}</strong> records</p>
            <select id="attendanceRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeAttendanceRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="attendancePaginationControls"></div>
    </div>
</section>
