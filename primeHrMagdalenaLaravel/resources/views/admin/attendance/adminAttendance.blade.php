@extends('layouts.app')

@section('content')
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

$attendanceRecords = [
    ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'present' => 22, 'absent' => 0, 'late' => 1, 'halfday' => 0, 'overtime' => 3.5, 'status' => 'Complete'],
    ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'present' => 20, 'absent' => 1, 'late' => 2, 'halfday' => 1, 'overtime' => 0, 'status' => 'Complete'],
    ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'present' => 21, 'absent' => 0, 'late' => 0, 'halfday' => 0, 'overtime' => 8.0, 'status' => 'Complete'],
    ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'present' => 19, 'absent' => 2, 'late' => 3, 'halfday' => 0, 'overtime' => 0, 'status' => 'Incomplete'],
    ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'present' => 22, 'absent' => 0, 'late' => 0, 'halfday' => 0, 'overtime' => 2.0, 'status' => 'Complete'],
    ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'present' => 18, 'absent' => 3, 'late' => 1, 'halfday' => 1, 'overtime' => 0, 'status' => 'Incomplete'],
    ['id' => 'PGS-0342', 'name' => 'Grace A. Villanueva', 'position' => 'Budget Officer II', 'dept' => 'Office of the Mun. Budget', 'present' => 21, 'absent' => 1, 'late' => 0, 'halfday' => 0, 'overtime' => 1.5, 'status' => 'Complete'],
    ['id' => 'PGS-0358', 'name' => 'Ramon D. Cruz', 'position' => 'Agriculturist I', 'dept' => 'Office of the Mun. Agriculturist', 'present' => 20, 'absent' => 0, 'late' => 4, 'halfday' => 0, 'overtime' => 0, 'status' => 'Complete'],
];

$totalPresent = array_sum(array_column($attendanceRecords, 'present'));
$totalAbsent = array_sum(array_column($attendanceRecords, 'absent'));
$totalLate = array_sum(array_column($attendanceRecords, 'late'));
$totalOT = array_sum(array_column($attendanceRecords, 'overtime'));
$completeCount = count(array_filter($attendanceRecords, fn($r) => $r['status'] === 'Complete'));
$incompleteCount = count(array_filter($attendanceRecords, fn($r) => $r['status'] === 'Incomplete'));
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
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
            <p class="stat-sub">June 2025</p>
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
            <h3 class="table-title">Daily Time Record — June 2025</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ count($attendanceRecords) }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option>January</option>
                <option>February</option>
                <option>March</option>
                <option>April</option>
                <option>May</option>
                <option selected>June</option>
                <option>July</option>
                <option>August</option>
                <option>September</option>
                <option>October</option>
                <option>November</option>
                <option>December</option>
            </select>
            <select class="filter-select">
                <option selected>2025</option>
                <option>2024</option>
                <option>2023</option>
            </select>
            <select class="filter-select">
                <option>All Departments</option>
                <option>Office of the Mayor</option>
                <option>Office of the Mun. Engineer</option>
                <option>Municipal Health Office</option>
                <option>MSWD – Pagsanjan</option>
                <option>Office of the Mun. Treasurer</option>
            </select>
            <select class="filter-select">
                <option>All Status</option>
                <option>Complete</option>
                <option>Incomplete</option>
            </select>
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
                @php
                    $workingDays = $record['present'] + $record['absent'] + $record['halfday'];
                    $rate = $workingDays > 0 ? round(($record['present'] / $workingDays) * 100) : 0;
                @endphp
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
                    <td style="color: {{ $record['absent'] > 0 ? '#8e1e18' : '#9999bb' }}; font-weight: {{ $record['absent'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['absent'] }}</td>
                    <td style="color: {{ $record['late'] > 0 ? '#a16207' : '#9999bb' }}; font-weight: {{ $record['late'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['late'] }}</td>
                    <td style="color: {{ $record['halfday'] > 0 ? '#a16207' : '#9999bb' }}; font-size: 13px;">{{ $record['halfday'] }}</td>
                    <td style="color: {{ $record['overtime'] > 0 ? '#0b044d' : '#9999bb' }}; font-weight: {{ $record['overtime'] > 0 ? '600' : '400' }}; font-size: 13px;">{{ $record['overtime'] > 0 ? $record['overtime'] . ' hrs' : '—' }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ $rate }}%; height: 100%; background: {{ $rate >= 90 ? '#15803d' : ($rate >= 75 ? '#d9bb00' : '#8e1e18') }}; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d; white-space: nowrap;">{{ $rate }}%</span>
                        </div>
                    </td>
                    <td><span class="badge-status {{ $record['status'] === 'Complete' ? 'processed' : 'pending' }}">{{ $record['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">DTR</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($attendanceRecords) }}</strong> of <strong>{{ count($attendanceRecords) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<style>
.badge-emptype {
    font-size: 11px; color: #0b044d; background: #f0effe;
    padding: 3px 10px; border-radius: 20px; font-weight: 600;
    border: 1px solid #dddcf0;
}
.btn-edit {
    padding: 6px 16px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-edit:hover { background: #e8e7f5; }
.row-actions { display: flex; gap: 6px; }
.table-footer {
    padding: 16px 24px; border-top: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: center;
}
.table-footer p { font-size: 13px; color: #6b6a8a; }
.pagination { display: flex; gap: 6px; }
.page-btn {
    width: 32px; height: 32px; border: 1px solid #e8e7f5;
    border-radius: 6px; background: #fff; color: #6b6a8a;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.page-btn.active { background: #0b044d; color: #fff; border-color: #0b044d; }
.page-btn:hover { background: #f7f6ff; }
.payroll-summary-bar {
    display: flex; align-items: center; gap: 20px;
    padding: 14px 24px; background: #fafafe;
    border: 1px solid #f0effe; border-radius: 8px;
}
.psummary-item { display: flex; flex-direction: column; gap: 2px; }
.psummary-item span { font-size: 11px; color: #9999bb; font-weight: 500; }
.psummary-item strong { font-size: 13px; color: #0b044d; font-weight: 600; }
.psummary-divider { width: 1px; height: 28px; background: #e8e7f5; }
</style>
@endsection
