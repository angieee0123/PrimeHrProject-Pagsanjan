<section class="table-section" id="pending-tab" style="display: none; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #f2f1fb 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Pending Travel Orders</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Awaiting approval · {{ $pendingOrders->total() }} records</p>
        </div>
    </div>

    @php $colors = ['#0b044d','#8e1e18','#150c63','#a52820','#150c63','#56547a']; @endphp

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Destination</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Purpose</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Travel Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Duration</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Actions</th>
                </tr>
            </thead>
            <tbody id="pendingOrdersTableBody">
                @forelse($pendingOrders as $order)
                <tr class="pending-order-row" data-department="{{ $order->employee->employmentDetail->departmentRelation->name ?? '' }}" data-mode="{{ $order->transportation_mode ?? '' }}" data-travel-date="{{ $order->travel_date->format('Y-m-d') }}" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @include('partials.travel-party-avatars', ['order' => $order])
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $order->employee->first_name }} {{ $order->employee->last_name }}</p>
                                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 500;">{{ $order->employee->employee_id }}@if($order->companions->count()) · +{{ $order->companions->count() }} {{ Str::plural('companion', $order->companions->count()) }}@endif</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #0b044d; font-weight: 600;">{{ $order->destination }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #64748b;">{{ Str::limit($order->purpose, 40) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">
                        <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f2f1fb; color: #0b044d;">{{ $order->duration }} days</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div style="position: relative; display: flex; justify-content: center;">
                            <button class="action-ellipsis-btn" onclick="toggleTravelActionMenu(event, this)" style="background: none; border: none; color: #8f8daf; cursor: pointer; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#0b044d'" onmouseout="this.style.background='none'; this.style.color='#8f8daf'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="travel-action-menu" style="display: none; position: absolute; right: 0; top: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 10px 24px rgba(15,23,42,0.15); z-index: 100; min-width: 160px; margin-top: 6px; overflow: hidden;">
                                <button onclick="viewOrder({{ $order->id }})" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #0b044d; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f2f1fb'" onmouseout="this.style.background='none'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Details
                                </button>
                                <form method="POST" action="{{ route('admin.travelorder.approve', $order->id) }}" style="margin: 0;">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Approve this travel order?')" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #22c55e; font-weight: 600; cursor: pointer; border-top: 1px solid #f1f5f9; transition: all 0.2s;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='none'">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><polyline points="20 6 9 17 4 12"/></svg>
                                        Approve
                                    </button>
                                </form>
                                <button onclick="disapproveOrder({{ $order->id }})" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #ef4444; font-weight: 600; cursor: pointer; border-top: 1px solid #f1f5f9; transition: all 0.2s;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Disapprove
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No pending travel orders</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Pending travel orders will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $pendingOrders->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $pendingOrders->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $pendingOrders->total() }}</strong> records</p>
            <select id="pendingRowsPerPage" class="filter-select" style="width: auto; padding: 6px 10px; font-size: 12px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; color: #56547a; font-weight: 600; cursor: pointer;" onchange="changePendingRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination" id="pendingPaginationControls" style="display: flex; gap: 4px;">
            @php $params = $pendingOrders->appends(request()->except('page')); @endphp
            {!! $pendingOrders->onFirstPage()
                ? '<button class="page-btn" disabled style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#56547a;font-size:12px;font-weight:600;opacity:0.5;cursor:not-allowed;">‹</button>'
                : '<a href="' . $params->previousPageUrl() . '" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#56547a;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;">‹</a>' !!}
            @foreach($pendingOrders->getUrlRange(1, $pendingOrders->lastPage()) as $page => $url)
                {!! $page == $pendingOrders->currentPage()
                    ? '<button class="page-btn active" style="padding:6px 12px;border:1px solid #0b044d;border-radius:6px;background:#0b044d;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">' . $page . '</button>'
                    : '<a href="' . $params->url($page) . '" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');" style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#56547a;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;">' . $page . '</a>' !!}
            @endforeach
            {!! $pendingOrders->hasMorePages()
                ? '<a href="' . $params->nextPageUrl() . '" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#56547a;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;">›</a>'
                : '<button class="page-btn" disabled style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#56547a;font-size:12px;font-weight:600;opacity:0.5;cursor:not-allowed;">›</button>' !!}
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
    const dept = document.getElementById('travelOrderFilterDept').value;
    const mode = document.getElementById('travelOrderFilterMode').value;
    const dateFrom = document.getElementById('travelOrderFilterDateFrom').value;
    const dateTo = document.getElementById('travelOrderFilterDateTo').value;
    document.querySelectorAll('.pending-order-row').forEach(row => {
        const matchDept = dept === 'all' || row.dataset.department === dept;
        const matchMode = mode === 'all' || row.dataset.mode === mode;
        const matchDateFrom = !dateFrom || row.dataset.travelDate >= dateFrom;
        const matchDateTo = !dateTo || row.dataset.travelDate <= dateTo;
        row.style.display = matchDept && matchMode && matchDateFrom && matchDateTo ? '' : 'none';
    });
}

function toggleTravelActionMenu(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.travel-action-menu').forEach(m => { if (m !== menu) m.style.display = 'none'; });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

function disapproveOrder(id) {
    const reason = prompt('Reason for disapproval:');
    if (!reason) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/travelorder/${id}/disapprove`;
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}"><input type="hidden" name="reason" value="${reason}">`;
    document.body.appendChild(form);
    form.submit();
}

function viewOrder(id) {
    window.location.href = `/admin/travelorder/${id}`;
}

document.addEventListener('click', () => {
    document.querySelectorAll('.travel-action-menu').forEach(m => m.style.display = 'none');
});
</script>
