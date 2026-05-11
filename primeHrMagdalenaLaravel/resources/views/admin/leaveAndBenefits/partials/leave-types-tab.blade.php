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
                    <th>Code</th>
                    <th>Leave Type</th>
                    <th>Annual Limit</th>
                    <th>Type</th>
                    <th>Attachment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                <tr class="leave-type-row" data-status="{{ $type->is_active ? 'active' : 'inactive' }}" data-accrual="{{ $type->is_accrued ? 'accrued' : 'fixed' }}">
                    <td><span class="leave-code-badge">{{ $type->leave_code }}</span></td>
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
        <p>Showing <strong>{{ $leaveTypes->firstItem() ?? 0 }}</strong> to <strong>{{ $leaveTypes->lastItem() ?? 0 }}</strong> of <strong>{{ $leaveTypes->total() }}</strong> leave types</p>
        <div class="pagination">
            @if ($leaveTypes->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $leaveTypes->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach ($leaveTypes->getUrlRange(1, $leaveTypes->lastPage()) as $page => $url)
                @if ($page == $leaveTypes->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if ($leaveTypes->hasMorePages())
                <a href="{{ $leaveTypes->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</section>
