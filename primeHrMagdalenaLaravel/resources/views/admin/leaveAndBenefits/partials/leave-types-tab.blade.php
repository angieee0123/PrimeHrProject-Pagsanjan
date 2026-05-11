<section class="table-section" id="types-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Types Configuration</h3>
            <p class="table-sub">Manage all leave types for LGU Pagsanjan · {{ $leaveTypes->total() }} records</p>
        </div>
        <div class="table-actions">
            <input type="text" id="searchLeaveTypes" class="search-input" placeholder="Search leave types..." onkeyup="searchLeaveTypes()">
            <select class="filter-select" id="filterLeaveStatus" onchange="filterLeaveTypes()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select class="filter-select" id="filterLeaveAccrual" onchange="filterLeaveTypes()">
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

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="sortable" onclick="sortLeaveTypes('leave_code')">
                        Code
                        <svg class="sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </th>
                    <th class="sortable" onclick="sortLeaveTypes('leave_name')">
                        Leave Type
                        <svg class="sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </th>
                    <th class="sortable" onclick="sortLeaveTypes('annual_limit')">
                        Annual Limit
                        <svg class="sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </th>
                    <th class="sortable" onclick="sortLeaveTypes('is_accrued')">
                        Type
                        <svg class="sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </th>
                    <th>Attachment</th>
                    <th class="sortable" onclick="sortLeaveTypes('is_active')">
                        Status
                        <svg class="sort-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                <tr class="leave-type-row" data-status="{{ $type->is_active ? 'active' : 'inactive' }}" data-accrual="{{ $type->is_accrued ? 'accrued' : 'fixed' }}">
                    <td>
                        <div class="emp-avatar" style="background: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'][$loop->index % 6] }}; width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; color: white; font-weight: 600; font-size: 13px;">
                            {{ $type->leave_code }}
                        </div>
                    </td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $type->leave_name }}</td>
                    <td style="font-weight: 600; color: #0b044d; font-size: 13px;">
                        @if($type->annual_limit > 0)
                            {{ number_format($type->annual_limit, 0) }} days
                        @else
                            <span style="color: #6b6a8a;">As needed</span>
                        @endif
                    </td>
                    <td><span class="badge-type {{ $type->is_accrued ? 'accrued' : 'fixed' }}">{{ $type->is_accrued ? 'Accrued' : 'Fixed' }}</span></td>
                    <td style="font-size: 13px; color: #6b6a8a;">
                        @if($type->attachment_info)
                            {{ $type->attachment_info }}
                        @else
                            <span style="color: #9ca3af;">No requirement</span>
                        @endif
                    </td>
                    <td><span class="badge-status {{ $type->is_active ? 'processed' : 'on-hold' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewLeaveType('{{ $type->leave_code }}')">View</button>
                            <button class="btn-edit" onclick="editLeaveType('{{ $type->leave_code }}')">Edit</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No leave types found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0;">Showing <strong>{{ $leaveTypes->firstItem() ?? 0 }}</strong> to <strong>{{ $leaveTypes->lastItem() ?? 0 }}</strong> of <strong>{{ $leaveTypes->total() }}</strong> leave types</p>
            <select class="filter-select" style="width: auto; padding: 6px 10px;" onchange="changePerPage(this.value)">
                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination">
            @if ($leaveTypes->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $leaveTypes->appends(request()->except('page'))->previousPageUrl() }}#types-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage('{{ $leaveTypes->appends(request()->except('page'))->previousPageUrl() }}');">‹</a>
            @endif

            @foreach ($leaveTypes->getUrlRange(1, $leaveTypes->lastPage()) as $page => $url)
                @if ($page == $leaveTypes->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $leaveTypes->appends(request()->except('page'))->url($page) }}#types-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage('{{ $leaveTypes->appends(request()->except('page'))->url($page) }}');">{{ $page }}</a>
                @endif
            @endforeach

            @if ($leaveTypes->hasMorePages())
                <a href="{{ $leaveTypes->appends(request()->except('page'))->nextPageUrl() }}#types-tab" class="page-btn" onclick="event.preventDefault(); navigateToPage('{{ $leaveTypes->appends(request()->except('page'))->nextPageUrl() }}');">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</section>

<script>
// Highlight active sort column
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const sortBy = urlParams.get('sort_by');
    const sortOrder = urlParams.get('sort_order') || 'asc';
    
    if (sortBy) {
        const columnMap = {
            'leave_code': 0,
            'leave_name': 1,
            'annual_limit': 2,
            'is_accrued': 3,
            'is_active': 5
        };
        
        const columnIndex = columnMap[sortBy];
        if (columnIndex !== undefined) {
            const headers = document.querySelectorAll('#types-tab th.sortable');
            const targetHeader = Array.from(headers).find((th, idx) => {
                const onclick = th.getAttribute('onclick');
                return onclick && onclick.includes(sortBy);
            });
            
            if (targetHeader) {
                targetHeader.classList.add('active');
                if (sortOrder === 'desc') {
                    targetHeader.classList.add('desc');
                }
            }
        }
    }
});
</script>
