<!-- View Employee Schedules Modal -->
<x-schedule-modal id="viewSchedulesModal" close="closeViewSchedulesModal" max-width="900px"
                   overlay-style="overflow-y:auto;" box-style="margin:20px; max-height:90vh; display:flex; flex-direction:column;"
                   eyebrow="WORK SCHEDULES" title="Employee Name" title-id="viewSchedulesEmployeeName">
    <x-slot:icon>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
    </x-slot:icon>

    <div style="padding:24px; overflow-y:auto; flex:1;">
        <div id="schedulesListContainer">
            <!-- Schedules will be loaded here -->
        </div>
    </div>

    <div style="padding:16px 24px; border-top:1px solid #f2f1fb; display:flex; justify-content:flex-end; gap:10px;">
        <button onclick="closeViewSchedulesModal()" style="padding:10px 24px; background:#fff; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-weight:600; color:#56547a; cursor:pointer; font-family:'Poppins',sans-serif;">
            Close
        </button>
        <button onclick="openAddScheduleFromView()" style="padding:10px 24px; background:#0b044d; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add New Schedule
        </button>
    </div>
</x-schedule-modal>

@push('scripts')
    @vite('resources/js/personnel/viewSchedules.js')
@endpush
