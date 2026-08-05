<section class="table-section" id="summary-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">Daily Time Record</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $periodDisplay }} · {{ count($attendanceRecords) }} records</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table attendance-summary-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th class="th-center">Present</th>
                    <th class="th-center">Leave</th>
                    <th class="th-center">Absent</th>
                    <th class="th-center">Late</th>
                    <th class="th-center">½ Day</th>
                    <th class="th-center">OT</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th class="row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody id="attendanceSummaryBody">
                @foreach($attendanceRecords as $index => $record)
                <tr data-department="{{ $record['dept'] }}" data-status="{{ $record['status'] }}" data-name="{{ $record['name'] }}" data-id="{{ $record['id'] }}">
                    <td>
                        <div class="emp-cell">
                            @if(isset($record['photo']) && $record['photo'])
                                <img src="{{ $record['photo'] }}" alt="{{ $record['name'] }}" class="emp-avatar">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                    {{ getInitials($record['name']) }}
                                </div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $record['name'] }}</p>
                                <p class="emp-id">{{ $record['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag" data-tooltip="{{ $record['dept'] }}">{{ $record['dept'] }}</span></td>
                    <td class="num-cell num-present">{{ $record['present'] }}</td>
                    <td class="num-cell">
                        @if(isset($record['on_leave']) && $record['on_leave'] > 0)
                            <span class="num-leave">{{ $record['on_leave'] }}</span>
                        @else
                            <span class="num-muted">—</span>
                        @endif
                    </td>
                    <td class="num-cell {{ $record['absent'] > 0 ? 'num-absent' : 'num-muted' }}">{{ $record['absent'] }}</td>
                    <td class="num-cell {{ $record['late'] > 0 ? 'num-late' : 'num-muted' }}">{{ $record['late'] }}</td>
                    <td class="num-cell {{ $record['halfday'] > 0 ? 'num-late' : 'num-muted' }}">{{ $record['halfday'] }}</td>
                    <td class="num-cell {{ $record['overtime'] > 0 ? 'num-ot' : 'num-muted' }}">{{ $record['overtime'] > 0 ? $record['overtime'] : '—' }}</td>
                    <td>
                        <div class="rate-cell">
                            <div class="rate-track">
                                <div class="rate-fill {{ $record['rate'] >= 90 ? 'good' : ($record['rate'] >= 75 ? 'mid' : 'low') }}" style="width: {{ $record['rate'] }}%;"></div>
                            </div>
                            <span class="rate-pct">{{ $record['rate'] }}%</span>
                        </div>
                    </td>
                    <td><span class="badge-status {{ $record['status'] === 'Complete' ? 'processed' : 'pending' }}">{{ $record['status'] }}</span></td>
                    <td class="row-menu-cell">
                        <div class="row-actions">
                            <button class="act-btn" title="Actions" onclick="toggleActionMenu(event, 'action-menu-{{ $index }}')">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                            </button>
                            <div id="action-menu-{{ $index }}" class="action-dropdown">
                                <button class="action-dropdown-item" onclick='openDTRModal(@json($record), {{ $index }}); closeAllActionMenus()'>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Quick View
                                </button>
                                <button class="action-dropdown-item" onclick="openDetailedDTRModal({{ $record['employee_id'] }}, '{{ addslashes($record['name']) }}', '{{ $record['id'] }}'); closeAllActionMenus()">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    Detailed DTR
                                </button>
                                <button class="action-dropdown-item" onclick='openEditModal(@json($record)); closeAllActionMenus()'>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="table-footer-left">
            <p id="attendanceSummaryFooter">Showing <strong id="attendanceRowStart">1</strong>-<strong id="attendanceRowEnd">{{ min(10, count($attendanceRecords)) }}</strong> of <strong id="attendanceRowTotal">{{ count($attendanceRecords) }}</strong> records</p>
            <select id="attendanceRowsPerPage" class="rows-select" onchange="changeAttendanceRowsPerPage()">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
        <div class="pagination" id="attendancePaginationControls"></div>
    </div>
</section>
