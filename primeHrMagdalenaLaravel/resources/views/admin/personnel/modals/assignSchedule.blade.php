<!-- Assign Schedule Modal -->
<div id="assignScheduleModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:550px; box-shadow:0 8px 32px rgba(11,4,77,0.2); overflow:hidden;">
        <div style="background:linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; background:rgba(255,255,255,0.12); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </div>
                <div>
                    <p style="margin:0; font-size:10px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">WORK SCHEDULE</p>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#fff;" id="scheduleEmployeeName">Employee Name</h3>
                </div>
            </div>
            <button onclick="closeAssignScheduleModal()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">&times;</button>
        </div>

        <form action="{{ route('admin.schedules.assign') }}" method="POST">
            @csrf
            <input type="hidden" name="employee_id" id="scheduleEmployeeId">
            
            <div style="padding:24px; display:flex; flex-direction:column; gap:18px;">
                <div style="background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:10px; padding:16px;">
                    <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#9999bb;">MORNING SHIFT</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time In <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="am_in" id="scheduleAmIn" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time Out <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="am_out" id="scheduleAmOut" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <div style="background:#f7f6ff; border:1.5px solid #e8e7f5; border-radius:10px; padding:16px;">
                    <p style="margin:0 0 12px; font-size:11px; font-weight:700; letter-spacing:1px; color:#9999bb;">AFTERNOON SHIFT</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px;">
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time In <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="pm_in" id="schedulePmIn" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                        <div>
                            <label style="display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px;">
                                Time Out <span style="color:#8e1e18;">*</span>
                            </label>
                            <input type="time" name="pm_out" id="schedulePmOut" required style="width:100%; padding:10px 12px; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-family:'Poppins',sans-serif; color:#0b044d; background:#fff; box-sizing:border-box;">
                        </div>
                    </div>
                </div>

                <div style="background:#fff9e6; border:1.5px solid #ffe9a3; border-radius:10px; padding:12px; display:flex; align-items:start; gap:10px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2" style="flex-shrink:0; margin-top:2px;">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <p style="margin:0; font-size:12px; color:#8b7500; line-height:1.5;">
                        Set the employee's daily work schedule. This will be used for attendance tracking and reporting.
                    </p>
                </div>
            </div>

            <div style="padding:16px 24px; border-top:1px solid #f0effe; display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="closeAssignScheduleModal()" style="padding:10px 24px; background:#fff; border:1.5px solid #e8e7f5; border-radius:8px; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; font-family:'Poppins',sans-serif;">
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
    </div>
</div>

<script>
function closeAssignScheduleModal() {
    document.getElementById('assignScheduleModal').style.display = 'none';
    document.body.style.overflow = '';
}
</script>
