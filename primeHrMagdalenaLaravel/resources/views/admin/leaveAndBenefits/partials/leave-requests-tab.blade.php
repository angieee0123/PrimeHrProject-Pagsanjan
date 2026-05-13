<section class="table-section" id="leave-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Requests — {{ now()->format('F Y') }}</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · <span id="leaveRequestCount">{{ $leaveApplications->count() }}</span> records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterDepartment" onchange="applyAdminLeaveFilters()">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="filterLeaveType" onchange="applyAdminLeaveFilters()">
                <option value="">All Types</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->leave_name }}">{{ $type->leave_name }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="filterLeaveStatus" onchange="applyAdminLeaveFilters()">
                <option value="">All Status</option>
                <option value="Approved">Approved</option>
                <option value="Pending">Pending</option>
                <option value="Rejected">Rejected</option>
                <option value="Cancelled">Cancelled</option>
            </select>
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;" onclick="openManualCreditModal('add')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Manual Credits
            </button>
            <button class="btn-export" style="background: #8e1e18; color: #fff; border-color: #8e1e18;" onclick="openManualCreditModal('deduct')">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Deduct Credits
            </button>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Leave Type</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="leaveRequestsTableBody">
                @forelse($leaveApplications as $application)
                <tr data-department="{{ $application->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}" 
                    data-type="{{ $application->leaveType->leave_name ?? 'N/A' }}" 
                    data-status="{{ ucfirst($application->status) }}">
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[($application->employee->id ?? 0) % count($avatarColors)] }};">
                                {{ strtoupper(substr($application->employee->first_name ?? 'N', 0, 1) . substr($application->employee->last_name ?? 'A', 0, 1)) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $application->employee->first_name ?? 'N/A' }} {{ $application->employee->last_name ?? '' }}</p>
                                <p class="emp-id">{{ $application->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $application->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $application->leaveType->leave_name ?? 'N/A' }}</td>
                    <td style="font-size: 13px; color: #6b6a8a;">{{ $application->start_date->format('M d, Y') }}</td>
                    <td style="font-size: 13px; color: #6b6a8a;">{{ $application->end_date->format('M d, Y') }}</td>
                    <td style="font-weight: 600; color: #0b044d; font-size: 13px;">{{ number_format($application->number_of_days, 1) }}</td>
                    <td>
                        @if($application->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($application->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($application->status === 'rejected')
                            <span class="badge-status on-hold">Rejected</span>
                        @else
                            <span class="badge-status cancelled">Cancelled</span>
                        @endif
                    </td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick="openAdminLeaveDetailModal(
                                {{ $application->id }},
                                '{{ addslashes($application->employee->first_name ?? 'N/A') }} {{ addslashes($application->employee->last_name ?? '') }}',
                                '{{ $application->employee->employee_id ?? 'N/A' }}',
                                '{{ addslashes($application->leaveType->leave_name ?? 'N/A') }}',
                                '{{ $application->start_date->format('M d, Y') }}',
                                '{{ $application->end_date->format('M d, Y') }}',
                                {{ $application->number_of_days }},
                                '{{ addslashes($application->reason) }}',
                                '{{ ucfirst($application->status) }}',
                                '{{ $application->application_number }}',
                                '{{ $application->attachment_path ? asset('storage/' . $application->attachment_path) : '' }}',
                                '{{ addslashes($application->approver_remarks ?? '') }}'
                            )">View</button>
                            @if($application->status === 'pending')
                                <button class="btn-approve" onclick="approveLeaveRequest({{ $application->id }}, '{{ $application->application_number }}')">Approve</button>
                                <button class="btn-reject" onclick="openRejectModal({{ $application->id }}, '{{ $application->application_number }}')">Reject</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No leave requests found</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Leave applications will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p id="leaveRequestFooter">Showing <strong>{{ $leaveApplications->count() }}</strong> of <strong>{{ $leaveApplications->count() }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
        </div>
    </div>
</section>

{{-- Admin Leave Detail Modal --}}
<div class="modal-overlay" id="adminLeaveDetailModal" onclick="closeAdminLeaveDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="adminLeaveAppNumber">LEAVE REQUEST · LV-2025-001</span>
                <h3 class="modal-title" id="adminLeaveEmployeeName">Employee Name</h3>
                <p class="modal-sub" id="adminLeaveEmployeeId">PGS-0115</p>
            </div>
            <button class="modal-close" onclick="closeAdminLeaveDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <span class="modal-section-label">LEAVE DETAILS</span>
            <div class="modal-row"><span>Leave Type</span><strong id="adminLeaveType">Vacation Leave</strong></div>
            <div class="modal-row"><span>Date From</span><strong id="adminLeaveFrom">Jun 15, 2025</strong></div>
            <div class="modal-row"><span>Date To</span><strong id="adminLeaveTo">Jun 16, 2025</strong></div>
            <div class="modal-row"><span>No. of Days</span><strong id="adminLeaveDays">2 days</strong></div>
            <div class="modal-row"><span>Status</span><span class="badge-status pending" id="adminLeaveStatus">Pending</span></div>
            <span class="modal-section-label modal-section-deductions">REASON</span>
            <div class="modal-row"><span id="adminLeaveReason" style="color: #6b7280;">Medical consultation</span></div>
            <div id="adminRemarksSection" style="display: none;">
                <span class="modal-section-label modal-section-deductions">APPROVER REMARKS</span>
                <div class="modal-row"><span id="adminRemarksText" style="color: #6b7280; font-style: italic;"></span></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeAdminLeaveDetailModal()">Close</button>
            <button class="modal-btn-primary" id="adminDownloadBtn" style="display: none;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Attachment
            </button>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal" onclick="closeRejectModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">REJECT LEAVE REQUEST</span>
                <h3 class="modal-title" id="rejectModalTitle">Confirm Rejection</h3>
                <p class="modal-sub">Please provide a reason for rejecting this leave request</p>
            </div>
            <button class="modal-close" onclick="closeRejectModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-field">
                <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 8px;">Rejection Reason <span style="color: #8e1e18;">*</span></label>
                <textarea id="rejectionReason" rows="4" placeholder="Explain why this leave request is being rejected..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeRejectModal()">Cancel</button>
            <button class="modal-btn-primary" id="confirmRejectBtn" style="background: #dc2626;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                Reject Request
            </button>
        </div>
    </div>
</div>

<script>
function applyAdminLeaveFilters() {
    const department = document.getElementById('filterDepartment').value;
    const type = document.getElementById('filterLeaveType').value;
    const status = document.getElementById('filterLeaveStatus').value;
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    let visible = 0;
    
    rows.forEach(row => {
        if (row.querySelector('.emp-cell')) {
            const matchDept = !department || row.dataset.department === department;
            const matchType = !type || row.dataset.type === type;
            const matchStatus = !status || row.dataset.status === status;
            const show = matchDept && matchType && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        }
    });
    
    const total = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);
    document.getElementById('leaveRequestCount').textContent = visible;
    document.getElementById('leaveRequestFooter').innerHTML = `Showing <strong>${visible}</strong> of <strong>${total}</strong> records`;
}

function openAdminLeaveDetailModal(id, name, empId, type, from, to, days, reason, status, appNumber, attachmentUrl, remarks) {
    document.getElementById('adminLeaveAppNumber').textContent = 'LEAVE REQUEST · ' + appNumber;
    document.getElementById('adminLeaveEmployeeName').textContent = name;
    document.getElementById('adminLeaveEmployeeId').textContent = empId;
    document.getElementById('adminLeaveType').textContent = type;
    document.getElementById('adminLeaveFrom').textContent = from;
    document.getElementById('adminLeaveTo').textContent = to;
    document.getElementById('adminLeaveDays').textContent = days + ' day' + (days > 1 ? 's' : '');
    document.getElementById('adminLeaveReason').textContent = reason;
    
    const statusBadge = document.getElementById('adminLeaveStatus');
    statusBadge.textContent = status;
    statusBadge.className = 'badge-status ' + 
        (status === 'Approved' ? 'processed' : 
         status === 'Pending' ? 'pending' : 
         status === 'Rejected' ? 'on-hold' : 'cancelled');
    
    const remarksSection = document.getElementById('adminRemarksSection');
    const remarksText = document.getElementById('adminRemarksText');
    if (remarks && remarks.trim() !== '') {
        remarksText.textContent = remarks;
        remarksSection.style.display = 'block';
    } else {
        remarksSection.style.display = 'none';
    }
    
    const downloadBtn = document.getElementById('adminDownloadBtn');
    if (attachmentUrl && attachmentUrl.trim() !== '') {
        downloadBtn.style.display = 'flex';
        downloadBtn.onclick = () => window.open(attachmentUrl, '_blank');
    } else {
        downloadBtn.style.display = 'none';
    }
    
    document.getElementById('adminLeaveDetailModal').style.display = 'flex';
}

function closeAdminLeaveDetailModal() {
    document.getElementById('adminLeaveDetailModal').style.display = 'none';
}

let currentRejectId = null;
let currentRejectAppNumber = null;

function openRejectModal(id, appNumber) {
    currentRejectId = id;
    currentRejectAppNumber = appNumber;
    document.getElementById('rejectModalTitle').textContent = `Reject ${appNumber}`;
    document.getElementById('rejectionReason').value = '';
    document.getElementById('rejectModal').style.display = 'flex';
}

function closeRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
    currentRejectId = null;
    currentRejectAppNumber = null;
}

function approveLeaveRequest(id, appNumber) {
    if (!confirm(`Are you sure you want to approve leave request ${appNumber}?`)) {
        return;
    }
    
    fetch(`/admin/leave/${id}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Leave request approved successfully!');
            location.reload();
        } else {
            alert(data.message || 'Failed to approve leave request');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while approving the leave request');
    });
}

document.getElementById('confirmRejectBtn')?.addEventListener('click', function() {
    const reason = document.getElementById('rejectionReason').value.trim();
    
    if (!reason) {
        alert('Please provide a reason for rejection');
        return;
    }
    
    const btn = this;
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/></svg> Rejecting...';
    
    fetch(`/admin/leave/${currentRejectId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ remarks: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Rejected!';
            btn.style.background = '#15803d';
            setTimeout(() => {
                closeRejectModal();
                location.reload();
            }, 1000);
        } else {
            alert(data.message || 'Failed to reject leave request');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while rejecting the leave request');
        btn.disabled = false;
        btn.innerHTML = originalContent;
    });
});
</script>
