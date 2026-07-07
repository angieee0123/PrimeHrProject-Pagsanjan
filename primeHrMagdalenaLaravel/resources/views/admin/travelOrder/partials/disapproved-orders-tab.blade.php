<section class="table-section" id="disapproved-tab" style="display: none; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15,23,42,.04), 0 1px 3px rgba(15,23,42,.03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #fdf0ef 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Disapproved Travel Orders</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Rejected requests · {{ $disapprovedOrders->total() }} records</p>
        </div>
    </div>

    @php $colors = ['#0b044d','#8e1e18','#1a0f6e','#5a0f0b','#2d1a8e','#6b3fa0']; @endphp

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Destination</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Travel Date</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Disapproved By</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6; min-width: 200px;">Reason</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($disapprovedOrders as $order)
                <tr class="disapproved-order-row" style="transition: all 0.15s ease;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @if($order->employee->photo)
                                <img src="{{ $order->employee->photo }}" alt="{{ $order->employee->first_name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;">
                            @else
                                <div style="background: {{ $colors[$loop->index % 6] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid #e2e8f0; flex-shrink: 0;">{{ strtoupper(substr($order->employee->first_name, 0, 1) . substr($order->employee->last_name, 0, 1)) }}</div>
                            @endif
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $order->employee->first_name }} {{ $order->employee->last_name }}</p>
                                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 500;">{{ $order->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #0b044d; font-weight: 600;">{{ $order->destination }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #111827; font-weight: 600;">{{ \Carbon\Carbon::parse($order->travel_date)->format('M d, Y') }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #8e1e18; font-weight: 600;">{{ $order->disapprover ? ($order->disapprover->employee ? $order->disapprover->employee->first_name . ' ' . $order->disapprover->employee->last_name : 'Admin User') : 'Admin User' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <span style="font-size: 13px; color: #64748b;">{{ Str::limit($order->disapproval_reason ?? $order->remarks ?? 'No reason provided', 50) }}</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div style="display: flex; justify-content: center;">
                            <button onclick="viewOrder({{ $order->id }})" style="padding: 6px 14px; border: 1px solid #e5e7eb; border-radius: 8px; background: #fff; color: #0b044d; font-size: 12px; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#f0effe'; this.style.borderColor='#c4b5fd'" onmouseout="this.style.background='#fff'; this.style.borderColor='#e5e7eb'">View</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No disapproved travel orders</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Disapproved travel orders will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedOrders->firstItem() ?? 0 }}</strong>–<strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedOrders->lastItem() ?? 0 }}</strong> of <strong style="color: #0b044d; font-weight: 700;">{{ $disapprovedOrders->total() }}</strong> records</p>
            <select id="disapprovedRowsPerPage" class="filter-select" style="width: auto; padding: 6px 10px; font-size: 12px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; color: #344054; font-weight: 600; cursor: pointer;" onchange="changeDisapprovedRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination" id="disapprovedPaginationControls" style="display: flex; gap: 4px;">
            @php $params = $disapprovedOrders->appends(request()->except('page')); @endphp
            {!! $disapprovedOrders->onFirstPage()
                ? '<button class="page-btn" disabled style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#344054;font-size:12px;font-weight:600;opacity:0.5;cursor:not-allowed;">‹</button>'
                : '<a href="' . $params->previousPageUrl() . '" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#344054;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;">‹</a>' !!}
            @foreach($disapprovedOrders->getUrlRange(1, $disapprovedOrders->lastPage()) as $page => $url)
                {!! $page == $disapprovedOrders->currentPage()
                    ? '<button class="page-btn active" style="padding:6px 12px;border:1px solid #0b044d;border-radius:6px;background:#0b044d;color:#fff;font-size:12px;font-weight:700;cursor:pointer;">' . $page . '</button>'
                    : '<a href="' . $params->url($page) . '" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');" style="padding:6px 12px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#344054;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;">' . $page . '</a>' !!}
            @endforeach
            {!! $disapprovedOrders->hasMorePages()
                ? '<a href="' . $params->nextPageUrl() . '" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');" style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#344054;font-size:12px;font-weight:600;cursor:pointer;text-decoration:none;">›</a>'
                : '<button class="page-btn" disabled style="padding:6px 10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;color:#344054;font-size:12px;font-weight:600;opacity:0.5;cursor:not-allowed;">›</button>' !!}
        </div>
    </div>
</section>

<script>
function changeDisapprovedRowsPerPage() {
    const perPage = document.getElementById('disapprovedRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'disapproved');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function filterDisapprovedOrders() {
    const dept = document.getElementById('travelOrderFilterDept').value;
    document.querySelectorAll('.disapproved-order-row').forEach(row => {
        row.style.display = dept === 'all' || row.dataset.department === dept ? '' : 'none';
    });
}
</script>
