@extends('layouts.app')

@push('styles')
    @vite('resources/css/departments.css')
    <style>
        .sortable-th { cursor:pointer; user-select:none; white-space:nowrap; }
        .sortable-th:hover { background:#f0effe; }
        .sort-icon { font-size:11px; color:#bbb; margin-left:4px; transition:color .15s; }
        .filter-bar { display:flex; align-items:center; gap:10px; padding:12px 24px; border-top:1px solid #f0effe; flex-wrap:wrap; }
        .filter-bar-left { display:flex; align-items:center; gap:10px; flex:1; flex-wrap:wrap; }
        .filter-label { display:flex; align-items:center; gap:6px; font-size:12px; color:#9999bb; font-weight:600; letter-spacing:.3px; text-transform:uppercase; white-space:nowrap; }
        .filter-bar select { font-size:13px; padding:9px 16px; border:none; border-radius:8px; color:#0b044d; background:#f7f6ff; cursor:pointer; font-family:'Poppins',sans-serif; outline:none; transition:box-shadow .2s; min-width:150px; }
        .filter-bar select:focus { box-shadow:0 0 0 2px rgba(11,4,77,0.15); }
        .filter-bar select.active-filter { background:#ebe9ff; font-weight:600; box-shadow:0 0 0 2px rgba(11,4,77,0.15); }
        .filter-clear { font-size:12.5px; color:#8e1e18; background:#fee8e8; border:none; border-radius:8px; padding:9px 14px; cursor:pointer; font-family:'Poppins',sans-serif; display:none; transition:background .2s; white-space:nowrap; margin-left:auto; }
        .filter-clear:hover { background:#fecaca; }
        .filter-clear.visible { display:inline-flex; align-items:center; gap:5px; }
    </style>
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
            <div class="stat-icon-wrap" style="background:#0b044d15;color:#0b044d;box-shadow:0 4px 12px rgba(11,4,77,0.18);">
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
            <div class="stat-icon-wrap" style="background:#15803d15;color:#15803d;box-shadow:0 4px 12px rgba(21,128,61,0.18);">
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
            <div class="stat-icon-wrap" style="background:#d9bb0015;color:#d9bb00;box-shadow:0 4px 12px rgba(217,187,0,0.18);">
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
            <div class="stat-icon-wrap" style="background:#8e1e1815;color:#8e1e18;box-shadow:0 4px 12px rgba(142,30,24,0.18);">
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
    <div class="filter-bar">
        <div class="filter-bar-left">
            <span class="filter-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </span>
            <select id="dept-filter-status" onchange="applyDeptFilters()">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>
        <button class="filter-clear" id="dept-filter-clear" onclick="clearDeptFilters()">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Clear filters
        </button>
    </div>
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="sortable-th" onclick="sortDept('name')" data-col="name">Department / Office <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDept('code')" data-col="code">Code <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDept('head')" data-col="head">Department Head <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDept('personnel_count')" data-col="personnel_count">Personnel <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDept('status')" data-col="status">Status <span class="sort-icon">⇅</span></th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="dept-tbody"></tbody>
        </table>
    </div>
    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:8px;">
            <p>Showing <strong><span id="showing-start">1</span>–<span id="showing-end">10</span></strong> of <strong><span id="showing-total">{{ $departments->count() }}</span></strong> offices</p>
            <select id="dept-rows-select" onchange="changeRowsDept(this.value)" style="font-size:13px;padding:6px 12px;border:none;border-radius:8px;color:#0b044d;background:#f7f6ff;font-family:'Poppins',sans-serif;outline:none;cursor:pointer;">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="dept-pagination"></div>
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
    <div class="filter-bar">
        <div class="filter-bar-left">
            <span class="filter-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </span>
            <select id="desig-filter-dept" onchange="applyDesigFilters()">
                <option value="">All Departments</option>
                @foreach($departments->sortBy('name') as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <select id="desig-filter-type" onchange="applyDesigFilters()">
                <option value="">All Employment Types</option>
                <option value="Permanent">Permanent</option>
                <option value="Casual">Casual</option>
                <option value="Contractual">Contractual</option>
                <option value="Job Order">Job Order</option>
            </select>
        </div>
        <button class="filter-clear" id="desig-filter-clear" onclick="clearDesigFilters()">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            Clear filters
        </button>
    </div>
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="sortable-th" onclick="sortDesig('title')" data-col="title">Designation Title <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('department')" data-col="department">Department <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('dept_code')" data-col="dept_code">Code <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('salary_grade')" data-col="salary_grade">Salary Grade <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('monthly_rate')" data-col="monthly_rate">Monthly Rate <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('employment_type')" data-col="employment_type">Employment Type <span class="sort-icon">⇅</span></th>
                </tr>
            </thead>
            <tbody id="desig-tbody"></tbody>
        </table>
    </div>
    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:8px;">
            <p>Showing <strong><span id="desig-showing-start">1</span>–<span id="desig-showing-end">10</span></strong> of <strong><span id="desig-showing-total">{{ $designations->count() }}</span></strong> designations</p>
            <select id="desig-rows-select" onchange="changeRowsDesig(this.value)" style="font-size:13px;padding:6px 12px;border:none;border-radius:8px;color:#0b044d;background:#f7f6ff;font-family:'Poppins',sans-serif;outline:none;cursor:pointer;">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="desig-pagination"></div>
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
const designations = @json($designations->values());
const avatarColors = @json($avatarColors);

// --- Sort state ---
let deptSort  = { col: null, dir: 'asc' };
let desigSort = { col: null, dir: 'asc' };

function sortDept(col) {
    deptSort.dir = deptSort.col === col && deptSort.dir === 'asc' ? 'desc' : 'asc';
    deptSort.col = col;
    applySortDept();
    updateSortHeaders('#departments thead th', col, deptSort.dir);
    deptPage = 1;
    renderTable();
}

function applySortDept() {
    const { col, dir } = deptSort;
    filteredDepartments = [...filteredDepartments].sort((a, b) => {
        const av = col === 'personnel_count' ? +a[col] : (a[col] || '').toString().toLowerCase();
        const bv = col === 'personnel_count' ? +b[col] : (b[col] || '').toString().toLowerCase();
        return av < bv ? (dir === 'asc' ? -1 : 1) : av > bv ? (dir === 'asc' ? 1 : -1) : 0;
    });
}

function sortDesig(col) {
    desigSort.dir = desigSort.col === col && desigSort.dir === 'asc' ? 'desc' : 'asc';
    desigSort.col = col;
    applyDesigSort();
    updateSortHeaders('#designations thead th', col, desigSort.dir);
    desigPage = 1;
    renderDesigTable();
}

let sortedDesignations = [...designations];

function applyDesigSort() {
    const { col, dir } = desigSort;
    sortedDesignations = [...filteredDesignations].sort((a, b) => {
        let av, bv;
        if (col === 'department')         { av = (a.department?.name || '').toLowerCase(); bv = (b.department?.name || '').toLowerCase(); }
        else if (col === 'dept_code')      { av = (a.department?.code || '').toLowerCase(); bv = (b.department?.code || '').toLowerCase(); }
        else if (col === 'monthly_rate' || col === 'salary_grade') { av = +(a[col] || 0); bv = +(b[col] || 0); }
        else { av = (a[col] || '').toString().toLowerCase(); bv = (b[col] || '').toString().toLowerCase(); }
        return av < bv ? (dir === 'asc' ? -1 : 1) : av > bv ? (dir === 'asc' ? 1 : -1) : 0;
    });
}

function updateSortHeaders(selector, activeCol, dir) {
    document.querySelectorAll(selector).forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (!icon) return;
        const col = th.dataset.col;
        icon.textContent = col === activeCol ? (dir === 'asc' ? '↑' : '↓') : '⇅';
        icon.style.color = col === activeCol ? '#0b044d' : '#bbb';
    });
}

// --- Filter state ---
let deptFilters  = { status: '' };
let desigFilters = { dept_id: '', type: '' };

function applyDeptFilters() {
    deptFilters.status = document.getElementById('dept-filter-status').value;
    const q = document.getElementById('dept-search').value.toLowerCase();
    filteredDepartments = departments.filter(d => {
        const matchSearch = !q || d.name.toLowerCase().includes(q) || d.code.toLowerCase().includes(q) || d.head.toLowerCase().includes(q);
        const matchStatus = !deptFilters.status || d.status === deptFilters.status;
        return matchSearch && matchStatus;
    });
    if (deptSort.col) applySortDept();
    deptPage = 1;
    renderTable();
    const hasFilter = !!deptFilters.status;
    document.getElementById('dept-filter-clear').classList.toggle('visible', hasFilter);
    document.getElementById('dept-filter-status').classList.toggle('active-filter', hasFilter);
}

function clearDeptFilters() {
    deptFilters = { status: '' };
    document.getElementById('dept-filter-status').value = '';
    applyDeptFilters();
}

function applyDesigFilters() {
    desigFilters.dept_id = document.getElementById('desig-filter-dept').value;
    desigFilters.type    = document.getElementById('desig-filter-type').value;
    const q = document.getElementById('dept-search').value.toLowerCase();
    filteredDesignations = designations.filter(d => {
        const matchSearch = !q ||
            (d.title || '').toLowerCase().includes(q) ||
            (d.department?.name || '').toLowerCase().includes(q) ||
            (d.department?.code || '').toLowerCase().includes(q) ||
            (d.employment_type || '').toLowerCase().includes(q) ||
            (d.salary_grade || '').toString().includes(q);
        const matchDept = !desigFilters.dept_id || String(d.department_id) === desigFilters.dept_id;
        const matchType = !desigFilters.type    || d.employment_type === desigFilters.type;
        return matchSearch && matchDept && matchType;
    });
    if (desigSort.col) applyDesigSort();
    desigPage = 1;
    renderDesigTable();
    const hasFilter = !!(desigFilters.dept_id || desigFilters.type);
    document.getElementById('desig-filter-clear').classList.toggle('visible', hasFilter);
    document.getElementById('desig-filter-dept').classList.toggle('active-filter', !!desigFilters.dept_id);
    document.getElementById('desig-filter-type').classList.toggle('active-filter', !!desigFilters.type);
}

function clearDesigFilters() {
    desigFilters = { dept_id: '', type: '' };
    document.getElementById('desig-filter-dept').value = '';
    document.getElementById('desig-filter-type').value = '';
    applyDesigFilters();
}

// --- Departments pagination ---
let deptPage = 1, deptRowsPerPage = 10, filteredDepartments = [...departments];

function renderTable() {
    const total = filteredDepartments.length;
    const totalPages = Math.ceil(total / deptRowsPerPage) || 1;
    if (deptPage > totalPages) deptPage = totalPages;
    const start = (deptPage - 1) * deptRowsPerPage;
    const end   = Math.min(start + deptRowsPerPage, total);
    const tbody = document.getElementById('dept-tbody');
    tbody.innerHTML = '';

    if (total === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#9999bb;padding:32px;font-size:13px;">No departments found.</td></tr>';
    } else {
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
                    <td><button class="btn-view" onclick="showDeptModal(${departments.indexOf(dept)})">View</button></td>
                </tr>`;
        });
    }

    document.getElementById('showing-start').textContent  = total ? start + 1 : 0;
    document.getElementById('showing-end').textContent    = end;
    document.getElementById('showing-total').textContent  = total;
    renderPagination('dept-pagination', deptPage, totalPages, (p) => { deptPage = p; renderTable(); });
}

function changeRowsDept(val) { deptRowsPerPage = parseInt(val); deptPage = 1; renderTable(); }

// --- Designations pagination ---
let desigPage = 1, desigRowsPerPage = 10;

function renderDesigTable() {
    const source = desigSort.col ? sortedDesignations : filteredDesignations;
    const total = source.length;
    const totalPages = Math.ceil(total / desigRowsPerPage) || 1;
    if (desigPage > totalPages) desigPage = totalPages;
    const start = (desigPage - 1) * desigRowsPerPage;
    const end   = Math.min(start + desigRowsPerPage, total);
    const tbody = document.getElementById('desig-tbody');
    tbody.innerHTML = '';

    if (total === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#9999bb;padding:32px;font-size:13px;">No designations added yet.</td></tr>';
    } else {
        source.slice(start, end).forEach(d => {
            const rate = d.monthly_rate ? '₱' + parseFloat(d.monthly_rate).toLocaleString('en-PH', {minimumFractionDigits:2}) : '—';
            const type = d.employment_type || '—';
            const deptCode = d.department?.code || '—';
            tbody.innerHTML += `
                <tr>
                    <td><p class="emp-name">${d.title}</p></td>
                    <td><span class="dept-tag">${d.department ? d.department.name : 'N/A'}</span></td>
                    <td><span class="dept-tag" style="background:#f0effe;color:#1a0f6e;">${deptCode}</span></td>
                    <td style="font-size:13px;color:#0b044d;">${d.salary_grade || '—'}</td>
                    <td style="font-size:13px;font-weight:600;color:#15803d;">${rate}</td>
                    <td><span class="badge-status ${type === 'Permanent' ? 'processed' : 'pending'}">${type}</span></td>
                </tr>`;
        });
    }

    document.getElementById('desig-showing-start').textContent = total ? start + 1 : 0;
    document.getElementById('desig-showing-end').textContent   = end;
    document.getElementById('desig-showing-total').textContent = total;
    renderPagination('desig-pagination', desigPage, totalPages, (p) => { desigPage = p; renderDesigTable(); });
}

function changeRowsDesig(val) { desigRowsPerPage = parseInt(val); desigPage = 1; renderDesigTable(); }

// --- Shared pagination renderer ---
function renderPagination(containerId, current, total, onPageClick) {
    const container = document.getElementById(containerId);
    let html = `<button class="page-btn" ${current===1?'disabled style="opacity:.5"':''} onclick="(${onPageClick.toString()})(${current-1})">‹</button>`;
    const pages = [];
    if (total <= 7) { for (let i=1;i<=total;i++) pages.push(i); }
    else {
        pages.push(1);
        if (current > 3) pages.push('...');
        for (let i=Math.max(2,current-1); i<=Math.min(total-1,current+1); i++) pages.push(i);
        if (current < total-2) pages.push('...');
        pages.push(total);
    }
    pages.forEach(p => {
        if (p === '...') { html += `<span style="padding:0 4px;color:#9999bb;">…</span>`; }
        else { html += `<button class="page-btn ${p===current?'active':''}" onclick="(${onPageClick.toString()})(${p})">${p}</button>`; }
    });
    html += `<button class="page-btn" ${current===total?'disabled style="opacity:.5"':''} onclick="(${onPageClick.toString()})(${current+1})">›</button>`;
    container.innerHTML = html;
}

function goToPage(page)  { deptPage = page; renderTable(); }
function changePage(dir) { goToPage(dir === 'prev' ? deptPage - 1 : deptPage + 1); }

// --- Modal & UI helpers ---
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
function closeImportSummaryModal() { document.getElementById('import-summary-modal').classList.remove('open'); document.body.style.overflow = ''; }

const searchPlaceholders = {
    departments:  'Search department, code, or head...',
    designations: 'Search designation, department, or type...'
};

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
        const searchEl = document.getElementById('dept-search');
        searchEl.value = '';
        searchEl.placeholder = searchPlaceholders[this.dataset.tab] || 'Search...';
        filteredDepartments  = [...departments];
        filteredDesignations = [...designations];
        clearDeptFilters();
        clearDesigFilters();
    });
});

// Topbar search filter
let filteredDesignations = [...designations];

document.getElementById('dept-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    const activeTab = document.querySelector('.tab-btn.active')?.dataset.tab;

    if (activeTab === 'designations') {
        applyDesigFilters();
    } else {
        applyDeptFilters();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('dept-search').placeholder = searchPlaceholders.departments;
    renderTable();
    renderDesigTable();
    @if(session('success'))  openSuccessModal(); @endif
    @if($errors->any())      openFailedModal('{{ $errors->first() }}'); @endif

    @if(session('import_imported') !== null)
    (function() {
        const imported = {{ session('import_imported') }};
        const skipped  = @json(session('import_skipped', []));
        const type     = '{{ session('import_type', 'record') }}';

        document.getElementById('import-summary-title').textContent =
            type === 'department' ? 'Department Import Summary' : 'Designation Import Summary';
        document.getElementById('import-count').textContent  = imported;
        document.getElementById('skipped-count').textContent = skipped.length;

        if (skipped.length > 0) {
            const wrap = document.getElementById('skipped-list-wrap');
            const list = document.getElementById('skipped-list');
            wrap.style.display = 'block';
            list.innerHTML = skipped.map(r =>
                `<p style="font-size:12px;color:#8e1e18;margin:0 0 4px;padding-bottom:4px;border-bottom:1px solid #fecaca;">⚠ ${r}</p>`
            ).join('');
        }

        document.getElementById('import-summary-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    })();
    @endif
});
</script>
@endsection
