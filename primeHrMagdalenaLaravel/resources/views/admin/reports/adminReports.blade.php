@extends('layouts.app')

@section('content')
<div>
    <!-- Report Type Tabs -->
    <div style="display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap;">
        @php
        $reportTypes = [
            ['id' => 'payroll', 'label' => 'Payroll Summary', 'icon' => 'creditCard'],
            ['id' => 'department', 'label' => 'Department Breakdown', 'icon' => 'building'],
            ['id' => 'deductions', 'label' => 'Deductions Report', 'icon' => 'trendingUp'],
            ['id' => 'headcount', 'label' => 'Headcount Report', 'icon' => 'users'],
            ['id' => 'recruitment', 'label' => 'Recruitment Report', 'icon' => 'clipboard'],
            ['id' => 'training', 'label' => 'Training Report', 'icon' => 'bookOpen'],
            ['id' => 'performance', 'label' => 'Performance Report', 'icon' => 'star'],
        ];
        @endphp

        @foreach($reportTypes as $rt)
        <button class="report-tab-btn" data-report="{{ $rt['id'] }}" onclick="switchReport('{{ $rt['id'] }}')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                @if($rt['icon'] === 'creditCard')
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                @elseif($rt['icon'] === 'building')
                <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M8 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/>
                @elseif($rt['icon'] === 'trendingUp')
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                @elseif($rt['icon'] === 'users')
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                @elseif($rt['icon'] === 'clipboard')
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                @elseif($rt['icon'] === 'bookOpen')
                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                @elseif($rt['icon'] === 'star')
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                @endif
            </svg>
            {{ $rt['label'] }}
        </button>
        @endforeach
    </div>

    <!-- Filters Section -->
    <section class="table-section" style="margin-bottom: 20px;">
        <div class="table-header">
            <div>
                <h3 class="table-title" id="report-title">Payroll Summary — June 16–30, 2025</h3>
                <p class="table-sub">Municipal Government of Pagsanjan · Fiscal Year 2025</p>
            </div>
            <div class="table-actions">
                <div class="search-box" style="padding: 7px 12px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input type="text" placeholder="Search..." id="search-input" style="width: 160px;">
                </div>
                <select class="filter-select" id="semi-select">
                    <option>1st (1–15)</option>
                    <option selected>2nd (16–30)</option>
                </select>
                <select class="filter-select" id="month-select">
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
                <select class="filter-select" id="year-select">
                    <option selected>2025</option>
                    <option>2024</option>
                    <option>2023</option>
                </select>
                <button class="btn-export" onclick="window.print()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export / Print
                </button>
            </div>
        </div>
    </section>

    <!-- Summary Stats -->
    <section class="stats-grid" style="margin-bottom: 20px;">
        @php
        $summaryStats = [
            ['label' => 'Gross Payroll', 'value' => '₱128,485.00', 'sub' => '8 employees · June 16–30, 2025', 'accent' => '#0b044d', 'icon' => 'peso'],
            ['label' => 'Total Net Pay', 'value' => '₱102,788.00', 'sub' => 'After all deductions', 'accent' => '#15803d', 'icon' => 'peso'],
            ['label' => 'Total Deductions', 'value' => '₱25,697.00', 'sub' => 'GSIS, PhilHealth, Pag-IBIG, Tax', 'accent' => '#8e1e18', 'icon' => 'creditCard'],
            ['label' => 'Processed', 'value' => '6', 'sub' => '2 pending/on-hold', 'accent' => '#d9bb00', 'icon' => 'checkCircle'],
        ];
        @endphp

        @foreach($summaryStats as $s)
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">{{ $s['label'] }}</p>
                <div class="stat-icon-wrap" style="background: {{ $s['accent'] }}18;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="{{ $s['accent'] }}" stroke-width="2">
                        @if($s['icon'] === 'peso')
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        @elseif($s['icon'] === 'creditCard')
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                        @elseif($s['icon'] === 'checkCircle')
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        @endif
                    </svg>
                </div>
            </div>
            <h2 class="stat-value" style="font-size: 18px;">{{ $s['value'] }}</h2>
            <div class="stat-footer">
                <span class="stat-dot" style="background: {{ $s['accent'] }};"></span>
                <p class="stat-sub">{{ $s['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </section>

    <!-- Payroll Summary Report -->
    <section class="table-section report-content" id="payroll-report">
        <div class="table-header">
            <div>
                <h3 class="table-title">Payroll Summary Register</h3>
                <p class="table-sub">8 records · June 16–30, 2025 · Pay Date: Jun 30, 2025</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Basic Pay</th>
                        <th>Total Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $payrollData = [
                        ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'dept' => 'Office of the Mayor', 'basic' => 21079.50, 'deductions' => 4215.50, 'status' => 'Processed'],
                        ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'dept' => 'Office of the Mun. Engineer', 'basic' => 19042.50, 'deductions' => 3809.00, 'status' => 'Processed'],
                        ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'dept' => 'Municipal Health Office', 'basic' => 16921.50, 'deductions' => 3384.00, 'status' => 'Pending'],
                        ['id' => 'PGS-0203', 'name' => 'Carlos M. Mendoza', 'dept' => 'Office of the Mun. Treasurer', 'basic' => 23627.50, 'deductions' => 4726.00, 'status' => 'Processed'],
                        ['id' => 'PGS-0267', 'name' => 'Liza G. Gomez', 'dept' => 'MSWD – Pagsanjan', 'basic' => 17548.50, 'deductions' => 3509.50, 'status' => 'On Hold'],
                        ['id' => 'PGS-0310', 'name' => 'Roberto T. Flores', 'dept' => 'Municipal Civil Registrar', 'basic' => 15265.50, 'deductions' => 3053.00, 'status' => 'Processed'],
                        ['id' => 'PGS-0342', 'name' => 'Grace A. Villanueva', 'dept' => 'Office of the Mun. Budget', 'basic' => 14500.00, 'deductions' => 2817.50, 'status' => 'Pending'],
                        ['id' => 'PGS-0358', 'name' => 'Ramon D. Cruz', 'dept' => 'Office of the Mun. Agriculturist', 'basic' => 13500.00, 'deductions' => 2592.50, 'status' => 'Processed'],
                    ];
                    @endphp

                    @foreach($payrollData as $r)
                    <tr>
                        <td class="emp-id">{{ $r['id'] }}</td>
                        <td class="emp-name">{{ $r['name'] }}</td>
                        <td><span class="dept-tag">{{ $r['dept'] }}</span></td>
                        <td class="pay-cell">₱{{ number_format($r['basic'], 2) }}</td>
                        <td class="deduction">₱{{ number_format($r['deductions'], 2) }}</td>
                        <td class="net-pay">₱{{ number_format($r['basic'] - $r['deductions'], 2) }}</td>
                        <td><span class="badge-status {{ strtolower(str_replace(' ', '-', $r['status'])) }}">{{ $r['status'] }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr style="font-weight: 700; background: #f7f6ff;">
                        <td colspan="3" style="padding: 10px 14px; font-size: 13px;">TOTAL (8 employees)</td>
                        <td class="pay-cell">₱141,485.00</td>
                        <td class="deduction">₱28,107.00</td>
                        <td class="net-pay">₱113,378.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <!-- Department Breakdown Report -->
    <section class="table-section report-content" id="department-report" style="display: none;">
        <div class="table-header">
            <div>
                <h3 class="table-title">Department Payroll Breakdown</h3>
                <p class="table-sub">8 departments · June 16–30, 2025</p>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Department / Office</th>
                        <th>Headcount</th>
                        <th>Gross Payroll</th>
                        <th>Net Payroll</th>
                        <th>% of Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="dept-tag">Office of the Mun. Treasurer</span></td>
                        <td style="font-weight: 600; color: #0b044d;">1</td>
                        <td class="pay-cell">₱23,627.50</td>
                        <td class="net-pay">₱18,901.50</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4px; overflow: hidden;">
                                    <div style="width: 17%; height: 100%; background: #0b044d; border-radius: 4px;"></div>
                                </div>
                                <span style="font-size: 12px; color: #6b6a8a; min-width: 32px;">17%</span>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="dept-tag">Office of the Mayor</span></td>
                        <td style="font-weight: 600; color: #0b044d;">1</td>
                        <td class="pay-cell">₱21,079.50</td>
                        <td class="net-pay">₱16,864.00</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <div style="flex: 1; height: 6px; background: #eceaf8; border-radius: 4px; overflow: hidden;">
                                    <div style="width: 15%; height: 100%; background: #0b044d; border-radius: 4px;"></div>
                                </div>
                                <span style="font-size: 12px; color: #6b6a8a; min-width: 32px;">15%</span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Other Reports (Deductions, Headcount, etc.) -->
    <section class="table-section report-content" id="deductions-report" style="display: none;">
        <div class="table-header">
            <div>
                <h3 class="table-title">Deductions Breakdown Report</h3>
                <p class="table-sub">8 employees · June 16–30, 2025</p>
            </div>
        </div>
        <p style="padding: 40px; text-align: center; color: #9999bb;">Deductions report content</p>
    </section>

    <section class="table-section report-content" id="headcount-report" style="display: none;">
        <div class="table-header">
            <div>
                <h3 class="table-title">Headcount Report</h3>
                <p class="table-sub">8 total personnel · June 16–30, 2025</p>
            </div>
        </div>
        <p style="padding: 40px; text-align: center; color: #9999bb;">Headcount report content</p>
    </section>

    <section class="table-section report-content" id="recruitment-report" style="display: none;">
        <div class="table-header">
            <div>
                <h3 class="table-title">Recruitment Report</h3>
                <p class="table-sub">Job postings and applicant statistics · 2025</p>
            </div>
        </div>
        <p style="padding: 40px; text-align: center; color: #9999bb;">Recruitment report content</p>
    </section>

    <section class="table-section report-content" id="training-report" style="display: none;">
        <div class="table-header">
            <div>
                <h3 class="table-title">Training & Development Report</h3>
                <p class="table-sub">Training programs and participation · 2025</p>
            </div>
        </div>
        <p style="padding: 40px; text-align: center; color: #9999bb;">Training report content</p>
    </section>

    <section class="table-section report-content" id="performance-report" style="display: none;">
        <div class="table-header">
            <div>
                <h3 class="table-title">Performance Evaluation Report</h3>
                <p class="table-sub">Employee performance ratings · Jan-Jun 2025</p>
            </div>
        </div>
        <p style="padding: 40px; text-align: center; color: #9999bb;">Performance report content</p>
    </section>
</div>

<style>
.report-tab-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 9px 18px;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    border: 1.5px solid #e4e3f0;
    background: #ffffff;
    color: #5a5888;
    transition: all 0.18s;
}

