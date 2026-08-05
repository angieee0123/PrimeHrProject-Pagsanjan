<section class="table-section" id="travel-history-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">My Travel Orders</h3>
            <p class="table-sub">Track your travel order requests · {{ $travelOrders->total() ?? 0 }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterTravelStatus" onchange="filterTravelOrders()">
                <option value="all">All Status</option>
                <option value="awaiting_companions">Awaiting Companions</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <button class="btn-export to-btn-primary-solid" onclick="openTravelOrderModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                File Travel Order
            </button>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="to-session-alert-success">
        <div class="to-flex-gap-8">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <p class="to-flash-text-success">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="to-session-alert-error">
        <div class="to-flex-gap-8">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="15" y1="9" x2="9" y2="15"/>
                <line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <p class="to-flash-text-error">{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @php
        $sortIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="to-sort-icon"><polyline points="18 15 12 9 6 15"></polyline></svg>';
    @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th onclick="sortTravelOrders('destination')" class="to-th-sort">Destination {!! $sortIcon !!}</th>
                    <th>Travel Party</th>
                    <th onclick="sortTravelOrders('purpose')" class="to-th-sort">Purpose {!! $sortIcon !!}</th>
                    <th onclick="sortTravelOrders('travel_date')" class="to-th-sort">Travel Date {!! $sortIcon !!}</th>
                    <th class="to-ta-center">Duration</th>
                    <th onclick="sortTravelOrders('status')" class="to-th-sort to-ta-center">Status {!! $sortIcon !!}</th>
                    <th class="to-ta-center row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($travelOrders ?? [] as $order)
                <tr class="travel-order-row" data-status="{{ $order->status }}">
                    <td data-label="Destination" class="to-td-semibold">
                        <div class="to-flex-gap-8">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0b044d" stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            {{ $order->destination }}
                        </div>
                    </td>
                    <td data-label="Travel Party">
                        @include('partials.travel-party-avatars', ['order' => $order])
                    </td>
                    <td data-label="Purpose" class="to-td-muted">{{ Str::limit($order->purpose, 50) }}</td>
                    <td data-label="Travel Date" class="to-td-semibold">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td data-label="Duration" class="to-td-duration">{{ $order->duration }} days</td>
                    <td data-label="Status" class="to-ta-center">
                        @if($order->status === 'awaiting_companions')
                            @php
                                $companionTotal = $order->companions->count();
                                $companionResponded = $order->companions->where('status', '!=', 'pending')->count();
                            @endphp
                            <span class="badge-status pending to-badge-awaiting" title="{{ $companionResponded }} of {{ $companionTotal }} companions responded">Awaiting Companions ({{ $companionResponded }}/{{ $companionTotal }})</span>
                        @elseif($order->status === 'pending')
                            <span class="badge-status pending">Pending</span>
                        @elseif($order->status === 'approved')
                            <span class="badge-status processed">Approved</span>
                        @elseif($order->status === 'rejected')
                            <span class="badge-status on-hold">Rejected</span>
                        @else
                            <span class="badge-status to-badge-cancelled">Cancelled</span>
                        @endif
                    </td>
                    <td data-label="Actions" class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="travelRowMenu{{ $order->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for the travel order to {{ $order->destination }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        {{-- Opened by app.js, which relocates it to <body> and
                             positions it fixed — .table-section clips its overflow, so a menu
                             left in this cell would be cut off at the card edge. --}}
                        <div class="row-menu" id="travelRowMenu{{ $order->id }}" role="menu" aria-label="Travel order actions">
                            <button type="button" role="menuitem" class="row-menu-item"
                                    onclick="closeRowMenu(); viewTravelOrder({{ $order->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                                </svg>
                                View details
                            </button>
                            @if($order->status === 'awaiting_companions' && !$order->companions->where('status', 'pending')->count())
                                <form method="POST" action="{{ route('travelorder.forward', $order->id) }}" class="row-menu-form" onsubmit="return confirm('Forward this travel order to HR for approval?');">
                                    @csrf
                                    <button type="submit" role="menuitem" class="row-menu-item is-accept">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                                        </svg>
                                        Forward to HR
                                    </button>
                                </form>
                            @endif
                            @if(in_array($order->status, ['pending', 'awaiting_companions']))
                                <div class="row-menu-sep"></div>
                                <form method="POST" action="{{ route('travelorder.delete', $order->id) }}" class="row-menu-form" onsubmit="return confirm('Cancel this travel order to {{ $order->destination }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" role="menuitem" class="row-menu-item is-danger">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                                        </svg>
                                        Cancel travel order
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="to-empty-cell">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d1d5db" stroke-width="1.5" class="to-icon-center">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <p class="to-empty-title">No travel orders yet</p>
                        <button onclick="openTravelOrderModal()" class="to-btn-empty-cta">
                            File Your First Travel Order
                        </button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="to-flex-gap-12">
            <p id="travelFooter">Showing <strong id="travelRowStart">{{ $travelOrders->firstItem() ?? 0 }}</strong>-<strong id="travelRowEnd">{{ $travelOrders->lastItem() ?? 0 }}</strong> of <strong id="travelRowTotal">{{ $travelOrders->total() ?? 0 }}</strong> records</p>
            <select id="travelRowsPerPage" class="filter-select to-select-inline" onchange="changeTravelRowsPerPage()">
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
