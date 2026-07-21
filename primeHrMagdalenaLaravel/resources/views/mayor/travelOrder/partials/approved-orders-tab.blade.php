<section class="table-section to-table-section to-hidden" id="approved-tab">
    <div class="table-header to-table-header to-header-green">
        <div>
            <h3 class="table-title to-table-title">Approved Travel Orders</h3>
            <p class="table-sub to-table-sub">Successfully approved · {{ $approvedOrders->total() }} records</p>
        </div>
    </div>

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
                    <th class="to-th to-th-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedOrders as $order)
                <tr class="approved-order-row to-row" data-department="{{ $order->employee->employmentDetail->departmentRelation->name ?? '' }}" data-mode="{{ $order->transportation_mode ?? '' }}" data-travel-date="{{ $order->travel_date->format('Y-m-d') }}">
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
                    <td class="to-td to-td-strong">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td class="to-td to-td-center">
                        <span class="to-badge-duration to-badge-green">{{ $order->duration }} days</span>
                    </td>
                    <td class="to-td to-td-green">{{ $order->approver ? ($order->approver->employee ? $order->approver->employee->first_name . ' ' . $order->approver->employee->last_name : 'Admin User') : 'Admin User' }}</td>
                    <td class="to-td to-td-muted">{{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('M d, Y') : 'N/A' }}</td>
                    <td class="to-td">
                        <div class="to-actions-wrap">
                            <button onclick="viewMayorTravelOrder({{ $order->id }})" class="to-btn-view">View</button>
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
