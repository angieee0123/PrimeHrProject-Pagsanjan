<section class="table-section" id="travel-history-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Travel Orders</h3>
            <p class="table-sub">Track your travel order requests · {{ $travelOrders->total() ?? 0 }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterTravelStatus" onchange="filterTravelOrders()">
                <option value="all">All Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;" onclick="openTravelOrderModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                File Travel Order
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
                    <th onclick="sortTravelOrders('destination')" style="cursor: pointer;">Destination {!! $sortIcon !!}</th>
                    <th onclick="sortTravelOrders('purpose')" style="cursor: pointer;">Purpose {!! $sortIcon !!}</th>
                    <th onclick="sortTravelOrders('travel_date')" style="cursor: pointer;">Travel Date {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Duration</th>
                    <th onclick="sortTravelOrders('status')" style="cursor: pointer; text-align: center;">Status {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($travelOrders ?? [] as $order)
                <tr class="travel-order-row" data-status="{{ $order->status }}">
                    <td data-label="Destination" style="font-size: 13px; color: #0b044d; font-weight: 600;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $order->destination }}
                        </div>
                    </td>
                    <td data-label="Purpose" style="font-size: 13px; color: #6b6a8a;">{{ Str::limit($order->purpose, 50) }}</td>
                    <td data-label="Travel Date" style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td data-label="Duration" style="text-align: center; font-weight: 600; color: #0b044d;">{{ $order->duration }} days</td>
                    <td data-label="Status" style="text-align: center;">
                        @if($order->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($order->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($order->status === 'rejected')
                            <span class="badge-status on-hold">Rejected</span>
                        @else
                            <span class="badge-status" style="background: #f3f4f6; color: #6b7280;">Cancelled</span>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewTravelOrder({{ $order->id }})">View</button>
                            @if($order->status === 'pending')
                                <form method="POST" action="{{ route('travelorder.delete', $order->id) }}" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this travel order?');">
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
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <p style="margin: 0; font-size: 14px;">No travel orders yet</p>
                        <button onclick="openTravelOrderModal()" style="margin-top: 12px; padding: 8px 16px; background: #0b044d; color: white; border: none; border-radius: 6px; font-size: 13px; cursor: pointer;">
                            File Your First Travel Order
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="travelFooter">Showing <strong id="travelRowStart">{{ $travelOrders->firstItem() ?? 0 }}</strong>-<strong id="travelRowEnd">{{ $travelOrders->lastItem() ?? 0 }}</strong> of <strong id="travelRowTotal">{{ $travelOrders->total() ?? 0 }}</strong> records</p>
            <select id="travelRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeTravelRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
            </select>
        </div>
        <div class="pagination" id="travelPaginationControls">
            @if(isset($travelOrders) && method_exists($travelOrders, 'links'))
                {!! $travelOrders->links() !!}
            @endif
        </div>
    </div>
</section>

<script>
function changeTravelRowsPerPage() {
    const perPage = document.getElementById('travelRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterTravelOrders() {
    const statusFilter = document.getElementById('filterTravelStatus').value;
    const rows = document.querySelectorAll('.travel-order-row');
    
    rows.forEach(row => {
        const show = statusFilter === 'all' || row.dataset.status === statusFilter;
        row.style.display = show ? '' : 'none';
    });
}

function sortTravelOrders(column) {
    console.log('Sort by:', column);
}

function viewTravelOrder(id) {
    // Open view modal
    console.log('View travel order:', id);
}

function editTravelOrder(id) {
    // Open edit modal
    console.log('Edit travel order:', id);
}
</script>