.report-tab-btn:hover {
    border-color: #0b044d;
    background: #f0effe;
}

.report-tab-btn.active {
    border: 2px solid #0b044d;
    background: #0b044d;
    color: #ffffff;
}

.report-tab-btn.active svg {
    stroke: #ffffff;
}

.search-box {
    display: flex;
    align-items: center;
    gap: 9px;
    background: #fafafe;
    border: 1.5px solid #e4e3f0;
    border-radius: 10px;
    transition: all 0.2s;
}

.search-box:focus-within {
    border-color: #0b044d;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(11,4,77,0.06);
}

.search-box input {
    border: none;
    outline: none;
    font-size: 13.5px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: transparent;
}

.search-box input::placeholder {
    color: #b8b6d4;
}
</style>

<script>
function switchReport(reportId) {
    // Update tab buttons
    document.querySelectorAll('.report-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-report="${reportId}"]`).classList.add('active');

    // Hide all reports
    document.querySelectorAll('.report-content').forEach(section => {
        section.style.display = 'none';
    });

    // Show selected report
    document.getElementById(`${reportId}-report`).style.display = 'block';
}

// Set initial active tab
document.addEventListener('DOMContentLoaded', function() {
    document.querySelector('[data-report="payroll"]').classList.add('active');
});
</script>
@endsection
