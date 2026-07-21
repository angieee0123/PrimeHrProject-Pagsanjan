<div class="table-header">
    <div>
        <h3 class="table-title">Payslip Management</h3>
        <p class="table-sub">View and manage all generated payslips</p>
    </div>
    <div class="table-actions">
        <select id="statusFilter" class="filter-select" onchange="filterPayslips()">
            <option value="">All Status</option>
            <option value="pending">Pending/Draft</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
        <button class="btn-export" onclick="exportPayslips()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Period</th>
                <th>Basic Pay</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="payslipsTableBody">
            @forelse($salaryComputations ?? [] as $computation)
            <tr data-status="{{ $computation->status }}">
                <td>{{ $computation->employee->employee_id ?? 'N/A' }}</td>
                <td>
                    <div class="emp-cell">
                        @if($computation->employee->photo ?? false)
                            <img src="{{ $computation->employee->photo }}" alt="{{ $computation->employee->first_name }}" class="pr-avatar-img">
                        @else
                            @php
                                $colors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#150c63', '#56547a'];
                                $empIndex = $loop->index % 6;
                            @endphp
                            <div class="pr-emp-avatar" style="background: {{ $colors[$empIndex] }};">
                                {{ strtoupper(substr($computation->employee->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($computation->employee->last_name ?? 'A', 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <p class="emp-name">{{ $computation->employee->first_name ?? '' }} {{ $computation->employee->last_name ?? '' }}</p>
                            <p class="emp-id">{{ $computation->employee->employee_id ?? 'N/A' }}</p>
                        </div>
                    </div>
                </td>
                <td>{{ $computation->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</td>
                <td class="table-cell-period">{{ $computation->period_start->format('M d') }}-{{ $computation->period_end->format('d, Y') }}</td>
                <td class="pay-cell">₱{{ number_format($computation->basic_pay ?? 0, 2) }}</td>
                <td class="deduction">₱{{ number_format(($computation->late_deduction ?? 0) + ($computation->undertime_deduction ?? 0) + ($computation->other_deductions ?? 0), 2) }}</td>
                <td class="net-pay">₱{{ number_format($computation->net_pay ?? 0, 2) }}</td>
                <td>
                    @if($computation->status === 'pending' || $computation->status === 'draft')
                        <span class="badge-status pending">Pending</span>
                    @elseif($computation->status === 'approved')
                        <span class="badge-status approved">Approved</span>
                    @elseif($computation->status === 'rejected')
                        <span class="badge-status rejected">Rejected</span>
                    @else
                        <span class="badge-status processed">{{ ucfirst($computation->status) }}</span>
                    @endif
                </td>
                <td>
                    <div class="row-actions">
                        <button class="btn-action btn-view" onclick="viewPayslipDetail({{ $computation->id }})" title="View Details">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        <button class="btn-action btn-print" onclick="printPayslipDirect({{ $computation->id }})" title="Print">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        </button>
                        @if($computation->status === 'pending' || $computation->status === 'draft')
                        <button class="btn-action btn-approve" onclick="approvePayslip({{ $computation->id }})" title="Approve">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                        <button class="btn-action btn-reject" onclick="rejectPayslip({{ $computation->id }})" title="Reject">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="pr-empty-cell">
                    No payslips generated yet. Go to "Generate Payroll" tab to create payslips.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(isset($salaryComputations) && $salaryComputations->count() > 0)
<div class="table-footer">
    <span>Showing <strong>{{ $salaryComputations->firstItem() ?? 0 }}–{{ $salaryComputations->lastItem() ?? 0 }}</strong> of <strong>{{ $salaryComputations->total() }}</strong> payslips</span>
    <div class="pagination">
        @if($salaryComputations->onFirstPage())
            <button class="page-btn" disabled>‹</button>
        @else
            <a href="{{ $salaryComputations->appends(request()->except('page'))->previousPageUrl() }}" class="page-btn">‹</a>
        @endif
        
        @foreach($salaryComputations->getUrlRange(1, $salaryComputations->lastPage()) as $page => $url)
            <a href="{{ $salaryComputations->appends(request()->except('page'))->url($page) }}" class="page-btn {{ $page == $salaryComputations->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        
        @if($salaryComputations->hasMorePages())
            <a href="{{ $salaryComputations->appends(request()->except('page'))->nextPageUrl() }}" class="page-btn">›</a>
        @else
            <button class="page-btn" disabled>›</button>
        @endif
    </div>
</div>
@endif

@push('scripts')
    @vite('resources/js/admin/payroll/payslip-management.js')
@endpush
