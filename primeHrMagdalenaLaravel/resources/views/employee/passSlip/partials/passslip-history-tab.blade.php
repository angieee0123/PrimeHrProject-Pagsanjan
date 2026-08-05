<section class="table-section" id="passslip-history-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Pass Slips</h3>
            <p class="table-sub">Track your pass slip requests · {{ $passSlips->total() ?? 0 }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterPassSlipStatus" onchange="filterPassSlips()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
            {{-- Same navy pill as "File Travel Order" — see the .ps-btn-primary-solid
                 rules in employeePassSlip.css for why both classes are needed. --}}
            <button class="btn-export ps-btn-primary-solid" onclick="openPassSlipModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                File Pass Slip
            </button>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="ps-session-alert-success">
        <div class="ps-flex-gap-8">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <p class="ps-flash-text-success">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="ps-session-alert-error">
        <div class="ps-flex-gap-8">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <p class="ps-flash-text-error">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @php
        $sortIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ps-sort-icon"><polyline points="18 15 12 9 6 15"></polyline></svg>';
    @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th onclick="sortPassSlips('reason')" class="ps-th-sort">Reason {!! $sortIcon !!}</th>
                    <th onclick="sortPassSlips('date')" class="ps-th-sort">Date {!! $sortIcon !!}</th>
                    <th class="ps-ta-center">Time Out</th>
                    <th class="ps-ta-center">Time In</th>
                    <th onclick="sortPassSlips('status')" class="ps-th-sort ps-ta-center">Status {!! $sortIcon !!}</th>
                    <th class="ps-ta-center row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passSlips ?? [] as $slip)
                <tr class="passslip-row" data-status="{{ $slip->status }}">
                    <td data-label="Reason" class="ps-td-semibold">
                        <div class="ps-flex-gap-8">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            {{ Str::limit($slip->reason, 40) }}
                        </div>
                    </td>
                    <td data-label="Date" class="ps-td-semibold">{{ $slip->date->format('M d, Y') }}</td>
                    <td data-label="Time Out" class="ps-td-muted-center">{{ \Carbon\Carbon::parse($slip->time_out)->format('g:i A') }}</td>
                    <td data-label="Time In" class="ps-td-muted-center">{{ $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('g:i A') : '—' }}</td>
                    <td data-label="Status" class="ps-ta-center">
                        @if($slip->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($slip->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($slip->status === 'rejected')
                            <span class="badge-status on-hold">Rejected</span>
                        @else
                            <span class="badge-status ps-badge-cancelled">Cancelled</span>
                        @endif
                    </td>
                    <td data-label="Actions" class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="passSlipRowMenu{{ $slip->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for pass slip {{ $slip->slip_number ?? $slip->id }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="passSlipRowMenu{{ $slip->id }}" role="menu" aria-label="Pass slip actions">
                            <button type="button" role="menuitem" class="row-menu-item"
                                    onclick="closeRowMenu(); viewPassSlip({{ $slip->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                View details
                            </button>
                            @if($slip->status === 'pending')
                                <div class="row-menu-sep"></div>
                                <form method="POST" action="{{ route('passslip.delete', $slip->id) }}" class="row-menu-form" onsubmit="return confirm('Cancel this pass slip? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" role="menuitem" class="row-menu-item is-danger">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                        Cancel pass slip
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="ps-empty-cell">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="ps-icon-center">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <p class="ps-empty-title">No pass slips yet</p>
                        <button onclick="openPassSlipModal()" class="ps-btn-empty-cta">
                            File Your First Pass Slip
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="ps-flex-gap-12">
            <p id="passSlipFooter">Showing <strong id="passSlipRowStart">{{ $passSlips->firstItem() ?? 0 }}</strong>-<strong id="passSlipRowEnd">{{ $passSlips->lastItem() ?? 0 }}</strong> of <strong id="passSlipRowTotal">{{ $passSlips->total() ?? 0 }}</strong> records</p>
            <select id="passSlipRowsPerPage" class="filter-select ps-select-inline" onchange="changePassSlipRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
            </select>
        </div>
        <div class="pagination" id="passSlipPaginationControls">
            @if(isset($passSlips) && method_exists($passSlips, 'links'))
                {!! $passSlips->links() !!}
            @endif
        </div>
    </div>
</section>
