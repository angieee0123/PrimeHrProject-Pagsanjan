@extends('layouts.mayor')

@section('title', "Personnel Directory · PRIME HRIS")

@section('content')

<main class="enterprise-hr-dashboard">

@include('mayor.topbar.personnelTopbar')

{{-- Stats Grid --}}
<div class="stats-grid stats-grid-4">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Personnel</p>
            <div class="stat-icon-wrap" style="background:var(--gp-bg-tint-2)">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['total']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:var(--gp-pri)"></span>
            <p class="stat-sub">All records</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Active</p>
            <div class="stat-icon-wrap" style="background:#e9f9ef">
                <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['active']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:var(--theme-success)"></span>
            <p class="stat-sub">Currently active</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Inactive</p>
            <div class="stat-icon-wrap" style="background:#fdedec">
                <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['inactive']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:var(--theme-danger)"></span>
            <p class="stat-sub">Deactivated accounts</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Permanent</p>
            <div class="stat-icon-wrap" style="background:var(--theme-warning-subtle)">
                <svg width="17" height="17" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ number_format($stats['permanent']) }}</p>
        <div class="stat-footer">
            <span class="stat-dot" style="background:#c9a227"></span>
            <p class="stat-sub">Permanent employees</p>
        </div>
    </div>
</div>

{{-- Roster / Departments --}}
<div class="table-section" style="margin:0">
    <div class="table-header" style="background:linear-gradient(135deg,var(--gp-bg-tint-2) 0%,#fff 100%)">
        <div>
            <p class="table-title" id="personnelTabTitle">Employee Roster</p>
            <p class="table-sub" id="personnelTabSub">{{ number_format($stats['total']) }} employees · read-only</p>
        </div>
        <div class="mayor-highlights-tabs">
            <button id="tabPersEmployees" onclick="switchPersonnelTab('employees')" class="chart-tab active">Employees</button>
            <button id="tabPersDepartments" onclick="switchPersonnelTab('departments')" class="chart-tab">Departments</button>
        </div>
    </div>

    {{-- Employees panel --}}
    <div id="panelPersEmployees">
        <div class="table-actions" style="padding:14px 20px 0">
            <div style="position:relative">
                <svg width="14" height="14" fill="none" stroke="#8f8daf" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="mayorPersonnelSearch" placeholder="Search employees..." oninput="mayorFilterPersonnel()" style="font-size:11px;padding:6px 12px 6px 32px;border-radius:6px;border:1.5px solid var(--theme-neutral-300);width:200px;font-family:inherit">
            </div>
            <select class="filter-select" id="mayorPersonnelDept" onchange="mayorFilterPersonnel()">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <select class="filter-select" id="mayorPersonnelStatus" onchange="mayorFilterPersonnel()">
                <option value="">All Status</option>
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
            </select>
        </div>

        <div class="table-wrapper">
            <table class="payroll-table" id="mayorPersonnelTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Position</th>
                        <th>Department</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $avatarColors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#c9a227', '#56547a']; @endphp
                    @forelse($employees as $index => $employee)
                        @php
                            $fullName = trim($employee->first_name . ' ' . $employee->last_name);
                            $initials = strtoupper(substr($employee->first_name, 0, 1) . substr($employee->last_name, 0, 1));
                            $status = $employee->user ? $employee->user->status : 'Inactive';
                            $empType = $employee->employmentDetail->employment_status ?? 'N/A';
                            $position = $employee->employmentDetail->designationRelation->title ?? 'N/A';
                            $department = $employee->employmentDetail->departmentRelation->name ?? 'N/A';
                        @endphp
                        <tr data-dept="{{ $department }}" data-status="{{ $status }}" data-search="{{ strtolower($fullName . ' ' . $employee->employee_id) }}">
                            <td>
                                <div class="emp-cell">
                                    @if($employee->photo)
                                        <img src="{{ $employee->photo }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid var(--gp-border)">
                                    @else
                                        <div style="width:38px;height:38px;border-radius:50%;background:{{ $avatarColors[$index % count($avatarColors)] }};display:flex;align-items:center;justify-content:center;color:#fff;font-weight:600;font-size:12px;border:2px solid var(--gp-border)">{{ $initials }}</div>
                                    @endif
                                    <div>
                                        <p class="emp-name">{{ $fullName }}</p>
                                        <p class="emp-id">{{ $employee->employee_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $position }}</td>
                            <td><span class="dept-tag">{{ $department }}</span></td>
                            <td><span class="badge-emptype">{{ $empType }}</span></td>
                            <td><span class="badge-status {{ $status === 'Active' ? 'processed' : 'on-hold' }}">{{ $status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;padding:32px 0;color:var(--theme-neutral-600);font-size:12.5px">No employee records yet</td></tr>
                    @endforelse
                </tbody>
            </table>
            <p id="mayorPersonnelNoResults" style="display:none;text-align:center;padding:32px 0;color:var(--theme-neutral-600);font-size:12.5px;margin:0">No employees match your search/filter</p>
        </div>
    </div>

    {{-- Departments panel --}}
    <div id="panelPersDepartments" style="display:none">
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Department</th>
                        <th>Head</th>
                        <th>Personnel</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departmentRows as $dept)
                        <tr>
                            <td><span class="dept-tag">{{ $dept->code }}</span></td>
                            <td style="font-weight:600;color:var(--theme-neutral-900)">{{ $dept->name }}</td>
                            <td>{{ $dept->head }}</td>
                            <td>{{ number_format($dept->headcount) }}</td>
                            <td><span class="badge-status {{ $dept->status === 'Active' ? 'processed' : 'on-hold' }}">{{ $dept->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align:center;padding:32px 0;color:var(--theme-neutral-600);font-size:12.5px">No department records yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</main>

@push('scripts')
<script>
    window.mayorPersonnelData = {
        totalEmployeesLabel: @json(number_format($stats['total'])),
        totalDepartmentsLabel: @json(number_format($departmentRows->count())),
    };
</script>
    @vite('resources/js/mayor/personnel/mayorPersonnel.js')
@endpush
@endsection
