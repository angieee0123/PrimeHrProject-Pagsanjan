@extends('layouts.app')

@section('title', 'Personnel · PRIME HRIS')

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

        @php
        $personnel = collect([
            ['id' => 'PGS-0041', 'name' => 'Maria B. Santos',    'position' => 'Administrative Officer IV',   'dept' => 'Office of the Mayor',          'status' => 'Active',    'empType' => 'Permanent', 'dateHired' => 'Mar 12, 2015', 'gender' => 'Female', 'birthday' => 'Apr 5, 1982',  'contact' => '09171234567', 'email' => 'maria.santos@pagsanjan.gov.ph',   'gsis' => '1234567890', 'philhealth' => '12-345678901-2', 'pagibig' => '1234-5678-9012', 'tin' => '123-456-789'],
            ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz',  'position' => 'Municipal Engineer II',        'dept' => 'Office of the Mun. Engineer',  'status' => 'Active',    'empType' => 'Permanent', 'dateHired' => 'Jun 1, 2012',  'gender' => 'Male',   'birthday' => 'Sep 14, 1979', 'contact' => '09189876543', 'email' => 'juan.delacruz@pagsanjan.gov.ph',  'gsis' => '2345678901', 'philhealth' => '23-456789012-3', 'pagibig' => '2345-6789-0123', 'tin' => '234-567-890'],
            ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes',       'position' => 'Nurse II',                    'dept' => 'Municipal Health Office',      'status' => 'Active',    'empType' => 'Permanent', 'dateHired' => 'Jan 15, 2018', 'gender' => 'Female', 'birthday' => 'Jul 22, 1990', 'contact' => '09201122334', 'email' => 'ana.reyes@pagsanjan.gov.ph',      'gsis' => '3456789012', 'philhealth' => '34-567890123-4', 'pagibig' => '3456-7890-1234', 'tin' => '345-678-901'],
            ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza',  'position' => 'Municipal Treasurer III',     'dept' => 'Office of the Mun. Treasurer', 'status' => 'Active',    'empType' => 'Permanent', 'dateHired' => 'Aug 3, 2009',  'gender' => 'Male',   'birthday' => 'Feb 28, 1975', 'contact' => '09155544332', 'email' => 'carlos.mendoza@pagsanjan.gov.ph', 'gsis' => '4567890123', 'philhealth' => '45-678901234-5', 'pagibig' => '4567-8901-2345', 'tin' => '456-789-012'],
            ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez',      'position' => 'Social Welfare Officer II',   'dept' => 'MSWD Pagsanjan',             'status' => 'Inactive',  'empType' => 'Permanent', 'dateHired' => 'Nov 20, 2016', 'gender' => 'Female', 'birthday' => 'Dec 10, 1988', 'contact' => '09276677889', 'email' => 'liza.gomez@pagsanjan.gov.ph',     'gsis' => '5678901234', 'philhealth' => '56-789012345-6', 'pagibig' => '5678-9012-3456', 'tin' => '567-890-123'],
            ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores',  'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar',    'status' => 'Active',    'empType' => 'Permanent', 'dateHired' => 'Feb 7, 2020',  'gender' => 'Male',   'birthday' => 'Jun 3, 1993',  'contact' => '09309988776', 'email' => 'roberto.flores@pagsanjan.gov.ph', 'gsis' => '6789012345', 'philhealth' => '67-890123456-7', 'pagibig' => '6789-0123-4567', 'tin' => '678-901-234'],
            ['id' => 'PGS-0342', 'name' => 'Grace A. Villanueva','position' => 'Budget Officer II',           'dept' => 'Office of the Mun. Budget',    'status' => 'Active',    'empType' => 'Casual',    'dateHired' => 'Apr 1, 2022',  'gender' => 'Female', 'birthday' => 'Mar 17, 1995', 'contact' => '09181234321', 'email' => 'grace.villanueva@pagsanjan.gov.ph','gsis' => '7890123456', 'philhealth' => '78-901234567-8', 'pagibig' => '7890-1234-5678', 'tin' => '789-012-345'],
            ['id' => 'PGS-0358', 'name' => 'Ramon D. Cruz',      'position' => 'Agriculturist I',             'dept' => 'Office of the Mun. Agriculturist', 'status' => 'Active', 'empType' => 'Casual',    'dateHired' => 'Jul 15, 2023', 'gender' => 'Male',   'birthday' => 'Aug 9, 1997',  'contact' => '09224433221', 'email' => 'ramon.cruz@pagsanjan.gov.ph',     'gsis' => '8901234567', 'philhealth' => '89-012345678-9', 'pagibig' => '8901-2345-6789', 'tin' => '890-123-456'],
        ]);

        $avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
        if (!function_exists('getInitials')) {
            function getInitials($name) {
                $parts = explode(' ', $name);
                $initials = '';
                foreach ($parts as $part) {
                    if (preg_match('/^[A-Z]/', $part)) { $initials .= $part[0]; }
                }
                return strtoupper(substr($initials, 0, 2));
            }
        }

        $departments = ['All Departments', 'Office of the Mayor', 'Office of the Vice Mayor', 'Sangguniang Bayan', 'Office of the Mun. Treasurer', "Municipal Assessor's Office", 'Municipal Civil Registrar', 'Municipal Health Office', 'MSWD - Pagsanjan', "Municipal Planning & Dev't Office", 'Office of the Mun. Engineer', 'Office of the Mun. Agriculturist', 'Municipal Environment & Natural Resources', "Municipal Business & Dev't Office", 'Human Resource Management Office', 'Municipal Disaster Risk Reduction & Mgmt', 'Office of the Mun. Budget', 'Municipal Circuit Trial Court'];

        $totalActive    = $personnel->where('status', 'Active')->count();
        $totalInactive  = $personnel->where('status', 'Inactive')->count();
        $totalPermanent = $personnel->where('empType', 'Permanent')->count();
        $totalPersonnel = $personnel->count();
        @endphp

        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <h2>Personnel Management</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;Â·&nbsp; Employee Records</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    {{ $totalActive }} Active
                </span>
                <span class="banner-badge outline">FY {{ now()->year }}</span>
            </div>
        </div>

        <div class="stats-grid stats-grid-4" style="margin-bottom: 24px;">
            <div class="stat-card" style="--accent-color: #0b044d">
                <div class="stat-top">
                    <p class="stat-label">Total Personnel</p>
                    <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalPersonnel }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All records</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #15803d">
                <div class="stat-top">
                    <p class="stat-label">Active</p>
                    <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalActive }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#15803d"></span>
                    <p class="stat-sub">Currently active</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #8e1e18">
                <div class="stat-top">
                    <p class="stat-label">Inactive</p>
                    <div class="stat-icon-wrap" style="background: rgba(142, 30, 24, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalInactive }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Deactivated accounts</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #d9bb00">
                <div class="stat-top">
                    <p class="stat-label">Permanent</p>
                    <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalPermanent }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#d9bb00"></span>
                    <p class="stat-sub">Permanent employees</p>
                </div>
            </div>
        </div>

        <div class="table-section" style="margin-bottom: 22px;">
            <div class="table-header">
                <div>
                    <p class="table-title">Employee Records</p>
                    <p class="table-sub">Municipal Government of Pagsanjan Â· <span id="showing-count">{{ $totalPersonnel }}</span> of {{ $totalPersonnel }} records</p>
                </div>
                <div class="table-actions">
                    <div class="search-wrap">
                        <svg width="13" height="13" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Search employees..." class="search-input">
                    </div>
                    <select class="filter-select" id="dept-filter">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="status-filter">
                        <option value="All">All Status</option>
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" id="add-employee-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Employee
                    </button>
                </div>
            </div>
        </div>

        <div class="table-section">
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
                    <tbody id="personnel-table-body">
                        @foreach($personnel as $index => $emp)
                        @php
                        $colorIndex = $index % count($avatarColors);
                        $statusClass = $emp['status'] === 'Active' ? 'processed' : 'on-hold';
                        @endphp
                        <tr data-dept="{{ $emp['dept'] }}" data-status="{{ $emp['status'] }}">
                            <td>
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background: {{ $avatarColors[$colorIndex] }}">{{ getInitials($emp['name']) }}</div>
                                    <div>
                                        <p class="emp-name">{{ $emp['name'] }}</p>
                                        <p class="emp-id">{{ $emp['id'] }}</p>
                                    </div>
                                </div>
                            </td>
                            <td><span class="position-cell">{{ $emp['position'] }}</span></td>
                            <td><span class="dept-tag">{{ $emp['dept'] }}</span></td>
                            <td><span class="badge-emptype">{{ $emp['empType'] }}</span></td>
                            <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $emp['dateHired'] }}</td>
                            <td><span class="badge-status {{ $statusClass }}">{{ $emp['status'] }}</span></td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-view" onclick="viewEmployee('{{ $emp['id'] }}')">View</button>
                                    <button class="btn-edit" onclick="editEmployee('{{ $emp['id'] }}')">Edit</button>
                                    @if($emp['status'] === 'Active')
                                    <button class="btn-deactivate" onclick="showDeleteModal('{{ $emp['id'] }}')">Deactivate</button>
                                    @else
                                    <button class="btn-activate" onclick="toggleStatus('{{ $emp['id'] }}')">Activate</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="empty-state" id="empty-state" style="display: none; text-align: center; padding: 40px 20px;">
                    <p style="font-size: 13px; color: #9999bb; margin: 0;">No employee records found</p>
                </div>
            </div>

            <div class="table-footer">
                <p>Showing <strong id="visible-count">{{ $totalPersonnel }}</strong> of <strong>{{ $totalPersonnel }}</strong> records</p>
                <div class="pagination">
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">â€º</button>
                </div>
            </div>
        </div>

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

