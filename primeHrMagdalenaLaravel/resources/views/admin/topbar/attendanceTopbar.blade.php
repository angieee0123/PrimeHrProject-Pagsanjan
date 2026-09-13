<x-topbar title="Attendance Management">
    <x-slot:icon><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; HRIS Admin Panel</x-slot:subtitle>
    <x-slot:actions>
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="attendanceSearchInput" class="topbar-search-input" placeholder="Search employee, ID or department..." oninput="searchAttendance(this.value)">
        </div>
    </x-slot:actions>
</x-topbar>

<style>
.welcome-banner {
    font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Poppins', sans-serif !important;
}
</style>

{{-- `searchAttendance()` is defined in the page's own bundle
     (resources/js/admin/attendance/adminAttendance.js), not here. This partial
     used to carry a second copy that filtered by replacing the table body with
     cloned rows, which detached the rows the pagination then tried to show --
     and only this page includes this topbar, so the bundle is always present. --}}
