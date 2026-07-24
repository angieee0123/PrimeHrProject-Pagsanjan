{{-- Enhanced Stats Grid with Trends — expects: $stats (array: total_employees, new_this_month, present_today, attendance_rate, pending_leave, on_leave, monthly_payroll). --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onclick="window.location.href='#employee-directory'" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">Total Employees</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['total_employees']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">+{{ $stats['new_this_month'] }} new this month</p>
        </div>
    </div>

    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onclick="document.getElementById('birdsTabTitle').scrollIntoView({behavior:'smooth'})" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">Present Today</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['present_today']) }}<span style="font-size:14px;color:#8f8daf;font-weight:500"> / {{ number_format($stats['total_employees']) }}</span></p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d"></span>
            <p class="stat-sub">{{ $stats['attendance_rate'] }}% attendance rate</p>
        </div>
    </div>

    {{-- Jumps to the On Leave Today card in the overview grid (the list behind
         this number), not just the first table heading on the page. --}}
    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onclick="document.getElementById('on-leave-today')?.scrollIntoView({behavior:'smooth',block:'center'})" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">On Leave Today</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['on_leave']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227"></span>
            <p class="stat-sub">{{ $stats['pending_leave'] }} pending approval</p>
        </div>
    </div>

    <div class="stat-card" style="cursor:pointer;transition:all 0.3s" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='translateY(0)'">
        <div class="stat-top">
            <p class="stat-label">Monthly Payroll</p>
            <div class="stat-icon-wrap" style="background:#f2f1fb">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="#0b044d" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        {{-- Scale the unit to the amount: a five-figure payroll rendered as
             "₱0.11M" hides its real size. Full value stays in the tooltip. --}}
        @php
            $payroll = (float) $stats['monthly_payroll'];
            $payrollDisplay = $payroll >= 1000000
                ? '₱' . number_format($payroll / 1000000, 2) . 'M'
                : ($payroll >= 1000
                    ? '₱' . number_format($payroll / 1000, 1) . 'K'
                    : '₱' . number_format($payroll, 2));
        @endphp
        <p class="stat-value" style="font-size:20px" title="₱{{ number_format($payroll, 2) }}">{{ $payrollDisplay }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d"></span>
            <p class="stat-sub">{{ now()->format('M Y') }} total</p>
        </div>
    </div>
</div>
