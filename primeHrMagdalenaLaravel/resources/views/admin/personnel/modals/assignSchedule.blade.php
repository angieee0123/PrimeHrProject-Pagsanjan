<!-- Assign Schedule Modal -->
<x-schedule-modal id="assignScheduleModal" close="closeAssignScheduleModal"
                   eyebrow="WORK SCHEDULE" title="Employee Name" title-id="scheduleEmployeeName">
    <x-slot:icon>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/>
            <line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
    </x-slot:icon>

    <form action="{{ route('admin.schedules.assign') }}" method="POST" id="assignScheduleForm" onsubmit="return validateScheduleDates()">
        @csrf
        <input type="hidden" name="schedule_id" id="scheduleId">
        <input type="hidden" name="employee_id" id="scheduleEmployeeId">

        <div style="padding:24px; display:flex; flex-direction:column; gap:18px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:8px;">
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                        Start Date <span style="color:#8e1e18;">*</span>
                    </label>
                    <input type="date" name="start_date" id="scheduleStartDate" required onchange="validateScheduleDates()" style="width:100%; padding:10px 12px; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                        End Date <span style="color:#8e1e18;">*</span>
                    </label>
                    <input type="date" name="end_date" id="scheduleEndDate" required onchange="validateScheduleDates()" style="width:100%; padding:10px 12px; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                </div>
            </div>

            <div style="background:#f7f6fc; border:1.5px solid #ecebf6; border-radius:10px; padding:16px;">
                <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#8f8daf;">MORNING SHIFT</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                            Time In <span style="color:#8e1e18;">*</span>
                        </label>
                        <input type="time" name="am_in" id="scheduleAmIn" required style="width:100%; padding:10px 12px; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                            Time Out <span style="color:#8e1e18;">*</span>
                        </label>
                        <input type="time" name="am_out" id="scheduleAmOut" required style="width:100%; padding:10px 12px; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                    </div>
                </div>
            </div>

            <div style="background:#f7f6fc; border:1.5px solid #ecebf6; border-radius:10px; padding:16px;">
                <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#8f8daf;">AFTERNOON SHIFT</p>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                            Time In <span style="color:#8e1e18;">*</span>
                        </label>
                        <input type="time" name="pm_in" id="schedulePmIn" required style="width:100%; padding:10px 12px; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                    </div>
                    <div>
                        <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                            Time Out <span style="color:#8e1e18;">*</span>
                        </label>
                        <input type="time" name="pm_out" id="schedulePmOut" required style="width:100%; padding:10px 12px; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                    </div>
                </div>
            </div>

            <div style="background:#fbf6e3; border:1.5px solid #ecdca4; border-radius:10px; padding:12px; display:flex; align-items:start; gap:10px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#c9a227" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <p style="margin:0; font-size:12px; color:#c9a227; line-height:1.5;">
                    Set the effectivity period for this schedule. The employee will follow this schedule only within the specified date range. You can create multiple schedules for different periods.
                </p>
            </div>

            <div id="scheduleOverlapWarning" style="display:none; background:#fee8e8; border:1.5px solid #f5d0ce; border-radius:10px; padding:12px;">
                <div style="display:flex; align-items:start; gap:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        <p style="margin:0 0 4px; font-size:12px; font-weight:700; color:#8e1e18;">Schedule Overlap Detected</p>
                        <p style="margin:0; font-size:12px; color:#8e1e18; line-height:1.5;" id="overlapDetails"></p>
                    </div>
                </div>
            </div>
        </div>

        <div style="padding:16px 24px; border-top:1px solid #f2f1fb; display:flex; justify-content:flex-end; gap:10px;">
            <button type="button" onclick="closeAssignScheduleModal()" style="padding:10px 24px; background:#fff; border:1.5px solid #ecebf6; border-radius:8px; font-size:13px; font-weight:600; color:#56547a; cursor:pointer; font-family:'Poppins',sans-serif;">
                Cancel
            </button>
            <button type="submit" style="padding:10px 24px; background:#0b044d; border:none; border-radius:8px; font-size:13px; font-weight:600; color:#fff; cursor:pointer; font-family:'Poppins',sans-serif; display:flex; align-items:center; gap:8px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Save Schedule
            </button>
        </div>
    </form>
</x-schedule-modal>

@push('scripts')
    @vite('resources/js/personnel/assignSchedule.js')
@endpush
