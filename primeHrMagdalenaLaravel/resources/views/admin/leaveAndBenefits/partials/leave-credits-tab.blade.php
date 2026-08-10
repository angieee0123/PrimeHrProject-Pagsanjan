<section class="table-section" id="leave-credits-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Leave Credits Balance</h3>
            <p class="table-sub">
                Track all employees leave usage and accumulated credits · {{ count($employeeLeaveBalances ?? []) }} records · 
                @if(request('filter_credits_date_from') && request('filter_credits_date_to'))
                    As of {{ \Carbon\Carbon::parse(request('filter_credits_date_to'))->format('M d, Y') }}
                @elseif(request('filter_credits_year')) 
                    Year {{ request('filter_credits_year') }} 
                @else 
                    Most Recent Balances 
                @endif
            </p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Employee</th>
                    <th>Year</th>
                    <th>Leave Type</th>
                    <th>Total Credits</th>
                    <th>Used</th>
                    <th>Pending</th>
                    <th>Available</th>
                    <th>Type</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody id="leaveCreditsTableBody">
                @php
                    $groupedBalances = collect($employeeLeaveBalances ?? [])->groupBy('employee_id');
                @endphp
                
                @if($groupedBalances->count() > 0)
                    @foreach($groupedBalances as $employeeId => $balances)
                        @foreach($balances as $index => $balance)
                        @php
                            $leaveType = $allLeaveTypes->firstWhere('leave_code', $balance->leave_code);
                            $percentage = $balance->total_credits > 0 ? (($balance->available_credits / $balance->total_credits) * 100) : 0;
                        @endphp
                        <tr class="leave-credits-row" 
                            data-employee-id="{{ $balance->employee_id }}" 
                            data-leave-code="{{ $balance->leave_code }}"
                            data-year="{{ $balance->year }}"
                            data-type="{{ $leaveType && $leaveType->is_accrued ? 'accrued' : 'fixed' }}">
                            @if($index === 0)
                            <td data-label="Employee" style="text-align: left;" rowspan="{{ $balances->count() }}">
                                <div class="emp-cell">
                                    @if($balance->employee->photo)
                                        <img src="{{ $balance->employee->photo }}" alt="{{ $balance->employee->first_name }}" class="emp-avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid var(--gp-border);">
                                    @else
                                        <div class="emp-avatar" style="background: {{ $avatarColors[($balance->employee_id ?? 0) % count($avatarColors)] }}; width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border:2px solid var(--gp-border);">
                                            {{ strtoupper(substr($balance->employee->first_name ?? 'N', 0, 1) . substr($balance->employee->last_name ?? 'A', 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="emp-name">{{ $balance->employee->first_name ?? 'N/A' }} {{ $balance->employee->last_name ?? '' }}</p>
                                        <p class="emp-id">{{ $balance->employee->employee_id ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            @endif
                            <td data-label="Year" style="font-weight: 600; color: var(--gp-text-mid);">
                                {{ $balance->year }}
                            </td>
                            <td data-label="Leave Type">
                                <div class="emp-cell">
                                    <div class="emp-avatar" style="background: {{ ['#ede9fe', '#fee2e2', '#dbeafe', '#fef3c7', '#d1fae5', '#fce7f3'][$index % 6] }}; color: {{ ['#7c3aed', 'var(--theme-danger)', '#2563eb', '#f59e0b', '#10b981', '#ec4899'][$index % 6] }}; margin-left: 0;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="emp-name">{{ $leaveType->leave_name ?? $balance->leave_code }}</p>
                                        <p class="emp-id">{{ $balance->leave_code }}</p>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Total Credits" class="pay-cell">
                                <strong>{{ number_format($balance->total_credits, 2) }}</strong> <span style="font-size: 11px; color: var(--theme-neutral-700);">days</span>
                            </td>
                            <td data-label="Used" class="deduction">
                                {{ number_format($balance->used_credits, 2) }}
                            </td>
                            <td data-label="Pending" class="ot-pay" style="color: #c9a227; font-weight: 600;">
                                {{ number_format($balance->pending_credits, 2) }}
                            </td>
                            <td data-label="Available" class="net-pay">
                                {{ number_format($balance->available_credits, 2) }}
                            </td>
                            <td data-label="Type">
                                @if($leaveType && $leaveType->is_accrued)
                                    <span class="badge-status processed">Accrued</span>
                                @else
                                    <span class="badge-status pending">Fixed</span>
                                @endif
                            </td>
                            <td data-label="Progress">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="flex: 1; height: 6px; background: var(--gp-bg-tint-2); border-radius: 3px; overflow: hidden; min-width: 80px;">
                                        <div style="width: {{ min($percentage, 100) }}%; height: 100%; background: {{ $percentage > 70 ? 'var(--theme-success)' : ($percentage > 30 ? '#c9a227' : 'var(--theme-danger)') }}; transition: width 0.3s;"></div>
                                    </div>
                                    <span style="font-size: 12px; color: var(--gp-text-mid); font-weight: 600; min-width: 38px;">{{ number_format(min($percentage, 100), 0) }}%</span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                @else
                <tr>
                    <td colspan="9" style="text-align: center; padding: 60px 20px;">
                        <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 16px; display: block;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 15px; color: var(--theme-neutral-700); font-weight: 500;">No leave credits found</p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: var(--theme-neutral-600);">Employee leave balances will appear here once initialized</p>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p style="margin: 0;" id="leaveCreditsFooter">
                Showing <strong id="leaveCreditsRowStart">1</strong>-<strong id="leaveCreditsRowEnd">{{ min(10, count($employeeLeaveBalances ?? [])) }}</strong> of <strong id="leaveCreditsRowTotal">{{ count($employeeLeaveBalances ?? []) }}</strong> records
            </p>
            <select id="leaveCreditsRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeLeaveCreditsRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
                <option value="all">Show All</option>
            </select>
        </div>
        <div class="pagination" id="leaveCreditsPagination"></div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/admin/leaveAndBenefits/leave-credits-tab.js')
@endpush
