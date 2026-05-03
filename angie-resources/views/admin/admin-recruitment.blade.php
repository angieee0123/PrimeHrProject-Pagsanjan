@extends('layouts.app')

@section('title', 'Recruitment · PRIME HRIS')

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
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <div>
                    <h2>Recruitment Management</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Job Postings & Applications</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    HR Active
                </span>
                <span class="banner-badge outline">FY {{ now()->year }}</span>
            </div>
        </div>

        @php
        $jobPostings = [
            ['id' => 'JOB-001', 'title' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'type' => 'Permanent', 'slots' => 1, 'applicants' => 12, 'status' => 'Open', 'posted' => 'Jun 1, 2025', 'deadline' => 'Jun 30, 2025'],
            ['id' => 'JOB-002', 'title' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'type' => 'Permanent', 'slots' => 1, 'applicants' => 8, 'status' => 'Open', 'posted' => 'Jun 5, 2025', 'deadline' => 'Jul 5, 2025'],
            ['id' => 'JOB-003', 'title' => 'Nurse II', 'dept' => 'Municipal Health Office', 'type' => 'Permanent', 'slots' => 2, 'applicants' => 24, 'status' => 'Closed', 'posted' => 'May 15, 2025', 'deadline' => 'Jun 15, 2025'],
            ['id' => 'JOB-004', 'title' => 'Social Welfare Officer', 'dept' => 'MSWD – Pagsanjan', 'type' => 'Casual', 'slots' => 1, 'applicants' => 15, 'status' => 'Open', 'posted' => 'Jun 10, 2025', 'deadline' => 'Jul 10, 2025'],
        ];

        $totalJobs = count($jobPostings);
        $openPositions = count(array_filter($jobPostings, fn($j) => $j['status'] === 'Open'));
        $totalApplicants = array_sum(array_column($jobPostings, 'applicants'));
        $totalSlots = array_sum(array_column($jobPostings, 'slots'));

        $departments = ['All Departments', 'Office of the Mayor', 'Office of the Mun. Engineer', 'Municipal Health Office', 'MSWD – Pagsanjan', 'Office of the Mun. Treasurer'];
        @endphp

        <div class="stats-grid stats-grid-4" style="margin-bottom: 24px;">
            <div class="stat-card" style="--accent-color: #0b044d">
                <div class="stat-top">
                    <p class="stat-label">Total Job Postings</p>
                    <div class="stat-icon-wrap" style="background: rgba(11, 4, 77, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalJobs }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All positions</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #15803d">
                <div class="stat-top">
                    <p class="stat-label">Open Positions</p>
                    <div class="stat-icon-wrap" style="background: rgba(21, 128, 61, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $openPositions }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#15803d"></span>
                    <p class="stat-sub">Currently accepting</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #d9bb00">
                <div class="stat-top">
                    <p class="stat-label">Total Applicants</p>
                    <div class="stat-icon-wrap" style="background: rgba(217, 187, 0, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalApplicants }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#d9bb00"></span>
                    <p class="stat-sub">All applications</p>
                </div>
            </div>

            <div class="stat-card" style="--accent-color: #8e1e18">
                <div class="stat-top">
                    <p class="stat-label">Available Slots</p>
                    <div class="stat-icon-wrap" style="background: rgba(142, 30, 24, 0.1)">
                        <svg width="18" height="18" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $totalSlots }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Positions to fill</p>
                </div>
            </div>
        </div>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Job Postings</p>
                    <p class="table-sub">Municipal Government of Pagsanjan · {{ $totalJobs }} postings</p>
                </div>
                <div class="table-actions">
                    <div class="search-wrap">
                        <svg width="13" height="13" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" id="search-input" placeholder="Search jobs..." class="search-input">
                    </div>
                    <button class="view-mode-btn active" id="grid-view-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                        Grid
                    </button>
                    <button class="view-mode-btn" id="list-view-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        List
                    </button>
                    <select class="filter-select" id="dept-filter">
                        @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                        @endforeach
                    </select>
                    <select class="filter-select" id="status-filter">
                        <option value="All">All Status</option>
                        <option value="Open">Open</option>
                        <option value="Closed">Closed</option>
                    </select>
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" id="post-job-btn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Post Job
                    </button>
                </div>
            </div>
        </div>

        <div id="grid-view">
            <div class="job-grid" id="job-grid-container">
                @foreach($jobPostings as $job)
                <div class="job-card" data-id="{{ $job['id'] }}" data-dept="{{ $job['dept'] }}" data-status="{{ $job['status'] }}">
                    <div class="job-card-header">
                        <span class="badge-status {{ $job['status'] === 'Open' ? 'processed' : 'on-hold' }}">{{ $job['status'] }}</span>
                    </div>
                    <div class="job-card-body">
                        <div class="job-card-icon">
                            <div class="job-slot-badge">{{ $job['slots'] }}</div>
                        </div>
                        <div class="job-card-info">
                            <p class="job-id">{{ $job['id'] }}</p>
                            <h4 class="job-title">{{ $job['title'] }}</h4>
                        </div>
                    </div>
                    <p class="job-dept">{{ $job['dept'] }}</p>
                    <div class="job-badges">
                        <span class="badge-emptype">{{ $job['type'] }}</span>
                        <span class="badge-applicants">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                            {{ $job['applicants'] }} applicants
                        </span>
                    </div>
                    <div class="job-card-footer">
                        <div class="job-deadline">
                            <p class="job-deadline-label">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                Deadline
                            </p>
                            <p class="job-deadline-date">{{ $job['deadline'] }}</p>
                        </div>
                        <div class="job-actions">
                            <button class="btn-view" onclick="viewJob('{{ $job['id'] }}')">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </button>
                            <button class="btn-edit" onclick="editJob('{{ $job['id'] }}')">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="empty-state" id="grid-empty-state" style="display: none; text-align: center; padding: 60px 20px;">
                <div class="empty-icon" style="width: 80px; height: 80px; margin: 0 auto 20px; background: linear-gradient(135deg, #f7f6ff 0%, #eceaf8 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <svg width="32" height="32" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                </div>
                <h3 style="font-size: 16px; font-weight: 700; color: #0b044d; margin: 0 0 8px;">No Job Postings Found</h3>
                <p style="font-size: 13px; color: #9999bb; margin: 0;">Try adjusting your filters or search criteria</p>
            </div>
        </div>

        <div id="list-view" style="display: none;">
            <div class="table-section">

                <div class="table-wrapper">
                    <table class="payroll-table">
                        <thead>
                            <tr>
                                <th>Job ID</th>
                                <th>Position</th>
                                <th>Department</th>
                                <th>Type</th>
                                <th>Slots</th>
                                <th>Applicants</th>
                                <th>Deadline</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="list-table-body">
                            @foreach($jobPostings as $job)
                            <tr data-dept="{{ $job['dept'] }}" data-status="{{ $job['status'] }}">
                                <td style="font-size: 12.5px; color: #6b6a8a; font-weight: 500;">{{ $job['id'] }}</td>
                                <td><span class="position-cell">{{ $job['title'] }}</span></td>
                                <td><span class="dept-tag">{{ $job['dept'] }}</span></td>
                                <td><span class="badge-emptype">{{ $job['type'] }}</span></td>
                                <td style="font-size: 13px; color: #6b6a8a; text-align: center;">{{ $job['slots'] }}</td>
                                <td style="font-size: 13px; color: #0b044d; font-weight: 600; text-align: center;">{{ $job['applicants'] }}</td>
                                <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $job['deadline'] }}</td>
                                <td><span class="badge-status {{ $job['status'] === 'Open' ? 'processed' : 'on-hold' }}">{{ $job['status'] }}</span></td>
                                <td>
                                    <div class="row-actions">
                                        <button class="btn-view" onclick="viewJob('{{ $job['id'] }}')">View</button>
                                        <button class="btn-edit" onclick="editJob('{{ $job['id'] }}')">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="empty-state" id="list-empty-state" style="display: none; text-align: center; padding: 40px 20px;">
                        <p style="font-size: 13px; color: #9999bb; margin: 0;">No job postings match your criteria</p>
                    </div>
                </div>

                <div class="table-footer">
                    <p>Showing <strong id="showing-count">{{ $totalJobs }}</strong> of <strong>{{ $totalJobs }}</strong> postings</p>
                    <div class="pagination">
                        <button class="page-btn active">1</button>
                        <button class="page-btn">›</button>
                    </div>
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
            <div>
                <span class="modal-eyebrow" id="modal-job-id">JOB POSTING · JOB-001</span>
                <h3 class="modal-title" id="modal-job-title">Administrative Officer IV</h3>
                <p class="modal-sub" id="modal-job-dept">Office of the Mayor</p>
            </div>
            <button class="modal-close" onclick="closeModal('view-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-row"><span>Position</span><strong id="modal-position">Administrative Officer IV</strong></div>
            <div class="modal-row"><span>Department</span><strong id="modal-department">Office of the Mayor</strong></div>
            <div class="modal-row"><span>Employment Type</span><strong id="modal-type">Permanent</strong></div>
            <div class="modal-row"><span>Available Slots</span><strong id="modal-slots">1</strong></div>
            <div class="modal-row"><span>Total Applicants</span><strong id="modal-applicants">12</strong></div>
            <div class="modal-row"><span>Posted Date</span><strong id="modal-posted">Jun 1, 2025</strong></div>
            <div class="modal-row"><span>Deadline</span><strong id="modal-deadline">Jun 30, 2025</strong></div>
            <div class="modal-row"><span>Status</span><span class="badge-status processed" id="modal-status">Open</span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="modal-btn-primary" style="padding: 9px 18px; border-radius: 9px; border: none; background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); box-shadow: 0 2px 8px rgba(21, 128, 61, 0.3);">View Applicants</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="job-form-modal" style="display: none;">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="form-eyebrow">NEW JOB POSTING</span>
                <h3 class="modal-title" id="form-title">Create Job Posting</h3>
            </div>
            <button class="modal-close" onclick="closeModal('job-form-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="job-form" method="POST" action="{{ route('recruitment.store') }}">
                @csrf
                <div class="form-grid">
                    <div class="form-field form-full">
                        <label>Position Title</label>
                        <input type="text" name="title" id="form-title-input" placeholder="e.g. Administrative Officer IV" required />
                    </div>
                    <div class="form-field form-full">
                        <label>Department</label>
                        <select name="department" id="form-dept" required>
                            @foreach(array_slice($departments, 1) as $dept)
                            <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Employment Type</label>
                        <select name="type" id="form-type">
                            <option value="Permanent">Permanent</option>
                            <option value="Casual">Casual</option>
                            <option value="Contractual">Contractual</option>
                            <option value="Job Order">Job Order</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label>Available Slots</label>
                        <input type="number" name="slots" id="form-slots" value="1" min="1" />
                    </div>
                    <div class="form-field">
                        <label>Application Deadline</label>
                        <input type="date" name="deadline" id="form-deadline" required />
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('job-form-modal')">Cancel</button>
            <button class="modal-btn-primary" onclick="submitJobForm()">Post Job</button>
        </div>
    </div>
