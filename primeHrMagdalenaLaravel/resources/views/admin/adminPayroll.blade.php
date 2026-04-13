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

function peso($amount) {
    return '₱' . number_format($amount, 2);
}

// Semi-monthly amounts (monthly basic ÷ 2, deductions ÷ 2)
$payrollRecords = [
    ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'basic' => 21079.50, 'gsis' => 1897, 'philhealth' => 525, 'pagibig' => 50, 'tax' => 1743.50, 'status' => 'Processed'],
    ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'basic' => 19042.50, 'gsis' => 1714, 'philhealth' => 475, 'pagibig' => 50, 'tax' => 1569.50, 'status' => 'Processed'],
    ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'basic' => 16921.50, 'gsis' => 1523, 'philhealth' => 425, 'pagibig' => 50, 'tax' => 1386, 'status' => 'Pending'],
    ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'position' => 'Municipal Treasurer III', 'dept' => 'Office of the Mun. Treasurer', 'basic' => 23627.50, 'gsis' => 2126.50, 'philhealth' => 575, 'pagibig' => 50, 'tax' => 1974, 'status' => 'Processed'],
    ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'position' => 'Social Welfare Officer II', 'dept' => 'MSWD – Pagsanjan', 'basic' => 17548.50, 'gsis' => 1579.50, 'philhealth' => 437.50, 'pagibig' => 50, 'tax' => 1442.50, 'status' => 'On Hold'],
    ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'position' => 'Municipal Civil Registrar I', 'dept' => 'Municipal Civil Registrar', 'basic' => 15265.50, 'gsis' => 1374, 'philhealth' => 387.50, 'pagibig' => 50, 'tax' => 1241.50, 'status' => 'Processed'],
    ['id' => 'PGS-0342', 'name' => 'Grace A. Villanueva', 'position' => 'Budget Officer II', 'dept' => 'Office of the Mun. Budget', 'basic' => 14500, 'gsis' => 1305, 'philhealth' => 362.50, 'pagibig' => 50, 'tax' => 1100, 'status' => 'Pending'],
    ['id' => 'PGS-0358', 'name' => 'Ramon D. Cruz', 'position' => 'Agriculturist I', 'dept' => 'Office of the Mun. Agriculturist', 'basic' => 13500, 'gsis' => 1215, 'philhealth' => 337.50, 'pagibig' => 50, 'tax' => 990, 'status' => 'Processed'],
];

$grossPayroll = array_sum(array_column($payrollRecords, 'basic'));
$totalDeductions = 0;
$totalNet = 0;
foreach ($payrollRecords as $record) {
    $deductions = $record['gsis'] + $record['philhealth'] + $record['pagibig'] + $record['tax'];
    $totalDeductions += $deductions;
    $totalNet += ($record['basic'] - $deductions);
}
$processedCount = count(array_filter($payrollRecords, fn($r) => $r['status'] === 'Processed'));
$pendingCount = count(array_filter($payrollRecords, fn($r) => $r['status'] === 'Pending'));
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Gross Payroll</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">{{ peso($grossPayroll) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">June 16–30, 2025</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Net Pay</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">{{ peso($totalNet) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">After deductions</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Deductions</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
            </div>
        </div>
        <h2 class="stat-value" style="font-size: 18px;">{{ peso($totalDeductions) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">GSIS, PhilHealth etc</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Records</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $pendingCount }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">{{ $processedCount }} processed</p>
        </div>
    </div>
</div>

<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Payroll Register — June 16–30, 2025</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: Jun 30, 2025 · {{ count($payrollRecords) }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option>1st (1–15)</option>
                <option selected>2nd (16–30)</option>
            </select>
            <select class="filter-select">
                <option>January</option>
                <option>February</option>
                <option>March</option>
                <option>April</option>
                <option>May</option>
                <option selected>June</option>
                <option>July</option>
                <option>August</option>
                <option>September</option>
                <option>October</option>
                <option>November</option>
                <option>December</option>
            </select>
            <select class="filter-select">
                <option selected>2025</option>
                <option>2024</option>
                <option>2023</option>
            </select>
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
                <option>Processed</option>
                <option>Pending</option>
                <option>On Hold</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Run Payroll
            </button>
        </div>
    </div>

    <div class="payroll-summary-bar" style="margin-top: 0; margin-bottom: 16px;">
        <div class="psummary-item">
            <span>Gross Total</span>
            <strong>{{ peso($grossPayroll) }}</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Total Deductions</span>
            <strong class="deduction">{{ peso($totalDeductions) }}</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Total Net Pay</span>
            <strong class="net-pay">{{ peso($totalNet) }}</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Pay Date</span>
            <strong>Jun 30, 2025</strong>
        </div>
        <div class="psummary-divider"></div>
        <div class="psummary-item">
            <span>Records</span>
            <strong>{{ count($payrollRecords) }}</strong>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Basic Pay</th>
                    <th>GSIS</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                    <th>Tax</th>
                    <th>Net Pay</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payrollRecords as $index => $record)
                @php
                    $deductions = $record['gsis'] + $record['philhealth'] + $record['pagibig'] + $record['tax'];
                    $net = $record['basic'] - $deductions;
                @endphp
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($record['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $record['name'] }}</p>
                                <p class="emp-id">{{ $record['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                    <td class="pay-cell">{{ peso($record['basic']) }}</td>
                    <td class="deduction">{{ peso($record['gsis']) }}</td>
                    <td class="deduction">{{ peso($record['philhealth']) }}</td>
                    <td class="deduction">{{ peso($record['pagibig']) }}</td>
                    <td class="deduction">{{ peso($record['tax']) }}</td>
                    <td class="net-pay">{{ peso($net) }}</td>
                    <td><span class="badge-status {{ $record['status'] === 'Processed' ? 'processed' : ($record['status'] === 'Pending' ? 'pending' : 'on-hold') }}">{{ $record['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">Payslip</button>
                            <button class="btn-edit">Edit</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($payrollRecords) }}</strong> of <strong>{{ count($payrollRecords) }}</strong> records</p>
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
.payroll-summary-bar {
    display: flex; align-items: center; gap: 20px;
    padding: 14px 24px; background: #fafafe;
    border: 1px solid #f0effe; border-radius: 8px;
}
.psummary-item { display: flex; flex-direction: column; gap: 2px; }
.psummary-item span { font-size: 11px; color: #9999bb; font-weight: 500; }
.psummary-item strong { font-size: 13px; color: #0b044d; font-weight: 600; }
.psummary-divider { width: 1px; height: 28px; background: #e8e7f5; }
.pay-cell {
    font-size: 13px; color: #0b044d; font-weight: 600;
}
.deduction {
    font-size: 13px; color: #8e1e18; font-weight: 600;
}
.net-pay {
    font-size: 13px; color: #15803d; font-weight: 700;
}
</style>
@endsection
