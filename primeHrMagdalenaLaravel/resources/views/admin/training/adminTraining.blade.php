@extends('layouts.app')

@section('content')
<div class="admin-training glass-shell" data-fiscal-year="{{ date('Y') }}" data-flash-success="{{ session('success') ? '1' : '0' }}">

@include('admin.topbar.trainingTopbar')
@include('admin.notification.adminNotification')

{{-- Stats row --}}
<div class="stats-grid stats-grid-4 training-stats-grid">
    <div class="stat-card training-stat-hero">
        <div class="stat-top">
            <p class="stat-label">Pending Review</p>
            <div class="stat-icon-wrap stat-icon-wrap-warning">
                <svg width="17" height="17" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
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
    <svg width="16" height="16" fill="none" stroke="#c9a227" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" class="training-enroll-note-icon" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
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
            {{-- A button rather than a bare link: the CSV is built by
                 TrainingExportController from the records themselves, and the
                 status chips, position filter and search box on this page are
                 handed to it as query params so the file covers the queue the
                 reviewer is actually looking at. --}}
            <button type="button" class="btn-export" data-export-url="{{ route('admin.training.export') }}"
                    onclick="exportTrainingReport(this)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Report
            </button>
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
        {{--
            The column widths are declared once, here, and the table is
            `table-layout: fixed` -- so a sponsor name of eighty characters
            can no longer steal the width from the hours column and leave the
            header labels sitting over the wrong content. Each column's
            alignment is set on `th` and `td` together in adminTraining.css
            (one `:is(th, td):nth-child(n)` rule per column), which is what
            keeps a centred figure under a centred heading.
        --}}
        <table class="payroll-table training-pds-table" id="adminTrainingTable">
            <colgroup>
                <col class="tcol-employee">
                <col class="tcol-title">
                <col class="tcol-dates">
                <col class="tcol-hours">
                <col class="tcol-position">
                <col class="tcol-sponsor">
                <col class="tcol-status">
                <col class="tcol-actions">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col">Employee</th>
                    <th scope="col">Title of Seminar / Conference / Training Program</th>
                    <th scope="col">Inclusive Dates</th>
                    <th scope="col">No. of Hours</th>
                    <th scope="col">Type of Position</th>
                    <th scope="col">Conducted / Sponsored By</th>
                    <th scope="col">Status</th>
                    <th scope="col" class="row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody id="adminTrainingBody">
                @forelse($trainings as $t)
                @php
                    $emp      = $t->employee;
                    $empName  = $emp ? trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? '')) : '';
                    $empName  = $empName !== '' ? $empName : 'Unknown Employee';
                    $dept     = $emp?->employmentDetail?->departmentRelation?->name ?? 'N/A';
                    $badgeClass = match($t->position_type) {
                        'Managerial'  => 'managerial',
                        'Supervisory' => 'supervisory',
                        'Technical'   => 'technical',
                        'Clerical'    => 'clerical',
                        default       => 'technical',
                    };
                    $initials = $emp ? strtoupper(mb_substr($emp->first_name ?? 'U', 0, 1) . mb_substr($emp->last_name ?? 'E', 0, 1)) : 'UE';
                    $hasCert  = (bool) $t->certificate_path;
                    // Inclusive of both ends -- a one-day seminar reads "1 day",
                    // not "0". Null whenever either end was never recorded.
                    $dayCount = ($t->date_from && $t->date_to)
                        ? (int) abs($t->date_from->diffInDays($t->date_to)) + 1
                        : null;
                @endphp
                <tr class="training-row-{{ $t->status }}"
                    data-id="{{ $t->id }}"
                    data-status="{{ $t->status }}"
                    data-hours="{{ $t->hours }}"
                    data-position="{{ $t->position_type }}"
                    data-ref="{{ $t->ref_doc_no }}"
                    data-employee="{{ $empName }}"
                    data-first-name="{{ $emp->first_name ?? 'Employee' }}"
                    data-photo="{{ $emp->photo ?? '' }}"
                    data-initials="{{ $initials }}"
                    data-approve-url="{{ route('admin.training.approve', $t->id) }}"
                    data-reject-url="{{ route('admin.training.reject', $t->id) }}"
                    data-emp-id="{{ $emp->employee_id ?? '—' }}"
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
                        {{-- The face of whoever filed the certificate. The same
                             photo the review dialog shows, so the row and the
                             dialog opened from it read as one person. --}}
                        <div class="admin-employee-cell">
                            <span class="training-avatar" aria-hidden="true">
                                @if($emp?->photo)
                                    <img src="{{ $emp->photo }}" alt="" loading="lazy">
                                @else
                                    {{ $initials }}
                                @endif
                            </span>
                            <span class="admin-employee-text">
                                <span class="admin-employee-name">{{ $empName }}</span>
                                <span class="admin-employee-meta">{{ $emp->employee_id ?? '—' }} · {{ $dept }}</span>
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="training-title-wrap">
                            <span class="training-title-text" title="{{ $t->title }}">{{ $t->title }}</span>
                            <span class="training-title-meta">
                                <span class="training-ref-doc">{{ $t->ref_doc_no ?: 'No ref. no.' }}</span>
                                {{-- Whether anything was attached is the first
                                     thing a verifier checks, so it is on the row
                                     rather than only inside the dialog. --}}
                                @if($hasCert)
                                    <span class="training-cert-flag" title="Certificate attached">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                        Certificate
                                    </span>
                                @else
                                    <span class="training-cert-flag is-missing" title="No certificate attached">No file</span>
                                @endif
                            </span>
                        </div>
                    </td>
                    <td>
                        <div class="training-table-date">
                            <span class="training-date-range">
                                {{ $t->date_from ? $t->date_from->format('M d, Y') : '—' }}
                                <span class="training-date-sep" aria-hidden="true">→</span>
                                {{ $t->date_to ? $t->date_to->format('M d, Y') : '—' }}
                            </span>
                            @if($dayCount)
                                <span class="training-date-span">{{ $dayCount }} {{ $dayCount === 1 ? 'day' : 'days' }}</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="training-hours-pill {{ $t->status === 'rejected' ? 'training-hours-pill-muted' : ($t->status === 'pending' ? 'training-hours-pill-pending' : '') }}">
                            {{ $t->status === 'rejected' ? 0 : $t->hours }}
                        </span>
                    </td>
                    <td><span class="type-badge {{ $badgeClass }}">{{ $t->position_type }}</span></td>
                    <td><span class="training-sponsor" title="{{ $t->conducted_by }}">{{ $t->conducted_by }}</span></td>
                    <td>
                        @if($t->status === 'verified')
                            <span class="verify-badge verified">Verified</span>
                        @elseif($t->status === 'rejected')
                            <span class="verify-badge rejected" title="{{ $t->rejected_reason }}">Rejected</span>
                        @else
                            <span class="verify-badge pending">Pending</span>
                        @endif
                    </td>
                    <td class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="trainingAdminMenu{{ $t->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for {{ $t->title }}">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="trainingAdminMenu{{ $t->id }}" role="menu" aria-label="Training submission actions">
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); reviewSubmission({{ $t->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
                                Review submission
                            </button>
                        @if($t->status === 'pending')
                            <div class="row-menu-sep"></div>
                            <button type="button" role="menuitem" class="row-menu-item is-accept" onclick="closeRowMenu(); openTrainingDecision({{ $t->id }}, 'approve')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                Approve
                            </button>
                            <button type="button" role="menuitem" class="row-menu-item is-danger" onclick="closeRowMenu(); openTrainingDecision({{ $t->id }}, 'reject')">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                Reject
                            </button>
                        @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr class="training-empty-row">
                    <td colspan="8">
                        <div class="training-empty">
                            <span class="training-empty-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>
                            </span>
                            <p class="training-empty-title">No training submissions yet</p>
                            <p class="training-empty-text">Certificates employees file from their own Training page arrive here for verification.</p>
                        </div>
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

{{--
    Submission review.

    The reviewer's job here is a judgement: are these hours real, and does the
    certificate back them up? The old layout put every field in one flat
    nine-row list, so the three facts that answer that question -- how many
    hours are claimed, over which days, and whether anything was uploaded --
    read with exactly the same weight as the reference document number.

    So the figures being judged lead, as three tiles; who filed it comes next
    as a named face; and the PDS paperwork sits below as rows, with the
    employee and the hours no longer repeated there. The certificate is a file
    card with a real button, not a dashed empty-state box drawn around a file
    that exists.
--}}
<div class="modal-overlay training-modal-overlay" id="reviewModal" onclick="closeAdminModal('reviewModal')">
    <div class="modal-box training-view-cert-modal" onclick="event.stopPropagation()"
         role="dialog" aria-modal="true" aria-labelledby="rvTitle">
        <div class="modal-header rv-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon training-hero-icon">
                    <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div class="rv-heading">
                    <span class="modal-eyebrow">SUBMISSION REVIEW</span>
                    {{-- The seminar title, which routinely runs past one line. --}}
                    <h3 class="modal-title rv-title" id="rvTitle">-</h3>
                    <div class="pmodal-badges" id="rvBadges"></div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeAdminModal('reviewModal')" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="modal-body rv-body">
            {{-- The three facts the decision turns on. --}}
            <div class="rv-figures">
                <div class="rv-figure rv-figure-lead">
                    <span class="rv-figure-label">Hours claimed</span>
                    <strong class="rv-figure-value" id="rvHours">-</strong>
                    <span class="rv-figure-note" id="rvHoursNote"></span>
                </div>
                <div class="rv-figure">
                    <span class="rv-figure-label">Inclusive dates</span>
                    <strong class="rv-figure-value rv-figure-value-sm" id="rvDates">-</strong>
                    <span class="rv-figure-note" id="rvDatesNote"></span>
                </div>
                <div class="rv-figure" id="rvCertFigure">
                    <span class="rv-figure-label">Certificate</span>
                    <strong class="rv-figure-value rv-figure-value-sm" id="rvCertState">-</strong>
                    <span class="rv-figure-note" id="rvCertNote"></span>
                </div>
            </div>

            <p class="modal-section-label rv-section-label">Employee</p>
            <div class="rv-employee">
                <span class="rv-avatar" id="rvAvatar">--</span>
                <div class="rv-employee-text">
                    <p class="rv-employee-name" id="rvEmployee">-</p>
                    <p class="rv-employee-meta" id="rvEmployeeMeta">-</p>
                </div>
            </div>

            <p class="modal-section-label rv-section-label">Submission details · CSC PDS Section IV</p>
            <div class="rv-details" id="rvDetails"></div>

            {{-- Rejected submissions only: the reason already on record. --}}
            <div class="rv-returned" id="rvRejectBanner" hidden>
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <div class="rv-returned-text-wrap">
                    <p class="rv-returned-label">Returned for correction</p>
                    <p class="rv-returned-text" id="rvRejectText"></p>
                </div>
            </div>

            <p class="modal-section-label rv-section-label">Certificate on file</p>
            <div class="rv-file" id="rvPreview">
                <span class="rv-file-icon" id="rvFileIcon" aria-hidden="true"></span>
                <div class="rv-file-text">
                    <p class="rv-file-name" id="rvFile">-</p>
                    <p class="rv-file-note" id="rvFileNote">-</p>
                </div>
                <a id="rvCertLink" href="#" target="_blank" rel="noopener" class="rv-file-btn">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    View file
                </a>
            </div>
        </div>

        {{-- Close sits apart from the two decisions, so a dismissal is never
             one slip of the mouse away from an approval. --}}
        <div class="modal-footer rv-footer" id="rvFooterActions">
            <button type="button" class="modal-btn-ghost rv-close-btn" onclick="closeAdminModal('reviewModal')">Close</button>
            <button type="button" class="modal-btn-danger-outline" id="rvRejectBtn" onclick="decideFromReview('reject')" hidden>
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                Reject
            </button>
            <button type="button" class="modal-btn-primary" id="rvApproveBtn" onclick="decideFromReview('approve')" hidden>
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                Approve
            </button>
        </div>
    </div>
</div>

{{--
    Approve / reject decision.

    The Approve item used to be `confirm('Approve this training submission?')`
    and Reject a bare reason box titled only "Send Back for Correction". Both
    sit behind the same ellipsis menu on a table that can hold dozens of
    pending rows, so neither question named the submission being decided — the
    reviewer answered it about whichever row they *believed* they had clicked.

    One dialog answers both, built from the row that was pressed, because the
    submission summary is identical either way; only the accent, the wording,
    the reason field and the consequence differ. Splitting it into two copies
    is how an approval starts describing a different submission than the
    refusal beside it.

    The consequence is written per decision because the two do opposite things
    to the employee's record: verifying credits the hours to CSC PDS Section IV
    (they are what `EmployeeTrainingController` sums into `total_hours` and the
    L&D breakdown), while rejecting zeroes them and hands back a reason the
    employee reads on their own copy.
--}}
<x-modal id="trainingDecisionModal" overlay-class="modal-overlay training-modal-overlay"
         close="closeTrainingDecision" max-width="560px">
    <div class="tdm-head">
        <div class="tdm-icon" id="tdmIcon">
            {{-- Swapped for the reject glyph by JS. --}}
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
        </div>
        <span class="tdm-eyebrow" id="tdmEyebrow">TRAINING SUBMISSION</span>
        <h3 class="tdm-title" id="tdmTitle">-</h3>
        <p class="tdm-lede" id="tdmLede">-</p>
    </div>

    <div class="modal-body tdm-body">
        {{-- Whose record this credits. --}}
        <div class="tdm-filer">
            <span class="tdm-avatar" id="tdmAvatar">--</span>
            <div class="tdm-filer-text">
                <p class="tdm-filer-name" id="tdmEmployee">-</p>
                <p class="tdm-filer-role" id="tdmEmployeeMeta">-</p>
            </div>
            <span class="tdm-ref" id="tdmRefDoc">-</span>
        </div>

        {{-- What is being decided. The hours lead: they are the figure this
             decision writes to, or withholds from, the PDS. --}}
        <div class="tdm-slip">
            <div class="tdm-slip-row">
                <span class="tdm-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Hours · position
                </span>
                <strong class="tdm-slip-value" id="tdmHours">-</strong>
            </div>
            <div class="tdm-slip-row">
                <span class="tdm-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Inclusive dates
                </span>
                <strong class="tdm-slip-value" id="tdmDates">-</strong>
            </div>
            <div class="tdm-slip-row">
                <span class="tdm-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg>
                    Conducted by
                </span>
                <strong class="tdm-slip-value" id="tdmConducted">-</strong>
            </div>
            <div class="tdm-slip-row">
                <span class="tdm-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Certificate
                </span>
                <strong class="tdm-slip-value" id="tdmCert">-</strong>
            </div>
            <div class="tdm-slip-row tdm-slip-training">
                <span class="tdm-slip-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1.66 2.69 3 6 3s6-1.34 6-3v-5"/></svg>
                    Title of training
                </span>
                <span class="tdm-slip-training-text" id="tdmTraining">-</span>
            </div>
        </div>

        {{-- Approve only, and only when nothing was uploaded: there is then
             no document to have verified the hours against. --}}
        <p class="tdm-warn" id="tdmWarn" hidden>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span id="tdmWarnText"></span>
        </p>

        {{-- Reject only: the reason, as a real field carrying the
             controller's own `required|string|max:1000`. --}}
        <div class="tdm-note" id="tdmNoteBlock" hidden>
            <label class="tdm-note-label" for="tdmNote">
                What needs to be corrected? <span class="tdm-required">Required</span>
            </label>
            <textarea id="tdmNote" class="tdm-note-input" rows="3" maxlength="1000"
                      placeholder="e.g. The certificate dates do not match the inclusive dates declared — re-upload the certificate for these exact days."></textarea>
            <div class="tdm-note-foot">
                <span>The employee reads this on their own submission.</span>
                <span id="tdmNoteCount">0 / 1000</span>
            </div>
        </div>

        <p class="tdm-consequence" id="tdmConsequence">-</p>
    </div>

    <div class="modal-footer tdm-footer">
        <button type="button" class="modal-btn-ghost" id="tdmCancel" onclick="closeTrainingDecision()">Go back</button>
        <button type="button" class="modal-btn-primary" id="tdmConfirm" onclick="submitTrainingDecision()">
            <span id="tdmConfirmLabel">Confirm</span>
        </button>
    </div>
</x-modal>

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

        // No-results feedback, wearing the same empty state the server-rendered
        // "no submissions yet" row does -- a filter that matches nothing should
        // not look like a different page from an empty queue.
        let emptyRow = document.getElementById('adminTrainingNoResults');
        if (total === 0) {
            if (!emptyRow) {
                emptyRow = document.createElement('tr');
                emptyRow.id = 'adminTrainingNoResults';
                emptyRow.className = 'training-empty-row';
                emptyRow.innerHTML =
                    '<td colspan="8"><div class="training-empty">' +
                    '<span class="training-empty-icon" aria-hidden="true">' +
                    '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
                    '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>' +
                    '<p class="training-empty-title">No matching submissions</p>' +
                    '<p class="training-empty-text">No record matches the filters and search terms currently applied.</p>' +
                    '</div></td>';
                document.getElementById('adminTrainingBody').appendChild(emptyRow);
            }
        } else if (emptyRow) {
            emptyRow.remove();
        }

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
            if (startPage > 2) html += '<span style="padding:0 8px;color:var(--gp-text-soft);">...</span>';
        }
        for (let i = startPage; i <= endPage; i++) {
            html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToPage(' + i + ')">' + i + '</button>';
        }
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += '<span style="padding:0 8px;color:var(--gp-text-soft);">...</span>';
            html += '<button class="page-btn" onclick="goToPage(' + totalPages + ')">' + totalPages + '</button>';
        }
        if (page < totalPages) html += '<button class="page-btn" onclick="goToPage(' + (page + 1) + ')">›</button>';
        
        controls.innerHTML = html;
    };

    /**
     * Export the verification queue as it is currently filtered.
     *
     * The three filters live in three different places on this page -- the
     * chip row's state variable, the position <select>, and the topbar search
     * box -- so they are read here rather than off the rendered rows: the
     * export is built server-side from the records, and scraping the table
     * would cap it at the columns the screen happens to show.
     */
    window.exportTrainingReport = function (btn) {
        const url = btn?.dataset.exportUrl;
        if (!url) return;

        const params = new URLSearchParams();
        const status   = window._adminStatusFilter;
        const position = document.getElementById('adminPositionFilter')?.value || '';
        const search   = (document.getElementById('adminTrainingSearch')?.value || '').trim();

        // 'all' is the chip's word for no filter; sending it would print a
        // status named "All" in the file's parameter block as though it were one.
        if (status && status !== 'all') params.set('status', status);
        if (position) params.set('position_type', position);
        if (search) params.set('search', search);

        const query = params.toString();
        window.location.href = query ? url + '?' + query : url;
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

    /* Accepts a row id, a <tr>, or any element inside one. The row actions now
       live in a ⋮ menu that app.js moves to <body> while it is open, so a
       menu button has no ancestor <tr> to climb to — closest('tr') returned
       null and both handlers silently did nothing. */
    function resolveTrainingRow(ref) {
        if (ref && ref.closest) {
            const row = ref.closest('tr');
            if (row) return row;
        }
        return document.querySelector('#adminTrainingBody tr[data-id="' + ref + '"]');
    }

    const RV_FILE_ICONS = {
        pdf:   '<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        image: '<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>',
        none:  '<svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="14" x2="15" y2="20"/><line x1="15" y1="14" x2="9" y2="20"/></svg>',
    };

    const rvEl  = id => document.getElementById(id);
    const rvSet = (id, text) => { const node = rvEl(id); if (node) node.textContent = text; };

    /** "3 days" across an inclusive range; '' when either date is unparseable. */
    function rvDaySpan(from, to) {
        const a = Date.parse(from);
        const b = Date.parse(to);
        if (isNaN(a) || isNaN(b) || b < a) return '';
        const days = Math.round((b - a) / 86400000) + 1;
        return days === 1 ? '1 day' : days + ' days';
    }

    /**
     * Every value below is written with textContent, never innerHTML. These
     * come out of `dataset`, which hands back the *decoded* attribute -- so a
     * seminar title containing markup would be re-parsed as HTML if it were
     * interpolated into a string. Only the fixed icons above are innerHTML.
     */
    window.reviewSubmission = function (ref) {
        const row = resolveTrainingRow(ref);
        if (!row) return;
        activeRow = row;

        const d = row.dataset;
        const status = d.status || 'pending';
        const certUrl = d.certUrl || '';
        const rejected = status === 'rejected';

        rvSet('rvTitle', d.title);

        // Status and position type, as the same badges the table row wears.
        const badges = rvEl('rvBadges');
        badges.textContent = '';
        const badge = (className, text) => {
            const span = document.createElement('span');
            span.className = className;
            span.textContent = text;
            badges.appendChild(span);
        };
        badge('verify-badge ' + status, status.charAt(0).toUpperCase() + status.slice(1));
        if (d.position) badge('type-badge ' + d.position.toLowerCase(), d.position);

        // The three figures the decision turns on.
        // A rejected submission is credited 0 -- the table shows it that way,
        // and so must the screen the reviewer reads before re-deciding.
        rvSet('rvHours', rejected ? '0' : (d.hours || '0'));
        rvSet('rvHoursNote', rejected
            ? 'Not credited (' + (d.hours || '0') + ' claimed)'
            : (status === 'verified' ? 'Credited to PDS Section IV' : 'Awaiting verification'));

        rvSet('rvDates', d.dateFrom + ' – ' + d.dateTo);
        rvSet('rvDatesNote', rvDaySpan(d.dateFrom, d.dateTo));

        rvSet('rvCertState', certUrl ? 'On file' : 'None');
        rvSet('rvCertNote', certUrl ? 'Attached by the employee' : 'Nothing to verify against');
        rvEl('rvCertFigure').classList.toggle('is-missing', !certUrl);

        // Who filed it.
        const avatar = rvEl('rvAvatar');
        avatar.textContent = '';
        if (d.photo) {
            const img = document.createElement('img');
            img.src = d.photo;
            img.alt = '';
            avatar.appendChild(img);
        } else {
            avatar.textContent = d.initials || '--';
        }
        rvSet('rvEmployee', d.employee);
        rvSet('rvEmployeeMeta', d.dept ? d.empId + ' · ' + d.dept : d.empId);

        // The paperwork. The employee, the hours and the dates are deliberately
        // absent -- they are above, and repeating them is what made this a wall.
        const details = rvEl('rvDetails');
        details.textContent = '';
        [
            ['Conducted / sponsored by', d.conducted || 'Not stated'],
            ['Reference document',       d.ref || 'Not stated'],
            ['Date submitted',           d.submitted || '—'],
            ['Verified on',              d.verified || '—'],
        ].forEach(pair => {
            const rowEl = document.createElement('div');
            rowEl.className = 'rv-detail-row';
            const label = document.createElement('span');
            label.textContent = pair[0];
            const value = document.createElement('strong');
            value.textContent = pair[1];
            rowEl.append(label, value);
            details.appendChild(rowEl);
        });

        const rejectBanner = rvEl('rvRejectBanner');
        rejectBanner.hidden = !(rejected && d.rejectNote);
        if (!rejectBanner.hidden) rvSet('rvRejectText', d.rejectNote);

        // The certificate, as a file card rather than an empty-state box.
        const isImage = /\.(jpg|jpeg|png|webp)(\?|$)/i.test(certUrl);
        const kind = !certUrl ? 'none' : (isImage ? 'image' : 'pdf');
        const fileIcon = rvEl('rvFileIcon');
        fileIcon.className = 'rv-file-icon is-' + kind;
        fileIcon.innerHTML = RV_FILE_ICONS[kind];

        rvEl('rvPreview').classList.toggle('is-empty', !certUrl);
        rvSet('rvFile', certUrl ? (isImage ? 'Scanned certificate (image)' : 'Certificate document (PDF)') : 'No certificate uploaded');
        rvSet('rvFileNote', certUrl
            ? 'Check the dates and hours against the reference document.'
            : 'The employee submitted these hours without attaching proof.');
        const certLink = rvEl('rvCertLink');
        certLink.hidden = !certUrl;
        if (certUrl) certLink.href = certUrl;

        // Only a pending submission can still be decided.
        const isPending = status === 'pending';
        rvEl('rvApproveBtn').hidden = !isPending;
        rvEl('rvRejectBtn').hidden  = !isPending;

        openAdminModal('reviewModal');
    };

    /* -- Approve / reject decision -----------------------------------------
       Replaces confirm('Approve this training submission?') and a reject box
       that named only the title. Both decisions are built from the row that
       was pressed, so the question names the submission it is deciding, and
       both post the same route the buttons always did. */

    const TDM_ICONS = {
        approve: '<polyline points="20 6 9 17 4 12"/>',
        reject:  '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
    };
    const TDM_MAX_REASON = 1000;

    /** The submission currently open in the dialog. */
    let decisionContext = null;

    const tdmEl  = id => document.getElementById(id);
    const tdmSet = (id, text) => { const node = tdmEl(id); if (node) node.textContent = text; };

    const tdmHours = value => {
        const h = parseFloat(value) || 0;
        return h === 1 ? '1 hour' : h + ' hours';
    };

    /**
     * @param {number|HTMLElement} ref  a row id, a <tr>, or an element inside one
     * @param {'approve'|'reject'} decision
     */
    window.openTrainingDecision = function (ref, decision) {
        const row = resolveTrainingRow(ref);
        if (!row) return;
        // Both actions are rendered only on pending rows, but the review modal
        // can reach this from a row a second tab has already decided.
        if (row.dataset.status !== 'pending') return;

        activeRow = row;
        const d = row.dataset;
        const approving = decision === 'approve';
        const first = d.firstName || d.employee;
        const hours = tdmHours(d.hours);
        const hasCert = !!d.certUrl;

        decisionContext = { action: approving ? d.approveUrl : d.rejectUrl, decision };

        // Who submitted it. The photo goes in as an <img> with `src` set as a
        // property rather than interpolated into a CSS url(...) string -- a
        // filename holding a quote would break out of the latter.
        const avatar = tdmEl('tdmAvatar');
        avatar.textContent = '';
        if (d.photo) {
            const img = document.createElement('img');
            img.src = d.photo;
            img.alt = '';
            avatar.appendChild(img);
        } else {
            avatar.textContent = d.initials || '--';
        }
        tdmSet('tdmEmployee', d.employee);
        tdmSet('tdmEmployeeMeta', d.dept ? d.empId + ' · ' + d.dept : d.empId);
        tdmSet('tdmRefDoc', d.ref || 'No ref. no.');

        // The submission
        tdmSet('tdmHours', hours + ' · ' + d.position);
        tdmSet('tdmDates', d.dateFrom + ' – ' + d.dateTo);
        tdmSet('tdmConducted', d.conducted || 'Not specified');
        tdmSet('tdmCert', hasCert ? 'On file' : 'None uploaded');
        tdmSet('tdmTraining', d.title);

        const modal = tdmEl('trainingDecisionModal');
        modal.classList.toggle('is-approve', approving);
        modal.classList.toggle('is-reject', !approving);

        tdmEl('tdmIcon').innerHTML =
            '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">'
            + TDM_ICONS[decision] + '</svg>';

        const fiscalYear = document.querySelector('.admin-training')?.dataset.fiscalYear || '';

        if (approving) {
            tdmSet('tdmEyebrow', 'APPROVE TRAINING SUBMISSION');
            tdmSet('tdmTitle', 'Credit ' + hours + ' to ' + first + "'s record?");
            tdmSet('tdmLede', d.title + ' — ' + d.dateFrom + ' – ' + d.dateTo + '.');
            // Verified hours are what EmployeeTrainingController sums into the
            // employee's total_hours and L&D breakdown, so approving changes
            // their PDS -- it is not just a status flag.
            tdmSet('tdmConsequence',
                'Verifying this credits ' + hours + ' to ' + first
                + "'s CSC PDS Section IV (Learning & Development) record and counts toward their "
                + (fiscalYear ? fiscalYear + ' ' : '') + "L&D hours. The submission is stamped with your name and today's date. "
                + 'Undoing it means rejecting the submission afterwards, which drops the hours back to 0.');
            tdmSet('tdmConfirmLabel', 'Yes, approve');
            tdmSet('tdmCancel', 'Go back');
        } else {
            tdmSet('tdmEyebrow', 'REJECT TRAINING SUBMISSION');
            tdmSet('tdmTitle', 'Send ' + first + "'s submission back for correction?");
            tdmSet('tdmLede', d.title + ' — ' + d.dateFrom + ' – ' + d.dateTo + '.');
            tdmSet('tdmConsequence',
                first + ' is credited 0 hours for this training and the submission is marked rejected. '
                + 'Your reason is saved on it and is what ' + first + ' reads, so it needs to say what to fix '
                + 'before they can correct their documents and submit again.');
            tdmSet('tdmConfirmLabel', 'Reject submission');
            tdmSet('tdmCancel', 'Keep pending');
        }

        // Approving with nothing uploaded credits hours against no document.
        const warn = tdmEl('tdmWarn');
        warn.hidden = !(approving && !hasCert);
        if (!warn.hidden) {
            tdmSet('tdmWarnText', 'No certificate was uploaded with this submission, so there is no file to check the '
                + hours + ' against.');
        }

        // The reason belongs to the rejection only; approve() nulls
        // rejected_reason, so a note attached to it has nowhere to be read.
        tdmEl('tdmNoteBlock').hidden = approving;
        tdmEl('tdmNote').value = '';
        tdmSet('tdmNoteCount', '0 / ' + TDM_MAX_REASON);

        // The server refuses an empty reason with a 422 this page has nowhere
        // to show, so the button stays out of reach until there is one to send.
        syncTrainingConfirmState();

        closeAdminModal('reviewModal');
        openAdminModal('trainingDecisionModal');
        (approving ? tdmEl('tdmConfirm') : tdmEl('tdmNote')).focus();
    };

    window.closeTrainingDecision = function () {
        decisionContext = null;
        closeAdminModal('trainingDecisionModal');
    };

    /** Rejection needs a reason; approval needs nothing. */
    function syncTrainingConfirmState() {
        if (!decisionContext) return;
        tdmEl('tdmConfirm').disabled =
            decisionContext.decision === 'reject' && tdmEl('tdmNote').value.trim() === '';
    }

    window.submitTrainingDecision = function () {
        if (!decisionContext) return;
        const { action, decision } = decisionContext;
        const reason = decision === 'approve' ? '' : tdmEl('tdmNote').value.trim().slice(0, TDM_MAX_REASON);
        if (decision === 'reject' && !reason) return;

        // Double submission on a slow connection would post the decision twice.
        tdmEl('tdmConfirm').disabled = true;
        tdmSet('tdmConfirmLabel', decision === 'approve' ? 'Approving…' : 'Rejecting…');

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = action;

        const field = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        };
        field('_token', document.querySelector('meta[name="csrf-token"]')?.content ?? '');
        if (reason) field('reason', reason);

        document.body.appendChild(form);
        form.submit();
    };

    /** The review modal's own Approve / Reject -- same dialog, same row. */
    window.decideFromReview = function (decision) {
        if (activeRow) openTrainingDecision(activeRow, decision);
    };

    // Live counter, so the 1000-character ceiling is visible rather than a
    // silent truncation at submit time.
    const tdmNote = tdmEl('tdmNote');
    if (tdmNote) {
        tdmNote.addEventListener('input', () => {
            const count = tdmEl('tdmNoteCount');
            count.textContent = tdmNote.value.length + ' / ' + TDM_MAX_REASON;
            count.classList.toggle('is-full', tdmNote.value.length >= TDM_MAX_REASON);
            syncTrainingConfirmState();
        });
    }

    // Escape is a "no" -- the safe answer for both decisions.
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        const modal = tdmEl('trainingDecisionModal');
        if (modal && modal.style.display === 'flex') closeTrainingDecision();
    });

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
