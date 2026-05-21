<section class="table-section" id="types-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Types Configuration</h3>
            <p class="table-sub">Manage all leave types for LGU Pagsanjan · {{ $leaveTypes->total() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterLeaveTypeStatus" onchange="filterLeaveTypes()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select class="filter-select" id="filterLeaveTypeAccrual" onchange="filterLeaveTypes()">
                <option value="all">All Types</option>
                <option value="accrued">Accrued</option>
                <option value="fixed">Fixed</option>
            </select>
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;" onclick="openAddLeaveTypeModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Leave Type
            </button>
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
                    <th onclick="sortLeaveTypes('leave_code')" style="cursor: pointer; text-align: left;">Code {!! $sortIcon !!}</th>
                    <th onclick="sortLeaveTypes('leave_name')" style="cursor: pointer;">Leave Type {!! $sortIcon !!}</th>
                    <th onclick="sortLeaveTypes('annual_limit')" style="cursor: pointer;">Annual Limit {!! $sortIcon !!}</th>
                    <th style="min-width: 250px;">Attachment</th>
                    <th onclick="sortLeaveTypes('is_active')" style="cursor: pointer; text-align: center;">Status {!! $sortIcon !!}</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                <tr class="leave-type-row" data-status="{{ $type->is_active ? 'active' : 'inactive' }}" data-accrual="{{ $type->is_accrued ? 'accrued' : 'fixed' }}">
                    <td data-label="Code" style="text-align: left;">
                        <div class="emp-avatar" style="background: {{ $colors[$loop->index % 6] }}; margin-left: 0;">{{ $type->leave_code }}</div>
                    </td>
                    <td data-label="Leave Type" style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $type->leave_name }}</td>
                    <td data-label="Annual Limit" style="font-weight: 600; color: #0b044d; font-size: 13px;">
                        {{ $type->annual_limit > 0 ? number_format($type->annual_limit, 0) . ' days' : '<span style="color: #9ca3af;">—</span>' }}
                    </td>
                    <td data-label="Attachment" style="font-size: 13px;">
                        <span style="color: {{ $type->attachment_info ? '#0b044d' : '#9ca3af' }}; font-weight: {{ $type->attachment_info ? '500' : 'normal' }}; font-style: {{ $type->attachment_info ? 'normal' : 'italic' }};">
                            {{ $type->attachment_info ?: 'Not required' }}
                        </span>
                    </td>
                    <td data-label="Status" style="text-align: center;">
                        <span class="badge-status {{ $type->is_active ? 'processed' : 'on-hold' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                    <td data-label="Actions">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewLeaveType('{{ $type->leave_code }}')">View</button>
                            <button class="btn-edit" onclick="editLeaveType('{{ $type->leave_code }}')">Edit</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b6a8a;">No leave types found</td>
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

<script>
function changeLeaveTypesRowsPerPage() {
    const perPage = document.getElementById('leaveTypesRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('tab', 'types');
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>
</section>


