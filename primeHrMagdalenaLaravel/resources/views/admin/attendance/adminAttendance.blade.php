@extends('layouts.app')

@section('content')
@include('admin.topbar.attendanceTopbar')
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

$startDateDisplay = request('start_date', now()->startOfMonth()->format('Y-m-d'));
$endDateDisplay = request('end_date', now()->endOfMonth()->format('Y-m-d'));
$periodDisplay = date('M d, Y', strtotime($startDateDisplay)) . ' - ' . date('M d, Y', strtotime($endDateDisplay));
@endphp

<div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">DTR Submitted</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $completeCount }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">{{ $incompleteCount }} incomplete</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Present</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPresent }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">{{ $periodDisplay }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Absences</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalAbsent }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Across all personnel</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Overtime Hours</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalOT }} hrs</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">{{ $totalLate }} late arrivals</p>
        </div>
    </div>
</div>

<section class="table-section">
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
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Present</th>
                    <th>On Leave</th>
                    <th>Absent</th>
                    <th>Late</th>
                    <th>Half Day</th>
                    <th>OT Hours</th>
                    <th>Rate</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($attendanceRecords as $index => $record)
                <tr>
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
                    <td style="color: #15803d; font-weight: 600; font-size: 13px;">{{ $record['present'] }}</td>
                    <td>
                        @if(isset($record['on_leave']) && $record['on_leave'] > 0)
                            <span style="color: #0369a1; font-weight: 600; font-size: 13px; display: flex; align-items: center; gap: 4px;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                </svg>
                                {{ $record['on_leave'] }}
                            </span>
                        @else
                            <span style="color: #9999bb; font-size: 13px;">—</span>
                        @endif
                    </td>
                    <td style="color: {{ $record['absent'] > 0 ? '#8e1e18' : '#9999bb' }}; font-weight: {{ $record['absent'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['absent'] }}</td>
                    <td style="color: {{ $record['late'] > 0 ? '#a16207' : '#9999bb' }}; font-weight: {{ $record['late'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['late'] }}</td>
                    <td style="color: {{ $record['halfday'] > 0 ? '#a16207' : '#9999bb' }}; font-size: 13px;">{{ $record['halfday'] }}</td>
                    <td style="color: {{ $record['overtime'] > 0 ? '#0b044d' : '#9999bb' }}; font-weight: {{ $record['overtime'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['overtime'] > 0 ? $record['overtime'] . ' hrs' : '—' }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ $record['rate'] }}%; height: 100%; background: {{ $record['rate'] >= 90 ? '#15803d' : ($record['rate'] >= 75 ? '#d9bb00' : '#8e1e18') }}; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d; white-space: nowrap;">{{ $record['rate'] }}%</span>
                        </div>
                    </td>
                    <td><span class="badge-status {{ $record['status'] === 'Complete' ? 'processed' : 'pending' }}">{{ $record['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick="openDTRModal({{ json_encode($record) }}, {{ $index }})">DTR</button>
                            <button class="btn-detailed" onclick="openDetailedDTRModal({{ $record['employee_id'] }}, '{{ $record['name'] }}', '{{ $record['id'] }}')">Detailed DTR</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($attendanceRecords) }}</strong> records</p>
    </div>
</section>

@include('admin.attendance.modals.dtrDetailModal')
@include('admin.attendance.modals.detailedDtrModal')
@include('admin.attendance.modals.editDtrModal')
@include('admin.attendance.modals.correctAttendanceModal')
@include('admin.attendance.modals.successModal')

@push('styles')
@vite('resources/css/adminAttendance.css')
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    window.periodDisplay = '{{ $periodDisplay }}';
    window.periodDisplayFile = '{{ str_replace([' ', ',', '-'], '_', $periodDisplay) }}';

    // Search functionality
    function searchAttendance(query) {
        const searchTerm = query.toLowerCase().trim();
        const tbody = document.querySelector('.payroll-table tbody');
        if (!tbody) return;

        if (!window.allAttendanceRows || window.allAttendanceRows.length === 0) {
            window.allAttendanceRows = Array.from(tbody.querySelectorAll('tr'));
        }

        const filtered = window.allAttendanceRows.filter(row => {
            const name = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
            const id = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
            const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
            return searchTerm === '' || name.includes(searchTerm) || id.includes(searchTerm) || dept.includes(searchTerm);
        });

        tbody.innerHTML = '';
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" style="text-align: center; padding: 40px; color: #6b6a8a;">No records found matching your search.</td></tr>';
        } else {
            filtered.forEach(row => tbody.appendChild(row.cloneNode(true)));
        }
    }
</script>
@vite('resources/js/adminAttendance.js')
@endpush
@endsection

