@extends('layouts.app')

@push('styles')
@vite('resources/css/adminRecruitment.css')
@endpush

@section('content')

@php
$jobPostings = [
    ['id' => 'JOB-001', 'title' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'type' => 'Permanent', 'slots' => 1, 'applicants' => 12, 'status' => 'Open', 'posted' => 'Jun 1, 2025', 'deadline' => 'Jun 30, 2025'],
    ['id' => 'JOB-002', 'title' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'type' => 'Permanent', 'slots' => 1, 'applicants' => 8, 'status' => 'Open', 'posted' => 'Jun 5, 2025', 'deadline' => 'Jul 5, 2025'],
    ['id' => 'JOB-003', 'title' => 'Nurse II', 'dept' => 'Municipal Health Office', 'type' => 'Permanent', 'slots' => 2, 'applicants' => 24, 'status' => 'Closed', 'posted' => 'May 15, 2025', 'deadline' => 'Jun 15, 2025'],
    ['id' => 'JOB-004', 'title' => 'Social Welfare Officer', 'dept' => 'MSWD – Pagsanjan', 'type' => 'Casual', 'slots' => 1, 'applicants' => 15, 'status' => 'Open', 'posted' => 'Jun 10, 2025', 'deadline' => 'Jul 10, 2025'],
];
$totalJobs      = count($jobPostings);
$openPositions  = count(array_filter($jobPostings, fn($j) => $j['status'] === 'Open'));
$totalApplicants = array_sum(array_column($jobPostings, 'applicants'));
$totalSlots     = array_sum(array_column($jobPostings, 'slots'));
$departments    = ['All Departments', 'Office of the Mayor', 'Office of the Mun. Engineer', 'Municipal Health Office', 'MSWD – Pagsanjan', 'Office of the Mun. Treasurer'];
@endphp

@include('admin.topbar.recruitmentTopbar')
@include('admin.notification.adminNotification')

{{-- Stats --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Job Postings</p>
            <div class="stat-icon-wrap rc-icon-primary">
                <svg width="18" height="18" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalJobs }}</h2>
        <div class="stat-footer"><span class="stat-dot rc-dot-primary"></span><p class="stat-sub">All positions</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Open Positions</p>
            <div class="stat-icon-wrap rc-icon-success">
                <svg width="18" height="18" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $openPositions }}</h2>
        <div class="stat-footer"><span class="stat-dot rc-dot-success"></span><p class="stat-sub">Currently accepting</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Applicants</p>
            <div class="stat-icon-wrap rc-icon-warning">
                <svg width="18" height="18" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalApplicants }}</h2>
        <div class="stat-footer"><span class="stat-dot rc-dot-warning"></span><p class="stat-sub">All applications</p></div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Available Slots</p>
            <div class="stat-icon-wrap rc-icon-danger">
                <svg width="18" height="18" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalSlots }}</h2>
        <div class="stat-footer"><span class="stat-dot rc-dot-danger"></span><p class="stat-sub">Positions to fill</p></div>
    </div>
</div>

{{-- Table Section Header (search + view toggle + filters) --}}
<div class="table-section">
    <div class="table-header">
        <div>
            <p class="table-title">Job Postings</p>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $totalJobs }} postings</p>
        </div>
        <div class="table-actions">
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

{{-- Grid View --}}
<div id="grid-view">
    <div class="job-grid" id="job-grid-container">
        @foreach($jobPostings as $job)
        <div class="job-card" data-id="{{ $job['id'] }}" data-dept="{{ $job['dept'] }}" data-status="{{ $job['status'] }}">
            <div class="job-card-header">
                <span class="badge-status {{ $job['status'] === 'Open' ? 'processed' : 'on-hold' }}">{{ $job['status'] }}</span>
            </div>
            <div class="job-card-body">
                <div class="job-slot-badge">{{ $job['slots'] }}</div>
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
    <div class="empty-state" id="grid-empty-state" style="display:none">
        <div class="empty-icon">
            <svg width="32" height="32" fill="none" stroke="#9999bb" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
        </div>
        <h3 class="empty-title">No Job Postings Found</h3>
        <p class="empty-sub">Try adjusting your filters or search criteria</p>
    </div>
</div>

{{-- List View --}}
<div id="list-view" style="display:none">
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
                        <td class="job-id-cell">{{ $job['id'] }}</td>
                        <td><span class="position-cell">{{ $job['title'] }}</span></td>
                        <td><span class="dept-tag">{{ $job['dept'] }}</span></td>
                        <td><span class="badge-emptype">{{ $job['type'] }}</span></td>
                        <td class="slots-cell">{{ $job['slots'] }}</td>
                        <td class="applicants-cell">{{ $job['applicants'] }}</td>
                        <td class="deadline-cell">{{ $job['deadline'] }}</td>
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
            <div class="empty-state" id="list-empty-state" style="display:none">
                <p class="empty-sub">No job postings match your criteria</p>
            </div>
        </div>
        <div class="table-footer">
            <span>Showing <strong id="showing-count">{{ $totalJobs }}</strong> of <strong>{{ $totalJobs }}</strong> postings</span>
            <div class="pagination">
                <button class="page-btn active">1</button>
                <button class="page-btn">›</button>
            </div>
        </div>
    </div>
</div>

