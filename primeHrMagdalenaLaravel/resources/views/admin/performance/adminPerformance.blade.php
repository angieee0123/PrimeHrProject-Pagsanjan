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
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Evaluations</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">6</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All employees</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Completed</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">4</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">Finished evaluations</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">2</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Awaiting evaluation</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Average Rating</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
        </div>
        <h2 class="stat-value">4.7</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Out of 5.0</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Performance Evaluations</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · 6 evaluations</p>
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
                <option>All Status</option>
                <option>Completed</option>
                <option>Pending</option>
            </select>
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
                    <th>Position</th>
                    <th>Department</th>
                    <th>Period</th>
                    <th>Evaluator</th>
                    <th>Rating</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                $employees = [
                    ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'period' => 'Jan-Jun 2025', 'rating' => 4.8, 'status' => 'Completed', 'evaluator' => 'Mayor Office'],
                    ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'period' => 'Jan-Jun 2025', 'rating' => 4.5, 'status' => 'Completed', 'evaluator' => 'Municipal Engineer'],
                    ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'period' => 'Jan-Jun 2025', 'rating' => 4.9, 'status' => 'Completed', 'evaluator' => 'Health Officer'],
                    ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'period' => 'Jan-Jun 2025', 'rating' => 4.6, 'status' => 'Completed', 'evaluator' => 'Municipal Treasurer'],
                    ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'period' => 'Jan-Jun 2025', 'rating' => null, 'status' => 'Pending', 'evaluator' => 'MSWD Head'],
                    ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'period' => 'Jan-Jun 2025', 'rating' => null, 'status' => 'Pending', 'evaluator' => 'Civil Registrar'],
                ];
                @endphp

                @foreach($employees as $index => $emp)
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
                    <td style="font-size: 12.5px; color: #6b6a8a; white-space: nowrap;">{{ $emp['period'] }}</td>
                    <td style="font-size: 12.5px; color: #6b6a8a;">{{ $emp['evaluator'] }}</td>
                    <td>
                        @if($emp['rating'])
                            <span style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $emp['rating'] }} / 5.0</span>
                        @else
                            <span style="font-size: 12.5px; color: #9999bb;">Not rated</span>
                        @endif
                    </td>
                    <td><span class="badge-status {{ $emp['status'] === 'Completed' ? 'processed' : 'pending' }}">{{ $emp['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            @if($emp['status'] === 'Pending')
                                <button class="btn-edit">Evaluate</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>6</strong> of <strong>6</strong> evaluations</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

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
</style>
@endsection
