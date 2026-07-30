{{-- Quick Actions Bar --}}
<div class="enterprise-action-bar">
    <div style="display:flex;align-items:center;gap:10px" class="enterprise-action-spacer">
        <div style="width:34px;height:34px;border-radius:10px;background:#eef2ff;display:flex;align-items:center;justify-content:center;color:#0b044d">
            <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        </div>
        <div>
            <p style="font-size:13px;font-weight:800;color:#111827;margin:0">Quick Actions</p>
            <p style="font-size:11px;color:#667085;margin:0">Frequently used HR workflows</p>
        </div>
    </div>
    <x-modal-btn onclick="openEmployeeWizard()" style="font-size:11px;padding:0 14px;height:34px;display:flex;align-items:center;gap:6px">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Add Employee
    </x-modal-btn>
    <button class="btn-export" onclick="window.location.href='/admin/attendance'" style="font-size:11px;padding:0 14px;height:34px">Attendance</button>
    <button class="btn-export" onclick="window.location.href='/admin/leave'" style="font-size:11px;padding:0 14px;height:34px">Leave Request</button>
    <button class="btn-export" onclick="window.location.href='/admin/payroll'" style="font-size:11px;padding:0 14px;height:34px">Payroll</button>
    <button class="btn-export" style="font-size:11px;padding:0 14px;height:34px">Report</button>
</div>
