<section class="table-section" id="leave-tab" style="display: block;">
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
                <option value="Disapproved">Disapproved</option>
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
                            @if($application->employee->photo)
                                <img src="{{ $application->employee->photo }}" alt="{{ $application->employee->first_name }}" class="emp-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e8e7f5;">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[($application->employee->id ?? 0) % count($avatarColors)] }}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border:2px solid #e8e7f5;">
                                    {{ strtoupper(substr($application->employee->first_name ?? 'N', 0, 1) . substr($application->employee->last_name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
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
                            <span class="badge-status on-hold">Disapproved</span>
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
                                <button class="btn-reject" onclick="openRejectModal({{ $application->id }}, '{{ $application->application_number }}')">Disapprove</button>
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
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="leaveRequestFooter">Showing <strong id="leaveRequestRowStart">1</strong>-<strong id="leaveRequestRowEnd">{{ min(10, $leaveApplications->count()) }}</strong> of <strong id="leaveRequestRowTotal">{{ $leaveApplications->count() }}</strong> records</p>
            <select id="leaveRequestRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeLeaveRequestRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="leaveRequestPaginationControls"></div>
    </div>
</section>

{{-- Admin Leave Detail Modal — CS Form No. 6 Preview --}}
<div class="modal-overlay" id="adminLeaveDetailModal" onclick="closeAdminLeaveDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 920px; width: 95vw;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="adminLeaveAppNumber">CS FORM NO. 6 · LV-2025-001</span>
                <h3 class="modal-title" id="adminLeaveEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="adminLeaveEmployeeId">PGS-0115</span>
                    · <span id="adminLeaveType">Vacation Leave</span>
                    · <span id="adminLeaveStatus" class="badge-status pending" style="font-size: 11px;">Pending</span>
                </p>
            </div>
            <button class="modal-close" onclick="closeAdminLeaveDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0; background: #e8e8f0;">
            <iframe id="adminLeaveFormFrame"
                title="CS Form No. 6 — Application for Leave"
                style="width: 100%; height: 65vh; border: none; display: block; background: #fff;"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeAdminLeaveDetailModal()">Close</button>
            <button class="modal-btn-primary" id="adminDownloadBtn" style="display: none; background: #6b7280;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Attachment
            </button>
            <div style="display: flex; gap: 8px; margin-left: auto;">
                <button class="modal-btn-primary" id="adminPrintFormBtn" onclick="printLeaveForm()" style="background: #6366f1;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary" id="adminDownloadFormBtn" onclick="downloadLeaveForm()" style="background: #10b981;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal" onclick="closeRejectModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DISAPPROVE LEAVE REQUEST</span>
                <h3 class="modal-title" id="rejectModalTitle">Confirm Disapproval</h3>
                <p class="modal-sub">Please provide a reason for disapproving this leave request</p>
            </div>
            <button class="modal-close" onclick="closeRejectModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-field">
                <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 8px;">Disapproval Reason <span style="color: #8e1e18;">*</span></label>
                <textarea id="rejectionReason" rows="4" placeholder="Explain why this leave request is being disapproved..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical;"></textarea>
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
                Disapprove Request
            </button>
        </div>
    </div>
</div>

<script>
let leaveRequestCurrentPage = 1;
let leaveRequestRowsPerPage = 10;
let leaveRequestTotalRows = {{ $leaveApplications->count() }};

function changeLeaveRequestRowsPerPage() {
    leaveRequestRowsPerPage = parseInt(document.getElementById('leaveRequestRowsPerPage').value);
    leaveRequestCurrentPage = 1;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
}

function renderLeaveRequestPagination() {
    const totalPages = Math.ceil(leaveRequestTotalRows / leaveRequestRowsPerPage);
    const paginationControls = document.getElementById('leaveRequestPaginationControls');
    let html = '';

    html += `<button class="page-btn" ${leaveRequestCurrentPage === 1 ? 'disabled' : ''} onclick="changeLeaveRequestPage(${leaveRequestCurrentPage - 1})">‹</button>`;

    for (let i = 1; i <= totalPages; i++) {
        html += `<button class="page-btn ${i === leaveRequestCurrentPage ? 'active' : ''}" onclick="changeLeaveRequestPage(${i})">${i}</button>`;
    }

    html += `<button class="page-btn" ${leaveRequestCurrentPage === totalPages ? 'disabled' : ''} onclick="changeLeaveRequestPage(${leaveRequestCurrentPage + 1})">›</button>`;

    paginationControls.innerHTML = html;
}

function changeLeaveRequestPage(page) {
    const totalPages = Math.ceil(leaveRequestTotalRows / leaveRequestRowsPerPage);
    if (page < 1 || page > totalPages) return;
    leaveRequestCurrentPage = page;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
}

function paginateLeaveRequestTable() {
    const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
    const start = (leaveRequestCurrentPage - 1) * leaveRequestRowsPerPage;
    const end = start + leaveRequestRowsPerPage;
    let visibleCount = 0;

    rows.forEach((row, index) => {
        if (row.querySelector('.emp-cell')) {
            if (index >= start && index < end && row.style.display !== 'none') {
                row.style.display = '';
                visibleCount++;
            } else if (index < start || index >= end) {
                row.style.display = 'none';
            }
        }
    });

    document.getElementById('leaveRequestRowStart').textContent = visibleCount > 0 ? start + 1 : 0;
    document.getElementById('leaveRequestRowEnd').textContent = start + visibleCount;
}

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

    leaveRequestTotalRows = visible;
    leaveRequestCurrentPage = 1;
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
    document.getElementById('leaveRequestRowTotal').textContent = total;
}

document.addEventListener('DOMContentLoaded', function() {
    renderLeaveRequestPagination();
    paginateLeaveRequestTable();
});
</script>
