{{-- Permanent Dashboard Topbar --}}
<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        <div>
            <h2>Welcome back, {{ $employee->first_name ?? 'Employee' }}!</h2>
            <p>
                <span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> 
                &nbsp;·&nbsp; 
                {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} 
                · 
                {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }} 
                · 
                {{ $employee->employee_id ?? 'N/A' }}
            </p>
        </div>
    </div>
    <div class="banner-right">
        @php
            $currentMonth = now()->format('F Y');
            $nextPayDate = now()->day <= 15 ? now()->day(15)->format('M d') : now()->endOfMonth()->format('M d');
        @endphp
        <span class="banner-badge">
            <span class="banner-badge-dot"></span>
            {{ $currentMonth }} Payroll Active
        </span>
        <span class="banner-badge outline">Next Pay: {{ $nextPayDate }}</span>
    </div>
</div>
