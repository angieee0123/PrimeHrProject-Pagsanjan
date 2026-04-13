@extends('layouts.app')

@section('content')
<div>
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="banner-left">
            <div class="banner-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d9bb00" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M12 10h.01M8 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01"/></svg>
            </div>
            <div>
                <h2>Welcome to PRIME HRIS Dashboard</h2>
                <p>Municipal Government of Pagsanjan · Human Resource Management Office</p>
            </div>
        </div>
        <div class="banner-right">
            <div class="banner-badge"><span class="banner-badge-dot"></span>System Active</div>
            <div class="banner-badge outline">Fiscal Year 2025</div>
        </div>
    </div>

    <!-- Stats Grid -->
    <section class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
        @php
        $stats = [
            ['label' => 'Total Personnel', 'value' => '348', 'sub' => '+6 this month', 'accent' => '#0b044d', 'icon' => 'users'],
            ['label' => 'Semi-Monthly Payroll', 'value' => '₱2,436,300', 'sub' => '+1.8% vs last period', 'accent' => '#8e1e18', 'icon' => 'peso'],
            ['label' => 'Open Job Positions', 'value' => '8', 'sub' => '45 applicants', 'accent' => '#15803d', 'icon' => 'user'],
            ['label' => 'Ongoing Trainings', 'value' => '5', 'sub' => '83 participants', 'accent' => '#d9bb00', 'icon' => 'bookOpen'],
            ['label' => 'Pending Evaluations', 'value' => '12', 'sub' => 'Due by Jun 30', 'accent' => '#1a6e3c', 'icon' => 'activity'],
            ['label' => 'Avg Performance', 'value' => '4.7', 'sub' => 'Out of 5.0', 'accent' => '#6b3fa0', 'icon' => 'award'],
        ];
        @endphp

        @foreach($stats as $stat)
        <div class="stat-card">
            <div class="stat-top">
                <p class="stat-label">{{ $stat['label'] }}</p>
                <div class="stat-icon-wrap" style="background: {{ $stat['accent'] }}15;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="{{ $stat['accent'] }}" stroke-width="2">
                        @if($stat['icon'] === 'users')
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
                        @elseif($stat['icon'] === 'peso')
                        <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                        @elseif($stat['icon'] === 'user')
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        @elseif($stat['icon'] === 'bookOpen')
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        @elseif($stat['icon'] === 'activity')
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                        @elseif($stat['icon'] === 'award')
                        <circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/>
                        @endif
                    </svg>
                </div>
            </div>
            <h2 class="stat-value">{{ $stat['value'] }}</h2>
            <div class="stat-footer">
                <span class="stat-dot" style="background: {{ $stat['accent'] }};"></span>
                <p class="stat-sub">{{ $stat['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </section>

    <!-- Quick Actions -->
    <section style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px; margin-bottom: 22px;">
        <div class="stat-card" style="cursor: pointer; transition: all 0.2s;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #15803d 0%, #22c55e 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(21, 128, 61, 0.2);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 14px; font-weight: 700; color: #0b044d; margin-bottom: 3px;">Recruitment</p>
                    <p style="font-size: 12px; color: #9999bb;">8 open positions · 45 applicants</p>
                </div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </div>

        <div class="stat-card" style="cursor: pointer; transition: all 0.2s;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #d9bb00 0%, #fbbf24 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(217, 187, 0, 0.2);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 14px; font-weight: 700; color: #0b044d; margin-bottom: 3px;">Training Programs</p>
                    <p style="font-size: 12px; color: #9999bb;">5 ongoing · 83 participants</p>
                </div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </div>

        <div class="stat-card" style="cursor: pointer; transition: all 0.2s;">
            <div style="display: flex; align-items: center; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #6b3fa0 0%, #8b5cf6 100%); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(107, 63, 160, 0.2);">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 14px; font-weight: 700; color: #0b044d; margin-bottom: 3px;">Performance Reviews</p>
                    <p style="font-size: 12px; color: #9999bb;">12 pending evaluations</p>
                </div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </div>
        </div>
    </section>

    <!-- Tabs -->
    <div style="display: flex; gap: 8px; margin-bottom: 20px; border-bottom: 2px solid #f0effe; padding-bottom: 0;">
        <button class="tab-btn active" data-tab="overview">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
            Overview
        </button>
        <button class="tab-btn" data-tab="payroll">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            Recent Payroll
        </button>
        <button class="tab-btn" data-tab="activity">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Recent Activity
        </button>
    </div>

    <!-- Overview Tab -->
    <section class="table-section tab-content active" id="overview">
        <div class="table-header">
            <div>
                <h3 class="table-title">Recent Activity</h3>
                <p class="table-sub">Latest updates and actions across all modules</p>
            </div>
        </div>
        <div style="padding: 20px 24px;">
            @php
            $activities = [
                ['type' => 'success', 'title' => 'Payroll Processed', 'desc' => 'Jun 16-30, 2025 payroll completed for 348 employees', 'time' => '2 hours ago', 'icon' => 'checkCircle'],
                ['type' => 'info', 'title' => 'New Job Posting', 'desc' => 'Administrative Officer IV position opened in Office of the Mayor', 'time' => '5 hours ago', 'icon' => 'clipboard'],
                ['type' => 'warning', 'title' => 'Pending Evaluations', 'desc' => '12 performance evaluations due by Jun 30, 2025', 'time' => '1 day ago', 'icon' => 'clock'],
                ['type' => 'info', 'title' => 'Training Completed', 'desc' => '30 employees completed Customer Service Excellence training', 'time' => '2 days ago', 'icon' => 'bookOpen'],
                ['type' => 'success', 'title' => 'New Employee Onboarded', 'desc' => 'Roberto T. Flores (PGS-0310) successfully onboarded', 'time' => '3 days ago', 'icon' => 'user'],
            ];
            @endphp

            @foreach($activities as $i => $activity)
            <div style="display: flex; gap: 14px; padding: 14px 0; border-bottom: {{ $i < 4 ? '1px solid #f7f6ff' : 'none' }};">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: {{ $activity['type'] === 'success' ? '#e8f9ef' : ($activity['type'] === 'warning' ? '#fefce8' : '#f0effe') }}; display: flex; align-items: center; justify-content: center; flex-shrink: 0; color: {{ $activity['type'] === 'success' ? '#15803d' : ($activity['type'] === 'warning' ? '#d9bb00' : '#0b044d') }};">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        @if($activity['icon'] === 'checkCircle')
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        @elseif($activity['icon'] === 'clipboard')
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                        @elseif($activity['icon'] === 'clock')
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        @elseif($activity['icon'] === 'bookOpen')
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                        @elseif($activity['icon'] === 'user')
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        @endif
                    </svg>
                </div>
                <div style="flex: 1;">
                    <p style="font-size: 13.5px; font-weight: 600; color: #0b044d; margin-bottom: 3px;">{{ $activity['title'] }}</p>
                    <p style="font-size: 12px; color: #6b6a8a; line-height: 1.5;">{{ $activity['desc'] }}</p>
                    <p style="font-size: 11px; color: #aaa8cc; margin-top: 4px;">{{ $activity['time'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </section>

    <!-- Payroll Tab -->
    <section class="table-section tab-content" id="payroll">
        <div class="table-header">
            <div>
                <h3 class="table-title">Payroll Register — Jun 16–30, 2025</h3>
                <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: Jun 30, 2025</p>
            </div>
            <div class="table-actions">
                <select class="filter-select">
                    <option>All Departments</option>
                    <option>Office of the Mayor</option>
                    <option>Municipal Health Office</option>
                </select>
                <select class="filter-select">
                    <option>June 2025</option>
                    <option>May 2025</option>
                </select>
                <button class="btn-export">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </button>
                <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    Process Payroll
                </button>
            </div>
        </div>

        <div class="table-wrapper">
            <table class="payroll-table">
                <thead>
                    <tr>
                        <th>Personnel</th>
                        <th>Position</th>
                        <th>Department / Office</th>
                        <th>Basic Pay</th>
                        <th>Deductions</th>
                        <th>Net Pay</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                    $payrollData = [
                        ['id' => 'PGS-0041', 'name' => 'Maria B. Santos', 'position' => 'Administrative Officer IV', 'dept' => 'Office of the Mayor', 'basic' => '₱21,079.50', 'deductions' => '₱4,215.50', 'net' => '₱16,864.00', 'status' => 'Processed'],
                        ['id' => 'PGS-0082', 'name' => 'Juan P. dela Cruz', 'position' => 'Municipal Engineer II', 'dept' => 'Office of the Mun. Engineer', 'basic' => '₱19,042.50', 'deductions' => '₱3,809.00', 'net' => '₱15,233.50', 'status' => 'Processed'],
                        ['id' => 'PGS-0115', 'name' => 'Ana R. Reyes', 'position' => 'Nurse II', 'dept' => 'Municipal Health Office', 'basic' => '₱16,921.50', 'deductions' => '₱3,384.00', 'net' => '₱13,537.50', 'status' => 'Pending'],
                    ];
                    $avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#0b044d'];
                    @endphp

                    @foreach($payrollData as $i => $row)
                    <tr>
                        <td>
                            <div class="emp-cell">
                                <div class="emp-avatar" style="background: {{ $avatarColors[$i % count($avatarColors)] }};">
                                    {{ strtoupper(substr($row['name'], 0, 1) . substr(explode(' ', $row['name'])[1] ?? '', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="emp-name">{{ $row['name'] }}</p>
                                    <p class="emp-id">{{ $row['id'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="position-cell">{{ $row['position'] }}</td>
                        <td><span class="dept-tag">{{ $row['dept'] }}</span></td>
                        <td class="pay-cell">{{ $row['basic'] }}</td>
                        <td class="deduction">{{ $row['deductions'] }}</td>
                        <td class="net-pay">{{ $row['net'] }}</td>
                        <td><span class="badge-status {{ strtolower(str_replace(' ', '-', $row['status'])) }}">{{ $row['status'] }}</span></td>
                        <td><button class="btn-view">View</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- Activity Tab -->
    <section class="table-section tab-content" id="activity">
        <div class="table-header">
            <div>
                <h3 class="table-title">System Activity Log</h3>
                <p class="table-sub">Comprehensive activity tracking across all HRIS modules</p>
            </div>
        </div>
        <div style="padding: 20px 24px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 20px;">
                @php
                $modules = [
                    ['module' => 'Recruitment', 'count' => 8, 'color' => '#15803d'],
                    ['module' => 'Training', 'count' => 5, 'color' => '#d9bb00'],
                    ['module' => 'Performance', 'count' => 12, 'color' => '#6b3fa0'],
                    ['module' => 'Payroll', 'count' => 348, 'color' => '#8e1e18'],
                ];
                @endphp

                @foreach($modules as $mod)
                <div style="background: #f7f6ff; border-radius: 10px; padding: 16px; display: flex; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 10px; background: {{ $mod['color'] }}15; border: 2px solid {{ $mod['color'] }}; display: flex; align-items: center; justify-content: center;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="{{ $mod['color'] }}" stroke-width="2"><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                    <div>
                        <p style="font-size: 11px; color: #9999bb; font-weight: 600; margin-bottom: 2px;">{{ $mod['module'] }}</p>
                        <p style="font-size: 20px; font-weight: 800; color: {{ $mod['color'] }};">{{ $mod['count'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <p style="font-size: 12px; color: #9999bb; text-align: center; padding: 20px;">Detailed activity logs and audit trails available in Reports module</p>
        </div>
    </section>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});
</script>
@endsection
