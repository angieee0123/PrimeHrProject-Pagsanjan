<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Credits Balance</h3>
            <p class="table-sub">Track your leave usage and accumulated credits · Last updated in real-time</p>
        </div>
        <div class="table-actions">
            <!-- View Toggle -->
            <div style="display: flex; gap: 8px; align-items: center;">
                <button class="view-toggle-btn {{ $viewMode === 'current' ? 'active' : '' }}" onclick="switchView('current')" style="padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; background: {{ $viewMode === 'current' ? '#0284c7' : 'white' }}; color: {{ $viewMode === 'current' ? 'white' : '#6b7280' }}; transition: all 0.2s;">
                    Current Year
                </button>
                <button class="view-toggle-btn {{ $viewMode === 'history' ? 'active' : '' }}" onclick="switchView('history')" style="padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; background: {{ $viewMode === 'history' ? '#0284c7' : 'white' }}; color: {{ $viewMode === 'history' ? 'white' : '#6b7280' }}; transition: all 0.2s;">
                    Yearly History
                </button>
            </div>

            <!-- Current Year View Filters -->
            @if($viewMode === 'current')
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <div style="display: flex; gap: 6px; align-items: center; background: white; border: 1px solid #e5e7eb; border-radius: 6px; padding: 6px 10px;">
                    <input type="date" id="filterStartDate" class="filter-input" style="border: none; padding: 4px 0; background: none;" placeholder="Start Date">
                    <span style="color: #9ca3af; font-size: 12px;">to</span>
                    <input type="date" id="filterEndDate" class="filter-input" style="border: none; padding: 4px 0; background: none;" placeholder="End Date">
                </div>

                <select class="filter-select" id="filterLeaveType">
                    <option value="">All Leave Types</option>
                    @foreach($leaveTypes ?? [] as $type)
                    <option value="{{ $type->leave_code }}">{{ $type->leave_name }}</option>
                    @endforeach
                </select>

                <button class="btn-filter" onclick="applyLeaveFilters()" style="background: #0284c7; color: white; padding: 6px 14px; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    Apply
                </button>

                <button class="btn-clear" id="clearBtn" onclick="clearLeaveFilters()" style="background: #f3f4f6; color: #6b7280; padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; display: none;">
                    Clear
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Filter Summary -->
    <div id="filterSummary" style="display: none; background: #f0f9ff; border-left: 4px solid #0284c7; padding: 12px 16px; margin: 0 0 16px 0; border-radius: 4px; font-size: 13px; color: #0c4a6e;">
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
                    <th>Used <span style="font-size: 11px; color: #6b7280;">(in period)</span></th>
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
                        <strong>{{ number_format($totalCredits, 2) }}</strong> <span style="font-size: 11px; color: #6b7280;">days</span>
                    </td>
                    <td class="deduction" @if($hasDateFilter) title="Used from {{ request('start_date') }} to {{ request('end_date') }}" @else title="Total used in {{ $selectedYear }}" @endif>
                        {{ number_format($displayedUsage, 2) }}
                    </td>
                    <td class="ot-pay" style="color: #d9bb00; font-weight: 600;">
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
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ min($percentage, 100) }}%; height: 100%; background: {{ $percentage > 70 ? '#15803d' : ($percentage > 30 ? '#d9bb00' : '#8e1e18') }}; transition: width 0.3s;"></div>
                            </div>
                            <span style="font-size: 12px; color: #6b6a8a; font-weight: 600; min-width: 38px;">{{ number_format(min($percentage, 100), 0) }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No leave credits available</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #9ca3af;">Leave balances will appear here once initialized</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- YEARLY HISTORY VIEW -->
    @else
    <div class="table-wrapper">
        @forelse($leaveTypes ?? [] as $type)
        @php
            $history = $leaveHistory[$type->leave_code] ?? [];
            $totalAccumulated = collect($history)->sum('total_credits');
            $totalUsed = collect($history)->sum('used_credits');
            $totalRemaining = $totalAccumulated - $totalUsed;
        @endphp
        <div style="margin-bottom: 32px;">
            <div style="background: linear-gradient(135deg, {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$loop->index % 6] }} 0%, transparent 100%); padding: 16px; border-radius: 8px; border-left: 4px solid {{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }}; margin-bottom: 16px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="width: 40px; height: 40px; background: {{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }}; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                        {{ substr($type->leave_code, 0, 1) }}
                    </div>
                    <div>
                        <h4 style="margin: 0; font-size: 15px; font-weight: 600; color: #1f2937;">{{ $type->leave_name }} ({{ $type->leave_code }})</h4>
                        <p style="margin: 4px 0 0 0; font-size: 12px; color: #6b7280;">
                            Total Accumulated: <strong>{{ number_format($totalAccumulated, 2) }}</strong> days | 
                            Total Used: <strong style="color: #dc2626;">{{ number_format($totalUsed, 2) }}</strong> days | 
                            Remaining: <strong style="color: #10b981;">{{ number_format($totalRemaining, 2) }}</strong> days
                        </p>
                    </div>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f9fafb; border-bottom: 2px solid #e5e7eb;">
                        <th style="padding: 12px; text-align: left; font-size: 12px; font-weight: 600; color: #4b5563;">Year</th>
                        <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600; color: #4b5563;">Annual Alloc</th>
                        <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600; color: #4b5563;">Used in Year</th>
                        <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600; color: #4b5563;">Carried Over</th>
                        <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600; color: #4b5563;">Running Total</th>
                        <th style="padding: 12px; text-align: right; font-size: 12px; font-weight: 600; color: #4b5563;">Year-End Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($history as $record)
                    <tr style="border-bottom: 1px solid #e5e7eb; transition: background 0.2s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background=''">
                        <td style="padding: 14px 12px; font-weight: 600; color: #1f2937;">{{ $record->year }}</td>
                        <td style="padding: 14px 12px; text-align: right; color: #0284c7; font-weight: 600;">
                            <span title="Amount credited in {{ $record->year }}">{{ number_format($record->total_credits, 2) }}</span>
                        </td>
                        <td style="padding: 14px 12px; text-align: right; color: #dc2626; font-weight: 600;">
                            <span title="Utilized in {{ $record->year }}">{{ number_format($record->used_credits, 2) }}</span>
                        </td>
                        <td style="padding: 14px 12px; text-align: right; color: #7c3aed; font-weight: 600;">
                            <span title="Carried forward to next year">{{ number_format($record->carried_over, 2) }}</span>
                        </td>
                        <td style="padding: 14px 12px; text-align: right; color: #10b981; font-weight: 600;">
                            <span title="Accumulated credits up to {{ $record->year }}">{{ number_format($record->total_credits + $record->carried_over, 2) }}</span>
                        </td>
                        <td style="padding: 14px 12px; text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 8px;">
                                <div style="width: 80px; height: 6px; background: #f0effe; border-radius: 3px; overflow: hidden;">
                                    @php
                                        $yearEndBalance = $record->total_credits + $record->carried_over - $record->used_credits;
                                        $barPercentage = ($yearEndBalance / max($record->total_credits + $record->carried_over, 1)) * 100;
                                    @endphp
                                    <div style="width: {{ min($barPercentage, 100) }}%; height: 100%; background: {{ $yearEndBalance > 0 ? '#10b981' : '#ef4444' }}; transition: width 0.3s;"></div>
                                </div>
                                <span style="font-weight: 600; color: {{ $yearEndBalance > 0 ? '#059669' : '#dc2626' }}; min-width: 50px;">
                                    {{ number_format($yearEndBalance, 2) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" style="padding: 20px; text-align: center; color: #9ca3af;">No history available</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @empty
        <div style="text-align: center; padding: 60px 20px;">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p style="margin: 0; font-size: 15px; color: #6b7280; font-weight: 500;">No leave history available</p>
        </div>
        @endforelse
    </div>
    @endif
</section>

<style>
    .filter-input { padding: 4px 8px; border: none; background: transparent; font-size: 13px; }
    .filter-input:focus { outline: none; }
    .filter-select { padding: 6px 10px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 13px; background: white; cursor: pointer; }
    .filter-select:focus { outline: none; border-color: #0284c7; box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1); }
    .btn-filter { transition: all 0.2s; }
    .btn-filter:hover { background: #0369a1 !important; }
    .btn-clear { transition: all 0.2s; }
    .btn-clear:hover { background: #e5e7eb !important; border-color: #d1d5db !important; }
</style>

<script>
function switchView(mode) {
    const url = new URL(window.location);
    url.searchParams.set('view_mode', mode);
    window.location = url;
}

function applyLeaveFilters() {
    const startDate = document.getElementById('filterStartDate').value;
    const endDate = document.getElementById('filterEndDate').value;
    const leaveType = document.getElementById('filterLeaveType').value;
    
    if (startDate && endDate && startDate > endDate) {
        alert('Start date must be before end date');
        return;
    }
    
    let params = new URLSearchParams();
    params.append('view_mode', 'current');
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    if (leaveType) params.append('leave_type', leaveType);
    
    const url = new URL(window.location);
    url.search = params.toString();
    window.location = url;
}

function clearLeaveFilters() {
    document.getElementById('filterStartDate').value = '';
    document.getElementById('filterEndDate').value = '';
    document.getElementById('filterLeaveType').value = '';
    document.getElementById('filterSummary').style.display = 'none';
    document.getElementById('clearBtn').style.display = 'none';
    window.location = window.location.pathname + '?view_mode=current';
}

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const startDate = params.get('start_date');
    const endDate = params.get('end_date');
    const leaveType = params.get('leave_type');
    
    if (startDate) document.getElementById('filterStartDate').value = startDate;
    if (endDate) document.getElementById('filterEndDate').value = endDate;
    if (leaveType) document.getElementById('filterLeaveType').value = leaveType;
    
    if (startDate || endDate || leaveType) {
        let summary = '';
        if (startDate && endDate) summary += `📅 ${startDate} to ${endDate}`;
        if (leaveType) summary += (summary ? ' | ' : '') + `📋 ${leaveType}`;
        document.getElementById('filterSummaryText').textContent = summary;
        document.getElementById('filterSummary').style.display = 'block';
        document.getElementById('clearBtn').style.display = 'inline-block';
    }
});
</script>
