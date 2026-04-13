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

{{-- Table --}}
<section class="table-section">
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
            <tbody></tbody>
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

{{-- Modals --}}
@include('admin.departments.modals.addDepartment')
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
    const end   = Math.min(start + itemsPerPage, departments.length);
    const tbody = document.querySelector('.payroll-table tbody');
    tbody.innerHTML = '';

    departments.slice(start, end).forEach((dept, i) => {
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
function openAddModal()      { document.getElementById('add-dept-modal').classList.add('open');     document.body.style.overflow = 'hidden'; }
function closeAddModal()     { document.getElementById('add-dept-modal').classList.remove('open');  document.body.style.overflow = ''; }
function openSuccessModal()  { document.getElementById('success-modal').classList.add('open');      document.body.style.overflow = 'hidden'; }
function closeSuccessModal() { document.getElementById('success-modal').classList.remove('open');   document.body.style.overflow = ''; }
function openFailedModal(msg){ if (msg) document.getElementById('failed-msg').textContent = msg; document.getElementById('failed-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
function closeFailedModal()  { document.getElementById('failed-modal').classList.remove('open');    document.body.style.overflow = ''; openAddModal(); }

document.addEventListener('DOMContentLoaded', function () {
    renderTable();
    @if(session('success'))  openSuccessModal(); @endif
    @if($errors->any())      openFailedModal('{{ $errors->first() }}'); @endif
});
</script>
@endsection
