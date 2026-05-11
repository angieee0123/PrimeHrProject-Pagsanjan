<section class="table-section" id="leave-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Requests — June 2025</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ count($leaveRequests) }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option>All Departments</option>
                <option>Office of the Mayor</option>
                <option>Office of the Mun. Engineer</option>
                <option>Municipal Health Office</option>
                <option>MSWD – Pagsanjan</option>
                <option>Office of the Mun. Treasurer</option>
            </select>
            <select class="filter-select">
                <option>All Types</option>
                <option>Vacation Leave</option>
                <option>Sick Leave</option>
                <option>Maternity Leave</option>
                <option>Paternity Leave</option>
                <option>Emergency Leave</option>
                <option>Special Leave</option>
            </select>
            <select class="filter-select">
                <option>All Status</option>
                <option>Approved</option>
                <option>Pending</option>
                <option>Rejected</option>
            </select>
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;">
                + File Leave
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
            <tbody>
                @foreach($leaveRequests as $index => $leave)
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($leave['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $leave['name'] }}</p>
                                <p class="emp-id">{{ $leave['empId'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $leave['dept'] }}</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $leave['type'] }}</td>
                    <td style="font-size: 13px; color: #6b6a8a;">{{ $leave['from'] }}</td>
                    <td style="font-size: 13px; color: #6b6a8a;">{{ $leave['to'] }}</td>
                    <td style="font-weight: 600; color: #0b044d; font-size: 13px;">{{ $leave['days'] }}</td>
                    <td><span class="badge-status {{ $leave['status'] === 'Approved' ? 'processed' : ($leave['status'] === 'Pending' ? 'pending' : 'on-hold') }}">{{ $leave['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            @if($leave['status'] === 'Pending')
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($leaveRequests) }}</strong> of <strong>{{ count($leaveRequests) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>
