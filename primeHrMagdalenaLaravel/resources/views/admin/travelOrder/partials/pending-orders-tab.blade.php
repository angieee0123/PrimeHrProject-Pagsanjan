<section class="table-section to-table-section to-hidden" id="pending-tab">
    <div class="table-header to-table-header to-header-purple">
        <div>
            <h3 class="table-title to-table-title">Pending Travel Orders</h3>
            <p class="table-sub to-table-sub">Awaiting approval · {{ $pendingOrders->total() }} records</p>
        </div>
    </div>

    @php $colors = ['#0b044d','#8e1e18','#150c63','#a52820','#150c63','#56547a']; @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="to-th">Employee</th>
                    <th class="to-th">Destination</th>
                    <th class="to-th">Purpose</th>
                    <th class="to-th">Travel Date</th>
                    <th class="to-th to-th-center">Duration</th>
                    <th class="to-th to-th-center row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody id="pendingOrdersTableBody">
                @forelse($pendingOrders as $order)
                @php
                    // Everything the approve/disapprove confirmation needs to name
                    // *this* order rather than ask about "a travel order".
                    // Assembled once per row and handed to both menu items as data
                    // attributes, so the dialog never has to fetch it and the two
                    // decisions cannot disagree about which order they describe.
                    $orderEmployee = $order->employee;
                    $orderEmployeeName = trim(($orderEmployee->first_name ?? '') . ' ' . ($orderEmployee->last_name ?? '')) ?: 'this employee';
                    $orderFirstName = $orderEmployee->first_name ?? 'the employee';
                    $orderInitials = strtoupper(substr($orderEmployee->first_name ?? 'E', 0, 1) . substr($orderEmployee->last_name ?? 'E', 0, 1));
                    $orderPhoto = $orderEmployee?->photo
                        ? (\Illuminate\Support\Str::startsWith($orderEmployee->photo, ['/', 'http']) ? $orderEmployee->photo : asset('storage/' . $orderEmployee->photo))
                        : '';
                    $orderDepartment = $orderEmployee->employmentDetail->departmentRelation->name ?? '';
                    $orderParty = 1 + $order->companions->count();
                    $orderBudget = $order->estimated_budget ? '₱' . number_format((float) $order->estimated_budget, 2) : '';
                @endphp
                <tr class="pending-order-row to-row" data-department="{{ $orderDepartment }}" data-mode="{{ $order->transportation_mode ?? '' }}" data-travel-date="{{ $order->travel_date->format('Y-m-d') }}">
                    <td class="to-td">
                        <div class="emp-cell to-emp-cell">
                            @include('partials.travel-party-avatars', ['order' => $order])
                            <div class="to-emp-info">
                                <p class="to-emp-name">{{ $order->employee->first_name }} {{ $order->employee->last_name }}</p>
                                <p class="to-emp-id">{{ $order->employee->employee_id }}@if($order->companions->count()) · +{{ $order->companions->count() }} {{ Str::plural('companion', $order->companions->count()) }}@endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="to-td to-td-accent">{{ $order->destination }}</td>
                    <td class="to-td to-td-muted">{{ Str::limit($order->purpose, 40) }}</td>
                    <td class="to-td to-td-strong">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td class="to-td to-td-center">
                        <span class="to-badge-duration to-badge-purple">{{ $order->duration }} days</span>
                    </td>
                    <td class="to-td">
                        <div class="to-actions-wrap">
                            <button class="action-ellipsis-btn to-ellipsis-btn" onclick="toggleTravelActionMenu(event, this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="travel-action-menu to-action-menu">
                                <button onclick="viewOrder({{ $order->id }})" class="to-menu-btn to-menu-view">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="to-menu-icon"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Details
                                </button>
                                {{-- Both decisions carry the same order context. The
                                     confirmation reads it off whichever one was
                                     pressed, so it can say "Approve Juan's travel to
                                     Lucena City?" instead of "Are you sure?" --}}
                                <button type="button" class="to-menu-btn to-menu-approve"
                                        onclick="openTravelDecision(this)"
                                        data-decision="approve"
                                        data-action="{{ route('admin.travelorder.approve', $order->id) }}"
                                        data-order-number="{{ $order->order_number }}"
                                        data-employee="{{ $orderEmployeeName }}"
                                        data-employee-id="{{ $order->employee->employee_id }}"
                                        data-first-name="{{ $orderFirstName }}"
                                        data-initials="{{ $orderInitials }}"
                                        data-photo="{{ $orderPhoto }}"
                                        data-department="{{ $orderDepartment }}"
                                        data-destination="{{ $order->destination }}"
                                        data-dates="{{ $order->formatted_dates }}"
                                        data-duration="{{ $order->duration }}"
                                        data-party="{{ $orderParty }}"
                                        data-transport="{{ $order->transportation_mode }}"
                                        data-budget="{{ $orderBudget }}"
                                        data-purpose="{{ $order->purpose }}">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="to-menu-icon"><polyline points="20 6 9 17 4 12"/></svg>
                                    Approve
                                </button>
                                <button type="button" class="to-menu-btn to-menu-disapprove"
                                        onclick="openTravelDecision(this)"
                                        data-decision="disapprove"
                                        data-action="{{ route('admin.travelorder.disapprove', $order->id) }}"
                                        data-order-number="{{ $order->order_number }}"
                                        data-employee="{{ $orderEmployeeName }}"
                                        data-employee-id="{{ $order->employee->employee_id }}"
                                        data-first-name="{{ $orderFirstName }}"
                                        data-initials="{{ $orderInitials }}"
                                        data-photo="{{ $orderPhoto }}"
                                        data-department="{{ $orderDepartment }}"
                                        data-destination="{{ $order->destination }}"
                                        data-dates="{{ $order->formatted_dates }}"
                                        data-duration="{{ $order->duration }}"
                                        data-party="{{ $orderParty }}"
                                        data-transport="{{ $order->transportation_mode }}"
                                        data-budget="{{ $orderBudget }}"
                                        data-purpose="{{ $order->purpose }}">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="to-menu-icon"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Disapprove
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="to-empty-td">
                        <div class="to-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <p class="to-empty-title">No pending travel orders</p>
                        <p class="to-empty-sub">Pending travel orders will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="to-footer-inner">
            <p class="to-footer-text">Showing <strong class="to-footer-strong">{{ $pendingOrders->firstItem() ?? 0 }}</strong>–<strong class="to-footer-strong">{{ $pendingOrders->lastItem() ?? 0 }}</strong> of <strong class="to-footer-strong">{{ $pendingOrders->total() }}</strong> records</p>
            <select id="pendingRowsPerPage" class="filter-select to-rows-select" onchange="changePendingRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination to-pagination" id="pendingPaginationControls">
            @php $params = $pendingOrders->appends(request()->except('page')); @endphp
            {!! $pendingOrders->onFirstPage()
                ? '<button class="page-btn to-page-btn to-page-btn-disabled" disabled>‹</button>'
                : '<a href="' . $params->previousPageUrl() . '" class="page-btn to-page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');">‹</a>' !!}
            @foreach($pendingOrders->getUrlRange(1, $pendingOrders->lastPage()) as $page => $url)
                {!! $page == $pendingOrders->currentPage()
                    ? '<button class="page-btn active to-page-btn-active">' . $page . '</button>'
                    : '<a href="' . $params->url($page) . '" class="page-btn to-page-btn to-page-btn-num" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');">' . $page . '</a>' !!}
            @endforeach
            {!! $pendingOrders->hasMorePages()
                ? '<a href="' . $params->nextPageUrl() . '" class="page-btn to-page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');">›</a>'
                : '<button class="page-btn to-page-btn to-page-btn-disabled" disabled>›</button>' !!}
        </div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/admin/travelOrder/pending-orders-tab.js')
@endpush
