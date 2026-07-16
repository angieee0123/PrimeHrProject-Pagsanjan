<x-topbar title="Welcome back, {{ $employee->first_name ?? 'Employee' }}!">
    <x-slot:icon><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></x-slot:icon>
    <x-slot:subtitle>
        <span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span>
        &nbsp;·&nbsp;
        {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }}
        ·
        {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }}
        ·
        {{ $employee->employee_id ?? 'N/A' }}
    </x-slot:subtitle>
    <x-slot:actions>
        @php
            $currentMonth = now()->format('F Y');
            $nextPayDate = now()->day <= 15 ? now()->day(15)->format('M d') : now()->endOfMonth()->format('M d');
        @endphp
        <span class="banner-badge">
            <span class="banner-badge-dot"></span>
            {{ $currentMonth }} Payroll Active
        </span>
        <span class="banner-badge outline">Next Pay: {{ $nextPayDate }}</span>
    </x-slot:actions>
</x-topbar>
