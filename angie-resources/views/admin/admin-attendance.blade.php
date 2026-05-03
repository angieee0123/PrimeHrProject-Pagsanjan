@extends('layouts.app')

@section('title', 'Attendance · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
<style>
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-wrap svg {
    position: absolute;
    left: 10px;
    pointer-events: none;
}
.search-input {
    height: 34px;
    padding: 0 10px 0 30px;
    border: 1.5px solid #e4e3f0;
    border-radius: 8px;
    font-size: 12.5px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: #fafafe;
    outline: none;
    width: 180px;
    transition: border-color 0.2s;
}
.search-input:focus { border-color: #0b044d; }
</style>
@endpush

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('admin.admin-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('admin.admin-notification')

        @php
        $records = collect([
            ['id' => 'PGS-0041', 'name' => 'Maria B. Santos',     'position' => 'Administrative Officer IV',   'dept' => 'Office of the Mayor',              'present' => 22, 'absent' => 0, 'late' => 1, 'halfday' => 0, 'overtime' => 3.5, 'status' => 'Complete'],
            ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz',   'position' => 'Municipal Engineer II',        'dept' => 'Office of the Mun. Engineer',      'present' => 20, 'absent' => 1, 'late' => 2, 'halfday' => 1, 'overtime' => 0,   'status' => 'Complete'],
            ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes',        'position' => 'Nurse II',                    'dept' => 'Municipal Health Office',          'present' => 21, 'absent' => 0, 'late' => 0, 'halfday' => 0, 'overtime' => 8.0, 'status' => 'Complete'],
            ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza',   'position' => 'Municipal Treasurer III',     'dept' => 'Office of the Mun. Treasurer',     'present' => 19, 'absent' => 2, 'late' => 3, 'halfday' => 0, 'overtime' => 0,   'status' => 'Incomplete'],
            ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez',       'position' => 'Social Welfare Officer II',   'dept' => 'MSWD – Pagsanjan',                 'present' => 22, 'absent' => 0, 'late' => 0, 'halfday' => 0, 'overtime' => 2.0, 'status' => 'Complete'],
            ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores',   'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar',        'present' => 18, 'absent' => 3, 'late' => 1, 'halfday' => 1, 'overtime' => 0,   'status' => 'Incomplete'],
            ['id' => 'PGS-0342', 'name' => 'Grace A. Villanueva', 'position' => 'Budget Officer II',           'dept' => 'Office of the Mun. Budget',        'present' => 21, 'absent' => 1, 'late' => 0, 'halfday' => 0, 'overtime' => 1.5, 'status' => 'Complete'],
            ['id' => 'PGS-0358', 'name' => 'Ramon D. Cruz',       'position' => 'Agriculturist I',             'dept' => 'Office of the Mun. Agriculturist', 'present' => 20, 'absent' => 0, 'late' => 4, 'halfday' => 0, 'overtime' => 0,   'status' => 'Complete'],
        ]);
        $avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
        if (!function_exists('getInitials')) {
            function getInitials($name) {
                $parts = explode(' ', $name);
                $initials = '';
                foreach ($parts as $part) {
                    if (preg_match('/^[A-Z]/', $part)) $initials .= $part[0];
                }
                return strtoupper(substr($initials, 0, 2));
            }
        }
        $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
        $years = ['2025','2024','2023'];
        $departments = ['All Departments', 'Office of the Mayor', 'Office of the Vice Mayor', 'Sangguniang Bayan', 'Office of the Mun. Treasurer', "Municipal Assessor's Office", 'Municipal Civil Registrar', 'Municipal Health Office', 'MSWD – Pagsanjan', "Municipal Planning & Dev't Office", 'Office of the Mun. Engineer', 'Office of the Mun. Agriculturist', 'Municipal Environment & Natural Resources', "Municipal Business & Dev't Office", 'Human Resource Management Office', 'Municipal Disaster Risk Reduction & Mgmt', 'Office of the Mun. Budget', 'Municipal Circuit Trial Court'];
        $currentMonth = 'June';
        $currentYear  = '2025';
        $period       = $currentMonth . ' ' . $currentYear;
        $totalPresent   = $records->sum('present');
        $totalAbsent    = $records->sum('absent');
        $totalLate      = $records->sum('late');
        $totalOT        = $records->sum('overtime');
        $completeCount  = $records->where('status', 'Complete')->count();
        $incompleteCount = $records->where('status', 'Incomplete')->count();
        @endphp

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                </div>
                <div>
                    <h2>Attendance Management</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Daily Time Records</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    {{ $completeCount }} Complete
                </span>
                <span class="banner-badge outline">FY {{ now()->year }}</span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">DTR Submitted</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $completeCount }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">{{ $incompleteCount }} incomplete</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Present</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $totalPresent }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">{{ $period }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Absences</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $totalAbsent }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Across all personnel</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Overtime Hours</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $totalOT }} hrs</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">{{ $totalLate }} late arrivals</p>
                </div>
            </div>
        </div>

        {{-- Attendance Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Daily Time Record — {{ $period }}</p>
                    <p class="table-sub">Municipal Government of Pagsanjan · <span id="showing-count">{{ $records->count() }}</span> of {{ $records->count() }} records</p>
                </div>
                <div class="table-actions">
                    <div class="search-wrap">
                        <svg width="13" height="13" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Search records..." class="search-input">
                    </div>
                    <select class="filter-select" id="month-filter">
                        @foreach($months as $month)
                        <option value="{{ $month }}" {{ $month === $currentMonth ? 'selected' : '' }}>{{ $month }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="year-filter">
                        @foreach($years as $year)
                        <option value="{{ $year }}" {{ $year === $currentYear ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="dept-filter">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="status-filter">
                        <option value="All">All Status</option>
                        <option value="Complete">Complete</option>
                        <option value="Incomplete">Incomplete</option>
                    </select>
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            {{-- Summary Bar --}}
            <div style="background: linear-gradient(135deg, #fafafe 0%, #f7f6ff 100%); border: 1.5px solid #e4e3f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(11, 4, 77, 0.04);">
                <div style="text-align: center; flex: 1;">
                    <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Total Present</p>
                    <p style="font-size: 20px; font-weight: 700; color: #15803d; margin: 0; line-height: 1;">{{ $totalPresent }}</p>
                    <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">days</p>
                </div>
                <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
                <div style="text-align: center; flex: 1;">
                    <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Total Absent</p>
                    <p style="font-size: 20px; font-weight: 700; color: #8e1e18; margin: 0; line-height: 1;">{{ $totalAbsent }}</p>
                    <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">days</p>
                </div>
                <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
                <div style="text-align: center; flex: 1;">
                    <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Late Arrivals</p>
                    <p style="font-size: 20px; font-weight: 700; color: #a16207; margin: 0; line-height: 1;">{{ $totalLate }}</p>
                    <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">times</p>
                </div>
                <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
                <div style="text-align: center; flex: 1;">
                    <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Overtime</p>
                    <p style="font-size: 20px; font-weight: 700; color: #0b044d; margin: 0; line-height: 1;">{{ $totalOT }}</p>
                    <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">hrs</p>
                </div>
                <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
                <div style="text-align: center; flex: 1;">
                    <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Records</p>
                    <p style="font-size: 20px; font-weight: 700; color: #0b044d; margin: 0; line-height: 1;">{{ $records->count() }}</p>
                    <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">&nbsp;</p>
                </div>
            </div>
            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Position</th>
                            <th>Department</th>
                            <th>Present</th>
                            <th>Absent</th>
                            <th>Late</th>
                            <th>OT Hours</th>
                            <th>Rate</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="attendance-table-body">
                        @foreach($records as $index => $record)
                        @php
                        $colorIndex = $index % count($avatarColors);
                        $statusClass = $record['status'] === 'Complete' ? 'processed' : 'pending';
                        $workingDays = $record['present'] + $record['absent'] + $record['halfday'];
                        $rate = $workingDays > 0 ? round(($record['present'] / $workingDays) * 100) : 0;
                        @endphp
                        <tr data-dept="{{ $record['dept'] }}" data-status="{{ $record['status'] }}">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background: {{ $avatarColors[$colorIndex] }}">{{ getInitials($record['name']) }}</div>
                                    <div>
                                        <p class="emp-name">{{ $record['name'] }}</p>
                                        <p class="emp-id">{{ $record['id'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="position-cell">{{ $record['position'] }}</span></td>
                            <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                            <td style="color: #15803d; font-weight: 600;">{{ $record['present'] }}</td>
                            <td style="color: {{ $record['absent'] > 0 ? '#8e1e18' : '#9999bb' }}; font-weight: {{ $record['absent'] > 0 ? '600' : '400' }};">{{ $record['absent'] }}</td>
                            <td style="color: {{ $record['late'] > 0 ? '#a16207' : '#9999bb' }}; font-weight: {{ $record['late'] > 0 ? '600' : '400' }};">{{ $record['late'] }}</td>
                            <td style="color: {{ $record['overtime'] > 0 ? '#0b044d' : '#9999bb' }}; font-weight: {{ $record['overtime'] > 0 ? '600' : '400' }};">{{ $record['overtime'] > 0 ? $record['overtime'] . ' hrs' : '—' }}</td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                        <div style="width: {{ $rate }}%; height: 100%; background: {{ $rate >= 90 ? '#15803d' : ($rate >= 75 ? '#d9bb00' : '#8e1e18') }}; border-radius: 3px;"></div>
                                    </div>
                                    <span style="font-size: 12px; font-weight: 600; color: #0b044d; white-space: nowrap;">{{ $rate }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($record['status'] === 'Complete')
                                    <span class="badge-status processed">Complete</span>
                                @else
                                    <span class="badge-status pending">Incomplete</span>
                                @endif
                            </td>
                            <td><button class="btn-view" onclick="viewDTR('{{ $record['id'] }}')">View</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="empty-state" id="empty-state" style="display: none; text-align: center; padding: 40px 20px;">
                    <p style="font-size: 13px; color: #9999bb; margin: 0;">No records found</p>
                </div>
            </div>

            <div class="table-footer">
                <span>Showing <strong id="visible-count">{{ $records->count() }}</strong> of <strong>{{ $records->count() }}</strong> records</span>
                <div class="pagination">
                    <button class="page-btn">‹</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">›</button>
                </div>
            </div>
        </div>

    </main>

    @include('admin.admin-chatbot')

</div>

<div class="modal-overlay" id="view-modal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="modal-period">DTR · {{ $period }}</span>
                <h3 class="modal-title" id="modal-name">Employee Name</h3>
                <p class="modal-sub" id="modal-position">Position · Department</p>
            </div>
            <button class="modal-close" onclick="closeModal('view-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding: 16px; background: #f7f6ff; border-radius: 12px;">
                <div class="emp-avatar" id="modal-avatar" style="width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 700; color: #fff; background: #0b044d;">MS</div>
                <div>
                    <p id="modal-emp-id" style="font-size: 11px; color: #9999bb; margin: 0 0 4px;">PGS-0000</p>
                    <span class="badge-status" id="modal-status-badge">Complete</span>
                </div>
            </div>

            <p style="font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: 1px; margin-bottom: 12px;">ATTENDANCE SUMMARY</p>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f7f6ff"><span style="font-size:12.5px;color:#5a5888">Working Days</span><strong style="font-size:13px;color:#0b044d" id="modal-working-days">22 days</strong></div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f7f6ff"><span style="font-size:12.5px;color:#5a5888">Days Present</span><strong style="font-size:13px;color:#15803d" id="modal-present">22 days</strong></div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f7f6ff"><span style="font-size:12.5px;color:#5a5888">Days Absent</span><strong style="font-size:13px;color:#8e1e18" id="modal-absent">0 days</strong></div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f7f6ff"><span style="font-size:12.5px;color:#5a5888">Late Arrivals</span><strong style="font-size:13px;color:#a16207" id="modal-late">1 times</strong></div>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f7f6ff"><span style="font-size:12.5px;color:#5a5888">Half Days</span><strong style="font-size:13px;color:#a16207" id="modal-halfday">0 days</strong></div>

            <p style="font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: 1px; margin: 16px 0 12px;">OVERTIME</p>
            <div style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f7f6ff"><span style="font-size:12.5px;color:#5a5888">Total OT Hours</span><strong style="font-size:13px;color:#0b044d" id="modal-overtime">3.5 hrs</strong></div>

            <div style="margin-top: 16px; padding: 12px; background: #f7f6ff; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; color: #9999bb; letter-spacing: 1px;">ATTENDANCE RATE</span>
                <strong style="font-size: 18px; color: #15803d;" id="modal-rate">100%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download DTR
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="edit-modal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">EDIT DTR RECORD</span>
                <h3 class="modal-title" id="edit-name">Employee Name</h3>
                <p class="modal-sub" id="edit-id">PGS-0000</p>
            </div>
            <button class="modal-close" onclick="closeModal('edit-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                <div class="form-field">
                    <label>Days Present</label>
                    <input type="number" id="edit-present" min="0" value="22" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none;">
                </div>
                <div class="form-field">
                    <label>Days Absent</label>
                    <input type="number" id="edit-absent" min="0" value="0" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none;">
                </div>
                <div class="form-field">
                    <label>Late Arrivals</label>
                    <input type="number" id="edit-late" min="0" value="1" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none;">
                </div>
                <div class="form-field">
                    <label>Half Days</label>
                    <input type="number" id="edit-halfday" min="0" value="0" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none;">
                </div>
                <div class="form-field">
                    <label>Overtime (hrs)</label>
                    <input type="number" id="edit-overtime" min="0" step="0.5" value="3.5" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none;">
                </div>
                <div class="form-field">
                    <label>Status</label>
                    <select id="edit-status" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; background: #fff;">
                        <option value="Complete">Complete</option>
                        <option value="Incomplete">Incomplete</option>
                    </select>
                </div>
            </div>
            <div style="margin-top: 16px; padding: 12px; background: #f7f6ff; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11px; font-weight: 700; color: #9999bb; letter-spacing: 1px;">ATTENDANCE RATE PREVIEW</span>
                <strong style="font-size: 18px; color: #15803d;" id="edit-rate-preview">100%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('edit-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="saveDTR()">Save Changes</button>
        </div>
    </div>
</div>

<script>
const recordsData = @json($records);
const avatarColors = @json($avatarColors);

let currentEditId = null;

function getInitials(name) {
    const parts = name.split(' ').filter(n => /^[A-Z]/.test(n));
    return parts.map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

function viewDTR(recordId) {
    const record = recordsData.find(r => r.id === recordId);
    if (!record) return;
    
    const idx = recordsData.findIndex(r => r.id === recordId);
    const color = avatarColors[idx % avatarColors.length];
    const workingDays = record.present + record.absent + record.halfday;
    const rate = workingDays > 0 ? Math.round((record.present / workingDays) * 100) : 0;
    
    document.getElementById('modal-name').textContent = record.name;
    document.getElementById('modal-position').textContent = record.position + ' · ' + record.dept;
    document.getElementById('modal-avatar').style.background = color;
    document.getElementById('modal-avatar').textContent = getInitials(record.name);
    document.getElementById('modal-emp-id').textContent = record.id;
    
    const statusBadge = document.getElementById('modal-status-badge');
    statusBadge.textContent = record.status;
    statusBadge.className = 'badge-status ' + (record.status === 'Complete' ? 'processed' : 'pending');
    
    document.getElementById('modal-working-days').textContent = workingDays + ' days';
    document.getElementById('modal-present').textContent = record.present + ' days';
    document.getElementById('modal-absent').textContent = record.absent + ' days';
    document.getElementById('modal-late').textContent = record.late + ' times';
    document.getElementById('modal-halfday').textContent = record.halfday + ' days';
    document.getElementById('modal-overtime').textContent = record.overtime + ' hrs';
    document.getElementById('modal-rate').textContent = rate + '%';
    document.getElementById('modal-rate').style.color = rate >= 90 ? '#15803d' : (rate >= 75 ? '#d9bb00' : '#8e1e18');
    
    document.getElementById('view-modal').style.display = 'flex';
}

function editDTR(recordId) {
    const record = recordsData.find(r => r.id === recordId);
    if (!record) return;
    
    currentEditId = recordId;
    document.getElementById('edit-name').textContent = record.name;
    document.getElementById('edit-id').textContent = record.id;
    document.getElementById('edit-present').value = record.present;
    document.getElementById('edit-absent').value = record.absent;
    document.getElementById('edit-late').value = record.late;
    document.getElementById('edit-halfday').value = record.halfday;
    document.getElementById('edit-overtime').value = record.overtime;
    document.getElementById('edit-status').value = record.status;
    
    updateRatePreview();
    document.getElementById('edit-modal').style.display = 'flex';
}

function updateRatePreview() {
    const present = parseInt(document.getElementById('edit-present').value) || 0;
    const absent = parseInt(document.getElementById('edit-absent').value) || 0;
    const halfday = parseInt(document.getElementById('edit-halfday').value) || 0;
    const workingDays = present + absent + halfday;
    const rate = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;
    
    const rateEl = document.getElementById('edit-rate-preview');
    rateEl.textContent = rate + '%';
    rateEl.style.color = rate >= 90 ? '#15803d' : (rate >= 75 ? '#d9bb00' : '#8e1e18');
}

document.getElementById('edit-present').addEventListener('input', updateRatePreview);
document.getElementById('edit-absent').addEventListener('input', updateRatePreview);
document.getElementById('edit-halfday').addEventListener('input', updateRatePreview);

function saveDTR() {
    const present = parseInt(document.getElementById('edit-present').value) || 0;
    const absent = parseInt(document.getElementById('edit-absent').value) || 0;
    const late = parseInt(document.getElementById('edit-late').value) || 0;
    const halfday = parseInt(document.getElementById('edit-halfday').value) || 0;
    const overtime = parseFloat(document.getElementById('edit-overtime').value) || 0;
    const status = document.getElementById('edit-status').value;
    
    const rows = document.querySelectorAll('#attendance-table-body tr');
    rows.forEach(r => {
        const empId = r.querySelector('.emp-id').textContent;
        const record = recordsData.find(rec => rec.id === empId);
        if (record && record.id === currentEditId) {
            record.present = present;
            record.absent = absent;
            record.late = late;
            record.halfday = halfday;
            record.overtime = overtime;
            record.status = status;
            
            r.dataset.status = status;
            
            const cells = r.querySelectorAll('td');
            cells[2].textContent = present;
            cells[3].textContent = absent;
            cells[3].style.color = absent > 0 ? '#8e1e18' : '#9999bb';
            cells[4].textContent = late;
            cells[4].style.color = late > 0 ? '#a16207' : '#9999bb';
            cells[5].textContent = halfday;
            cells[5].style.color = halfday > 0 ? '#a16207' : '#9999bb';
            cells[6].textContent = overtime > 0 ? overtime + ' hrs' : '—';
            cells[6].style.color = overtime > 0 ? '#0b044d' : '#9999bb';
            
            const workingDays = present + absent + halfday;
            const rate = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;
            const rateCell = cells[7];
            rateCell.innerHTML = `
                <div style="display: flex; align-items: center; gap: 6px;">
                    <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                        <div style="width: ${rate}%; height: 100%; background: ${rate >= 90 ? '#15803d' : (rate >= 75 ? '#d9bb00' : '#8e1e18')}; border-radius: 3px;"></div>
                    </div>
                    <span style="font-size: 12px; font-weight: 600; color: #0b044d; white-space: nowrap;">${rate}%</span>
                </div>
            `;
            
            const statusCell = cells[8];
            statusCell.innerHTML = `<span class="badge-status ${status === 'Complete' ? 'processed' : 'pending'}">${status}</span>`;
        }
    });
    
    closeModal('edit-modal');
    filterAttendance();
    alert('DTR record saved successfully!');
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('dept-filter').addEventListener('change', filterAttendance);
document.getElementById('status-filter').addEventListener('change', filterAttendance);
document.getElementById('search-input').addEventListener('input', filterAttendance);

function filterAttendance() {
    const deptFilter = document.getElementById('dept-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('#attendance-table-body tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const dept = row.dataset.dept;
        const status = row.dataset.status;
        const name = row.querySelector('.emp-name').textContent.toLowerCase();
        const empId = row.querySelector('.emp-id').textContent.toLowerCase();
        
        const matchDept = deptFilter === 'All Departments' || dept === deptFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || name.includes(searchQuery) || empId.includes(searchQuery);
        
        const isVisible = matchDept && matchStatus && matchSearch;
        row.style.display = isVisible ? 'table-row' : 'none';
        if (isVisible) visibleCount++;
    });
    
    document.getElementById('showing-count').textContent = visibleCount;
    document.getElementById('visible-count').textContent = visibleCount;
    document.getElementById('empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('view-modal').style.display = 'none';
        document.getElementById('edit-modal').style.display = 'none';
    }
});

const sidebar      = document.getElementById('sidebar');
const toggleBtn    = document.getElementById('toggle-btn');
const logoText     = document.getElementById('logo-text');
const navLabel     = document.getElementById('nav-label');
const userInfo     = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn    = document.getElementById('mobile-menu-btn');
const overlay      = document.getElementById('mobile-overlay');

toggleBtn.addEventListener('click', () => {
    const collapsed = sidebar.classList.toggle('collapsed');
    toggleBtn.textContent = collapsed ? '›' : '‹';
    logoText.style.display  = collapsed ? 'none' : '';
    navLabel.style.display  = collapsed ? 'none' : '';
    userInfo.style.display  = collapsed ? 'none' : '';
    sidebarFooter.classList.toggle('collapsed-footer', collapsed);
    document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
        el.style.display = collapsed ? 'none' : '';
    });
});

mobileBtn.addEventListener('click', () => {
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
    sidebar.classList.remove('mobile-open');
    overlay.classList.remove('active');
});
</script>
@endsection