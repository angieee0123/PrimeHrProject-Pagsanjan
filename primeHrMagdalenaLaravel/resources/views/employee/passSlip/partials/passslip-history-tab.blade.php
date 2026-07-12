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
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;" onclick="openPassSlipModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                File Pass Slip
            </button>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div style="padding: 12px 16px; background: #d1fae5; border-left: 3px solid #10b981; border-radius: 6px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <p style="margin: 0; color: #065f46; font-size: 13px; font-weight: 500;">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div style="padding: 12px 16px; background: #fee2e2; border-left: 3px solid #ef4444; border-radius: 6px; margin-bottom: 16px;">
        <div style="display: flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <p style="margin: 0; color: #991b1b; font-size: 13px; font-weight: 500;">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @php
        $sortIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;"><polyline points="18 15 12 9 6 15"></polyline></svg>';
    @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th onclick="sortPassSlips('reason')" style="cursor: pointer;">Reason {!! $sortIcon !!}</th>
                    <th onclick="sortPassSlips('date')" style="cursor: pointer;">Date {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Time Out</th>
                    <th style="text-align: center;">Time In</th>
                    <th onclick="sortPassSlips('status')" style="cursor: pointer; text-align: center;">Status {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($passSlips ?? [] as $slip)
                <tr class="passslip-row" data-status="{{ $slip->status }}">
                    <td data-label="Reason" style="font-size: 13px; color: #0b044d; font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                            </svg>
                            {{ Str::limit($slip->reason, 40) }}
                        </div>
                    </td>
                    <td data-label="Date" style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ $slip->date->format('M d, Y') }}</td>
                    <td data-label="Time Out" style="text-align: center; font-size: 13px; color: #6b6a8a;">{{ \Carbon\Carbon::parse($slip->time_out)->format('g:i A') }}</td>
                    <td data-label="Time In" style="text-align: center; font-size: 13px; color: #6b6a8a;">{{ $slip->time_in ? \Carbon\Carbon::parse($slip->time_in)->format('g:i A') : '—' }}</td>
                    <td data-label="Status" style="text-align: center;">
                        @if($slip->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($slip->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($slip->status === 'rejected')
                            <span class="badge-status on-hold">Rejected</span>
                        @else
                            <span class="badge-status" style="background: #f3f4f6; color: #6b7280;">Cancelled</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewPassSlip({{ $slip->id }})">View</button>
                            @if($slip->status === 'pending')
                                <form method="POST" action="{{ route('passslip.delete', $slip->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this pass slip?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-edit" style="background: #dc2626; border-color: #dc2626;">Cancel</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b6a8a;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" style="margin: 0 auto 12px;">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <p style="margin: 0; font-size: 14px;">No pass slips yet</p>
                        <button onclick="openPassSlipModal()" style="margin-top: 12px; padding: 8px 16px; background: #0b044d; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer;">
                            File Your First Pass Slip
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="passSlipFooter">Showing <strong id="passSlipRowStart">{{ $passSlips->firstItem() ?? 0 }}</strong>-<strong id="passSlipRowEnd">{{ $passSlips->lastItem() ?? 0 }}</strong> of <strong id="passSlipRowTotal">{{ $passSlips->total() ?? 0 }}</strong> records</p>
            <select id="passSlipRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changePassSlipRowsPerPage()">
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

<script>
function changePassSlipRowsPerPage() {
    const perPage = document.getElementById('passSlipRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Applies the status dropdown and the topbar search together, so neither one
// re-shows rows the other has filtered out.
function filterPassSlips() {
    const statusFilter = document.getElementById('filterPassSlipStatus').value;
    const searchInput = document.getElementById('passSlipSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.passslip-row');

    rows.forEach(row => {
        const matchesStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
        const matchesQuery = query === '' || row.textContent.toLowerCase().includes(query);
        row.style.display = (matchesStatus && matchesQuery) ? '' : 'none';
    });
}

function sortPassSlips(column) {
    console.log('Sort by:', column);
}
</script>
