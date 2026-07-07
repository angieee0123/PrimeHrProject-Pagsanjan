<section class="table-section" id="types-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Types Configuration</h3>
            <p class="table-sub">Manage all leave types for LGU Pagsanjan · {{ $leaveTypes->total() }} records</p>
        </div>
        <div class="table-actions">
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;" onclick="openAddLeaveTypeModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Leave Type
            </button>
        </div>
    </div>

    @php
        $sortIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline-block; vertical-align: middle; margin-left: 4px;"><polyline points="18 15 12 9 6 15"></polyline></svg>';
        $colors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#150c63', '#56547a'];
    @endphp

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th onclick="sortLeaveTypes('leave_name')" style="cursor: pointer; text-align: left;">Leave Type {!! $sortIcon !!}</th>
                    <th onclick="sortLeaveTypes('annual_limit')" style="cursor: pointer;">Annual Limit {!! $sortIcon !!}</th>
                    <th style="min-width: 320px; padding-left: 24px; padding-right: 24px;">Attachment</th>
                    <th onclick="sortLeaveTypes('is_active')" style="cursor: pointer; text-align: right;">Status {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                <tr class="leave-type-row" data-status="{{ $type->is_active ? 'active' : 'inactive' }}" data-accrual="{{ $type->is_accrued ? 'accrued' : 'fixed' }}">
                    <td data-label="Leave Type" style="text-align: left;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="emp-avatar" style="background: {{ $colors[$loop->index % 6] }}; margin-left: 0; flex-shrink: 0;">{{ $type->leave_code }}</div>
                            <span style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $type->leave_name }}</span>
                        </div>
                    </td>
                    <td data-label="Annual Limit" style="font-weight: 600; color: #0b044d; font-size: 13px;">
                        {!! $type->annual_limit > 0 ? number_format($type->annual_limit, 0) . ' days' : '<span style="color: #9ca3af;">—</span>' !!}
                    </td>
                    <td data-label="Attachment" style="font-size: 13px; padding-left: 24px; padding-right: 24px;">
                        <span style="color: {{ $type->attachment_info ? '#0b044d' : '#9ca3af' }}; font-weight: {{ $type->attachment_info ? '500' : 'normal' }}; font-style: {{ $type->attachment_info ? 'normal' : 'italic' }};">
                            {{ $type->attachment_info ?: 'Not required' }}
                        </span>
                    </td>
                    <td data-label="Status" style="text-align: right;">
                        <span class="badge-status {{ $type->is_active ? 'processed' : 'on-hold' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td data-label="Actions">
                        <div style="position: relative; display: flex; justify-content: center;">
                            <button class="lt-ellipsis-btn" onclick="toggleLeaveTypeActionMenu(event, this)" title="Actions">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="lt-action-menu" style="display: none;">
                                <button onclick="viewLeaveType('{{ $type->leave_code }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View
                                </button>
                                <button onclick="editLeaveType('{{ $type->leave_code }}')">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 40px; color: #56547a;">No leave types found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="leaveTypesFooter">Showing <strong id="leaveTypesRowStart">{{ $leaveTypes->firstItem() ?? 0 }}</strong>-<strong id="leaveTypesRowEnd">{{ $leaveTypes->lastItem() ?? 0 }}</strong> of <strong id="leaveTypesRowTotal">{{ $leaveTypes->total() }}</strong> records</p>
            <select id="leaveTypesRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeLeaveTypesRowsPerPage()">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination" id="leaveTypesPaginationControls">
            @php $params = $leaveTypes->appends(request()->except('page')); @endphp
            
            {!! $leaveTypes->onFirstPage() 
                ? '<button class="page-btn" disabled>‹</button>' 
                : '<a href="' . $params->previousPageUrl() . '#types-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->previousPageUrl() . '\');">‹</a>' !!}

            @foreach ($leaveTypes->getUrlRange(1, $leaveTypes->lastPage()) as $page => $url)
                {!! $page == $leaveTypes->currentPage() 
                    ? '<button class="page-btn active">' . $page . '</button>' 
                    : '<a href="' . $params->url($page) . '#types-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->url($page) . '\');">' . $page . '</a>' !!}
            @endforeach

            {!! $leaveTypes->hasMorePages() 
                ? '<a href="' . $params->nextPageUrl() . '#types-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage(\'' . $params->nextPageUrl() . '\');">›</a>' 
                : '<button class="page-btn" disabled>›</button>' !!}
        </div>
    </div>
</section>

<style>
.lt-ellipsis-btn {
    background: none;
    border: none;
    color: #8f8daf;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}
.lt-ellipsis-btn:hover {
    background: #f1f5f9;
    color: #0b044d;
}
.lt-action-menu {
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15);
    z-index: 100;
    min-width: 140px;
    margin-top: 6px;
    overflow: hidden;
    animation: ltSlideDown 0.2s ease-out;
}
.lt-action-menu button {
    width: 100%;
    padding: 11px 14px;
    border: none;
    background: none;
    text-align: left;
    font-size: 12px;
    color: #0b044d;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}
.lt-action-menu button:hover {
    background: #f2f1fb;
}
@keyframes ltSlideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script>
function changeLeaveTypesRowsPerPage() {
    const perPage = document.getElementById('leaveTypesRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'types');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function toggleLeaveTypeActionMenu(event, btn) {
    event.stopPropagation();
    const menu = btn.nextElementSibling;
    document.querySelectorAll('.lt-action-menu').forEach(m => {
        if (m !== menu) m.style.display = 'none';
    });
    menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
}

document.addEventListener('click', () => {
    document.querySelectorAll('.lt-action-menu').forEach(m => m.style.display = 'none');
});
</script>
</section>
