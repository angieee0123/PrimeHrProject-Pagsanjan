@extends('layouts.app')

@section('content')
<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.08);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">8</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All records</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active</p>
            <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.08);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">7</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">Currently active</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Inactive</p>
            <div class="stat-icon-wrap" style="background: rgba(142, 30, 24, 0.08);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">1</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Deactivated accounts</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Permanent</p>
            <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.08);">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2">
                    <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                </svg>
            </div>
        </div>
        <h2 class="stat-value">6</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Permanent employees</p>
        </div>
    </div>
</div>

<!-- Employee Table -->
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Records</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · 8 of 8 records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option>All Departments</option>
                <option>Office of the Mayor</option>
                <option>Office of the Mun. Engineer</option>
                <option>Municipal Health Office</option>
                <option>MSWD – Pagsanjan</option>
                <option>Office of the Mun. Treasurer</option>
                <option>Municipal Civil Registrar</option>
                <option>Office of the Mun. Budget</option>
                <option>Office of the Mun. Agriculturist</option>
            </select>
            <select class="filter-select">
                <option>All Status</option>
                <option>Active</option>
                <option>Inactive</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <button class="modal-btn-primary" onclick="openEmployeeWizard()" style="padding: 8px 18px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Employee
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department / Office</th>
                    <th>Type</th>
                    <th>Date Hired</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
                $personnel = [
                    ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'status' => 'Active', 'empType' => 'Permanent', 'dateHired' => 'Mar 12, 2015'],
                    ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'status' => 'Active', 'empType' => 'Permanent', 'dateHired' => 'Jun 1, 2012'],
                    ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'status' => 'Active', 'empType' => 'Permanent', 'dateHired' => 'Jan 15, 2018'],
                    ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'status' => 'Active', 'empType' => 'Permanent', 'dateHired' => 'Aug 3, 2009'],
                    ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'status' => 'Inactive', 'empType' => 'Permanent', 'dateHired' => 'Nov 20, 2016'],
                    ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'status' => 'Active', 'empType' => 'Permanent', 'dateHired' => 'Feb 7, 2020'],
                    ['id' => 'PGS-0342', 'name' => 'Grace A. Villanueva', 'position' => 'Budget Officer II', 'dept' => 'Office of the Mun. Budget', 'status' => 'Active', 'empType' => 'Casual', 'dateHired' => 'Apr 1, 2022'],
                    ['id' => 'PGS-0358', 'name' => 'Ramon D. Cruz', 'position' => 'Agriculturist I', 'dept' => 'Office of the Mun. Agriculturist', 'status' => 'Active', 'empType' => 'Casual', 'dateHired' => 'Jul 15, 2023'],
                ];

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
                @endphp

                @foreach($personnel as $index => $emp)
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($emp['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $emp['name'] }}</p>
                                <p class="emp-id">{{ $emp['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="position-cell">{{ $emp['position'] }}</td>
                    <td><span class="dept-tag">{{ $emp['dept'] }}</span></td>
                    <td><span class="badge-emptype">{{ $emp['empType'] }}</span></td>
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $emp['dateHired'] }}</td>
                    <td><span class="badge-status {{ $emp['status'] === 'Active' ? 'processed' : 'on-hold' }}">{{ $emp['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            <button class="btn-edit">Edit</button>
                            @if($emp['status'] === 'Active')
                            <button class="btn-deactivate">Deactivate</button>
                            @else
                            <button class="btn-activate">Activate</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>8</strong> of <strong>8</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

@include('admin.personnel.modals.employeeWizardComplete')

<!-- Success Modal -->
<div id="successModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; background:#e8f9ef; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#15803d" stroke-width="2.5">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Registration Successful!</h3>
        <p id="successMessage" style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6;"></p>
        <button onclick="closeSuccessModal()" style="padding:12px 32px; background:#15803d; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
            Done
        </button>
    </div>
</div>

<!-- Error Modal -->
<div id="errorModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 8px 32px rgba(11,4,77,0.2);">
        <div style="width:64px; height:64px; background:#fee8e8; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Registration Failed</h3>
        <p id="errorMessage" style="margin:0 0 24px; font-size:14px; color:#6b6a8a; line-height:1.6;"></p>
        <button onclick="closeErrorModal()" style="padding:12px 32px; background:#8e1e18; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; font-family:'Poppins',sans-serif;">
            Close
        </button>
    </div>
</div>

<script>
function closeSuccessModal() {
    document.getElementById('successModal').style.display = 'none';
    location.reload();
}

function closeErrorModal() {
    document.getElementById('errorModal').style.display = 'none';
}

// Show modals based on session messages
@if(session('success'))
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('successMessage').textContent = "{{ session('success') }}";
        document.getElementById('successModal').style.display = 'flex';
        // Close wizard if it's open
        if (document.getElementById('employeeWizardModal')) {
            document.getElementById('employeeWizardModal').style.display = 'none';
        }
    });
@endif

@if(session('error'))
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('errorMessage').textContent = "{{ session('error') }}";
        document.getElementById('errorModal').style.display = 'flex';
    });
@endif
</script>

<style>
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
.btn-deactivate {
    padding: 6px 16px; background: #fee8e8; color: #8e1e18;
    border: 1px solid #f5d0ce; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-deactivate:hover { background: #fdd; }
.btn-activate {
    padding: 6px 16px; background: #e8f9ef; color: #15803d;
    border: 1px solid #bbf7d0; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-activate:hover { background: #d1fae5; }
.row-actions { display: flex; gap: 6px; }
.modal-label { display:block; font-size:12px; font-weight:600; color:#0b044d; margin-bottom:6px; }
.modal-input { width:100%; padding:8px 12px; border:1px solid #e8e7f5; border-radius:8px; font-size:13px; color:#0b044d; font-family:'Poppins',sans-serif; outline:none; box-sizing:border-box; }
.modal-input:focus { border-color:#0b044d; }
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
</style>
@endsection