<div class="modal-overlay" id="view-modal" style="display: none;">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div class="pmodal-hero" style="display: flex; gap: 16px; align-items: flex-start;">
                <div class="emp-avatar xl" id="modal-avatar" style="width: 56px; height: 56px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; color: #fff;">MS</div>
                <div>
                    <span class="modal-eyebrow" id="modal-emp-id">EMPLOYEE PROFILE Â· PGS-0041</span>
                    <h3 class="modal-title" id="modal-emp-name">Maria B. Santos</h3>
                    <p class="modal-sub" id="modal-emp-position">Administrative Officer IV Â· Office of the Mayor</p>
                    <div class="pmodal-badges" style="display: flex; gap: 8px; margin-top: 8px;">
                        <span class="badge-status" id="modal-status-badge">Active</span>
                        <span class="badge-emptype" id="modal-type-badge">Permanent</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('view-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="pmodal-tabs" style="display: flex; gap: 4px; padding: 0 24px; border-bottom: 1px solid #f0effe;">
            <button class="pmodal-tab active" onclick="setModalTab('info', this)">Personal Info</button>
            <button class="pmodal-tab" onclick="setModalTab('employment', this)">Employment</button>
            <button class="pmodal-tab" onclick="setModalTab('gov-ids', this)">Gov. IDs</button>
            <button class="pmodal-tab" onclick="setModalTab('development', this)">Development</button>
        </div>

        <div class="modal-body pmodal-body" id="modal-tab-content">
        </div>

        <div class="modal-footer">
            <button class="modal-btn-ghost" id="toggle-status-btn" onclick="toggleStatusFromModal()">Deactivate</button>
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="modal-btn-primary" id="edit-btn" onclick="editFromModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                Edit
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="employee-form-modal" style="display: none;">
    <div class="modal-box modal-lg">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="form-eyebrow">ADD EMPLOYEE</span>
                <h3 class="modal-title" id="form-title">New Employee Record</h3>
            </div>
            <button class="modal-close" onclick="closeModal('employee-form-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body pmodal-body" style="max-height: 60vh; overflow-y: auto;">
            <p class="form-section-label" style="font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: 1px; margin-bottom: 12px;">PERSONAL INFORMATION</p>
            <div class="form-grid">
                <div class="form-field form-full">
                    <label>Full Name</label>
                    <input type="text" name="name" id="form-name" placeholder="e.g. Maria B. Santos" required />
                </div>
                <div class="form-field">
                    <label>Gender</label>
                    <select name="gender" id="form-gender">
                        <option value="Female">Female</option>
                        <option value="Male">Male</option>
                    </select>
                </div>
                <div class="form-field">
                    <label>Date of Birth</label>
                    <input type="date" name="birthday" id="form-birthday" />
                </div>
                <div class="form-field">
                    <label>Contact No.</label>
                    <input type="text" name="contact" id="form-contact" placeholder="09XXXXXXXXX" />
                </div>
                <div class="form-field">
                    <label>Email Address</label>
                    <input type="email" name="email" id="form-email" placeholder="name@pagsanjan.gov.ph" />
                </div>
            </div>

            <p class="form-section-label" style="font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: 1px; margin: 20px 0 12px;">EMPLOYMENT DETAILS</p>
            <div class="form-grid">
                <div class="form-field form-full">
                    <label>Position / Designation</label>
                    <input type="text" name="position" id="form-position" placeholder="e.g. Administrative Officer IV" />
                </div>
                <div class="form-field form-full">
                    <label>Department / Office</label>
                    <select name="dept" id="form-dept">
                        @foreach(array_slice($departments, 1) as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-field">
                    <label>Employment Type</label>
                    <select name="empType" id="form-empType">
                        <option value="Permanent">Permanent</option>
                        <option value="Casual">Casual</option>
                        <option value="Contractual">Contractual</option>
                        <option value="Job Order">Job Order</option>
                    </select>
                </div>
            </div>

            <p class="form-section-label" style="font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: 1px; margin: 20px 0 12px;">GOVERNMENT IDs</p>
            <div class="form-grid">
                <div class="form-field"><label>GSIS No.</label><input type="text" name="gsis" id="form-gsis" placeholder="0000000000" /></div>
                <div class="form-field"><label>PhilHealth No.</label><input type="text" name="philhealth" id="form-philhealth" placeholder="00-000000000-0" /></div>
                <div class="form-field"><label>Pag-IBIG No.</label><input type="text" name="pagibig" id="form-pagibig" placeholder="0000-0000-0000" /></div>
                <div class="form-field"><label>TIN</label><input type="text" name="tin" id="form-tin" placeholder="000-000-000" /></div>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('employee-form-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="submitEmployeeForm()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span id="form-submit-text">Add Employee</span>
            </button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="delete-modal" style="display: none;">
    <div class="modal-box" style="max-width: 400px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">CONFIRM ACTION</span>
                <h3 class="modal-title">Deactivate Employee?</h3>
            </div>
            <button class="modal-close" onclick="closeModal('delete-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-confirm-info" style="background: #f7f6ff; border-radius: 10px; padding: 16px; margin-bottom: 12px;">
                <div class="modal-row"><span>Employee</span><strong id="delete-emp-name">-</strong></div>
                <div class="modal-row"><span>ID</span><strong id="delete-emp-id">-</strong></div>
                <div class="modal-row"><span>Department</span><strong id="delete-emp-dept">-</strong></div>
            </div>
            <p style="font-size: 12.5px; color: #8e1e18; background: #fdf0ef; padding: 12px; border-radius: 8px; margin: 0;">âš  This will mark the employee as Inactive. Their records will be retained but access will be disabled.</p>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('delete-modal')">Cancel</button>
            <button class="modal-btn-primary" style="background: #8e1e18;" onclick="confirmDeactivate()">Confirm Deactivate</button>
        </div>
    </div>
</div>

<style>
.search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.search-wrap svg {
    position: absolute;
    left: 10px;
    pointer-events: none;
}
.search-input {
    height: 34px;
    padding: 0 10px 0 30px;
    border: 1.5px solid #e4e3f0;
    border-radius: 8px;
    font-size: 12.5px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: #fafafe;
    outline: none;
    width: 180px;
    transition: border-color 0.2s;
}
.search-input:focus { border-color: #0b044d; }

/* â”€â”€ Personnel-specific modal styles â”€â”€ */
.modal-lg { max-width: 600px; }

.pmodal-tabs {
    display: flex;
    gap: 4px;
    padding: 0 24px;
    border-bottom: 1px solid #f0effe;
    overflow-x: auto;
    white-space: nowrap;
}
.pmodal-tab {
    padding: 12px 16px;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    font-size: 12.5px;
    font-weight: 600;
    color: #9999bb;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
    flex-shrink: 0;
}
.pmodal-tab:hover { color: #0b044d; }
.pmodal-tab.active { color: #0b044d; border-bottom-color: #0b044d; }

.pmodal-body { max-height: 400px; overflow-y: auto; }

.pmodal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.pmodal-field { display: flex; flex-direction: column; gap: 4px; }
.pmodal-field span { font-size: 11px; color: #9999bb; font-weight: 600; }
.pmodal-field strong { font-size: 13px; color: #0b044d; font-weight: 600; }
.pmodal-field.pmodal-full { grid-column: 1 / -1; }

.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-field { display: flex; flex-direction: column; gap: 5px; }
.form-full { grid-column: 1 / -1; }
.form-field label { font-size: 12px; font-weight: 600; color: #0b044d; }
.form-field input,
.form-field select {
    width: 100%;
    padding: 9px 12px;
    border: 1.5px solid #e4e3f0;
    border-radius: 9px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    outline: none;
    box-sizing: border-box;
    background: #fff;
}
.form-field input:focus,
.form-field select:focus { border-color: #0b044d; }

/* â”€â”€ Employee avatar (page-specific sizes) â”€â”€ */
.emp-avatar {
    width: 36px; height: 36px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 12px; font-weight: 700; flex-shrink: 0;
}
.emp-avatar.xl { width: 56px; height: 56px; border-radius: 14px; font-size: 20px; }

/* â”€â”€ Row action buttons â”€â”€ */
.btn-edit {
    padding: 5px 14px; border: 1.5px solid #0b044d; border-radius: 7px;
    background: #0b044d; font-size: 12px; font-weight: 600;
    font-family: 'Poppins', sans-serif; color: #fff; cursor: pointer; transition: all 0.2s;
}
.btn-edit:hover { background: #1a0f6e; border-color: #1a0f6e; }

.btn-deactivate {
    padding: 5px 14px; border: 1.5px solid #f5d0ce; border-radius: 7px;
    background: #fff; font-size: 12px; font-weight: 600;
    font-family: 'Poppins', sans-serif; color: #8e1e18; cursor: pointer; transition: all 0.2s;
}
.btn-deactivate:hover { background: #8e1e18; border-color: #8e1e18; color: #fff; }

.btn-activate {
    padding: 5px 14px; border: 1.5px solid #bbf7d0; border-radius: 7px;
    background: #fff; font-size: 12px; font-weight: 600;
    font-family: 'Poppins', sans-serif; color: #15803d; cursor: pointer; transition: all 0.2s;
}
.btn-activate:hover { background: #15803d; border-color: #15803d; color: #fff; }

.badge-emptype {
    font-size: 11px; font-weight: 700; padding: 3px 10px;
    border-radius: 20px; background: #f7f6ff; color: #0b044d; border: 1px solid #e4e3f0;
}

/* â”€â”€ Ghost button modifiers (danger/success) â”€â”€ */
.modal-btn-ghost.btn-danger { border-color: #8e1e18; color: #8e1e18; }
.modal-btn-ghost.btn-danger:hover { background: #8e1e18; color: #fff; border-color: #8e1e18; }
.modal-btn-ghost.btn-success { border-color: #15803d; color: #15803d; }
.modal-btn-ghost.btn-success:hover { background: #15803d; color: #fff; border-color: #15803d; }

/* â”€â”€ Responsive â”€â”€ */
@media (max-width: 900px) {
    .form-grid, .pmodal-grid { grid-template-columns: 1fr; }
    .form-full, .pmodal-field.pmodal-full { grid-column: 1; }
}

@media (max-width: 768px) {
    .modal-lg { max-width: 100%; }
    .pmodal-tab { padding: 10px 12px; font-size: 12px; }
    .row-actions { flex-direction: column; gap: 4px; }
    .row-actions button { width: 100%; text-align: center; }
}

@media (max-width: 480px) {
    .pmodal-hero { flex-direction: column; gap: 10px; }
    .emp-avatar.xl { width: 44px; height: 44px; font-size: 16px; }
    .pmodal-body { max-height: none; }
}
</style>

<script>
const personnelData = @json($personnel);
const avatarColors = @json($avatarColors);

let currentModalEmp = null;

function getInitials(name) {
    const parts = name.split(' ').filter(n => /^[A-Z]/.test(n));
    return parts.map(p => p[0]).join('').slice(0, 2).toUpperCase();
}

const tabContents = {
    'info': (emp) => `
        <div class="pmodal-grid">
            <div class="pmodal-field"><span>Full Name</span><strong>${emp.name}</strong></div>
            <div class="pmodal-field"><span>Gender</span><strong>${emp.gender}</strong></div>
            <div class="pmodal-field"><span>Date of Birth</span><strong>${emp.birthday}</strong></div>
            <div class="pmodal-field"><span>Contact No.</span><strong>${emp.contact}</strong></div>
            <div class="pmodal-field pmodal-full"><span>Email Address</span><strong>${emp.email}</strong></div>
        </div>
    `,
    'employment': (emp) => `
        <div class="pmodal-grid">
            <div class="pmodal-field"><span>Employee ID</span><strong>${emp.id}</strong></div>
            <div class="pmodal-field"><span>Employment Type</span><strong>${emp.empType}</strong></div>
            <div class="pmodal-field"><span>Date Hired</span><strong>${emp.dateHired}</strong></div>
            <div class="pmodal-field"><span>Status</span><strong>${emp.status}</strong></div>
            <div class="pmodal-field pmodal-full"><span>Position / Designation</span><strong>${emp.position}</strong></div>
            <div class="pmodal-field pmodal-full"><span>Department / Office</span><strong>${emp.dept}</strong></div>
        </div>
    `,
    'gov-ids': (emp) => `
        <div class="pmodal-grid">
            <div class="pmodal-field"><span>GSIS No.</span><strong>${emp.gsis}</strong></div>
            <div class="pmodal-field"><span>PhilHealth No.</span><strong>${emp.philhealth}</strong></div>
            <div class="pmodal-field"><span>Pag-IBIG No.</span><strong>${emp.pagibig}</strong></div>
            <div class="pmodal-field"><span>TIN</span><strong>${emp.tin}</strong></div>
        </div>
    `,
    'development': (emp) => `
        <div>
            <p style="font-size: 10.5px; font-weight: 700; color: #aaa8cc; letter-spacing: 1.2px; margin-bottom: 12px;">PERFORMANCE</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; margin-bottom: 20px;">
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #6b3fa0 0%, #8b5cf6 100%); display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2px;">Latest Rating</p>
                        <p style="font-size: 16px; font-weight: 800; color: #6b3fa0;">4.8 / 5.0</p>
                    </div>
                </div>
                <div style="background: #f7f6ff; border-radius: 10px; padding: 14px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); display: flex; align-items: center; justify-content: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; margin-bottom: 2px;">Evaluation Period</p>
                        <p style="font-size: 13px; font-weight: 700; color: #0b044d;">Janâ€“Jun 2025</p>
                        <span class="badge-status processed" style="font-size: 10px; margin-top: 4px; display: inline-block;">Completed</span>
                    </div>
                </div>
            </div>
            <p style="font-size: 10.5px; font-weight: 700; color: #aaa8cc; letter-spacing: 1.2px; margin-bottom: 10px;">TRAINING HISTORY</p>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="background: #f7f6ff; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%); display: flex; align-items: center; justify-content: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 12.5px; font-weight: 600; color: #0b044d; margin-bottom: 2px;">Leadership Development Program</p>
                        <p style="font-size: 11px; color: #9999bb;">Completed Â· Jun 2025</p>
                    </div>
                    <span class="badge-status processed" style="font-size: 10px;">Done</span>
                </div>
                <div style="background: #f7f6ff; border-radius: 10px; padding: 12px 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 36px; height: 36px; border-radius: 9px; background: linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%); display: flex; align-items: center; justify-content: center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                    <div style="flex: 1;">
                        <p style="font-size: 12.5px; font-weight: 600; color: #0b044d; margin-bottom: 2px;">Customer Service Excellence</p>
                        <p style="font-size: 11px; color: #9999bb;">Completed Â· May 2025</p>
                    </div>
                    <span class="badge-status processed" style="font-size: 10px;">Done</span>
                </div>
            </div>
        </div>
    `
};

function setModalTab(tab, btn) {
    document.querySelectorAll('.pmodal-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('modal-tab-content').innerHTML = tabContents[tab](currentModalEmp);
}

function viewEmployee(empId) {
    const emp = personnelData.find(e => e.id === empId);
    if (!emp) return;
    
    currentModalEmp = emp;
    const idx = personnelData.findIndex(e => e.id === empId);
    const color = avatarColors[idx % avatarColors.length];
    
    document.getElementById('modal-avatar').style.background = color;
    document.getElementById('modal-avatar').textContent = getInitials(emp.name);
    document.getElementById('modal-emp-id').textContent = 'EMPLOYEE PROFILE Â· ' + emp.id;
    document.getElementById('modal-emp-name').textContent = emp.name;
    document.getElementById('modal-emp-position').textContent = emp.position + ' Â· ' + emp.dept;
    
    const statusBadge = document.getElementById('modal-status-badge');
    statusBadge.textContent = emp.status;
    statusBadge.className = 'badge-status ' + (emp.status === 'Active' ? 'processed' : 'on-hold');
    
    document.getElementById('modal-type-badge').textContent = emp.empType;
    
    document.getElementById('toggle-status-btn').textContent = emp.status === 'Active' ? 'Deactivate' : 'Activate';
    document.getElementById('toggle-status-btn').className = 'modal-btn-ghost ' + (emp.status === 'Active' ? 'btn-danger' : 'btn-success');
    
    document.querySelector('.pmodal-tab').click();
    
    document.getElementById('view-modal').style.display = 'flex';
}

function editEmployee(empId) {
    const emp = personnelData.find(e => e.id === empId);
    if (!emp) return;
    
    document.getElementById('form-eyebrow').textContent = 'EDIT EMPLOYEE';
    document.getElementById('form-title').textContent = 'Edit: ' + emp.name;
    document.getElementById('form-name').value = emp.name;
    document.getElementById('form-gender').value = emp.gender;
    document.getElementById('form-birthday').value = '';
    document.getElementById('form-contact').value = emp.contact;
    document.getElementById('form-email').value = emp.email;
    document.getElementById('form-position').value = emp.position;
    document.getElementById('form-dept').value = emp.dept;
    document.getElementById('form-empType').value = emp.empType;
    document.getElementById('form-gsis').value = emp.gsis;
    document.getElementById('form-philhealth').value = emp.philhealth;
    document.getElementById('form-pagibig').value = emp.pagibig;
    document.getElementById('form-tin').value = emp.tin;
    document.getElementById('form-submit-text').textContent = 'Save Changes';
    
    document.getElementById('employee-form-modal').style.display = 'flex';
}

function editFromModal() {
    if (currentModalEmp) {
        closeModal('view-modal');
        editEmployee(currentModalEmp.id);
    }
}

function toggleStatusFromModal() {
    if (currentModalEmp) {
        toggleStatus(currentModalEmp.id);
        closeModal('view-modal');
    }
}

function showDeleteModal(empId) {
    const emp = personnelData.find(e => e.id === empId);
    if (!emp) return;
    
    document.getElementById('delete-emp-name').textContent = emp.name;
    document.getElementById('delete-emp-id').textContent = emp.id;
    document.getElementById('delete-emp-dept').textContent = emp.dept;
    document.getElementById('delete-modal').style.display = 'flex';
}

function confirmDeactivate() {
    const empName = document.getElementById('delete-emp-id').textContent;
    if (empName) {
        toggleStatus(empName);
    }
    closeModal('delete-modal');
    filterPersonnel();
}

function toggleStatus(empId) {
    const row = document.querySelector(`tr[data-status][td:first-child div div:last-child p:first-child]`);
    const rows = document.querySelectorAll('#personnel-table-body tr');
    rows.forEach(r => {
        const empName = r.querySelector('.emp-name').textContent;
        const emp = personnelData.find(e => e.name === empName);
        if (emp && emp.id === empId) {
            emp.status = emp.status === 'Active' ? 'Inactive' : 'Active';
            const statusCell = r.querySelector('td:nth-child(6) .badge-status');
            statusCell.textContent = emp.status;
            statusCell.className = 'badge-status ' + (emp.status === 'Active' ? 'processed' : 'on-hold');
            
            const actionsCell = r.querySelector('td:last-child .row-actions');
            if (emp.status === 'Active') {
                actionsCell.innerHTML = `
                    <button class="btn-view" onclick="viewEmployee('${emp.id}')">View</button>
                    <button class="btn-edit" onclick="editEmployee('${emp.id}')">Edit</button>
                    <button class="btn-deactivate" onclick="showDeleteModal('${emp.id}')">Deactivate</button>
                `;
            } else {
                actionsCell.innerHTML = `
                    <button class="btn-view" onclick="viewEmployee('${emp.id}')">View</button>
                    <button class="btn-edit" onclick="editEmployee('${emp.id}')">Edit</button>
                    <button class="btn-activate" onclick="toggleStatus('${emp.id}')">Activate</button>
                `;
            }
        }
    });
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function submitEmployeeForm() {
    const name = document.getElementById('form-name').value;
    const position = document.getElementById('form-position').value;
    
    if (name && position) {
        alert('Employee saved successfully!');
        closeModal('employee-form-modal');
    }
}

document.getElementById('add-employee-btn').addEventListener('click', function() {
    document.getElementById('form-eyebrow').textContent = 'ADD EMPLOYEE';
    document.getElementById('form-title').textContent = 'New Employee Record';
    document.getElementById('form-name').value = '';
    document.getElementById('form-gender').value = 'Female';
    document.getElementById('form-birthday').value = '';
    document.getElementById('form-contact').value = '';
    document.getElementById('form-email').value = '';
    document.getElementById('form-position').value = '';
    document.getElementById('form-dept').value = 'Office of the Mayor';
    document.getElementById('form-empType').value = 'Permanent';
    document.getElementById('form-gsis').value = '';
    document.getElementById('form-philhealth').value = '';
    document.getElementById('form-pagibig').value = '';
    document.getElementById('form-tin').value = '';
    document.getElementById('form-submit-text').textContent = 'Add Employee';
    document.getElementById('employee-form-modal').style.display = 'flex';
});

document.getElementById('dept-filter').addEventListener('change', filterPersonnel);
document.getElementById('status-filter').addEventListener('change', filterPersonnel);
document.getElementById('search-input').addEventListener('input', filterPersonnel);

function filterPersonnel() {
    const deptFilter = document.getElementById('dept-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const rows = document.querySelectorAll('#personnel-table-body tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const dept = row.dataset.dept;
        const status = row.dataset.status;
        const name = row.querySelector('.emp-name').textContent.toLowerCase();
        const position = row.querySelector('.position-cell').textContent.toLowerCase();
        const empId = row.querySelector('.emp-id').textContent.toLowerCase();
        
        const matchDept = deptFilter === 'All Departments' || dept === deptFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || name.includes(searchQuery) || position.includes(searchQuery) || empId.includes(searchQuery);
        
        const isVisible = matchDept && matchStatus && matchSearch;
        row.style.display = isVisible ? 'table-row' : 'none';
        if (isVisible) visibleCount++;
    });
    
    document.getElementById('showing-count').textContent = visibleCount;
    document.getElementById('visible-count').textContent = visibleCount;
    document.getElementById('empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('view-modal').style.display = 'none';
        document.getElementById('employee-form-modal').style.display = 'none';
        document.getElementById('delete-modal').style.display = 'none';
    }
});

</script>
@endsection
