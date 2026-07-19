<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Credits Balance</h3>
            <p class="table-sub">Track your leave usage and accumulated credits · Last updated in real-time</p>
        </div>
        <div class="table-actions">
            <!-- View Toggle -->
            <div class="lb-flex-gap-8">
                <button class="view-toggle-btn lb-toggle-btn {{ $viewMode === 'current' ? 'active' : '' }}" onclick="switchView('current')">
                    Current Year
                </button>
                <button class="view-toggle-btn lb-toggle-btn {{ $viewMode === 'history' ? 'active' : '' }}" onclick="switchView('history')">
                    Yearly History
                </button>
            </div>

            <!-- Current Year View Filters -->
            @if($viewMode === 'current')
            <div class="lb-flex-wrap-gap-8">
                <div class="lb-date-filter-box">
                    <input type="date" id="filterStartDate" class="filter-input" placeholder="Start Date">
                    <span class="lb-text-muted-12">to</span>
                    <input type="date" id="filterEndDate" class="filter-input" placeholder="End Date">
                </div>

                <select class="filter-select" id="filterLeaveType">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes ?? [] as $type)
                    <option value="{{ $type->leave_code }}">{{ $type->leave_name }}</option>
                    @endforeach
                </select>

                <button class="btn-filter lb-btn-apply" onclick="applyLeaveCreditsFilters()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Apply
                </button>

                <button class="btn-clear lb-btn-clear lb-hidden" id="clearBtn" onclick="clearLeaveCreditsFilters()">
                    Clear
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Filter Summary -->
    <div id="filterSummary" class="lb-filter-summary lb-hidden">
        <strong>Active Filters:</strong> <span id="filterSummaryText"></span>
    </div>

    <!-- CURRENT YEAR VIEW -->
    @if($viewMode === 'current')
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Leave Type</th>
                    <th>Total Credits</th>
                    <th>Used <span class="lb-text-soft-12">(in period)</span></th>
                    <th>Pending</th>
                    <th>Available</th>
                    <th>Type</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes ?? [] as $type)
                @php
                    $balance = $type->leaveBalances->first();
                    if ($balance) {
                        $totalCredits = (float) $balance->total_credits;
                        $used = (float) $balance->used_credits;
                        $pending = (float) $balance->pending_credits;
                        $available = (float) $balance->available_credits;
                    } else {
                        $totalCredits = $used = $pending = $available = 0;
                    }
                    $displayedUsage = isset($type->usage_in_period) ? (float)$type->usage_in_period : $used;
                    $hasDateFilter = request('start_date') && request('end_date');
                    $percentage = $totalCredits > 0 ? (($available / $totalCredits) * 100) : 0;
                @endphp
                <tr class="leave-credit-row" data-type="{{ $type->is_accrued ? 'accrued' : 'fixed' }}" data-available="{{ $available }}" data-leave-code="{{ $type->leave_code }}">
                    <td>
                        <span class="badge-emptype" style="background: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }}; color: white; border-color: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }};">
                            {{ $type->leave_code }}
                        </span>
                    </td>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$loop->index % 6] }}; color: {{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }};">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                </svg>
                            </div>
                            <div>
                                <p class="emp-name">{{ $type->leave_name }}</p>
                                @if($type->attachment_info)
                                <p class="emp-id">{{ Str::limit($type->attachment_info, 50) }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="pay-cell" data-total="{{ $totalCredits }}">
                        <strong>{{ number_format($totalCredits, 2) }}</strong> <span class="lb-text-soft-12">days</span>
                    </td>
                    <td class="deduction" @if($hasDateFilter) title="Used from {{ request('start_date') }} to {{ request('end_date') }}" @else title="Total used in {{ $selectedYear }}" @endif>
                        {{ number_format($displayedUsage, 2) }}
                    </td>
                    <td class="ot-pay lb-c-amber">
                        {{ number_format($pending, 2) }}
                    </td>
                    <td class="net-pay">
                        {{ number_format($available, 2) }}
                    </td>
                    <td>
                        @if($type->is_accrued)
                            <span class="badge-status processed">Accrued</span>
                        @else
                            <span class="badge-status pending">Fixed</span>
                        @endif
                    </td>
                    <td>
                        <div class="lb-flex-gap-8">
                            <div class="lb-progress-track">
                                <div class="lb-progress-fill" style="width: {{ min($percentage, 100) }}%; background: {{ $percentage > 70 ? '#15803d' : ($percentage > 30 ? '#d9bb00' : '#8e1e18') }};"></div>
                            </div>
                            <span class="lb-pct-text">{{ number_format(min($percentage, 100), 0) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="lb-empty-cell">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="lb-empty-icon">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="lb-empty-title">No leave credits available</p>
                        <p class="lb-empty-sub">Leave balances will appear here once initialized</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- YEARLY HISTORY VIEW -->
    @else
    <div class="table-wrapper">
        <!-- Leave Filing Statistics Section -->
        @if($leaveStatsHistory && count($leaveStatsHistory) > 0)
        <div class="lb-mb-32">
            <div class="lb-stats-banner">
                <div class="lb-flex-gap-12">
                    <div class="lb-stat-icon-box">
                        📊
                    </div>
                    <div>
                        <h4 class="lb-banner-title">Leave Filing & Tardiness Statistics</h4>
                        <p class="lb-banner-sub">Monthly breakdown of leave filings by type and tardiness incidents</p>
                    </div>
                </div>
            </div>

            <table class="lb-table">
                <thead>
                    <tr class="lb-thead-row">
                        <th class="lb-th-left">Month</th>
                        <th class="lb-th-left">Leave Filings by Type</th>
                        <th class="lb-th-center">Total Leaves</th>
                        <th class="lb-th-center">Tardiness Count</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($leaveStatsHistory as $monthYear => $stat)
                    @php
                        $totalLeaves = collect($stat['leaves_by_type'])->sum();
                        $dateObj = DateTime::createFromFormat('Y-m', $monthYear);
                        $monthName = $dateObj->format('F Y');
                    @endphp
                    <tr class="lb-tr-row">
                        <td class="lb-td lb-fw600 lb-c-dark">{{ $monthName }}</td>
                        <td class="lb-td">
                            <div class="lb-flex-wrap-gap-6">
                                @foreach($stat['leaves_by_type'] as $leaveCode => $count)
                                <span class="lb-pill-blue">
                                    {{ $leaveCode }}: <strong>{{ $count }}</strong>
                                </span>
                                @endforeach
                            </div>
                        </td>
                        <td class="lb-td lb-ta-center">
                            <span class="lb-pill-cyan">
                                {{ $totalLeaves }}
                            </span>
                        </td>
                        <td class="lb-td lb-ta-center">
                            @if($stat['tardiness_count'] > 0)
                            <span class="lb-pill-red">
                                ⚠️ {{ $stat['tardiness_count'] }}
                            </span>
                            @else
                            <span class="lb-text-soft-12">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
        @forelse($leaveTypes ?? [] as $type)
        @php
            $history = $leaveHistory[$type->leave_code] ?? [];
            $totalAccumulated = collect($history)->sum('total_credits');
            $totalUsed = collect($history)->sum('used_credits');
            $totalRemaining = $totalAccumulated - $totalUsed;
        @endphp
        <div class="lb-mb-32">
            <div style="background: linear-gradient(135deg, {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$loop->index % 6] }} 0%, transparent 100%); padding: 16px; border-radius: 8px; border-left: 4px solid {{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }}; margin-bottom: 16px;">
                <div class="lb-flex-gap-12">
                    <div class="lb-stat-icon-box-sm" style="background: {{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }};">
                        {{ substr($type->leave_code, 0, 1) }}
                    </div>
                    <div>
                        <h4 class="lb-banner-title">{{ $type->leave_name }} ({{ $type->leave_code }})</h4>
                        <p class="lb-banner-sub">
                            Total Accumulated: <strong>{{ number_format($totalAccumulated, 2) }}</strong> days |
                            Total Used: <strong class="lb-c-red">{{ number_format($totalUsed, 2) }}</strong> days |
                            Remaining: <strong class="lb-c-emerald">{{ number_format($totalRemaining, 2) }}</strong> days
                        </p>
                    </div>
                </div>
            </div>

            <table class="lb-table">
                <thead>
                    <tr class="lb-thead-row">
                        <th class="lb-th-left">Year</th>
                        <th class="lb-th-right">Annual Alloc</th>
                        <th class="lb-th-right">Used in Year</th>
                        <th class="lb-th-right">Carried Over</th>
                        <th class="lb-th-right">Running Total</th>
                        <th class="lb-th-right">Year-End Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $record)
                    <tr class="lb-tr-row">
                        <td class="lb-td lb-fw600 lb-c-dark">{{ $record->year }}</td>
                        <td class="lb-td lb-ta-right lb-fw600 lb-td-alloc">
                            <span title="Amount credited in {{ $record->year }}">{{ number_format($record->total_credits, 2) }}</span>
                        </td>
                        <td class="lb-td lb-ta-right lb-fw600 lb-td-used">
                            <span title="Utilized in {{ $record->year }}">{{ number_format($record->used_credits, 2) }}</span>
                        </td>
                        <td class="lb-td lb-ta-right lb-fw600 lb-td-carried">
                            <span title="Carried forward to next year">{{ number_format($record->carried_over, 2) }}</span>
                        </td>
                        <td class="lb-td lb-ta-right lb-fw600 lb-td-running">
                            <span title="Accumulated credits up to {{ $record->year }}">{{ number_format($record->total_credits + $record->carried_over, 2) }}</span>
                        </td>
                        <td class="lb-td lb-ta-right">
                            <div class="lb-inline-flex-gap-8">
                                <div class="lb-progress-track lb-progress-track-sm">
                                    @php
                                        $yearEndBalance = $record->total_credits + $record->carried_over - $record->used_credits;
                                        $barPercentage = ($yearEndBalance / max($record->total_credits + $record->carried_over, 1)) * 100;
                                    @endphp
                                    <div class="lb-progress-fill" style="width: {{ min($barPercentage, 100) }}%; background: {{ $yearEndBalance > 0 ? '#10b981' : '#ef4444' }};"></div>
                                </div>
                                <span class="lb-fw600 lb-minw-50" style="color: {{ $yearEndBalance > 0 ? '#059669' : '#dc2626' }};">
                                    {{ number_format($yearEndBalance, 2) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="lb-empty-td">No history available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @empty
        <div class="lb-empty-cell">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="lb-empty-icon">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="lb-empty-title">No leave history available</p>
        </div>
        @endforelse
    </div>
    @endif
</section>
