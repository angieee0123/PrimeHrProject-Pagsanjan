@extends('layouts.app')

@section('content')
@php
$avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

$totalApproved = count(array_filter($leaveRequests, fn($r) => $r['status'] === 'Approved'));
$totalPending = count(array_filter($leaveRequests, fn($r) => $r['status'] === 'Pending'));
$totalDays = array_sum(array_column($leaveRequests, 'days'));
@endphp

<div class="stats-grid" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Requests</p>
            <div class="stat-icon-wrap" style="background: #0b044d18; color: #0b044d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ count($leaveRequests) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background: #15803d18; color: #15803d;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalApproved }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap" style="background: #d9bb0018; color: #d9bb00;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPending }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Days</p>
            <div class="stat-icon-wrap" style="background: #8e1e1818; color: #8e1e18;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalDays }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Across all employees</p>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
    <button class="tab-btn active" onclick="switchTab('leave')">Leave Requests</button>
    <button class="tab-btn" onclick="switchTab('benefits')">Benefits Summary</button>
    <button class="tab-btn" onclick="switchTab('types')">Leave Types</button>
</div>

<!-- Leave Requests Tab -->
<section class="table-section" id="leave-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">Leave Requests — June 2025</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ count($leaveRequests) }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option>All Departments</option>
                <option>Office of the Mayor</option>
                <option>Office of the Mun. Engineer</option>
                <option>Municipal Health Office</option>
                <option>MSWD – Pagsanjan</option>
                <option>Office of the Mun. Treasurer</option>
            </select>
            <select class="filter-select">
                <option>All Types</option>
                <option>Vacation Leave</option>
                <option>Sick Leave</option>
                <option>Maternity Leave</option>
                <option>Paternity Leave</option>
                <option>Emergency Leave</option>
                <option>Special Leave</option>
            </select>
            <select class="filter-select">
                <option>All Status</option>
                <option>Approved</option>
                <option>Pending</option>
                <option>Rejected</option>
            </select>
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;">
                + File Leave
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
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Leave Type</th>
                    <th>Date From</th>
                    <th>Date To</th>
                    <th>Days</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($leaveRequests as $index => $leave)
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($leave['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $leave['name'] }}</p>
                                <p class="emp-id">{{ $leave['empId'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag">{{ $leave['dept'] }}</span></td>
                    <td style="font-size: 13px; color: #0b044d; font-weight: 500;">{{ $leave['type'] }}</td>
                    <td style="font-size: 13px; color: #6b6a8a;">{{ $leave['from'] }}</td>
                    <td style="font-size: 13px; color: #6b6a8a;">{{ $leave['to'] }}</td>
                    <td style="font-weight: 600; color: #0b044d; font-size: 13px;">{{ $leave['days'] }}</td>
                    <td><span class="badge-status {{ $leave['status'] === 'Approved' ? 'processed' : ($leave['status'] === 'Pending' ? 'pending' : 'on-hold') }}">{{ $leave['status'] }}</span></td>
                    <td>
                        <div class="row-actions">
                            <button class="btn-view">View</button>
                            @if($leave['status'] === 'Pending')
                                <button class="btn-approve">Approve</button>
                                <button class="btn-reject">Reject</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($leaveRequests) }}</strong> of <strong>{{ count($leaveRequests) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<!-- Benefits Summary Tab -->
<section class="table-section" id="benefits-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">Benefits Summary — June 2025</h3>
            <p class="table-sub">GSIS · PhilHealth · Pag-IBIG · Leave Credits</p>
        </div>
        <div class="table-actions">
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
                    <th>Employee</th>
                    <th>GSIS Premium</th>
                    <th>PhilHealth</th>
                    <th>Pag-IBIG</th>
                    <th>VL Balance</th>
                    <th>SL Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($benefitsData as $index => $benefit)
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                                {{ getInitials($benefit['name']) }}
                            </div>
                            <div>
                                <p class="emp-name">{{ $benefit['name'] }}</p>
                                <p class="emp-id">{{ $benefit['empId'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="deduction">{{ $benefit['gsis'] }}</td>
                    <td class="deduction">{{ $benefit['philhealth'] }}</td>
                    <td class="deduction">{{ $benefit['pagibig'] }}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['vlBalance'] / 15) * 100 }}%; height: 100%; background: #0b044d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #0b044d;">{{ $benefit['vlBalance'] }} days</span>
                        </div>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <div style="flex: 1; height: 6px; background: #f0effe; border-radius: 3px; min-width: 50px;">
                                <div style="width: {{ ($benefit['slBalance'] / 15) * 100 }}%; height: 100%; background: #15803d; border-radius: 3px;"></div>
                            </div>
                            <span style="font-size: 12px; font-weight: 600; color: #15803d;">{{ $benefit['slBalance'] }} days</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <p>Showing <strong>{{ count($benefitsData) }}</strong> of <strong>{{ count($benefitsData) }}</strong> records</p>
        <div class="pagination">
            <button class="page-btn active">1</button>
            <button class="page-btn">2</button>
            <button class="page-btn">›</button>
        </div>
    </div>
</section>

<!-- Leave Types Tab -->
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
        <table class="payroll-table" id="leaveTypesTable">
            <thead>
                <tr>
                    <th style="width: 90px;">Code</th>
                    <th>Leave Type</th>
                    <th style="width: 130px;">Annual Limit</th>
                    <th style="width: 110px;">Type</th>
                    <th style="width: 110px;">Status</th>
                    <th style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leaveTypes as $type)
                <tr class="leave-type-row" data-status="{{ $type->is_active ? 'active' : 'inactive' }}" data-accrual="{{ $type->is_accrued ? 'accrued' : 'fixed' }}">
                    <td>
                        <span class="leave-code-badge">{{ $type->leave_code }}</span>
                    </td>
                    <td>
                        <div>
                            <p style="font-size: 13px; color: #0b044d; font-weight: 600; margin: 0;">{{ $type->leave_name }}</p>
                            @if($type->attachment_info)
                            <p style="font-size: 11px; color: #6b6a8a; margin: 2px 0 0 0; line-height: 1.4;">{{ Str::limit($type->attachment_info, 100) }}</p>
                            @endif
                            @if($type->is_cumulative || $type->requires_6_months || $type->is_monetizable || $type->requires_attachment)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px;">
                                @if($type->is_cumulative)
                                <span class="prop-tag" title="Unused days carry over">Cumulative</span>
                                @endif
                                @if($type->requires_6_months)
                                <span class="prop-tag" title="Requires 6 months service">6-Month Rule</span>
                                @endif
                                @if($type->is_monetizable)
                                <span class="prop-tag" title="Can be converted to cash">Monetizable</span>
                                @endif
                                @if($type->requires_attachment)
                                <span class="prop-tag" title="Requires attachment">Attachment</span>
                                @endif
                            </div>
                            @endif
                        </div>
                    </td>
                    <td style="font-weight: 600; color: #0b044d; font-size: 13px;">
                        @if($type->annual_limit > 0)
                            {{ number_format($type->annual_limit, 0) }} days
                        @else
                            <span style="color: #6b6a8a;">As needed</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge-type {{ $type->is_accrued ? 'accrued' : 'fixed' }}">{{ $type->is_accrued ? 'Accrued' : 'Fixed' }}</span>
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
                    <td colspan="6" style="text-align: center; padding: 40px; color: #6b6a8a;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 12px; opacity: 0.3;">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p style="margin: 0; font-size: 14px;">No leave types found</p>
                    </td>
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

<!-- Add Leave Type Modal -->
<div id="addLeaveTypeModal" class="modal-overlay" onclick="closeAddLeaveTypeModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Add New Leave Type</h3>
                <p class="modal-subtitle">Create a new leave type for LGU Pagsanjan</p>
            </div>
            <button class="modal-close" onclick="closeAddLeaveTypeModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <form id="addLeaveTypeForm" action="{{ route('admin.leave.types.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Leave Code <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="leave_code" class="form-input" placeholder="e.g., SL" maxlength="10" required>
                    </div>
                    <div class="form-group" style="flex: 2;">
                        <label class="form-label">Leave Name <span style="color: #8e1e18;">*</span></label>
                        <input type="text" name="leave_name" class="form-input" placeholder="e.g., Study Leave" maxlength="100" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Annual Limit (Days) <span style="color: #8e1e18;">*</span></label>
                        <input type="number" name="annual_limit" class="form-input" placeholder="e.g., 15.00" step="0.01" min="0" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label class="form-label">Status <span style="color: #8e1e18;">*</span></label>
                        <select name="is_active" class="form-input" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Leave Type Configuration</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_accrued" class="form-checkbox">
                            <span>Accrued (Earned monthly, e.g., 1.25 days/month)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_cumulative" class="form-checkbox">
                            <span>Cumulative (Unused days carry over to next year)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="requires_6_months" class="form-checkbox">
                            <span>Requires 6 Months Service (CSC requirement)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_monetizable" class="form-checkbox">
                            <span>Monetizable (Can be converted to cash)</span>
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="requires_attachment" class="form-checkbox">
                            <span>Requires Attachment (Force upload before submission)</span>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Attachment Instructions</label>
                    <textarea name="attachment_info" class="form-input" rows="2" placeholder="e.g., Medical certificate required if more than 2 consecutive days"></textarea>
                    <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Instructions shown to employees when filing this leave type</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Attach Supporting Document (Optional)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="document" id="leaveTypeDocument" class="file-input" accept=".pdf" onchange="updateFileName(this)">
                        <label for="leaveTypeDocument" class="file-upload-label">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span id="fileNameDisplay">Choose PDF file or drag here</span>
                        </label>
                    </div>
                    <p style="font-size: 11px; color: #6b6a8a; margin: 4px 0 0 0;">Upload policy document, memo, or reference file (PDF only, max 5MB)</p>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeAddLeaveTypeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">Add Leave Type</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Leave Types Management Modal -->
<div id="leaveTypesModal" class="modal-overlay" onclick="closeLeaveTypesModal(event)">
    <div class="modal-container" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <h3 class="modal-title">Manage Leave Types</h3>
                <p class="modal-subtitle">Configure leave types for LGU Pagsanjan</p>
            </div>
            <button class="modal-close" onclick="closeLeaveTypesModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body">
            <button class="btn-add-leave-type" onclick="openAddLeaveTypeForm()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add New Leave Type
            </button>

            <div class="leave-types-list">
                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">Vacation Leave (VL)</h4>
                        <p class="leave-type-desc">For rest and recreation purposes</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">15 days/year</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">Sick Leave (SL)</h4>
                        <p class="leave-type-desc">For illness or medical consultation</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">15 days/year</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">Maternity Leave</h4>
                        <p class="leave-type-desc">For female employees giving birth</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">105 days</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">Paternity Leave</h4>
                        <p class="leave-type-desc">For male employees whose spouse gives birth</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">7 days</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">Special Privilege Leave</h4>
                        <p class="leave-type-desc">For female employees (RA 9710)</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">3 days/year</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">Solo Parent Leave</h4>
                        <p class="leave-type-desc">For solo parents (RA 8972)</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">7 days/year</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>

                <div class="leave-type-item">
                    <div class="leave-type-info">
                        <h4 class="leave-type-name">VAWC Leave</h4>
                        <p class="leave-type-desc">Violence Against Women and Children (RA 9262)</p>
                    </div>
                    <div class="leave-type-meta">
                        <span class="leave-type-days">10 days</span>
                        <span class="badge-active">Active</span>
                    </div>
                    <div class="leave-type-actions">
                        <button class="btn-icon-edit" title="Edit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        <button class="btn-icon-delete" title="Delete">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.tab-btn {
    background: none; border: none; cursor: pointer;
    padding: 10px 20px; font-family: 'Poppins', sans-serif;
    font-size: 13.5px; font-weight: 600;
    color: #9999bb;
    border-bottom: 2.5px solid transparent;
    margin-bottom: -1.5px;
    transition: all 0.2s;
}
.tab-btn.active {
    color: #0b044d;
    border-bottom: 2.5px solid #0b044d;
}
.tab-btn:hover { color: #0b044d; }
.badge-emptype {
    font-size: 11px; color: #0b044d; background: #f0effe;
    padding: 3px 10px; border-radius: 20px; font-weight: 600;
    border: 1px solid #dddcf0;
}
.btn-edit {
    padding: 6px 16px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-edit:hover { background: #e8e7f5; }
.btn-approve {
    padding: 6px 16px; background: #e8f9ef; color: #15803d;
    border: 1px solid #bbf7d0; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-approve:hover { background: #d1fae5; }
.btn-reject {
    padding: 6px 16px; background: #fdf0ef; color: #8e1e18;
    border: 1px solid #fecaca; border-radius: 6px;
    font-size: 12px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-reject:hover { background: #fee8e8; }
.row-actions { display: flex; gap: 8px; justify-content: center; }
.table-footer {
    padding: 16px 24px; border-top: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: center;
}
.table-footer p { font-size: 13px; color: #6b6a8a; }
.pagination { display: flex; gap: 6px; }
.page-btn {
    width: 32px; height: 32px; border: 1px solid #e8e7f5;
    border-radius: 6px; background: #fff; color: #6b6a8a;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.page-btn.active { background: #0b044d; color: #fff; border-color: #0b044d; }
.page-btn:hover { background: #f7f6ff; }
.deduction {
    font-size: 13px; color: #8e1e18; font-weight: 600;
}
.btn-manage-types {
    padding: 8px 16px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 12.5px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
    display: flex; align-items: center; gap: 6px;
}
.btn-manage-types:hover { background: #e8e7f5; }
.modal-overlay {
    display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(11, 4, 77, 0.4); backdrop-filter: blur(2px);
    z-index: 9999; align-items: center; justify-content: center;
}
.modal-overlay.active { display: flex; }
.modal-container {
    background: #fff; border-radius: 12px; width: 90%; max-width: 700px;
    max-height: 85vh; display: flex; flex-direction: column;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.3);
}
.modal-header {
    padding: 24px; border-bottom: 1px solid #f0effe;
    display: flex; justify-content: space-between; align-items: flex-start;
}
.modal-title {
    font-size: 18px; font-weight: 700; color: #0b044d; margin: 0;
    font-family: 'Poppins', sans-serif;
}
.modal-subtitle {
    font-size: 12.5px; color: #6b6a8a; margin: 4px 0 0 0;
    font-family: 'Poppins', sans-serif;
}
.modal-close {
    background: #f7f6ff; border: 1px solid #e8e7f5; border-radius: 6px;
    width: 32px; height: 32px; display: flex; align-items: center;
    justify-content: center; cursor: pointer; transition: all 0.2s;
}
.modal-close:hover { background: #e8e7f5; }
.modal-body {
    padding: 24px; overflow-y: auto; flex: 1; background: #fafaf9;
}
.btn-add-leave-type {
    width: 100%; padding: 12px; background: #0b044d; color: #fff;
    border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
    font-family: 'Poppins', sans-serif; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all 0.2s; margin-bottom: 20px;
}
.btn-add-leave-type:hover { background: #1a0f6e; }
.leave-types-list {
    display: flex; flex-direction: column; gap: 14px;
}
.leave-type-item {
    background: #f7f6ff; border: 1px solid #e8e7f5; border-radius: 8px;
    padding: 16px; display: flex; align-items: flex-start; gap: 16px;
    transition: all 0.2s;
    flex-wrap: wrap;
}
.leave-type-item:hover { border-color: #d0cfe8; background: #fafaf9; }
.leave-type-info { flex: 1; min-width: 200px; }
.leave-type-name {
    font-size: 14px; font-weight: 600; color: #0b044d; margin: 0;
    font-family: 'Poppins', sans-serif;
}
.leave-type-desc {
    font-size: 12px; color: #6b6a8a; margin: 4px 0 0 0;
    font-family: 'Poppins', sans-serif;
}
.leave-type-meta {
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 8px;
}
.leave-type-days {
    font-size: 12px; font-weight: 600; color: #0b044d;
    background: #fff; padding: 5px 12px; border-radius: 6px;
    border: 1px solid #e8e7f5;
}
.badge-active {
    font-size: 11px; font-weight: 600; color: #15803d;
    background: #dcfce7; padding: 5px 12px; border-radius: 20px;
    border: none;
}
.leave-type-actions {
    display: flex; gap: 8px; align-self: center;
}
.btn-icon-edit, .btn-icon-delete {
    width: 36px; height: 36px; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all 0.2s; border: none;
    flex-shrink: 0;
}
.btn-icon-edit {
    background: #f0effe; color: #0b044d;
}
.btn-icon-edit:hover { background: #e8e7f5; }
.btn-icon-delete {
    background: #fef0f0; color: #8e1e18;
}
.btn-icon-delete:hover { background: #fee2e2; }
.form-group {
    margin-bottom: 16px;
}
.form-label {
    display: block; font-size: 12.5px; font-weight: 600;
    color: #0b044d; margin-bottom: 6px;
    font-family: 'Poppins', sans-serif;
}
.form-input {
    width: 100%; padding: 10px 12px; border: 1px solid #e8e7f5;
    border-radius: 6px; font-size: 13px; color: #0b044d;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.form-input:focus {
    outline: none; border-color: #0b044d;
}
.form-actions {
    display: flex; gap: 10px; margin-top: 24px;
}
.btn-cancel {
    flex: 1; padding: 10px; background: #f7f6ff; color: #0b044d;
    border: 1px solid #e8e7f5; border-radius: 6px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-cancel:hover { background: #e8e7f5; }
.btn-submit {
    flex: 1; padding: 10px; background: #0b044d; color: #fff;
    border: none; border-radius: 6px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    font-family: 'Poppins', sans-serif; transition: all 0.2s;
}
.btn-submit:hover { background: #1a0f6e; }
.leave-code-badge {
    display: inline-block; padding: 6px 12px; background: #0b044d;
    color: #fff; border-radius: 6px; font-size: 12px;
    font-weight: 700; font-family: 'Courier New', monospace;
    letter-spacing: 0.5px;
}
.badge-type {
    font-size: 11px; padding: 5px 12px; border-radius: 20px;
    font-weight: 600; border: none;
}
.badge-type.accrued {
    background: #dcfce7; color: #15803d;
}
.badge-type.fixed {
    background: #f0effe; color: #0b044d;
}
.prop-tag {
    font-size: 11px; padding: 4px 8px; background: #fff;
    color: #0b044d; border: 1px solid #e8e7f5; border-radius: 5px;
    font-weight: 500; white-space: nowrap; display: inline-block;
}
.leave-type-row.hidden {
    display: none;
}
</style>

<script>
function switchTab(tab) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    // Show/hide tab content
    document.getElementById('leave-tab').style.display = 'none';
    document.getElementById('benefits-tab').style.display = 'none';
    document.getElementById('types-tab').style.display = 'none';

    if (tab === 'leave') {
        document.getElementById('leave-tab').style.display = 'block';
    } else if (tab === 'benefits') {
        document.getElementById('benefits-tab').style.display = 'block';
    } else if (tab === 'types') {
        document.getElementById('types-tab').style.display = 'block';
    }
}

function openLeaveTypesModal() {
    document.getElementById('leaveTypesModal').classList.add('active');
}

function closeLeaveTypesModal(event) {
    if (!event || event.target.id === 'leaveTypesModal') {
        document.getElementById('leaveTypesModal').classList.remove('active');
    }
}

function openAddLeaveTypeForm() {
    alert('Add Leave Type form will be implemented next!');
}

function openAddLeaveTypeModal() {
    document.getElementById('addLeaveTypeModal').classList.add('active');
}

function closeAddLeaveTypeModal(event) {
    if (!event || event.target.id === 'addLeaveTypeModal') {
        document.getElementById('addLeaveTypeModal').classList.remove('active');
    }
}

document.getElementById('addLeaveTypeForm')?.addEventListener('submit', function(e) {
    const fileInput = document.getElementById('leaveTypeDocument');

    if (fileInput.files.length > 0) {
        const file = fileInput.files[0];
        if (file.size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            return false;
        }
    }

    // Form will submit normally to the server
});

function viewLeaveType(code) {
    alert('View details for leave type: ' + code);
}

function editLeaveType(code) {
    alert('Edit leave type: ' + code);
}

function searchLeaveTypes() {
    const searchValue = document.getElementById('searchLeaveTypes').value.toLowerCase();
    const rows = document.querySelectorAll('.leave-type-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchValue)) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

function filterLeaveTypes() {
    const statusFilter = document.getElementById('filterLeaveStatus').value;
    const accrualFilter = document.getElementById('filterLeaveAccrual').value;
    const rows = document.querySelectorAll('.leave-type-row');

    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const accrual = row.getAttribute('data-accrual');

        let showRow = true;

        if (statusFilter !== 'all' && status !== statusFilter) {
            showRow = false;
        }

        if (accrualFilter !== 'all' && accrual !== accrualFilter) {
            showRow = false;
        }

        if (showRow) {
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    });
}

function updateFileName(input) {
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const fileSize = (file.size / 1024 / 1024).toFixed(2);

        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit. Please choose a smaller file.');
            input.value = '';
            fileNameDisplay.textContent = 'Choose PDF file or drag here';
            return;
        }

        fileNameDisplay.textContent = file.name + ' (' + fileSize + ' MB)';
    } else {
        fileNameDisplay.textContent = 'Choose PDF file or drag here';
    }
}
</script>
@endsection
