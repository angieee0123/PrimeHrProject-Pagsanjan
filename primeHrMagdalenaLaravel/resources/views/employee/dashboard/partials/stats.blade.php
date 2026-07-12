{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4 perm-stats">

    <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.payslip') }}'">
        <div class="stat-top">
            <p class="stat-label">Basic Pay</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <p class="stat-value">₱{{ number_format($basicPay, 2) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">{{ $startDate->format('M d') }}–{{ $endDate->format('d, Y') }}</p>
        </div>
    </div>

    <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.payslip') }}'">
        <div class="stat-top">
            <p class="stat-label">Net Pay</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <p class="stat-value">₱{{ number_format($netPay, 2) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#22c55e"></span>
            <p class="stat-sub">₱{{ number_format($totalDeductions, 2) }} deducted</p>
        </div>
    </div>

    @if($isPermanent ?? false)
    <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.leave') }}'">
        <div class="stat-top">
            <p class="stat-label" style="display:flex;align-items:center;gap:6px">
                Leave Credits
                @if($pendingLeaveCount > 0)
                    <span class="stat-pill-alert">{{ $pendingLeaveCount }}</span>
                @endif
            </p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($leaveBalances->sum('available_credits'), 1) }}<span style="font-size:14px;font-weight:500"> days</span></p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#d9bb00"></span>
            <p class="stat-sub">
                @if($pendingLeaveCount > 0)
                    {{ $pendingLeaveCount }} request{{ $pendingLeaveCount != 1 ? 's' : '' }} pending
                @else
                    {{ $approvedLeaveCount }} approved this year
                @endif
            </p>
        </div>
    </div>
    @endif

    <div class="stat-card perm-stat-hover" onclick="window.location.href='{{ route('employee.attendance') }}'">
        <div class="stat-top">
            <p class="stat-label">Attendance Rate</p>
            <div class="stat-icon-wrap">
                <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $attendanceRate }}<span style="font-size:14px;font-weight:500">%</span></p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:{{ $lateDays > 0 ? '#b7791f' : '#15803d' }}"></span>
            <p class="stat-sub">
                {{ $presentDays }} of {{ $totalDays }} days
                @if($lateDays > 0) · {{ $lateDays }} late @endif
            </p>
        </div>
    </div>

</div>

{{-- Quick Actions Bar --}}
<div class="perm-action-bar">
    <div style="display:flex;align-items:center;gap:10px" class="perm-action-spacer">
        <div style="width:34px;height:34px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#0b044d;flex-shrink:0">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:800;color:#111827;margin:0">Quick Actions</p>
            <p style="font-size:11px;color:#667085;margin:0">Frequently used HR workflows</p>
        </div>
    </div>
    <button class="modal-btn-primary perm-quick-btn" onclick="window.location.href='{{ route('employee.payslip') }}'">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        View Payslip
    </button>
    @if($isPermanent ?? false)
    <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.leave') }}'">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        File Leave
    </button>
    @endif
    <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.attendance') }}'">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
        Attendance
    </button>
    <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.passslip') }}'">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Pass Slip
    </button>
    <button class="btn-export perm-quick-btn" onclick="window.location.href='{{ route('employee.profile') }}'">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        My Profile
    </button>
</div>
