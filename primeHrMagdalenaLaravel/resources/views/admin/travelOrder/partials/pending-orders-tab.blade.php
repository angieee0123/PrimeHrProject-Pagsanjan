<section class="table-section" id="pending-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Pending Travel Orders</h3>
            <p class="table-sub">Awaiting approval · {{ $pendingOrders->total() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterPendingDept" onchange="filterPendingOrders()">
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
                    <th onclick="sortPendingOrders('employee')" style="cursor: pointer;">Employee {!! $sortIcon !!}</th>
                    <th onclick="sortPendingOrders('destination')" style="cursor: pointer;">Destination {!! $sortIcon !!}</th>
                    <th onclick="sortPendingOrders('purpose')" style="cursor: pointer;">Purpose {!! $sortIcon !!}</th>
                    <th onclick="sortPendingOrders('travel_date')" style="cursor: pointer;">Travel Date {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Duration</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pendingOrders as $order)
                <tr class="pending-order-row">
                    <td data-label="Employee">
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $colors[$loop->index % 6] }};">{{ strtoupper(substr($order->employee->first_name, 0, 2)) }}</div>
                            <div>
                                <p class="emp-name">{{ $order->employee->first_name }} {{ $order->employee->last_name }}</p>
                                <p class="emp-id">{{ $order->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td data-label="Destination" style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $order->destination }}</td>
                    <td data-label="Purpose" style="font-size: 13px; color: #6b6a8a;">{{ Str::limit($order->purpose, 40) }}</td>
                    <td data-label="Travel Date" style="font-size: 13px; color: #0b044d; font-weight: 600;">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td data-label="Duration" style="text-align: center; font-weight: 600; color: #0b044d;">{{ $order->duration }} days</td>
                    <td data-label="Actions">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewOrder({{ $order->id }})">View</button>
                            <form method="POST" action="{{ route('admin.travelorder.approve', $order->id) }}" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-edit" style="background: #15803d; border-color: #15803d;" onclick="return confirm('Approve this travel order?')">Approve</button>
                            </form>
                            <button class="btn-deactivate" onclick="disapproveOrder({{ $order->id }})">Disapprove</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b6a8a;">No pending travel orders</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="pendingFooter">Showing <strong id="pendingRowStart">{{ $pendingOrders->firstItem() ?? 0 }}</strong>-<strong id="pendingRowEnd">{{ $pendingOrders->lastItem() ?? 0 }}</strong> of <strong id="pendingRowTotal">{{ $pendingOrders->total() }}</strong> records</p>
            <select id="pendingRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changePendingRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination" id="pendingPaginationControls">
            @php $params = $pendingOrders->appends(request()->except('page')); @endphp
            
            {!! $pendingOrders->onFirstPage() 
                ? '<button class="page-btn" disabled>‹</button>' 
                : '<a href="' . $params->previousPageUrl() . '#pending-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');">‹</a>' !!}

            @foreach ($pendingOrders->getUrlRange(1, $pendingOrders->lastPage()) as $page => $url)
                {!! $page == $pendingOrders->currentPage() 
                    ? '<button class="page-btn active">' . $page . '</button>' 
                    : '<a href="' . $params->url($page) . '#pending-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');">' . $page . '</a>' !!}
            @endforeach

            {!! $pendingOrders->hasMorePages() 
                ? '<a href="' . $params->nextPageUrl() . '#pending-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');">›</a>' 
                : '<button class="page-btn" disabled>›</button>' !!}
        </div>
    </div>
</section>

<script>
function changePendingRowsPerPage() {
    const perPage = document.getElementById('pendingRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'pending');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterPendingOrders() {
    const deptFilter = document.getElementById('filterPendingDept').value;
    const rows = document.querySelectorAll('.pending-order-row');
    
    rows.forEach(row => {
        const show = deptFilter === 'all' || row.dataset.department === deptFilter;
        row.style.display = show ? '' : 'none';
    });
}

function sortPendingOrders(column) {
    console.log('Sort by:', column);
}

function approveOrder(id) {
    if(confirm('Approve this travel order?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/travelorder/${id}/approve`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function disapproveOrder(id) {
    const reason = prompt('Reason for disapproval:');
    if(reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/travelorder/${id}/disapprove`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        form.appendChild(reasonInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function viewOrder(id) {
    window.location.href = `/admin/travelorder/${id}`;
}
</script>
