@extends('layouts.app')

@section('content')
@php
$avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

$leaveRequests = [
    ['id' => 'LV-2025-001', 'empId' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'type' => 'Vacation Leave', 'from' => 'Jun 10, 2025', 'to' => 'Jun 12, 2025', 'days' => 3, 'reason' => 'Family vacation', 'status' => 'Approved'],
    ['id' => 'LV-2025-002', 'empId' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'type' => 'Sick Leave', 'from' => 'Jun 15, 2025', 'to' => 'Jun 16, 2025', 'days' => 2, 'reason' => 'Medical consultation', 'status' => 'Approved'],
    ['id' => 'LV-2025-003', 'empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'type' => 'Sick Leave', 'from' => 'Jun 20, 2025', 'to' => 'Jun 22, 2025', 'days' => 3, 'reason' => 'Flu and fever', 'status' => 'Pending'],
    ['id' => 'LV-2025-004', 'empId' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'type' => 'Emergency Leave', 'from' => 'Jun 18, 2025', 'to' => 'Jun 18, 2025', 'days' => 1, 'reason' => 'Family emergency', 'status' => 'Approved'],
    ['id' => 'LV-2025-005', 'empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'type' => 'Vacation Leave', 'from' => 'Jul 1, 2025', 'to' => 'Jul 3, 2025', 'days' => 3, 'reason' => 'Rest and recreation', 'status' => 'Pending'],
    ['id' => 'LV-2025-006', 'empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'type' => 'Vacation Leave', 'from' => 'Jun 25, 2025', 'to' => 'Jun 25, 2025', 'days' => 1, 'reason' => 'Personal errand', 'status' => 'Rejected'],
];

$benefitsData = [
    ['empId' => 'PGS-0041', 'name' => 'Maria B. Santos', 'gsis' => '₱3,794', 'philhealth' => '₱1,050', 'pagibig' => '₱100', 'vlBalance' => 15, 'slBalance' => 15],
    ['empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'gsis' => '₱3,428', 'philhealth' => '₱950', 'pagibig' => '₱100', 'vlBalance' => 12, 'slBalance' => 13],
    ['empId' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'gsis' => '₱3,046', 'philhealth' => '₱850', 'pagibig' => '₱100', 'vlBalance' => 13, 'slBalance' => 11],
    ['empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'gsis' => '₱4,253', 'philhealth' => '₱1,150', 'pagibig' => '₱100', 'vlBalance' => 10, 'slBalance' => 9],
    ['empId' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'gsis' => '₱3,159', 'philhealth' => '₱875', 'pagibig' => '₱100', 'vlBalance' => 14, 'slBalance' => 14],
    ['empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'gsis' => '₱2,748', 'philhealth' => '₱775', 'pagibig' => '₱100', 'vlBalance' => 8, 'slBalance' => 10],
];

$totalApproved = count(array_filter($leaveRequests, fn($r) => $r['status'] === 'Approved'));
$totalPending = count(array_filter($leaveRequests, fn($r) => $r['status'] === 'Pending'));
$totalDays = array_sum(array_column($leaveRequests, 'days'));
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Requests</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ count($leaveRequests) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalApproved }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPending }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Days</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalDays }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Across all employees</p>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
    <button class="tab-btn active" onclick="switchTab('leave')">Leave Requests</button>
    <button class="tab-btn" onclick="switchTab('benefits')">Benefits Summary</button>
</div>

<!-- Leave Requests Tab -->
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

<!-- Benefits Summary Tab -->
<section class="table-section" id="benefits-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Benefits Summary — June 2025</h3>
            <p class="table-sub">GSIS · PhilHealth · Pag-IBIG · Leave Credits</p>
        </div>
        <div class="table-actions">
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
                    <th>GSIS Premium</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                    <th>VL Balance</th>
                    <th>SL Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($benefitsData as $index => $benefit)
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($benefit['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $benefit['name'] }}</p>
                                <p class="emp-id">{{ $benefit['empId'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="deduction">{{ $benefit['gsis'] }}</td>
                    <td class="deduction">{{ $benefit['philhealth'] }}</td>
                    <td class="deduction">{{ $benefit['pagibig'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['vlBalance'] / 15) * 100 }}%; height: 100%; background: #0b044d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d;">{{ $benefit['vlBalance'] }} days</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['slBalance'] / 15) * 100 }}%; height: 100%; background: #15803d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #15803d;">{{ $benefit['slBalance'] }} days</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($benefitsData) }}</strong> of <strong>{{ count($benefitsData) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<style>
.tab-btn {
    background: none; border: none; cursor: pointer;
    padding: 10px 20px; font-family: 'Poppins', sans-serif;
    font-size: 13.5px; font-weight: 600;
    color: #9999bb;
    border-bottom: 2.5px solid transparent;
    margin-bottom: -1.5px;
    transition: all 0.2s;
}
.tab-btn.active {
    color: #0b044d;
    border-bottom: 2.5px solid #0b044d;
}
.tab-btn:hover { color: #0b044d; }
.badge-emptype {
    font-size: 11px; color: #0b044d; background: #f0effe;
    padding: 3px 10px; border-radius: 20px; font-weight: 600;
    border: 1px solid #dddcf0;
}
.btn-edit {
    padding: 6px 16px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-edit:hover { background: #e8e7f5; }
.btn-approve {
    padding: 6px 16px; background: #e8f9ef; color: #15803d;
    border: 1px solid #bbf7d0; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-approve:hover { background: #d1fae5; }
.btn-reject {
    padding: 6px 16px; background: #fdf0ef; color: #8e1e18;
    border: 1px solid #fecaca; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-reject:hover { background: #fee8e8; }
.row-actions { display: flex; gap: 6px; }
.table-footer {
    padding: 16px 24px; border-top: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: center;
}
.table-footer p { font-size: 13px; color: #6b6a8a; }
.pagination { display: flex; gap: 6px; }
.page-btn {
    width: 32px; height: 32px; border: 1px solid #e8e7f5;
    border-radius: 6px; background: #fff; color: #6b6a8a;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.page-btn.active { background: #0b044d; color: #fff; border-color: #0b044d; }
.page-btn:hover { background: #f7f6ff; }
.deduction {
    font-size: 13px; color: #8e1e18; font-weight: 600;
}
</style>

<script>
function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Show/hide tab content
    if (tab === 'leave') {
        document.getElementById('leave-tab').style.display = 'block';
        document.getElementById('benefits-tab').style.display = 'none';
    } else {
        document.getElementById('leave-tab').style.display = 'none';
        document.getElementById('benefits-tab').style.display = 'block';
    }
}
</script>
@endsection