</div>

<style>
.view-mode-btn {
    padding: 8px 16px;
    border-radius: 8px;
    border: 1.5px solid #e4e3f0;
    background: #fff;
    color: #6b6a8a;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.view-mode-btn:hover {
    border-color: #0b044d;
}

.view-mode-btn.active {
    border: 2px solid #0b044d;
    background: #0b044d;
    color: #fff;
}

.job-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 22px;
}

.job-card {
    background: #fff;
    border: 1.5px solid #eceaf8;
    border-radius: 14px;
    padding: 20px;
    cursor: pointer;
    transition: all 0.25s ease;
    position: relative;
    overflow: hidden;
}

.job-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #0b044d, #15803d);
    opacity: 0;
    transition: opacity 0.25s ease;
}

.job-card:hover {
    border-color: #0b044d;
    box-shadow: 0 8px 24px rgba(11, 4, 77, 0.12);
    transform: translateY(-2px);
}

.job-card:hover::before {
    opacity: 1;
}

.job-card-header {
    position: absolute;
    top: 16px;
    right: 16px;
}

.job-card-body {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.job-card-icon {
    display: flex;
    align-items: center;
}

.job-slot-badge {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: linear-gradient(135deg, #15803d 0%, #22c55e 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
}

.job-card-info {
    flex: 1;
}

.job-id {
    font-size: 10px;
    color: #9999bb;
    font-weight: 600;
    margin-bottom: 2px;
}

.job-title {
    font-size: 14px;
    font-weight: 700;
    color: #0b044d;
    margin: 0;
    line-height: 1.3;
}

.job-dept {
    font-size: 12px;
    color: #6b6a8a;
    margin-bottom: 12px;
}

.job-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.badge-applicants {
    font-size: 11px;
    color: #9999bb;
    background: #f7f6ff;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
}

.job-card-footer {
    border-top: 1px solid #f0effe;
    padding-top: 12px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.job-deadline-label {
    font-size: 10px;
    color: #9999bb;
    margin-bottom: 2px;
}

.job-deadline-date {
    font-size: 12px;
    color: #0b044d;
    font-weight: 600;
}

.job-actions {
    display: flex;
    gap: 6px;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(11, 4, 77, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-box {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 480px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    animation: slideUp 0.3s ease;
}

.modal-lg {
    max-width: 560px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px 24px 0;
}

.modal-eyebrow {
    font-size: 10.5px;
    color: #9999bb;
    font-weight: 700;
    letter-spacing: 1px;
}

.modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #0b044d;
    margin: 4px 0 2px;
}

.modal-sub {
    font-size: 13px;
    color: #6b6a8a;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    color: #9999bb;
    transition: color 0.2s;
}

.modal-close:hover {
    color: #0b044d;
}

.modal-body {
    padding: 20px 24px;
}

.modal-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0effe;
}

.modal-row span {
    font-size: 13px;
    color: #9999bb;
    font-weight: 600;
}

.modal-row strong {
    font-size: 13px;
    color: #0b044d;
    font-weight: 600;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 16px 24px 24px;
}

.modal-btn-ghost {
    padding: 9px 18px;
    border-radius: 9px;
    border: 1.5px solid #dddcf0;
    background: #fff;
    font-size: 13px;
    font-weight: 600;
    color: #6b6a8a;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}

.modal-btn-ghost:hover {
    border-color: #0b044d;
    color: #0b044d;
}

.modal-btn-primary {
    padding: 9px 18px;
    border-radius: 9px;
    border: none;
    background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(11, 4, 77, 0.3);
}

.modal-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(11, 4, 77, 0.4);
}

.modal-btn-ghost:hover {
    border-color: #0b044d;
    color: #0b044d;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-full {
    grid-column: 1 / -1;
}

.form-field label {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
}

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
.form-field select:focus {
    border-color: #0b044d;
}

.badge-emptype {
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    background: #f7f6ff;
    color: #0b044d;
    border: 1px solid #e4e3f0;
}

.badge-applicants {
    font-size: 11px;
    color: #9999bb;
    background: #f7f6ff;
    padding: 3px 10px;
    border-radius: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.badge-status {
    font-size: 10px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    display: inline-block;
}

.badge-status.processed {
    background: #e8f9ef;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.badge-status.on-hold {
    background: #fdf0ef;
    color: #8e1e18;
    border: 1px solid #f5d0ce;
}

.row-actions {
    display: flex;
    gap: 6px;
}

.btn-view, .btn-edit {
    padding: 6px 12px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 4px;
    transition: all 0.2s;
}

.btn-view {
    background: #fff;
    border: 1px solid #e4e3f0;
    color: #6b6a8a;
}

.btn-view:hover {
    border-color: #0b044d;
    color: #0b044d;
}

.btn-edit {
    background: #0b044d;
    border: 1px solid #0b044d;
    color: #fff;
}

.btn-edit:hover {
    background: #1a0f6e;
    border-color: #1a0f6e;
}

.btn-export:hover {
    border-color: #0b044d;
    color: #0b044d;
}



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
.search-input:focus {
    border-color: #0b044d;
}

@media (max-width: 900px) {
    .job-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    }
    .form-grid {
        grid-template-columns: 1fr;
    }
    .form-full {
        grid-column: 1;
    }
}

@media (max-width: 480px) {
    .job-grid {
        grid-template-columns: 1fr;
    }
    .job-card {
        padding: 16px;
    }
    .job-card-footer {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    .job-actions button {
        flex: 1;
        justify-content: center;
    }
    .modal-overlay {
        padding: 12px;
        align-items: flex-end;
    }
    .modal-box {
        border-radius: 16px 16px 0 0;
        max-height: 90vh;
        overflow-y: auto;
    }
    .modal-footer button {
        flex: 1;
        justify-content: center;
    }
}
</style>

<script>
const jobData = @json($jobPostings);

document.getElementById('grid-view-btn').addEventListener('click', function() {
    document.getElementById('grid-view').style.display = 'block';
    document.getElementById('list-view').style.display = 'none';
    this.classList.add('active');
    document.getElementById('list-view-btn').classList.remove('active');
});

document.getElementById('list-view-btn').addEventListener('click', function() {
    document.getElementById('grid-view').style.display = 'none';
    document.getElementById('list-view').style.display = 'block';
    this.classList.add('active');
    document.getElementById('grid-view-btn').classList.remove('active');
});

document.getElementById('post-job-btn').addEventListener('click', function() {
    document.getElementById('form-eyebrow').textContent = 'NEW JOB POSTING';
    document.getElementById('form-title').textContent = 'Create Job Posting';
    document.getElementById('job-form-modal').style.display = 'flex';
    document.getElementById('form-title-input').value = '';
    document.getElementById('form-slots').value = '1';
    document.getElementById('form-deadline').value = '';
});

function viewJob(jobId) {
    const job = jobData.find(j => j.id === jobId);
    if (!job) return;
    
    document.getElementById('modal-job-id').textContent = 'JOB POSTING · ' + job.id;
    document.getElementById('modal-job-title').textContent = job.title;
    document.getElementById('modal-job-dept').textContent = job.dept;
    document.getElementById('modal-position').textContent = job.title;
    document.getElementById('modal-department').textContent = job.dept;
    document.getElementById('modal-type').textContent = job.type;
    document.getElementById('modal-slots').textContent = job.slots;
    document.getElementById('modal-applicants').textContent = job.applicants;
    document.getElementById('modal-posted').textContent = job.posted;
    document.getElementById('modal-deadline').textContent = job.deadline;
    document.getElementById('modal-status').textContent = job.status;
    
    const statusBadge = document.getElementById('modal-status');
    statusBadge.className = 'badge-status ' + (job.status === 'Open' ? 'processed' : 'on-hold');
    
    document.getElementById('view-modal').style.display = 'flex';
}

function editJob(jobId) {
    const job = jobData.find(j => j.id === jobId);
    if (!job) return;
    
    document.getElementById('form-eyebrow').textContent = 'EDIT JOB POSTING';
    document.getElementById('form-title').textContent = 'Edit — ' + job.title;
    document.getElementById('form-title-input').value = job.title;
    document.getElementById('form-dept').value = job.dept;
    document.getElementById('form-type').value = job.type;
    document.getElementById('form-slots').value = job.slots;
    document.getElementById('form-deadline').value = job.deadline.split(' ')[0].replace('Jun','2025-06').replace('Jul','2025-07').replace('May','2025-05');
    document.getElementById('job-form-modal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function submitJobForm() {
    const title = document.getElementById('form-title-input').value;
    const deadline = document.getElementById('form-deadline').value;
    
    if (title && deadline) {
        document.getElementById('job-form').submit();
    }
}

document.getElementById('dept-filter').addEventListener('change', filterJobs);
document.getElementById('status-filter').addEventListener('change', filterJobs);
document.getElementById('search-input').addEventListener('input', filterJobs);

function filterJobs() {
    const deptFilter = document.getElementById('dept-filter').value;
    const statusFilter = document.getElementById('status-filter').value;
    const searchQuery = document.getElementById('search-input').value.toLowerCase();
    
    const gridCards = document.querySelectorAll('.job-card');
    const listRows = document.querySelectorAll('#list-view tbody tr');
    let visibleCount = 0;
    
    gridCards.forEach(card => {
        const dept = card.dataset.dept;
        const status = card.dataset.status;
        const title = card.querySelector('.job-title').textContent.toLowerCase();
        const jobId = card.dataset.id.toLowerCase();
        const matchDept = deptFilter === 'All Departments' || dept === deptFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || title.includes(searchQuery) || jobId.includes(searchQuery);
        const isVisible = matchDept && matchStatus && matchSearch;
        card.style.display = isVisible ? 'block' : 'none';
        if (isVisible) visibleCount++;
    });
    
    listRows.forEach(row => {
        const dept = row.dataset.dept;
        const status = row.dataset.status;
        const title = row.querySelector('.position-cell').textContent.toLowerCase();
        const jobId = row.querySelector('td').textContent.toLowerCase();
        const matchDept = deptFilter === 'All Departments' || dept === deptFilter;
        const matchStatus = statusFilter === 'All' || status === statusFilter;
        const matchSearch = !searchQuery || title.includes(searchQuery) || jobId.includes(searchQuery);
        const isVisible = matchDept && matchStatus && matchSearch;
        row.style.display = isVisible ? 'table-row' : 'none';
    });
    
    document.getElementById('list-count').textContent = visibleCount;
    document.getElementById('showing-count').textContent = visibleCount;
    
    document.getElementById('grid-empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
    document.getElementById('list-empty-state').style.display = visibleCount === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('view-modal').style.display = 'none';
        document.getElementById('job-form-modal').style.display = 'none';
    }
});

</script>
@endsection