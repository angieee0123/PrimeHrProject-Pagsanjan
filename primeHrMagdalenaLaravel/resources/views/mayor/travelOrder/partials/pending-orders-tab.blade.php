<section class="table-section to-table-section to-hidden" id="pending-tab">
    <div class="table-header to-table-header to-header-purple">
        <div>
            <h3 class="table-title to-table-title">Pending Travel Orders</h3>
            <p class="table-sub to-table-sub">Awaiting approval · {{ $pendingOrders->total() }} records</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="to-th">Employee</th>
                    <th class="to-th">Destination</th>
                    <th class="to-th">Purpose</th>
                    <th class="to-th">Travel Date</th>
                    <th class="to-th to-th-center">Duration</th>
                    <th class="to-th to-th-center">Actions</th>
                </tr>
            </thead>
            <tbody id="pendingOrdersTableBody">
                @forelse($pendingOrders as $order)
                <tr class="pending-order-row to-row" data-department="{{ $order->employee->employmentDetail->departmentRelation->name ?? '' }}" data-mode="{{ $order->transportation_mode ?? '' }}" data-travel-date="{{ $order->travel_date->format('Y-m-d') }}">
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
                            <button onclick="viewMayorTravelOrder({{ $order->id }})" class="to-btn-view">View</button>
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
