@extends('layouts.app')

@push('styles')
    @vite('resources/css/departments.css')
@endpush

@section('content')
@php
$avatarColors   = ['#0b044d','#8e1e18','#1a0f6e','#5a0f0b','#2d1a8e','#0b044d','#3b1a6e','#6b0f0b'];
$totalPersonnel = $departments->sum('personnel_count');
$activeDepts    = $departments->where('status','Active')->count();
$largestDept    = $departments->sortByDesc('personnel_count')->first();
@endphp

@include('admin.topbar.departmentsTopbar')

{{-- Stats --}}
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Departments</p>
            <div class="stat-icon-wrap" style="background:#0b044d15;color:#0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $departments->count() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#0b044d;"></span>
            <p class="stat-sub">All offices</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background:#15803d15;color:#15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPersonnel }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#15803d;"></span>
            <p class="stat-sub">Across all offices</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active Offices</p>
            <div class="stat-icon-wrap" style="background:#d9bb0015;color:#d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $activeDepts }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#d9bb00;"></span>
            <p class="stat-sub">Operational units</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Largest Office</p>
            <div class="stat-icon-wrap" style="background:#8e1e1815;color:#8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $largestDept ? $largestDept->personnel_count : 0 }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#8e1e18;"></span>
            <p class="stat-sub">{{ $largestDept ? $largestDept->code : 'N/A' }}</p>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div style="display:flex;gap:8px;margin-bottom:20px;border-bottom:2px solid #f0effe;padding-bottom:0;">
    <button class="tab-btn active" data-tab="departments">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        Departments & Offices
    </button>
    <button class="tab-btn" data-tab="designations">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        Designations
    </button>
</div>

{{-- Departments Tab --}}
<section class="table-section tab-content active" id="departments">
    <div class="table-header">
        <div>
            <h3 class="table-title">Departments & Offices</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Province of Laguna · {{ $departments->count() }} offices</p>
        </div>
        <div class="table-actions">
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="btn-export" style="color:#15803d;border-color:#15803d;" onclick="openBulkImportModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk Import
            </button>
            <button class="modal-btn-primary" style="padding:7px 16px;font-size:12.5px;" onclick="openAddModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Department
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Department / Office</th>
                    <th>Code</th>
                    <th>Department Head</th>
                    <th>Personnel</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="dept-tbody"></tbody>
        </table>
    </div>
    <div class="table-footer">
        <p>Showing <strong><span id="showing-start">1</span>–<span id="showing-end">10</span></strong> of <strong>{{ $departments->count() }}</strong> offices</p>
        <div class="pagination">
            <button class="page-btn" id="prev-btn" onclick="changePage('prev')">‹</button>
            <button class="page-btn active" data-page="1" onclick="goToPage(1)">1</button>
            <button class="page-btn" data-page="2" onclick="goToPage(2)">2</button>
            <button class="page-btn" id="next-btn" onclick="changePage('next')">›</button>
        </div>
    </div>
</section>

