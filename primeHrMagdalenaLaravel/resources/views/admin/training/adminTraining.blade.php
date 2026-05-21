@extends('layouts.app')

@section('content')
<div class="admin-training" data-fiscal-year="{{ date('Y') }}" data-flash-success="{{ session('success') ? '1' : '0' }}">

@include('admin.topbar.trainingTopbar')
@include('admin.notification.adminNotification')

{{-- Stats row --}}
<div class="stats-grid stats-grid-4 training-stats-grid">
    <div class="stat-card training-stat-hero">
        <div class="stat-top">
            <p class="stat-label">Pending Review</p>
            <div class="stat-icon-wrap stat-icon-wrap-warning">
                <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['pending'] }}</p>
        @php
            $reviewPct = $stats['total']
                ? (int) round((($stats['verified'] + $stats['rejected']) / $stats['total']) * 100)
                : 0;
        @endphp
        <div class="training-goal-track" aria-hidden="true">
            <div class="training-goal-fill training-goal-fill-queue" id="adminReviewGoalFill" data-goal-width="{{ $reviewPct }}"></div>
        </div>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-amber"></span>
            <p class="stat-sub">{{ $stats['verified'] + $stats['rejected'] }} of {{ $stats['total'] }} reviewed · FY {{ date('Y') }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Verified</p>
            <div class="stat-icon-wrap stat-icon-wrap-success">
                <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['verified'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-success"></span>
            <p class="stat-sub">Approved for PDS credit</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Rejected</p>
            <div class="stat-icon-wrap stat-icon-wrap-danger">
                <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['rejected'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-danger"></span>
            <p class="stat-sub">Sent back for correction</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Submissions</p>
            <div class="stat-icon-wrap stat-icon-wrap-primary">
                <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </div>
        <p class="stat-value">{{ $stats['total'] }}</p>
        <div class="stat-footer">
            <span class="stat-dot stat-dot-primary"></span>
            <p class="stat-sub">Permanent employee records · FY {{ date('Y') }}</p>
        </div>
    </div>
</div>

{{-- HR workflow note --}}
<div class="training-enroll-note admin-training-workflow-note">
    <svg width="16" height="16" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" class="training-enroll-note-icon" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <p class="training-enroll-note-text">Review the certificate and reference document for each submission. <strong>Approve</strong> to credit L&amp;D hours to the employee's PDS record, or <strong>Reject</strong> with a reason so they can correct and re-submit.</p>
</div>

{{-- Submissions table --}}
<div class="table-section">
    <div class="table-header">
        <div>
            <p class="table-title">Training Verification Queue</p>
            <p class="table-sub">Section IV — Learning &amp; Development (CSC PDS format)</p>
        </div>
        <div class="table-actions">
            <a href="{{ route('admin.training.export') }}" class="btn-export">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Report
            </a>
        </div>
    </div>

    <div class="training-filter-bar">
        <div class="training-filter-chips" role="group" aria-label="Filter by status">
            <button type="button" class="training-filter-chip active" onclick="setAdminStatusFilter('all', this)">All</button>
            <button type="button" class="training-filter-chip" onclick="setAdminStatusFilter('pending', this)">Pending</button>
            <button type="button" class="training-filter-chip" onclick="setAdminStatusFilter('verified', this)">Verified</button>
            <button type="button" class="training-filter-chip" onclick="setAdminStatusFilter('rejected', this)">Rejected</button>
        </div>
        <select id="adminPositionFilter" class="filter-select" onchange="filterAdminTraining()">
            <option value="">All position types</option>
            <option value="Managerial">Managerial</option>
            <option value="Supervisory">Supervisory</option>
            <option value="Technical">Technical</option>
            <option value="Clerical">Clerical</option>
        </select>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table training-pds-table" id="adminTrainingTable">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Title of Seminar / Conference / Training Program</th>
                    <th>Inclusive Dates</th>
                    <th>No. of Hours</th>
                    <th>Type of Position</th>
                    <th>Conducted / Sponsored By</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="adminTrainingBody">
                @forelse($trainings as $t)
                @php
                    $emp      = $t->employee;
                    $dept     = $emp->employmentDetail->departmentRelation->name ?? 'N/A';
                    $badgeClass = match($t->position_type) {
                        'Managerial'  => 'managerial',
                        'Supervisory' => 'supervisory',
                        'Technical'   => 'technical',
                        'Clerical'    => 'clerical',
                        default       => 'technical',
                    };
                @endphp
                <tr class="row-{{ $t->status }}"
                    data-id="{{ $t->id }}"
                    data-status="{{ $t->status }}"
                    data-hours="{{ $t->hours }}"
                    data-position="{{ $t->position_type }}"
                    data-ref="{{ $t->ref_doc_no }}"
                    data-employee="{{ $emp->first_name }} {{ $emp->last_name }}"
                    data-emp-id="{{ $emp->employee_id }}"
                    data-dept="{{ $dept }}"
                    data-title="{{ $t->title }}"
                    data-date-from="{{ $t->date_from ? $t->date_from->format('M d, Y') : '—' }}"
                    data-date-to="{{ $t->date_to ? $t->date_to->format('M d, Y') : '—' }}"
                    data-conducted="{{ $t->conducted_by }}"
                    data-cert-url="{{ $t->certificate_path ? route('admin.training.certificate', $t->id) : '' }}"
                    data-submitted="{{ $t->created_at ? $t->created_at->format('M d, Y') : '—' }}"
                    data-verified="{{ $t->verified_at ? $t->verified_at->format('M d, Y') : '' }}"
                    data-reject-note="{{ $t->rejected_reason }}">
                    <td>
                        <div class="admin-employee-cell">
                            <span class="admin-employee-name">{{ $emp->first_name }} {{ $emp->last_name }}</span>
                            <span class="admin-employee-meta">{{ $emp->employee_id }} · {{ $dept }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="training-title-wrap">
                            <span class="training-title-text">{{ $t->title }}</span>
                            <span class="training-ref-doc">{{ $t->ref_doc_no }}</span>
                        </div>
                    </td>
                    <td class="training-table-date">
                        <span class="training-date-from">{{ $t->date_from ? $t->date_from->format('M d, Y') : '—' }}</span>
                        <span class="training-date-sep">–</span>
                        <span class="training-date-to">{{ $t->date_to ? $t->date_to->format('M d, Y') : '—' }}</span>
                    </td>
                    <td>
                        <span class="training-hours-pill {{ $t->status === 'rejected' ? 'training-hours-pill-muted' : ($t->status === 'pending' ? 'training-hours-pill-pending' : '') }}">
                            {{ $t->status === 'rejected' ? 0 : $t->hours }}
                        </span>
                    </td>
                    <td><span class="type-badge {{ $badgeClass }}">{{ $t->position_type }}</span></td>
                    <td>{{ $t->conducted_by }}</td>
                    <td>
                        @if($t->status === 'verified')
                            <span class="verify-badge verified">Verified</span>
                        @elseif($t->status === 'rejected')
                            <span class="verify-badge rejected" title="{{ $t->rejected_reason }}">Rejected</span>
                        @else
                            <span class="verify-badge pending">Pending</span>
                        @endif
                    </td>
                    <td>
                        <div class="admin-action-group">
                            <button type="button" class="btn-admin-review {{ $t->status !== 'pending' ? 'btn-admin-review-muted' : '' }}" onclick="reviewSubmission(this)">Review</button>
                            @if($t->status === 'pending')
                            <form method="POST" action="{{ route('admin.training.approve', $t->id) }}" style="margin:0;" onsubmit="return confirm('Approve this training submission?')">
                                @csrf
                                <button type="submit" class="btn-admin-approve" title="Approve">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                </button>
                            </form>
                            <button type="button" class="btn-admin-reject" title="Reject" onclick="openRejectModal(this)">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:40px;color:#9999bb;">
                        No training submissions yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="adminRowFooter">Showing <strong id="adminRowStart">1</strong>-<strong id="adminRowEnd">{{ min(10, $trainings->count()) }}</strong> of <strong id="adminRowTotal">{{ $trainings->count() }}</strong> records</p>
            <select id="adminRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="adminPaginationControls"></div>
    </div>
</div>

</div>

<div class="training-toast" id="adminTrainingToast" role="alert" aria-live="polite"></div>

{{-- Review Modal --}}
<div class="modal-overlay training-modal-overlay" id="reviewModal" onclick="closeAdminModal('reviewModal')">
    <div class="modal-box training-view-cert-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon training-hero-icon">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow">SUBMISSION REVIEW</span>
                    <h3 class="modal-title" id="rvTitle">—</h3>
                    <p class="modal-sub" id="rvEmployee">—</p>
                    <div class="pmodal-badges" id="rvBadges"></div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeAdminModal('reviewModal')" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p class="modal-section-label">TRAINING DETAILS (CSC PDS)</p>
            <div class="training-modal-meta" id="rvDetails"></div>
            <div class="training-enroll-note admin-reject-note" id="rvRejectBanner" hidden>
                <svg width="16" height="16" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" class="training-enroll-note-icon"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p class="training-enroll-note-text" id="rvRejectText"></p>
            </div>
            <p class="modal-section-label modal-section-deductions">CERTIFICATE ON FILE</p>
            <div class="training-cert-preview" id="rvPreview">
                <div class="training-cert-preview-icon" id="rvFileIcon"></div>
                <div>
                    <p class="training-cert-preview-name" id="rvFile">—</p>
                    <p class="training-cert-preview-note">Submitted by employee — verify against reference document</p>
                </div>
                <a id="rvCertLink" href="#" target="_blank" class="modal-btn-ghost" style="margin-left:auto;padding:6px 14px;font-size:12px;">View File</a>
            </div>
        </div>
        <div class="modal-footer" id="rvFooterActions">
            <button type="button" class="modal-btn-ghost" onclick="closeAdminModal('reviewModal')">Close</button>
            <button type="button" class="modal-btn-danger-outline" id="rvRejectBtn" onclick="rejectFromReview()" style="display:none;">Reject</button>
            <button type="button" class="modal-btn-primary" id="rvApproveBtn" onclick="approveFromReview()" style="display:none;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay training-modal-overlay" id="rejectModal" onclick="closeAdminModal('rejectModal')">
    <div class="modal-box training-add-modal" onclick="event.stopPropagation()">
        <form id="rejectForm" method="POST" onsubmit="submitReject(event)">
            @csrf
            <input type="hidden" id="rejectTrainingId" name="training_id">
            <div class="modal-header">
                <div class="pmodal-hero">
                    <div class="pmodal-hero-icon admin-reject-hero-icon">
                        <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div>
                        <span class="modal-eyebrow">REJECT SUBMISSION</span>
                        <h3 class="modal-title">Send Back for Correction</h3>
                        <p class="modal-sub" id="rejectModalSub">—</p>
                    </div>
                </div>
                <button type="button" class="modal-close" onclick="closeAdminModal('rejectModal')" aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="training-form-field training-form-full">
                    <label for="rejectReason">Reason for rejection <span class="req">*</span></label>
                    <textarea id="rejectReason" name="reason" rows="4" required placeholder="Explain what needs to be corrected (e.g. certificate date mismatch, illegible scan, missing reference number)..."></textarea>
                    <p class="training-field-hint">The employee will see this message and can re-submit after correcting their documents.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-ghost" onclick="closeAdminModal('rejectModal')">Cancel</button>
                <button type="submit" class="modal-btn-danger">Confirm Rejection</button>
            </div>
        </form>
    </div>
</div>

{{-- Flash success modal --}}
@if(session('success'))
<div class="modal-overlay training-modal-overlay" id="adminFlashSuccessModal" onclick="closeAdminModal('adminFlashSuccessModal')">
    <div class="modal-box admin-success-modal" onclick="event.stopPropagation()" role="dialog" aria-labelledby="adminSuccessTitle" aria-modal="true">
        <div class="admin-success-modal-accent" aria-hidden="true"></div>
        <div class="admin-success-modal-body">
            <div class="admin-success-icon-wrap">
                <span class="admin-success-icon-ring" aria-hidden="true"></span>
                <span class="admin-success-icon-ring admin-success-icon-ring-delay" aria-hidden="true"></span>
                <svg class="admin-success-icon" width="32" height="32" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <span class="admin-success-eyebrow">HR VERIFICATION</span>
            <h3 class="admin-success-title" id="adminSuccessTitle">Successfully Saved</h3>
            <p class="admin-success-message">{{ session('success') }}</p>
        </div>
        <div class="modal-footer admin-success-footer">
            <button type="button" class="modal-btn-primary admin-success-btn" onclick="closeAdminModal('adminFlashSuccessModal')">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                Continue
            </button>
        </div>
    </div>
</div>
@endif

<script>
(function () {
    let activeRow = null;

    window.openAdminModal = id => {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
    };
    window.closeAdminModal = id => {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
        const anyOpen = Array.from(document.querySelectorAll('.training-modal-overlay')).some(m => m.style.display === 'flex');
        if (!anyOpen) document.body.style.overflow = '';
    };

    function showToast(msg) {
        const t = document.getElementById('adminTrainingToast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(showToast._t);
        showToast._t = setTimeout(() => t.classList.remove('show'), 3200);
    }

    window._currentPage = 1;
    window._rowsPerPage = 10;

    window.filterAdminTraining = function () {
        const posFilter = document.getElementById('adminPositionFilter')?.value || '';
        const q = (document.getElementById('adminTrainingSearch')?.value || '').toLowerCase().trim();
        const allRows = document.querySelectorAll('#adminTrainingBody tr[data-id]');
        const filtered = [];
        
        allRows.forEach(row => {
            const matchStatus   = !window._adminStatusFilter || window._adminStatusFilter === 'all' || row.dataset.status === window._adminStatusFilter;
            const matchPosition = !posFilter || row.dataset.position === posFilter;
            const matchSearch   = !q || [row.dataset.employee, row.dataset.empId, row.dataset.dept, row.dataset.title, row.dataset.ref].join(' ').toLowerCase().includes(q);
            if (matchStatus && matchPosition && matchSearch) filtered.push(row);
        });
        
        window._filteredRows = filtered;
        window._currentPage = 1;
        updatePagination();
    };

    window.updatePagination = function () {
        const rows = window._filteredRows || [];
        const total = rows.length;
        const perPage = window._rowsPerPage;
        const totalPages = Math.ceil(total / perPage) || 1;
        const page = Math.min(window._currentPage, totalPages);
        window._currentPage = page;
        
        const start = (page - 1) * perPage;
        const end = Math.min(start + perPage, total);
        
        document.querySelectorAll('#adminTrainingBody tr[data-id]').forEach(row => row.style.display = 'none');
        rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });
        
        document.getElementById('adminRowStart').textContent = total ? start + 1 : 0;
        document.getElementById('adminRowEnd').textContent = end;
        document.getElementById('adminRowTotal').textContent = total;
        
        const controls = document.getElementById('adminPaginationControls');
        if (totalPages <= 1) { controls.innerHTML = ''; return; }
        
        let html = '';
        const maxVisible = 5;
        let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);
        
        if (page > 1) html += '<button class="page-btn" onclick="goToPage(' + (page - 1) + ')">‹</button>';
        if (startPage > 1) {
            html += '<button class="page-btn" onclick="goToPage(1)">1</button>';
            if (startPage > 2) html += '<span style="padding:0 8px;color:#9999bb;">...</span>';
        }
        for (let i = startPage; i <= endPage; i++) {
            html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToPage(' + i + ')">' + i + '</button>';
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span style="padding:0 8px;color:#9999bb;">...</span>';
            html += '<button class="page-btn" onclick="goToPage(' + totalPages + ')">' + totalPages + '</button>';
        }
        if (page < totalPages) html += '<button class="page-btn" onclick="goToPage(' + (page + 1) + ')">›</button>';
        
        controls.innerHTML = html;
    };

    window.goToPage = function (page) {
        window._currentPage = page;
        updatePagination();
    };

    window.changeRowsPerPage = function () {
        window._rowsPerPage = parseInt(document.getElementById('adminRowsPerPage').value) || 10;
        window._currentPage = 1;
        updatePagination();
    };

    window._adminStatusFilter = 'all';
    window.setAdminStatusFilter = function (status, btn) {
        window._adminStatusFilter = status;
        document.querySelectorAll('.training-filter-chip').forEach(c => c.classList.toggle('active', c === btn));
        window._currentPage = 1;
        filterAdminTraining();
    };

    window.reviewSubmission = function (btn) {
        const row = btn.closest('tr');
        if (!row) return;
        activeRow = row;
        const d = row.dataset;
        document.getElementById('rvTitle').textContent = d.title;
        document.getElementById('rvEmployee').textContent = d.employee + ' · ' + d.dept + ' · Submitted ' + (d.submitted || '—');

        const badgeClass = (d.position || '').toLowerCase();
        const statusMap  = { verified: 'verified', rejected: 'rejected', pending: 'pending' };
        document.getElementById('rvBadges').innerHTML =
            '<span class="verify-badge ' + (statusMap[d.status] || 'pending') + '">' + (d.status.charAt(0).toUpperCase() + d.status.slice(1)) + '</span>' +
            '<span class="type-badge ' + badgeClass + '">' + d.position + '</span>';

        document.getElementById('rvDetails').innerHTML = [
            ['Employee',              d.employee + ' (' + d.empId + ')'],
            ['Department',            d.dept],
            ['Inclusive Dates',       d.dateFrom + ' – ' + d.dateTo],
            ['Number of Hours',       d.status === 'rejected' ? '0 (not credited)' : d.hours],
            ['Type of Position',      d.position],
            ['Conducted / Sponsored By', d.conducted],
            ['Reference Document',    d.ref],
            ['Submitted',             d.submitted || '—'],
            ['Verified',              d.verified  || '—'],
        ].map(p => '<div class="modal-row"><span>' + p[0] + '</span><strong>' + p[1] + '</strong></div>').join('');

        const rejectBanner = document.getElementById('rvRejectBanner');
        if (d.status === 'rejected' && d.rejectNote) {
            rejectBanner.hidden = false;
            document.getElementById('rvRejectText').textContent = d.rejectNote;
        } else {
            rejectBanner.hidden = true;
        }

        const certUrl = d.certUrl || '';
        document.getElementById('rvFile').textContent = certUrl ? 'Certificate on file' : 'No file uploaded';
        const certLink = document.getElementById('rvCertLink');
        if (certUrl) { certLink.href = certUrl; certLink.style.display = ''; }
        else { certLink.style.display = 'none'; }

        const isPdf = certUrl.toLowerCase().includes('.pdf') || !certUrl.match(/\.(jpg|jpeg|png)$/i);
        document.getElementById('rvFileIcon').innerHTML = isPdf
            ? '<svg width="40" height="40" fill="none" stroke="#8e1e18" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'
            : '<svg width="40" height="40" fill="none" stroke="#0369a1" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';

        const isPending = d.status === 'pending';
        document.getElementById('rvApproveBtn').style.display = isPending ? '' : 'none';
        document.getElementById('rvRejectBtn').style.display  = isPending ? '' : 'none';

        openAdminModal('reviewModal');
    };

    window.approveFromReview = function () {
        if (!activeRow || activeRow.dataset.status !== 'pending') return;
        closeAdminModal('reviewModal');
        // Submit approve form programmatically
        const id = activeRow.dataset.id;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/training/' + id + '/approve';
        const csrf = document.createElement('input');
        csrf.type = 'hidden'; csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    };

    window.openRejectModal = function (btn) {
        activeRow = btn.closest ? btn.closest('tr') : btn;
        if (!activeRow) return;
        const id = activeRow.dataset.id;
        if (!id) { showToast('Could not find training ID.'); return; }
        document.getElementById('rejectTrainingId').value = id;
        document.getElementById('rejectForm').action = '/admin/training/' + id + '/reject';
        document.getElementById('rejectModalSub').textContent = activeRow.dataset.title + ' — ' + activeRow.dataset.employee;
        document.getElementById('rejectReason').value = '';
        closeAdminModal('reviewModal');
        openAdminModal('rejectModal');
    };

    window.rejectFromReview = function () {
        if (activeRow) openRejectModal(activeRow);
    };

    window.submitReject = function (e) {
        e.preventDefault();
        const reason = document.getElementById('rejectReason').value.trim();
        if (!reason) { showToast('Please enter a rejection reason.'); return; }
        // Remove onsubmit to prevent loop, then submit natively
        const form = document.getElementById('rejectForm');
        form.onsubmit = null;
        form.submit();
    };

    filterAdminTraining();

    const reviewFill = document.getElementById('adminReviewGoalFill');
    if (reviewFill && reviewFill.dataset.goalWidth !== undefined) {
        reviewFill.style.width = reviewFill.dataset.goalWidth + '%';
    }

    const adminRoot = document.querySelector('.admin-training');
    if (adminRoot && adminRoot.dataset.flashSuccess === '1') {
        openAdminModal('adminFlashSuccessModal');
    }
})();
</script>
@endsection