{{-- View Job Modal --}}
<div class="modal-overlay" id="view-modal" style="display:none">
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
            <div class="modal-row"><span>Position</span><strong id="modal-position"></strong></div>
            <div class="modal-row"><span>Department</span><strong id="modal-department"></strong></div>
            <div class="modal-row"><span>Employment Type</span><strong id="modal-type"></strong></div>
            <div class="modal-row"><span>Available Slots</span><strong id="modal-slots"></strong></div>
            <div class="modal-row"><span>Total Applicants</span><strong id="modal-applicants"></strong></div>
            <div class="modal-row"><span>Posted Date</span><strong id="modal-posted"></strong></div>
            <div class="modal-row"><span>Deadline</span><strong id="modal-deadline"></strong></div>
            <div class="modal-row"><span>Status</span><span class="badge-status processed" id="modal-status"></span></div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('view-modal')">Close</button>
            <button class="modal-btn-primary modal-btn-success">View Applicants</button>
        </div>
    </div>
</div>

{{-- Post / Edit Job Modal --}}
<div class="modal-overlay" id="job-form-modal" style="display:none">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="form-eyebrow">NEW JOB POSTING</span>
                <h3 class="modal-title" id="form-modal-title">Create Job Posting</h3>
            </div>
            <button class="modal-close" onclick="closeModal('job-form-modal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <form id="job-form">
                @csrf
                <div class="form-grid">
                    <div class="form-field form-full">
                        <label>Position Title</label>
                        <input type="text" name="title" id="form-title-input" placeholder="e.g. Administrative Officer IV" required>
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
                        <input type="number" name="slots" id="form-slots" value="1" min="1">
                    </div>
                    <div class="form-field">
                        <label>Application Deadline</label>
                        <input type="date" name="deadline" id="form-deadline" required>
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

<script>
const jobData = @json($jobPostings);

// View toggle
document.getElementById('grid-view-btn').addEventListener('click', function () {
    document.getElementById('grid-view').style.display = 'block';
    document.getElementById('list-view').style.display = 'none';
    this.classList.add('active');
    document.getElementById('list-view-btn').classList.remove('active');
});
document.getElementById('list-view-btn').addEventListener('click', function () {
    document.getElementById('grid-view').style.display = 'none';
    document.getElementById('list-view').style.display = 'block';
    this.classList.add('active');
    document.getElementById('grid-view-btn').classList.remove('active');
});

// Post job
document.getElementById('post-job-btn').addEventListener('click', function () {
    document.getElementById('form-eyebrow').textContent = 'NEW JOB POSTING';
    document.getElementById('form-modal-title').textContent = 'Create Job Posting';
    document.getElementById('job-form').reset();
    document.getElementById('job-form-modal').style.display = 'flex';
});

function viewJob(jobId) {
    const job = jobData.find(j => j.id === jobId);
    if (!job) return;
    document.getElementById('modal-job-id').textContent   = 'JOB POSTING · ' + job.id;
    document.getElementById('modal-job-title').textContent = job.title;
    document.getElementById('modal-job-dept').textContent  = job.dept;
    document.getElementById('modal-position').textContent  = job.title;
    document.getElementById('modal-department').textContent = job.dept;
    document.getElementById('modal-type').textContent      = job.type;
    document.getElementById('modal-slots').textContent     = job.slots;
    document.getElementById('modal-applicants').textContent = job.applicants;
    document.getElementById('modal-posted').textContent    = job.posted;
    document.getElementById('modal-deadline').textContent  = job.deadline;
    const badge = document.getElementById('modal-status');
    badge.textContent = job.status;
    badge.className   = 'badge-status ' + (job.status === 'Open' ? 'processed' : 'on-hold');
    document.getElementById('view-modal').style.display = 'flex';
}

function editJob(jobId) {
    const job = jobData.find(j => j.id === jobId);
    if (!job) return;
    document.getElementById('form-eyebrow').textContent      = 'EDIT JOB POSTING';
    document.getElementById('form-modal-title').textContent  = 'Edit — ' + job.title;
    document.getElementById('form-title-input').value        = job.title;
    document.getElementById('form-dept').value               = job.dept;
    document.getElementById('form-type').value               = job.type;
    document.getElementById('form-slots').value              = job.slots;
    document.getElementById('job-form-modal').style.display  = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function submitJobForm() {
    const form = document.getElementById('job-form');
    if (!form.checkValidity()) { form.reportValidity(); return; }
    closeModal('job-form-modal');
}

// Filters
document.getElementById('dept-filter').addEventListener('change', filterJobs);
document.getElementById('status-filter').addEventListener('change', filterJobs);
document.getElementById('search-input').addEventListener('input', filterJobs);

function filterJobs() {
    const dept   = document.getElementById('dept-filter').value;
    const status = document.getElementById('status-filter').value;
    const q      = document.getElementById('search-input').value.toLowerCase();

    const gridCards = document.querySelectorAll('.job-card');
    const listRows  = document.querySelectorAll('#list-table-body tr');
    let visible = 0;

    gridCards.forEach(card => {
        const match = (dept === 'All Departments' || card.dataset.dept === dept)
            && (status === 'All' || card.dataset.status === status)
            && (!q || card.querySelector('.job-title').textContent.toLowerCase().includes(q) || card.dataset.id.toLowerCase().includes(q));
        card.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    listRows.forEach(row => {
        const match = (dept === 'All Departments' || row.dataset.dept === dept)
            && (status === 'All' || row.dataset.status === status)
            && (!q || row.querySelector('.position-cell').textContent.toLowerCase().includes(q) || row.querySelector('.job-id-cell').textContent.toLowerCase().includes(q));
        row.style.display = match ? '' : 'none';
    });

    document.getElementById('showing-count').textContent = visible;
    document.getElementById('grid-empty-state').style.display = visible === 0 ? 'block' : 'none';
    document.getElementById('list-empty-state').style.display = visible === 0 ? 'block' : 'none';
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeModal('view-modal');
        closeModal('job-form-modal');
    }
});
</script>
@endsection
