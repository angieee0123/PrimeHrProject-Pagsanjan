<x-topbar title="Performance Overview">
    <x-slot:icon><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></x-slot:icon>
    <x-slot:subtitle><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }} · {{ $employee->employee_id ?? 'N/A' }}</x-slot:subtitle>
    <x-slot:actions>
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="employeePerformanceSearch" class="topbar-search-input" placeholder="Search evaluation or goal..." oninput="filterPermanentPerformance(this.value)">
        </div>
    </x-slot:actions>
</x-topbar>
