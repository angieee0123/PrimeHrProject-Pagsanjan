@extends('layouts.app')

@section('content')
@include('admin.topbar.attendanceTopbar')
@include('admin.notification.adminNotification')

@php
$avatarColors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#150c63', '#56547a'];
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

<main class="attendance-dashboard glass-shell" data-period-display="{{ $periodDisplay }}" data-period-display-file="{{ str_replace([' ', ',', '-'], '_', $periodDisplay) }}">

@php
    $totalRecords = $completeCount + $incompleteCount;
    $attendanceRate = $totalRecords > 0 ? round(($completeCount / $totalRecords) * 100, 1) : 0;
    $totalHalfDay = array_sum(array_column($attendanceRecords, 'halfday'));
    $onLeaveTotal = $totalOnLeave ?? 0;
    $dayTotal = $totalPresent + $totalAbsent + $onLeaveTotal;
    $presentPct = $dayTotal > 0 ? round(($totalPresent / $dayTotal) * 100, 1) : 0;
    $leavePct   = $dayTotal > 0 ? round(($onLeaveTotal / $dayTotal) * 100, 1) : 0;
    $absentPct  = $dayTotal > 0 ? round(($totalAbsent / $dayTotal) * 100, 1) : 0;
@endphp

{{-- ============ STATISTICS CARDS ============ --}}
<div class="stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Present</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($totalPresent) }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-present"></span>
            <p class="stat-sub">Present days logged</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">On Leave</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($onLeaveTotal) }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-leave"></span>
            <p class="stat-sub">Approved leave days</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Absent</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="22" y2="13"/><line x1="22" y1="8" x2="17" y2="13"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($totalAbsent) }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-absent"></span>
            <p class="stat-sub">Absences recorded</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Overtime Hours</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $totalOT }} hrs</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-late"></span>
            <p class="stat-sub">{{ $totalLate }} late arrivals</p>
        </div>
    </div>
</div>

{{-- ============ ATTENDANCE OVERVIEW PANEL ============ --}}
<div class="overview-panel">
    <div class="ov-item">
        <div class="ov-icon ov-icon-present">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <p class="ov-label">Present</p>
            <p class="ov-value">{{ number_format($totalPresent) }}</p>
            <p class="ov-pct">{{ $presentPct }}%</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon ov-icon-leave">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <div>
            <p class="ov-label">On Leave</p>
            <p class="ov-value">{{ number_format($onLeaveTotal) }}</p>
            <p class="ov-pct">{{ $leavePct }}%</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon ov-icon-absent">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div>
            <p class="ov-label">Absent</p>
            <p class="ov-value">{{ number_format($totalAbsent) }}</p>
            <p class="ov-pct">{{ $absentPct }}%</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon ov-icon-late">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><line x1="22" y1="2" x2="18" y2="6"/></svg>
        </div>
        <div>
            <p class="ov-label">Late Arrivals</p>
            <p class="ov-value">{{ number_format($totalLate) }}</p>
            <p class="ov-pct">Times late</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon ov-icon-overtime">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <p class="ov-label">Overtime</p>
            <p class="ov-value">{{ $totalOT }} hrs</p>
            <p class="ov-pct">Total hours</p>
        </div>
    </div>
    <div class="ov-item">
        <div class="ov-icon ov-icon-records">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
            <p class="ov-label">Records</p>
            <p class="ov-value">{{ number_format(count($attendanceRecords)) }}</p>
            <p class="ov-pct">Total employees</p>
        </div>
    </div>
</div>

{{-- ============ FILTER TOOLBAR ============ --}}
<form method="GET" action="{{ route('admin.attendance') }}" id="attendanceFilterForm">
    <div class="filter-card">
        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" name="start_date" class="fc-input" value="{{ $startDateDisplay }}" title="Start date">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" name="end_date" class="fc-input" value="{{ $endDateDisplay }}" title="End date">
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
                <select name="department" class="fc-select">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
            </div>
            <div class="fc-divider"></div>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <select name="status" class="fc-select">
                    <option value="">All Status</option>
                    <option value="Complete" {{ request('status') == 'Complete' ? 'selected' : '' }}>Complete</option>
                    <option value="Incomplete" {{ request('status') == 'Incomplete' ? 'selected' : '' }}>Incomplete</option>
                </select>
            </div>
        </div>
        <div class="filter-card-actions">
            <button type="submit" class="btn-solid">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
            {{-- Same green outline pill as Personnel and Departments Bulk Import, off the shared .btn-export-green rule. --}}
            <button type="button" class="btn-export btn-export-green" onclick="openBulkImportAttendanceModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                Bulk Import
            </button>
            {{-- Exports the Attendance Summary tab this toolbar sits above.
                 It carried no handler at all until now -- rendered, styled,
                 clickable, wired to nothing. The filters in this form are sent
                 to the endpoint, which recomputes every matching employee
                 rather than the page on screen. --}}
            <button type="button" class="btn-ghost" id="attendanceExportBtn"
                    data-export-url="{{ route('admin.attendance.summary.export') }}"
                    onclick="exportAttendanceSummary(this)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>
</form>

{{-- ============ SEGMENTED TABS ============ --}}
<div class="seg-tabs">
    <button class="tab-btn active" onclick="switchTab('summary')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Attendance Summary
    </button>
    <button class="tab-btn" onclick="switchTab('detailed')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Detailed Time Record
    </button>
    <button class="tab-btn" onclick="switchTab('settings')">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
        Attendance Config
    </button>
</div>

@include('admin.attendance.partials.attendance-summary-tab')
@include('admin.attendance.partials.detailed-time-record-tab')
@include('admin.attendance.partials.attendance-settings-tab')

</main>

{{-- Modals live outside .attendance-dashboard on purpose: that wrapper has
     `isolation: isolate` (to contain its own decorative ::before gradient),
     which makes it establish a stacking context with z-index:auto (≈0).
     Any position:fixed modal nested inside it — no matter how high its own
     z-index — can never out-rank the sidebar (z-index: 200), because the
     comparison happens one level up, between the whole .attendance-dashboard
     bubble (level 0) and the sidebar (level 200). Keeping modals as siblings
     of .attendance-dashboard lets each one's own z-index compete directly
     against the sidebar instead. --}}
@include('admin.attendance.modals.dtrDetailModal')
@include('admin.attendance.modals.detailedDtrModal')
@include('admin.attendance.modals.editDtrModal')
@include('admin.attendance.modals.correctAttendanceModal')
@include('admin.attendance.modals.bulkImportAttendanceModal')
@include('admin.attendance.modals.successModal')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
@vite([
    'resources/js/admin/attendance/adminAttendance.js',
    'resources/js/admin/attendance/dtrDetailModal.js',
    'resources/js/admin/attendance/editDtrModal.js',
    'resources/js/admin/attendance/correctAttendanceModal.js',
    'resources/js/admin/attendance/detailedDtrModal.js',
    'resources/js/admin/attendance/successModal.js',
    'resources/js/admin/attendance/bulkImportAttendance.js',
])
@endpush
@endsection
