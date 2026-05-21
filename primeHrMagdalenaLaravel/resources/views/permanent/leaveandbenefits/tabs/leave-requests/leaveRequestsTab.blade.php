<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Requests</h3>
            <p class="table-sub">{{ $leaveApplications->count() }} of {{ $leaveApplications->count() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterType" onchange="applyLeaveFilters()">
                <option value="">All Types</option>
                @foreach($leaveTypes as $type)
                    <option value="{{ $type->leave_name }}">{{ $type->leave_name }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="filterStatus" onchange="applyLeaveFilters()">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button class="btn-export" onclick="openFileModal()">+ File Leave</button>
        </div>
    </div>
    
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Leave ID</th>
                    <th>Leave Type</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveApplications as $application)
                <tr data-type="{{ $application->leaveType->leave_name ?? 'N/A' }}" data-status="{{ ucfirst($application->status) }}">
                    <td class="emp-id">{{ $application->application_number }}</td>
                    <td><span class="dept-tag">{{ $application->leaveType->leave_name ?? 'N/A' }}</span></td>
                    <td class="work-date">{{ $application->start_date->format('M d, Y') }}</td>
                    <td class="work-date">{{ $application->end_date->format('M d, Y') }}</td>
                    <td class="days-count">{{ number_format($application->number_of_days, 0) }} {{ $application->number_of_days == 1 ? 'day' : 'days' }}</td>
                    <td>
                        @if($application->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($application->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($application->status === 'rejected')
                            <span class="badge-status rejected">Rejected</span>
                        @else
                            <span class="badge-status cancelled">Cancelled</span>
                        @endif
                    </td>
                    <td>
                        <button class="btn-view" onclick="openDetailModal(
                            '{{ addslashes($application->leaveType->leave_name ?? 'N/A') }}',
                            '{{ $application->start_date->format('M d, Y') }}',
                            '{{ $application->end_date->format('M d, Y') }}',
                            {{ $application->number_of_days }},
                            '{{ addslashes($application->reason) }}',
                            '{{ ucfirst($application->status) }}',
                            '{{ $application->application_number }}',
                            '{{ $application->attachment_path ? asset('storage/' . $application->attachment_path) : '' }}',
                            '{{ addslashes($application->approver_remarks ?? '') }}',
                            {{ $application->id }}
                        )">View</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No leave requests found</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Your leave applications will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="table-footer">
        <p id="leaveCount">Showing <strong>{{ $leaveApplications->count() }}</strong> of <strong>{{ $leaveApplications->count() }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn">‹</button>
            <button class="page-btn active">1</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>
