{{-- Payslip History Table --}}
<div class="table-section">
    <div class="table-header">
        <div>
            <p class="table-title">Payslip History</p>
            <p class="table-sub">Recent payroll records</p>
        </div>
        <div class="table-actions">
            {{-- The applied filters are baked into the link server-side, so the
                 file matches what the table is showing rather than whatever is
                 sitting un-applied in the toolbar. The search term is the one
                 filter that never reaches the URL, so the handler appends it. --}}
            <button type="button" class="btn-export" onclick="exportPayslips()"
                    data-export-url="{{ route('employee.payslip.export', array_filter($filters ?? [])) }}">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            {{-- Same navy pill as "File Travel Order", via the shared variant in
                 glassSystem.css — this page ships no stylesheet of its own. --}}
            <button class="btn-export btn-export-solid" onclick="openModal()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                View Latest
            </button>
        </div>
    </div>

    {{-- A GET form rather than a JS filter: this table paginates server-side,
         so filtering in the browser would only ever narrow the five rows on
         the current page. --}}
    <form method="GET" action="{{ route('employee.payslip') }}" class="emp-filter-bar">
        <div class="emp-filter-group">
            <label class="emp-filter-label" for="payslipStartDate">Period From</label>
            <input type="date" id="payslipStartDate" name="start_date" class="filter-select"
                   value="{{ $filters['start_date'] ?? '' }}">
        </div>
        <div class="emp-filter-group">
            <label class="emp-filter-label" for="payslipEndDate">Period To</label>
            <input type="date" id="payslipEndDate" name="end_date" class="filter-select"
                   value="{{ $filters['end_date'] ?? '' }}">
        </div>
        <div class="emp-filter-group">
            <label class="emp-filter-label" for="payslipStatusFilter">Status</label>
            <select id="payslipStatusFilter" name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Pending</option>
                <option value="processed" @selected(($filters['status'] ?? '') === 'processed')>Processed</option>
            </select>
        </div>
        <div class="emp-filter-actions">
            <button type="submit" class="btn-export btn-export-solid">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Apply
            </button>
            <a href="{{ route('employee.payslip') }}" class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reset
            </a>
        </div>
    </form>

    <div class="table-wrapper">
        <table class="payroll-table payslip-history-table">
            <thead>
                <tr>
                    <th>Period</th>
                    <th>Basic Pay</th>
                    <th>Deductions</th>
                    <th>Net Pay</th>
                    <th>Pay Date</th>
                    <th>Status</th>
                    <th class="row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payslips as $payslip)
                <tr>
                    <td class="table-cell-period">{{ $payslip->period_start->format('M d') }}-{{ $payslip->period_end->format('d, Y') }}</td>
                    <td class="table-cell-basic">₱{{ number_format($payslip->basic_pay, 2) }}</td>
                    <td class="table-cell-deduct">₱{{ number_format($payslip->late_deduction + $payslip->undertime_deduction + $payslip->other_deductions, 2) }}</td>
                    <td class="net-pay">₱{{ number_format($payslip->net_pay, 2) }}</td>
                    <td class="table-cell-date">{{ $payslip->period_end->format('M d, Y') }}</td>
                    <td>
                        {{-- `status` defaults to 'draft' on this table, and draft
                             means the same thing pending does: payroll has not
                             settled it. Testing for 'pending' alone badged an
                             untouched draft as "Processed", which is a statement
                             about somebody's pay that is not true — and the new
                             Status filter and the export both group the two the
                             way the rest of the system does. --}}
                        @if(in_array($payslip->status, ['draft', 'pending'], true))
                            <span class="badge-status pending">Pending</span>
                        @else
                            <span class="badge-status processed">Processed</span>
                        @endif
                    </td>
                    <td class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="payslipRowMenu{{ $payslip->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for this payslip">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="payslipRowMenu{{ $payslip->id }}" role="menu" aria-label="Payslip actions">
                            <button type="button" role="menuitem" class="row-menu-item"
                                    onclick="closeRowMenu(); viewPayslipDetail({{ $payslip->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                View details
                            </button>
                            <button type="button" role="menuitem" class="row-menu-item"
                                    onclick="closeRowMenu(); printPayslipDirect({{ $payslip->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>
                                </svg>
                                Print payslip
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="eh-empty-cell">No payslip records found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span>Showing <strong>{{ $payslips->firstItem() ?? 0 }}–{{ $payslips->lastItem() ?? 0 }}</strong> of <strong>{{ $payslips->total() }}</strong> payslips</span>
        <div class="pagination">
            @if($payslips->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $payslips->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach($payslips->getUrlRange(1, $payslips->lastPage()) as $page => $url)
                <a href="{{ $url }}" class="page-btn {{ $page == $payslips->currentPage() ? 'active' : '' }}">{{ $page }}</a>
            @endforeach

            @if($payslips->hasMorePages())
                <a href="{{ $payslips->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</div>
