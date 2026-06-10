<section class="table-section" id="approved-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Approved Travel Orders</h3>
            <p class="table-sub">Successfully approved · {{ $approvedOrders->total() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterApprovedDept" onchange="filterApprovedOrders()">
                <option value="all">All Departments</option>
                @foreach($departments ?? [] as $dept)
                    <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
        </div>
    </div>

    @php
        $sortIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;"><polyline points="18 15 12 9 6 15"></polyline></svg>';
        $colors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
    @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th onclick="sortApprovedOrders('employee')" style="cursor: pointer;">Employee {!! $sortIcon !!}</th>
                    <th onclick="sortApprovedOrders('destination')" style="cursor: pointer;">Destination {!! $sortIcon !!}</th>
                    <th onclick="sortApprovedOrders('travel_date')" style="cursor: pointer;">Travel Date {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Duration</th>
                    <th onclick="sortApprovedOrders('approved_by')" style="cursor: pointer;">Approved By {!! $sortIcon !!}</th>
                    <th onclick="sortApprovedOrders('approved_date')" style="cursor: pointer;">Approved Date {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($approvedOrders as $order)
                <tr class="approved-order-row">
                    <td data-label="Employee">
                        <div class="emp-cell">
                            @if($order->employee->photo)
                                <img src="{{ $order->employee->photo }}" alt="{{ $order->employee->first_name }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #e8e7f5;">
                            @else
                                <div class="emp-avatar" style="width:40px; height:40px; background: {{ $colors[$loop->index % 6] }}; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:12px; border-radius:50%; border:2px solid #e8e7f5;">{{ strtoupper(substr($order->employee->first_name, 0, 1)) }}{{ strtoupper(substr($order->employee->last_name, 0, 1)) }}</div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $order->employee->first_name }} {{ $order->employee->last_name }}</p>
                                <p class="emp-id">{{ $order->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td data-label="Destination" style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $order->destination }}</td>
                    <td data-label="Travel Date" style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td data-label="Duration" style="text-align: center; font-weight: 600; color: #0b044d;">{{ $order->duration }} days</td>
                    <td data-label="Approved By" style="font-size: 13px; color: #15803d; font-weight: 500;">{{ $order->approver ? ($order->approver->employee ? $order->approver->employee->first_name . ' ' . $order->approver->employee->last_name : 'Admin User') : 'Admin User' }}</td>
                    <td data-label="Approved Date" style="font-size: 13px; color: #6b6a8a;">{{ $order->approved_at ? \Carbon\Carbon::parse($order->approved_at)->format('M d, Y') : 'N/A' }}</td>
                    <td data-label="Actions">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewOrder({{ $order->id }})">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No approved travel orders</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="approvedFooter">Showing <strong id="approvedRowStart">{{ $approvedOrders->firstItem() ?? 0 }}</strong>-<strong id="approvedRowEnd">{{ $approvedOrders->lastItem() ?? 0 }}</strong> of <strong id="approvedRowTotal">{{ $approvedOrders->total() }}</strong> records</p>
            <select id="approvedRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeApprovedRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination" id="approvedPaginationControls">
            @php $params = $approvedOrders->appends(request()->except('page')); @endphp
            
            {!! $approvedOrders->onFirstPage() 
                ? '<button class="page-btn" disabled>‹</button>' 
                : '<a href="' . $params->previousPageUrl() . '#approved-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');">‹</a>' !!}

            @foreach ($approvedOrders->getUrlRange(1, $approvedOrders->lastPage()) as $page => $url)
                {!! $page == $approvedOrders->currentPage() 
                    ? '<button class="page-btn active">' . $page . '</button>' 
                    : '<a href="' . $params->url($page) . '#approved-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');">' . $page . '</a>' !!}
            @endforeach

            {!! $approvedOrders->hasMorePages() 
                ? '<a href="' . $params->nextPageUrl() . '#approved-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');">›</a>' 
                : '<button class="page-btn" disabled>›</button>' !!}
        </div>
    </div>
</section>

<script>
function changeApprovedRowsPerPage() {
    const perPage = document.getElementById('approvedRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'approved');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterApprovedOrders() {
    const deptFilter = document.getElementById('filterApprovedDept').value;
    const rows = document.querySelectorAll('.approved-order-row');
    
    rows.forEach(row => {
        const show = deptFilter === 'all' || row.dataset.department === deptFilter;
        row.style.display = show ? '' : 'none';
    });
}

function sortApprovedOrders(column) {
    console.log('Sort by:', column);
}
</script>
