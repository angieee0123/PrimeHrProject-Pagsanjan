@extends('layouts.app')

@section('title', 'Leave & Benefits · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('admin.admin-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('admin.admin-notification')

<div class="welcome-banner">
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <div>
            <h2>Leave & Benefits</h2>
            <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Employee Leave & Benefits</p>
        </div>
    </div>
    <div class="banner-right">
        <span class="banner-badge">
            <span class="banner-badge-dot"></span>
            2 Pending
        </span>
        <span class="banner-badge outline">FY {{ now()->year }}</span>
    </div>
</div>

@php
        $leaves = collect([
            ['id' => 'LV-2025-001', 'empId' => 'PGS-0041', 'name' => 'Maria B. Santos',   'position' => 'Administrative Officer IV',   'dept' => 'Office of the Mayor',          'type' => 'Vacation Leave',  'from' => 'Jun 10, 2025', 'to' => 'Jun 12, 2025', 'days' => 3, 'reason' => 'Family vacation', 'status' => 'Approved'],
            ['id' => 'LV-2025-002', 'empId' => 'PGS-0115', 'name' => 'Ana R. Reyes',      'position' => 'Nurse II',                    'dept' => 'Municipal Health Office',       'type' => 'Sick Leave',      'from' => 'Jun 15, 2025', 'to' => 'Jun 16, 2025', 'days' => 2, 'reason' => 'Medical consultation', 'status' => 'Approved'],
            ['id' => 'LV-2025-003', 'empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III',     'dept' => 'Office of the Mun. Treasurer',  'type' => 'Sick Leave',      'from' => 'Jun 20, 2025', 'to' => 'Jun 22, 2025', 'days' => 3, 'reason' => 'Flu and fever', 'status' => 'Pending'],
            ['id' => 'LV-2025-004', 'empId' => 'PGS-0267', 'name' => 'Liza G. Gomez',     'position' => 'Social Welfare Officer II',  'dept' => 'MSWD – Pagsanjan',              'type' => 'Emergency Leave', 'from' => 'Jun 18, 2025', 'to' => 'Jun 18, 2025', 'days' => 1, 'reason' => 'Family emergency', 'status' => 'Approved'],
            ['id' => 'LV-2025-005', 'empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II',       'dept' => 'Office of the Mun. Engineer',   'type' => 'Vacation Leave',  'from' => 'Jul 1, 2025',  'to' => 'Jul 3, 2025',  'days' => 3, 'reason' => 'Rest and recreation', 'status' => 'Pending'],
            ['id' => 'LV-2025-006', 'empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar',     'type' => 'Vacation Leave',  'from' => 'Jun 25, 2025', 'to' => 'Jun 25, 2025', 'days' => 1, 'reason' => 'Personal errand', 'status' => 'Rejected'],
        ]);

        $benefitsData = collect([
            ['empId' => 'PGS-0041', 'name' => 'Maria B. Santos',   'gsis' => '₱3,794', 'philhealth' => '₱1,050', 'pagibig' => '₱100', 'vlBalance' => 15, 'slBalance' => 15],
            ['empId' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'gsis' => '₱3,428', 'philhealth' => '₱950',  'pagibig' => '₱100', 'vlBalance' => 12, 'slBalance' => 13],
            ['empId' => 'PGS-0115', 'name' => 'Ana R. Reyes',      'gsis' => '₱3,046', 'philhealth' => '₱850',  'pagibig' => '₱100', 'vlBalance' => 13, 'slBalance' => 11],
            ['empId' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'gsis' => '₱4,253', 'philhealth' => '₱1,150', 'pagibig' => '₱100', 'vlBalance' => 10, 'slBalance' => 9],
            ['empId' => 'PGS-0267', 'name' => 'Liza G. Gomez',     'gsis' => '₱3,159', 'philhealth' => '₱875',  'pagibig' => '₱100', 'vlBalance' => 14, 'slBalance' => 14],
            ['empId' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'gsis' => '₱2,748', 'philhealth' => '₱775',  'pagibig' => '₱100', 'vlBalance' => 8,  'slBalance' => 10],
        ]);

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

        $departments = ['All Departments', 'Office of the Mayor', 'Office of the Vice Mayor', 'Sangguniang Bayan', 'Office of the Mun. Treasurer', "Municipal Assessor's Office", 'Municipal Civil Registrar', 'Municipal Health Office', 'MSWD – Pagsanjan', "Municipal Planning & Dev't Office", 'Office of the Mun. Engineer', 'Office of the Mun. Agriculturist', 'Municipal Environment & Natural Resources', "Municipal Business & Dev't Office", 'Human Resource Management Office', 'Municipal Disaster Risk Reduction & Mgmt', 'Office of the Mun. Budget', 'Municipal Circuit Trial Court'];
        $leaveTypes = ['Vacation Leave', 'Sick Leave', 'Maternity Leave', 'Paternity Leave', 'Emergency Leave', 'Special Leave'];

        $totalLeaves = $leaves->count();
        $approvedCount = $leaves->where('status', 'Approved')->count();
        $pendingCount = $leaves->where('status', 'Pending')->count();
        $totalDays = $leaves->sum('days');
        @endphp

        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Leave Requests</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $totalLeaves }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">All time</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Approved</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $approvedCount }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">This period</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Pending Approval</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $pendingCount }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">Needs action</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Leave Days</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $totalDays }}</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">Across all employees</p>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
            <button class="tab-btn active" id="tab-leave" onclick="switchTab('leave')">Leave Requests</button>
            <button class="tab-btn" id="tab-benefits" onclick="switchTab('benefits')">Benefits Summary</button>
        </div>

        <div id="leave-tab">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 14px;">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <div class="search-input-wrap" style="position: relative;">
                        <svg width="16" height="16" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Search requests..." style="padding: 8px 12px 8px 38px; border: 1.5px solid #e4e3f0; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; width: 200px; outline: none;">
                    </div>
                </div>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <select class="filter-select" id="dept-filter" style="padding: 8px 32px 8px 12px; border: 1.5px solid #e4e3f0; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; background: #fff url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b6a8a%22 stroke-width=%222%22><polyline points=%226 9 12 15 18 9%22/></svg>') no-repeat right 10px center; appearance: none; cursor: pointer;">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="type-filter" style="padding: 8px 32px 8px 12px; border: 1.5px solid #e4e3f0; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; background: #fff url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b6a8a%22 stroke-width=%222%22><polyline points=%226 9 12 15 18 9%22/></svg>') no-repeat right 10px center; appearance: none; cursor: pointer;">
                        <option value="All Types">All Types</option>
                        @foreach($leaveTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="status-filter" style="padding: 8px 32px 8px 12px; border: 1.5px solid #e4e3f0; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; background: #fff url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%236b6a8a%22 stroke-width=%222%22><polyline points=%226 9 12 15 18 9%22/></svg>') no-repeat right 10px center; appearance: none; cursor: pointer;">
                        <option value="All">All Status</option>
                        <option value="Approved">Approved</option>
                        <option value="Pending">Pending</option>
                        <option value="Rejected">Rejected</option>
                    </select>
                    <button class="btn-file-leave" id="file-leave-btn" onclick="showFileLeaveModal()">+ File Leave</button>
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            <div class="table-section">
                <div class="table-header">
                    <div>
                        <p class="table-title">Leave Requests — June 2025</p>
                        <p class="table-sub">Municipal Government of Pagsanjan · <span id="leave-showing">{{ $totalLeaves }}</span> of {{ $totalLeaves }} records</p>
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
                        <tbody id="leave-table-body">
                            @foreach($leaves as $index => $leave)
                            @php
                            $colorIndex = $index % count($avatarColors);
                            $statusClass = $leave['status'] === 'Approved' ? 'processed' : ($leave['status'] === 'Pending' ? 'pending' : 'on-hold');
                            @endphp
                            <tr data-dept="{{ $leave['dept'] }}" data-type="{{ $leave['type'] }}" data-status="{{ $leave['status'] }}">
                                <td>
                                    <div class="emp-cell">
                                        <div class="emp-avatar" style="background: {{ $avatarColors[$colorIndex] }}">{{ getInitials($leave['name']) }}</div>
                                        <div>
                                            <p class="emp-name">{{ $leave['name'] }}</p>
                                            <p class="emp-id">{{ $leave['empId'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="dept-tag">{{ $leave['dept'] }}</span></td>
                                <td style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $leave['type'] }}</td>
                                <td style="font-size: 13px;">{{ $leave['from'] }}</td>
                                <td style="font-size: 13px;">{{ $leave['to'] }}</td>
                                <td style="font-weight: 600; color: #0b044d;">{{ $leave['days'] }}</td>
                                <td><span class="badge-status {{ $statusClass }}">{{ $leave['status'] }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-view" onclick="viewLeave('{{ $leave['id'] }}')">View</button>
                                        @if($leave['status'] === 'Pending')
                                        <button class="btn-approve" onclick="changeStatus('{{ $leave['id'] }}', 'Approved')">Approve</button>
                                        <button class="btn-reject" onclick="changeStatus('{{ $leave['id'] }}', 'Rejected')">Reject</button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="empty-state" id="leave-empty" style="display: none; text-align: center; padding: 40px 20px;">
                        <p style="font-size: 13px; color: #9999bb; margin: 0;">No records found</p>
                    </div>
                </div>

                <div class="table-footer">
                    <p>Showing <strong id="leave-visible">{{ $totalLeaves }}</strong> of <strong>{{ $totalLeaves }}</strong> records</p>
                    <div class="pagination">
                        <button class="page-btn active">1</button>
                        <button class="page-btn">›</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="benefits-tab" style="display: none;">
            <div class="table-section">
                <div class="table-header">
                    <div>
                        <p class="table-title">Benefits Summary — June 2025</p>
                        <p class="table-sub">GSIS · PhilHealth · Pag-IBIG · Leave Credits</p>
                    </div>
                    <div class="table-actions">
                        <button class="btn-export">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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
                            @foreach($benefitsData as $index => $row)
                            @php
                            $colorIndex = $index % count($avatarColors);
                            @endphp
                            <tr>
                                <td>
                                    <div class="emp-cell">
                                        <div class="emp-avatar" style="background: {{ $avatarColors[$colorIndex] }}">{{ getInitials($row['name']) }}</div>
                                        <div>
                                            <p class="emp-name">{{ $row['name'] }}</p>
                                            <p class="emp-id">{{ $row['empId'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="deduction" style="font-weight: 600;">{{ $row['gsis'] }}</td>
                                <td class="deduction" style="font-weight: 600;">{{ $row['philhealth'] }}</td>
                                <td class="deduction" style="font-weight: 600;">{{ $row['pagibig'] }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3; min-width: 50px;">
                                            <div style="width: {{ ($row['vlBalance'] / 15) * 100 }}%; height: 100%; background: #0b044d; border-radius: 3;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 600; color: #0b044d;">{{ $row['vlBalance'] }} days</span>
                                    </div>
                                </td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3; min-width: 50px;">
                                            <div style="width: {{ ($row['slBalance'] / 15) * 100 }}%; height: 100%; background: #15803d; border-radius: 3;"></div>
                                        </div>
                                        <span style="font-size: 12px; font-weight: 600; color: #15803d;">{{ $row['slBalance'] }} days</span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <p>Showing <strong>{{ $benefitsData->count() }}</strong> of <strong>{{ $benefitsData->count() }}</strong> records</p>
                </div>
            </div>
        </div>

<script>
const leavesData = @json($leaves);
const avatarColors = @json($avatarColors);

function getInitials(name) {
    const parts = name.split(' ').filter(n => /^[A-Z]/.test(n));
    return parts.map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

function switchTab(tab) {
    document.getElementById('leave-tab').style.display = tab === 'leave' ? 'block' : 'none';
    document.getElementById('benefits-tab').style.display = tab === 'benefits' ? 'block' : 'none';
    document.getElementById('tab-leave').classList.toggle('active', tab === 'leave');
    document.getElementById('tab-benefits').classList.toggle('active', tab === 'benefits');
}

function viewLeave(leaveId) {
    const leave = leavesData.find(l => l.id === leaveId);
    if (!leave) return;
    
    const idx = leavesData.findIndex(l => l.id === leaveId);
    const color = avatarColors[idx % avatarColors.length];
    
    document.getElementById('modal-leave-id').textContent = 'LEAVE REQUEST · ' + leave.id;
    document.getElementById('modal-name').textContent = leave.name;
    document.getElementById('modal-position').textContent = leave.position + ' · ' + leave.dept;
    document.getElementById('modal-avatar').style.background = color;
    document.getElementById('modal-avatar').textContent = getInitials(leave.name);
    document.getElementById('modal-emp-id').textContent = leave.empId;
    
    const statusBadge = document.getElementById('modal-status-badge');
    statusBadge.textContent = leave.status;
    statusBadge.className = 'badge-status ' + (leave.status === 'Approved' ? 'processed' : leave.status === 'Pending' ? 'pending' : 'on-hold');
    
    document.getElementById('modal-type').textContent = leave.type;
    document.getElementById('modal-from').textContent = leave.from;
    document.getElementById('modal-to').textContent = leave.to;
    document.getElementById('modal-days').textContent = leave.days + ' day' + (leave.days > 1 ? 's' : '');
    document.getElementById('modal-reason').textContent = leave.reason;
    
    document.getElementById('view-modal').style.display = 'flex';
}

function showFileLeaveModal() {
    document.getElementById('file-leave-modal').style.display = 'flex';
}

function submitLeave() {
    const empId = document.getElementById('file-emp-id').value;
    const name = document.getElementById('file-name').value;
    
    if (empId && name) {
        alert('Leave request submitted successfully!');
        closeModal('file-leave-modal');
    }
}

function changeStatus(leaveId, newStatus) {
    const rows = document.querySelectorAll('#leave-table-body tr');
    rows.forEach(row => {
        const empIdCell = row.querySelector('.emp-id');
        if (empIdCell) {
            const leave = leavesData.find(l => l.id === leaveId);
            if (leave) {
                leave.status = newStatus;
                row.dataset.status = newStatus;
                
                const statusCell = row.querySelector('td:nth-child(7)');
                statusCell.innerHTML = `<span class="badge-status ${newStatus === 'Approved' ? 'processed' : newStatus === 'Pending' ? 'pending' : 'on-hold'}">${newStatus}</span>`;
                
                const actionsCell = row.querySelector('td:last-child .row-actions');
                if (newStatus !== 'Pending') {
                    actionsCell.innerHTML = `<button class="btn-view" onclick="viewLeave('${leaveId}')">View</button>`;
                }
            }
        }
    });
    filterLeaves();
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('dept-filter').addEventListener('change', filterLeaves);
document.getElementById('type-filter').addEventListener('change', filterLeaves);
document.getElementById('status-filter').addEventListener('change', filterLeaves);
document.getElementById('search-input').addEventListener('input', filterLeaves);

function filterLeaves() {
    const deptFilter = document.getElementById('dept-filter').value;
    const typeFilter = document.getElementById('type-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('#leave-table-body tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const dept = row.dataset.dept;
        const type = row.dataset.type;
        const status = row.dataset.status;
        const name = row.querySelector('.emp-name').textContent.toLowerCase();
        const empId = row.querySelector('.emp-id').textContent.toLowerCase();
        
        const matchDept = deptFilter === 'All Departments' || dept === deptFilter;
        const matchType = typeFilter === 'All Types' || type === typeFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || name.includes(searchQuery) || empId.includes(searchQuery);
        
        const isVisible = matchDept && matchType && matchStatus && matchSearch;
        row.style.display = isVisible ? 'table-row' : 'none';
        if (isVisible) visibleCount++;
    });
    
    document.getElementById('leave-showing').textContent = visibleCount;
    document.getElementById('leave-visible').textContent = visibleCount;
    document.getElementById('leave-empty').style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('view-modal').style.display = 'none';
        document.getElementById('file-leave-modal').style.display = 'none';
    }
});
</script>

<style>
.tab-btn {
    padding: 10px 20px;
    border: none;
    background: none;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #9999bb;
    cursor: pointer;
    border-bottom: 2.5px solid transparent;
    margin-bottom: -1.5px;
    transition: color 0.2s, border-color 0.2s;
}
.tab-btn.active {
    color: #0b044d;
    border-bottom-color: #0b044d;
}
.btn-file-leave {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    background: #0b044d;
    font-size: 12.5px;
    font-weight: 600;
    color: #fff;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: background 0.2s;
}
.btn-file-leave:hover { background: #8e1e18; }
.btn-approve {
    padding: 5px 12px;
    border: 1.5px solid #bbf7d0;
    border-radius: 7px;
    background: #e8f9ef;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #15803d;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-approve:hover { background: #15803d; color: #fff; border-color: #15803d; }
.btn-reject {
    padding: 5px 12px;
    border: 1.5px solid #f5d0ce;
    border-radius: 7px;
    background: #fdf0ef;
    font-size: 12px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    color: #8e1e18;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-reject:hover { background: #8e1e18; color: #fff; border-color: #8e1e18; }
.row-actions { display: flex; gap: 6px; align-items: center; flex-wrap: wrap; }
</style>
    </main>

    @include('admin.admin-chatbot')

</div>

<script>
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('toggle-btn');
    const logoText     = document.getElementById('logo-text');
    const navLabel     = document.getElementById('nav-label');
    const userInfo     = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn    = document.getElementById('mobile-menu-btn');
    const overlay      = document.getElementById('mobile-overlay');

    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        logoText.style.display  = collapsed ? 'none' : '';
        navLabel.style.display  = collapsed ? 'none' : '';
        userInfo.style.display  = collapsed ? 'none' : '';
        sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });

    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
</script>

{{-- Modals --}}
<div class="modal-overlay" id="view-modal" style="display: none;" onclick="if(event.target===this)closeModal('view-modal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="modal-leave-id">LEAVE REQUEST</span>
                <h3 class="modal-title" id="modal-name">Employee Name</h3>
                <p class="modal-sub" id="modal-position">Position · Department</p>
            </div>
            <button class="modal-close" onclick="closeModal('view-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px; padding: 16px; background: #f7f6ff; border-radius: 12px;">
                <div class="emp-avatar" id="modal-avatar" style="width: 48px; height: 48px; border-radius: 12px; font-size: 16px; font-weight: 700; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">MS</div>
                <div>
                    <p id="modal-emp-id" style="font-size: 11px; color: #9999bb; margin: 0 0 4px;">PGS-0000</p>
                    <span class="badge-status" id="modal-status-badge">Pending</span>
                </div>
            </div>
            <p style="font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: 1px; margin-bottom: 12px;">LEAVE DETAILS</p>
            <div class="modal-row"><span>Leave Type</span><strong id="modal-type">—</strong></div>
            <div class="modal-row"><span>Date From</span><strong id="modal-from">—</strong></div>
            <div class="modal-row"><span>Date To</span><strong id="modal-to">—</strong></div>
            <div class="modal-row"><span>No. of Days</span><strong id="modal-days">—</strong></div>
            <div class="modal-row"><span>Reason</span><strong id="modal-reason">—</strong></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="file-leave-modal" style="display: none;" onclick="if(event.target===this)closeModal('file-leave-modal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">FILE LEAVE REQUEST</span>
                <h3 class="modal-title">New Leave Application</h3>
            </div>
            <button class="modal-close" onclick="closeModal('file-leave-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #0b044d; margin-bottom: 4px;">Employee ID</label>
                    <input type="text" id="file-emp-id" placeholder="e.g. PGS-0041" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #0b044d; margin-bottom: 4px;">Employee Name</label>
                    <input type="text" id="file-name" placeholder="Full name" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; box-sizing: border-box;">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #0b044d; margin-bottom: 4px;">Leave Type</label>
                    <select style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; background: #fff; box-sizing: border-box;">
                        <option>Vacation Leave</option>
                        <option>Sick Leave</option>
                        <option>Maternity Leave</option>
                        <option>Paternity Leave</option>
                        <option>Emergency Leave</option>
                        <option>Special Leave</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #0b044d; margin-bottom: 4px;">Date From</label>
                    <input type="date" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #0b044d; margin-bottom: 4px;">Date To</label>
                    <input type="date" style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; box-sizing: border-box;">
                </div>
                <div style="grid-column: span 2;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: #0b044d; margin-bottom: 4px;">Reason</label>
                    <textarea rows="3" placeholder="State your reason..." style="width: 100%; padding: 9px 12px; border: 1.5px solid #e4e3f0; border-radius: 9px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; outline: none; resize: vertical; box-sizing: border-box;"></textarea>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('file-leave-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="submitLeave()">Submit Request</button>
        </div>
    </div>
</div>
@endsection