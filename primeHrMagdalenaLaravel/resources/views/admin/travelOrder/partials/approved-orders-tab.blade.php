<section class="table-section to-table-section to-hidden" id="approved-tab">
    <div class="table-header to-table-header to-header-green">
        <div>
            <h3 class="table-title to-table-title">Approved Travel Orders</h3>
            <p class="table-sub to-table-sub">Successfully approved · {{ $approvedOrders->total() }} records</p>
        </div>
    </div>

    @php $colors = ['#0b044d','#8e1e18','#150c63','#a52820','#150c63','#56547a']; @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="to-th">Employee</th>
                    <th class="to-th">Destination</th>
                    <th class="to-th">Travel Date</th>
                    <th class="to-th to-th-center">Duration</th>
                    <th class="to-th">Approved By</th>
                    <th class="to-th">Approved Date</th>
                    <th class="to-th to-th-center row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedOrders as $order)
                @php $apEmp = $order->employee; @endphp
                <tr class="approved-order-row to-row" data-department="{{ $apEmp?->employmentDetail?->departmentRelation?->name ?? '' }}" data-mode="{{ $order->transportation_mode ?? '' }}" data-travel-date="{{ $order->travel_date?->format('Y-m-d') ?? '' }}">
                    <td class="to-td">
                        <div class="emp-cell to-emp-cell">
                            @include('partials.travel-party-avatars', ['order' => $order])
                            <div class="to-emp-info">
                                <p class="to-emp-name">{{ $apEmp ? trim(($apEmp->first_name ?? '') . ' ' . ($apEmp->last_name ?? '')) : 'Unknown Employee' }}</p>
                                <p class="to-emp-id">{{ $apEmp->employee_id ?? '—' }}@if($order->companions->count()) · +{{ $order->companions->count() }} {{ Str::plural('companion', $order->companions->count()) }}@endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="to-td to-td-accent">{{ $order->destination }}</td>
                    <td class="to-td to-td-strong">{{ $order->travel_date ? \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') : '—' }}</td>
                    <td class="to-td to-td-center">
                        <span class="to-badge-duration to-badge-green">{{ $order->duration }} days</span>
                    </td>
                    <td class="to-td to-td-green">{{ $order->approver ? ($order->approver->employee ? trim(($order->approver->employee->first_name ?? '') . ' ' . ($order->approver->employee->last_name ?? '')) ?: ($order->approver->username ?? 'Admin User') : ($order->approver->username ?? 'Admin User')) : 'Admin User' }}</td>
                    <td class="to-td to-td-muted">{{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('M d, Y') : 'N/A' }}</td>
                    {{-- Two actions now, so they move into the shared ⋮ row menu
                         (resources/js/app.js) rather than sitting side by side:
                         a second button in this cell pushes the Approved Date
                         column off a laptop screen, and every other table in
                         the admin area with more than one row action already
                         uses that menu. --}}
                    <td class="to-td row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="approvedOrderMenu{{ $order->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for travel order {{ $order->order_number }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="approvedOrderMenu{{ $order->id }}" role="menu" aria-label="Travel order actions">
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); viewOrder({{ $order->id }})">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View
                            </button>
                            <div class="row-menu-sep"></div>
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); openTravelOrderFormModal(
                                {{ $order->id }},
                                @js(trim(($order->employee->first_name ?? '') . ' ' . ($order->employee->last_name ?? ''))),
                                @js($order->employee->employee_id ?? 'N/A'),
                                @js($order->order_number),
                                @js($order->destination)
                            )">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                View Form
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="to-empty-td">
                        <div class="to-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        </div>
                        <p class="to-empty-title">No approved travel orders</p>
                        <p class="to-empty-sub">Approved travel orders will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="to-footer-inner">
            <p class="to-footer-text">Showing <strong class="to-footer-strong">{{ $approvedOrders->firstItem() ?? 0 }}</strong>–<strong class="to-footer-strong">{{ $approvedOrders->lastItem() ?? 0 }}</strong> of <strong class="to-footer-strong">{{ $approvedOrders->total() }}</strong> records</p>
            <select id="approvedRowsPerPage" class="filter-select to-rows-select" onchange="changeApprovedRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination to-pagination" id="approvedPaginationControls">
            @php $params = $approvedOrders->appends(request()->except('page')); @endphp
            {!! $approvedOrders->onFirstPage()
                ? '<button class="page-btn to-page-btn to-page-btn-disabled" disabled>‹</button>'
                : '<a href="' . $params->previousPageUrl() . '" class="page-btn to-page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');">‹</a>' !!}
            @foreach($approvedOrders->getUrlRange(1, $approvedOrders->lastPage()) as $page => $url)
                {!! $page == $approvedOrders->currentPage()
                    ? '<button class="page-btn active to-page-btn-active">' . $page . '</button>'
                    : '<a href="' . $params->url($page) . '" class="page-btn to-page-btn to-page-btn-num" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');">' . $page . '</a>' !!}
            @endforeach
            {!! $approvedOrders->hasMorePages()
                ? '<a href="' . $params->nextPageUrl() . '" class="page-btn to-page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');">›</a>'
                : '<button class="page-btn to-page-btn to-page-btn-disabled" disabled>›</button>' !!}
        </div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/admin/travelOrder/approved-orders-tab.js')
@endpush
