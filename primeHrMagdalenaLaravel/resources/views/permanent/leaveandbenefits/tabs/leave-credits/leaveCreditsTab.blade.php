<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Leave Credits Balance</h3>
            <p class="table-sub">Current year leave balances and usage · Updated in real-time</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterLeaveCategory" onchange="filterLeaveCredits()">
                <option value="all">All Leave Types</option>
                <option value="accrued">Accrued Only</option>
                <option value="fixed">Fixed Only</option>
                <option value="available">With Balance</option>
            </select>
            <select class="filter-select" id="itemsPerPage" onchange="changeItemsPerPage()" style="width: auto;">
                <option value="10">Show 10</option>
                <option value="20" selected>Show 20</option>
                <option value="50">Show 50</option>
                <option value="all">Show All</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Report
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Leave Type</th>
                    <th>Total Credits</th>
                    <th>Used</th>
                    <th>Pending</th>
                    <th>Available</th>
                    <th>Type</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes ?? [] as $type)
                @php
                    // Get the balance for this leave type
                    $balance = $type->leaveBalances->first();
                    $totalCredits = $balance ? $balance->total_credits : 0;
                    $used = $balance ? $balance->used_credits : 0;
                    $pending = $balance ? $balance->pending_credits : 0;
                    $available = $balance ? $balance->available_credits : 0;
                @endphp
                <tr class="leave-credit-row" data-type="{{ $type->is_accrued ? 'accrued' : 'fixed' }}" data-available="{{ $available }}">
                    <td>
                        <span class="badge-emptype" style="background: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }}; color: white; border-color: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }};">
                            {{ $type->leave_code }}
                        </span>
                    </td>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$loop->index % 6] }}; color: {{ ['#7c3aed', '#dc2626', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$loop->index % 6] }};">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
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
                    <td class="pay-cell">
                        {{ number_format($totalCredits, 1) }} days
                    </td>
                    <td class="deduction">
                        {{ number_format($used, 1) }}
                    </td>
                    <td class="ot-pay" style="color: #d9bb00; font-weight: 600;">
                        {{ number_format($pending, 1) }}
                    </td>
                    <td class="net-pay">
                        {{ number_format($available, 1) }}
                    </td>
                    <td>
                        @if($type->is_accrued)
                            <span class="badge-status processed">Accrued</span>
                        @else
                            <span class="badge-status pending">Fixed</span>
                        @endif
                    </td>
                    <td>
                        @php
                            $percentage = $totalCredits > 0 ? (($available / $totalCredits) * 100) : 0;
                        @endphp
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ $percentage }}%; height: 100%; background: {{ $percentage > 70 ? '#15803d' : ($percentage > 30 ? '#d9bb00' : '#8e1e18') }}; transition: width 0.3s;"></div>
                            </div>
                            <span style="font-size: 12px; color: #6b6a8a; font-weight: 600; min-width: 38px;">{{ number_format($percentage, 0) }}%</span>
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

    <div class="table-footer">
        <p id="leaveCreditsCount">Showing <strong>{{ $leaveTypes->count() }}</strong> of <strong>{{ $leaveTypes->count() }}</strong> leave types</p>
        <div class="pagination" id="leaveCreditspagination">
            <button class="page-btn" id="prevPageBtn" onclick="changeLeaveCreditsPage('prev')" disabled>‹</button>
            <button class="page-btn active" data-page="1">1</button>
            <button class="page-btn" id="nextPageBtn" onclick="changeLeaveCreditsPage('next')" disabled>›</button>
        </div>
    </div>
</section>
