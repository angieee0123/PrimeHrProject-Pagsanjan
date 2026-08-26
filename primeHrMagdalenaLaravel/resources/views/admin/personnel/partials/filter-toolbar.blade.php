{{-- Filter Toolbar --}}
{{-- Employee Records only. It sits outside .tab-content (the two tabs are
     siblings below it), so the tab handler hides it by id when Work Schedules
     is showing: its filters describe employee records, and leaving its Export
     button on screen beside the Work Schedules one meant two buttons that
     download different files with nothing to tell them apart. --}}
<div class="filter-card" id="personnelFilterToolbar">
    <div class="filter-card-fields">
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/></svg>
            <select class="fc-select" id="departmentFilter" onchange="applyFilters()">
                <option value="">All Departments</option>
                @foreach($departments as $department)
                    <option value="{{ $department->name }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <select class="fc-select" id="statusFilter" onchange="applyFilters()">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <div class="fc-divider"></div>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" class="fc-input" id="hiredDateFrom" title="Date hired from" onchange="applyFilters()">
        </div>
        <span class="fc-sep">to</span>
        <div class="fld">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <input type="date" class="fc-input" id="hiredDateTo" title="Date hired to" onchange="applyFilters()">
        </div>
    </div>
    <div class="filter-card-actions">
        {{-- The CSV is built server-side (PersonnelExportController) so it can
             carry the letterhead block, the 201-file columns the table has no
             room for, and every matching record rather than the page on screen.
             The filters are read off this toolbar and sent as query params. --}}
        <button class="btn-ghost" onclick="exportTableData(this)" data-export-url="{{ route('admin.personnel.export') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>