{{-- Designations Tab --}}
<section class="table-section tab-content" id="designations">
    <div class="table-header">
        <div>
            <h3 class="table-title">Designations</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $designations->count() }} designations</p>
        </div>
        <div class="table-actions">
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="btn-export" style="color:#15803d;border-color:#15803d;" onclick="openBulkImportDesignationModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk Import
            </button>
            <button class="modal-btn-primary" style="padding:7px 16px;font-size:12.5px;background:#1a0f6e;" onclick="openAddDesignationModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Designation
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Designation Title</th>
                    <th>Department</th>
                    <th>Salary Grade</th>
                    <th>Monthly Rate</th>
                    <th>Employment Type</th>
                </tr>
            </thead>
            <tbody>
                @forelse($designations as $desig)
                <tr>
                    <td><p class="emp-name">{{ $desig->title }}</p></td>
                    <td><span class="dept-tag">{{ $desig->department->name ?? 'N/A' }}</span></td>
                    <td style="font-size:13px;color:#0b044d;">{{ $desig->salary_grade ?? '—' }}</td>
                    <td style="font-size:13px;font-weight:600;color:#15803d;">{{ $desig->monthly_rate ? '₱' . number_format($desig->monthly_rate, 2) : '—' }}</td>
                    <td><span class="badge-status {{ $desig->employment_type === 'Permanent' ? 'processed' : 'pending' }}">{{ $desig->employment_type ?? '—' }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#9999bb;padding:32px;font-size:13px;">No designations added yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-footer">
        <p>Showing <strong>{{ $designations->count() }}</strong> designations</p>
    </div>
</section>

{{-- Modals --}}
@include('admin.departments.modals.addDepartment')
@include('admin.departments.modals.addDesignation')
@include('admin.departments.modals.bulkImportDepartment')
@include('admin.departments.modals.bulkImportDesignation')
@include('admin.departments.modals.viewDepartment')
@include('admin.departments.modals.feedbackModals')

<script>
const departments  = @json($departments->values());
const avatarColors = @json($avatarColors);
const itemsPerPage = 10;
let currentPage    = 1;
const totalPages   = Math.ceil(departments.length / itemsPerPage);

function renderTable() {
    const start = (currentPage - 1) * itemsPerPage;
    const end   = Math.min(start + itemsPerPage, filteredDepartments.length);
    const tbody = document.getElementById('dept-tbody');
    tbody.innerHTML = '';

    filteredDepartments.slice(start, end).forEach((dept, i) => {
        const idx = start + i;
        tbody.innerHTML += `
            <tr>
                <td>
                    <div class="emp-cell">
                        <div class="emp-avatar" style="background:${avatarColors[idx % avatarColors.length]};font-size:11px;">${dept.code.slice(0,2)}</div>
                        <p class="emp-name">${dept.name}</p>
                    </div>
                </td>
                <td><span class="dept-tag">${dept.code}</span></td>
                <td class="position-cell">${dept.head}</td>
                <td class="pay-cell">${dept.personnel_count}</td>
                <td><span class="badge-status ${dept.status === 'Active' ? 'processed' : 'on-hold'}">${dept.status}</span></td>
                <td><button class="btn-view" onclick="showDeptModal(${idx})">View</button></td>
            </tr>`;
    });

    document.getElementById('showing-start').textContent = start + 1;
    document.getElementById('showing-end').textContent   = end;
    updatePagination();
}

function updatePagination() {
    document.querySelectorAll('.page-btn[data-page]').forEach(btn =>
        btn.classList.toggle('active', parseInt(btn.dataset.page) === currentPage)
    );
    const prev = document.getElementById('prev-btn');
    const next = document.getElementById('next-btn');
    const totalPages = Math.ceil(filteredDepartments.length / itemsPerPage);
    prev.disabled = currentPage === 1;           prev.style.opacity = currentPage === 1 ? '0.5' : '1';
    next.disabled = currentPage === totalPages;  next.style.opacity = currentPage === totalPages ? '0.5' : '1';
}

function goToPage(page)  { if (page >= 1 && page <= totalPages) { currentPage = page; renderTable(); } }
function changePage(dir) { goToPage(dir === 'prev' ? currentPage - 1 : currentPage + 1); }

function showDeptModal(index) {
    const dept  = departments[index];
    const color = avatarColors[index % avatarColors.length];
    const avatar = document.getElementById('modal-avatar');
    avatar.style.background = color;
    avatar.textContent       = dept.code.slice(0, 2);
    document.getElementById('modal-eyebrow').textContent         = 'DEPARTMENTS · ' + dept.code;
    document.getElementById('modal-title').textContent           = dept.name;
    document.getElementById('modal-code').textContent            = dept.code;
    document.getElementById('modal-personnel-count').textContent = dept.personnel_count;
    const sb = document.getElementById('modal-status-badge');
    sb.textContent = dept.status;
    sb.style.color = dept.status === 'Active' ? '#15803d' : '#8e1e18';
    document.getElementById('modal-head').textContent = dept.head;
    const descRow = document.getElementById('modal-desc-row');
    const descEl  = document.getElementById('modal-desc');
    if (dept.description) { descEl.textContent = dept.description; descRow.style.display = 'flex'; }
    else                  { descRow.style.display = 'none'; }
    document.getElementById('dept-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeDeptModal()    { document.getElementById('dept-modal').classList.remove('open');      document.body.style.overflow = ''; }
function openAddModal()             { document.getElementById('add-dept-modal').classList.add('open');          document.body.style.overflow = 'hidden'; }
function closeAddModal()            { document.getElementById('add-dept-modal').classList.remove('open');       document.body.style.overflow = ''; }
function openAddDesignationModal()  { document.getElementById('add-designation-modal').classList.add('open');   document.body.style.overflow = 'hidden'; }
function closeAddDesignationModal() { document.getElementById('add-designation-modal').classList.remove('open'); document.body.style.overflow = ''; }
function openSuccessModal()  { document.getElementById('success-modal').classList.add('open');      document.body.style.overflow = 'hidden'; }
function closeSuccessModal() { document.getElementById('success-modal').classList.remove('open');   document.body.style.overflow = ''; }
function openFailedModal(msg){ if (msg) document.getElementById('failed-msg').textContent = msg; document.getElementById('failed-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeFailedModal()  { document.getElementById('failed-modal').classList.remove('open');    document.body.style.overflow = ''; openAddModal(); }

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

// Topbar search filter
document.getElementById('dept-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    filteredDepartments = departments.filter(d =>
        d.name.toLowerCase().includes(q) ||
        d.code.toLowerCase().includes(q) ||
        d.head.toLowerCase().includes(q)
    );
    currentPage = 1;
    renderTable();
});

let filteredDepartments = departments;

document.addEventListener('DOMContentLoaded', function () {
    renderTable();
    @if(session('success'))  openSuccessModal(); @endif
    @if($errors->any())      openFailedModal('{{ $errors->first() }}'); @endif
});
</script>
@endsection
