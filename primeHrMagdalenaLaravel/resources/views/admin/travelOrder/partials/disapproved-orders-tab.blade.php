<section class="table-section to-table-section to-hidden" id="disapproved-tab">
    <div class="table-header to-table-header to-header-red">
        <div>
            <h3 class="table-title to-table-title">Disapproved Travel Orders</h3>
            <p class="table-sub to-table-sub">Rejected requests · {{ $disapprovedOrders->total() }} records</p>
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
                    <th class="to-th">Disapproved By</th>
                    <th class="to-th to-th-wide">Reason</th>
                    <th class="to-th to-th-center row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disapprovedOrders as $order)
                @php $disEmp = $order->employee; @endphp
                <tr class="disapproved-order-row to-row" data-department="{{ $disEmp?->employmentDetail?->departmentRelation?->name ?? '' }}" data-mode="{{ $order->transportation_mode ?? '' }}" data-travel-date="{{ $order->travel_date?->format('Y-m-d') ?? '' }}">
                    <td class="to-td">
                        <div class="emp-cell to-emp-cell">
                            @include('partials.travel-party-avatars', ['order' => $order])
                            <div class="to-emp-info">
                                <p class="to-emp-name">{{ $disEmp ? trim(($disEmp->first_name ?? '') . ' ' . ($disEmp->last_name ?? '')) : 'Unknown Employee' }}</p>
                                <p class="to-emp-id">{{ $disEmp->employee_id ?? '—' }}@if($order->companions->count()) · +{{ $order->companions->count() }} {{ Str::plural('companion', $order->companions->count()) }}@endif</p>
                            </div>
                        </div>
                    </td>
                    <td class="to-td to-td-accent">{{ $order->destination }}</td>
                    <td class="to-td to-td-strong">{{ $order->travel_date ? \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') : '—' }}</td>
                    @php $disActor = $order->disapprover ?? $order->approver; @endphp
                    <td class="to-td to-td-red">{{ $disActor ? ($disActor->employee ? trim(($disActor->employee->first_name ?? '') . ' ' . ($disActor->employee->last_name ?? '')) ?: ($disActor->username ?? 'Admin User') : ($disActor->username ?? 'Admin User')) : 'Admin User' }}</td>
                    <td class="to-td">
                        <span class="to-td-muted">{{ Str::limit($order->disapproval_reason ?? $order->remarks ?? 'No reason provided', 50) }}</span>
                    </td>
                    <td class="to-td">
                        <div class="to-actions-wrap">
                            <button onclick="viewOrder({{ $order->id }})" class="to-btn-view">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="to-empty-td">
                        <div class="to-empty-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <p class="to-empty-title">No disapproved travel orders</p>
                        <p class="to-empty-sub">Disapproved travel orders will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div class="to-footer-inner">
            <p class="to-footer-text">Showing <strong class="to-footer-strong">{{ $disapprovedOrders->firstItem() ?? 0 }}</strong>–<strong class="to-footer-strong">{{ $disapprovedOrders->lastItem() ?? 0 }}</strong> of <strong class="to-footer-strong">{{ $disapprovedOrders->total() }}</strong> records</p>
            <select id="disapprovedRowsPerPage" class="filter-select to-rows-select" onchange="changeDisapprovedRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination to-pagination" id="disapprovedPaginationControls">
            @php $params = $disapprovedOrders->appends(request()->except('page')); @endphp
            {!! $disapprovedOrders->onFirstPage()
                ? '<button class="page-btn to-page-btn to-page-btn-disabled" disabled>‹</button>'
                : '<a href="' . $params->previousPageUrl() . '" class="page-btn to-page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');">‹</a>' !!}
            @foreach($disapprovedOrders->getUrlRange(1, $disapprovedOrders->lastPage()) as $page => $url)
                {!! $page == $disapprovedOrders->currentPage()
                    ? '<button class="page-btn active to-page-btn-active">' . $page . '</button>'
                    : '<a href="' . $params->url($page) . '" class="page-btn to-page-btn to-page-btn-num" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');">' . $page . '</a>' !!}
            @endforeach
            {!! $disapprovedOrders->hasMorePages()
                ? '<a href="' . $params->nextPageUrl() . '" class="page-btn to-page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');">›</a>'
                : '<button class="page-btn to-page-btn to-page-btn-disabled" disabled>›</button>' !!}
        </div>
    </div>
</section>

@push('scripts')
    @vite('resources/js/admin/travelOrder/disapproved-orders-tab.js')
@endpush
