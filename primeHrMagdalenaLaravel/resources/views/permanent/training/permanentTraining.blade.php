@extends('layouts.permanent')

@section('title', 'Training · PRIME HRIS')

@section('content')
<div class="app-layout">

    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('permanent.sidebar.permanentSidebar')

    <main class="main-content permanent-dashboard permanent-training" data-fiscal-year="{{ date('Y') }}" data-flash-success="{{ session('success') ? '1' : '0' }}">

        @include('permanent.notification.permanentNotification')

        @include('permanent.topbar.trainingTopbar')

        @if(session('error'))
        <div style="background:#fdf0ef;border:1px solid #f5c6c3;color:#8e1e18;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;">
            {{ session('error') }}
        </div>
        @endif

        {{-- Stats row --}}
        <div class="stats-grid stats-grid-4 training-stats-grid">

            <div class="stat-card training-stat-hero">
                <div class="stat-top">
                    <p class="stat-label">Total L&amp;D Hours</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statTotalHours">{{ $stats['total_hours'] }}</p>
                <div class="training-goal-track" aria-hidden="true">
                    <div class="training-goal-fill" id="trainingGoalFill" data-goal-width="{{ min(100, (int) round(($stats['total_hours'] / 40) * 100)) }}"></div>
                </div>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub" id="statGoalSub">{{ $stats['total_hours'] }} of 40 hrs · FY {{ date('Y') }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Verified</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statVerifiedCount">{{ $stats['verified'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">Hours credited to PDS</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Pending</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statPendingCount">{{ $stats['pending'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">Awaiting HR verification</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Rejected</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value" id="statRejectedCount">{{ $stats['rejected'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Needs correction or re-upload</p>
                </div>
            </div>

        </div>

        {{-- Category breakdown --}}
        @php
            $bd = $breakdown ?? ['leadership' => 0, 'technical' => 0, 'core' => 0];
            $bdMax = max($bd['leadership'], $bd['technical'], $bd['core'], 1);
            $bdBar = fn ($h) => (int) round(($h / $bdMax) * 100);
            $barLeadershipPct = $bdBar($bd['leadership']);
            $barTechnicalPct  = $bdBar($bd['technical']);
            $barCorePct       = $bdBar($bd['core']);
        @endphp
        <div class="training-breakdown-panel">
            <p class="training-breakdown-panel-title">Breakdown by L&amp;D Category</p>
            <p class="training-breakdown-panel-sub">Verified L&amp;D hours only · FY {{ date('Y') }}</p>
            <div class="training-breakdown-grid">
                <div class="training-breakdown-card">
                    <div class="training-breakdown-card-head">
                        <span class="training-breakdown-dot leadership"></span>
                        <span class="training-breakdown-card-label">Leadership</span>
                        <span class="training-breakdown-card-hours" id="hoursLeadership">{{ $bd['leadership'] }} hrs</span>
                    </div>
                    <div class="training-mini-bar" aria-hidden="true">
                        <div class="training-mini-bar-fill leadership" id="barLeadership" data-hours="{{ $bd['leadership'] }}" data-bar-width="{{ $barLeadershipPct }}"></div>
                    </div>
                </div>
                <div class="training-breakdown-card">
                    <div class="training-breakdown-card-head">
                        <span class="training-breakdown-dot technical"></span>
                        <span class="training-breakdown-card-label">Technical</span>
                        <span class="training-breakdown-card-hours" id="hoursTechnical">{{ $bd['technical'] }} hrs</span>
                    </div>
                    <div class="training-mini-bar" aria-hidden="true">
                        <div class="training-mini-bar-fill technical" id="barTechnical" data-hours="{{ $bd['technical'] }}" data-bar-width="{{ $barTechnicalPct }}"></div>
                    </div>
                </div>
                <div class="training-breakdown-card">
                    <div class="training-breakdown-card-head">
                        <span class="training-breakdown-dot core"></span>
                        <span class="training-breakdown-card-label">Core / Foundation</span>
                        <span class="training-breakdown-card-hours" id="hoursCore">{{ $bd['core'] }} hrs</span>
                    </div>
                    <div class="training-mini-bar" aria-hidden="true">
                        <div class="training-mini-bar-fill core" id="barCore" data-hours="{{ $bd['core'] }}" data-bar-width="{{ $barCorePct }}"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Training History Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Training History</p>
                    <p class="table-sub">Section IV — Learning &amp; Development (CSC PDS format)</p>
                </div>
                <div class="table-actions">
                    <a href="{{ route('permanent.training.export') }}" class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        Export to PDS
                    </a>
                    <button type="button" class="modal-btn-primary" onclick="openAddTrainingModal()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add New Training
                    </button>
                </div>
            </div>

            <div class="training-filter-bar">
                <div class="training-filter-chips" role="group" aria-label="Filter by status">
                    <button type="button" class="training-filter-chip active" data-status-filter="all" onclick="setStatusFilter('all', this)">All</button>
                    <button type="button" class="training-filter-chip" data-status-filter="verified" onclick="setStatusFilter('verified', this)">Verified</button>
                    <button type="button" class="training-filter-chip" data-status-filter="pending" onclick="setStatusFilter('pending', this)">Pending</button>
                    <button type="button" class="training-filter-chip" data-status-filter="rejected" onclick="setStatusFilter('rejected', this)">Rejected</button>
                </div>
                <select id="trainingPositionFilter" class="filter-select training-position-filter" onchange="filterPermanentTraining()" aria-label="Filter by position type">
                    <option value="">All position types</option>
                    <option value="Managerial">Managerial</option>
                    <option value="Supervisory">Supervisory</option>
                    <option value="Technical">Technical</option>
                    <option value="Clerical">Clerical</option>
                </select>
            </div>
                
                <div class="table-wrapper">
                <table class="payroll-table training-pds-table" id="trainingHistoryTable">
                        <thead>
                            <tr>
                            <th>Title of Seminar / Conference / Training Program</th>
                            <th>Inclusive Dates</th>
                            <th>No. of Hours</th>
                            <th>Type of Position</th>
                            <th>Conducted / Sponsored By</th>
                            <th>Verification Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                    <tbody id="trainingHistoryBody">
                        @forelse($trainings as $t)
                        @php
                            $cat = $t->ldCategory();
                            $badgeClass = match($t->position_type) {
                                'Managerial'  => 'managerial',
                                'Supervisory' => 'supervisory',
                                'Technical'   => 'technical',
                                'Clerical'    => 'clerical',
                                default       => 'technical',
                            };
                        @endphp
                        <tr class="training-row-{{ $t->status }} row-{{ $t->status }}"
                            data-status="{{ $t->status }}"
                            data-hours="{{ $t->status === 'verified' ? $t->hours : 0 }}"
                            data-category="{{ $cat }}"
                            data-position="{{ $t->position_type }}"
                            data-ref="{{ $t->ref_doc_no }}"
                            @if($t->rejected_reason) data-reject-note="{{ $t->rejected_reason }}" @endif>
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
                                <span class="training-hours-pill {{ $t->status !== 'verified' ? 'training-hours-pill-pending' : '' }}">
                                    {{ $t->hours }}
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
                                <div style="display:flex;gap:6px;align-items:center;">
                                    @if($t->certificate_path)
                                    <a href="{{ route('permanent.training.certificate', $t->id) }}" target="_blank" class="btn-view-cert" title="View Certificate">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    @endif
                                    @if($t->status === 'pending')
                                    <form method="POST" action="{{ route('permanent.training.delete', $t->id) }}" onsubmit="return confirm('Delete this training record?')" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-view-cert" title="Delete" style="color:#8e1e18;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                    @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                        <tr id="noTrainingRow">
                            <td colspan="7" style="text-align:center;padding:40px;color:#9999bb;">
                                No training records yet. Click “Add New Training” to submit your first record.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

            <div class="table-footer">
                <span>Showing <strong id="trainingRowCount">{{ $trainings->count() }}</strong> training record(s)</span>
            </div>
        </div>

    </main>
            </div>
            
<div class="training-toast" id="trainingToast" role="alert" aria-live="polite"></div>

{{-- Add New Training Modal --}}
<div class="modal-overlay training-modal-overlay" id="addTrainingModal" onclick="closeModal('addTrainingModal')">
    <div class="modal-box training-add-modal" onclick="event.stopPropagation()">
        <form id="addTrainingForm" class="training-modal-form" method="POST" action="{{ route('permanent.training.store') }}" enctype="multipart/form-data" onsubmit="submitTraining(event)">
        @csrf
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon training-hero-icon">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                            <div>
                    <span class="modal-eyebrow">LEARNING &amp; DEVELOPMENT</span>
                    <h3 class="modal-title">Add New Training</h3>
                    <p class="modal-sub" id="modalStepSub">Step 1 of 2 — Upload your certificate to auto-fill details.</p>
                            </div>
                        </div>
            <button type="button" class="modal-close" onclick="closeModal('addTrainingModal')" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
                            </div>

            {{-- Step indicator --}}
            <div class="cert-steps" id="certSteps">
                <div class="cert-step active" id="step1Indicator">
                    <span class="cert-step-num">1</span>
                    <span class="cert-step-label">Upload Certificate</span>
                            </div>
                <div class="cert-step-line"></div>
                <div class="cert-step" id="step2Indicator">
                    <span class="cert-step-num">2</span>
                    <span class="cert-step-label">Review &amp; Submit</span>
                        </div>
                            </div>

            <div class="modal-body training-modal-body-scroll">

                {{-- STEP 1: Upload --}}
                <div id="certStep1">
                    <div class="training-form-grid">
                        <div class="training-form-field training-form-full">
                            <div class="training-dropzone" id="trainingDropZone">
                                <input type="file" id="trainingCertificate" name="certificate" accept=".pdf,.jpg,.jpeg,.png" hidden onchange="handleTrainingFile(this)">
                                <button type="button" class="training-dropzone-label" id="dropzoneBtn" onclick="document.getElementById('trainingCertificate').click()">
                                    <svg width="40" height="40" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    <p class="training-dropzone-title">Click to upload or drag and drop</p>
                                    <p class="training-dropzone-sub">PDF, JPEG, or PNG · Max 5 MB</p>
                                    <p class="training-dropzone-sub" style="margin-top:6px; color:#0b044d; font-weight:600;">Certificate details will be auto-extracted</p>
                                </button>
                                <p class="training-file-name" id="trainingFileName" hidden></p>
                            </div>
                        </div>
                    </div>

                    {{-- OCR scanning state --}}
                    <div id="certScanState" style="display:none; text-align:center; padding:24px 0;">
                        <div class="cert-scan-spinner"></div>
                        <p class="cert-scan-label" id="certScanLabel">Reading certificate...</p>
                        <p style="font-size:11px; color:#9999bb; margin-top:4px;">This may take a few seconds</p>
                            </div>

                    <div class="training-enroll-note" style="margin-top:16px;">
                        <svg width="16" height="16" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p class="training-enroll-note-text">Upload any Certificate of Completion (PDF or image). The system reads different certificate layouts—government, private, or agency-issued—and auto-fills the form. Review and correct any field before submitting.</p>
                        </div>
                            </div>

                {{-- STEP 2: Review auto-filled form --}}
                <div id="certStep2" style="display:none;">

                    <div class="cert-autofill-banner" id="certAutofillBanner">
                        <svg width="15" height="15" fill="none" stroke="#15803d" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        <span id="certAutofillMsg">Details extracted from your certificate. Please review and correct if needed.</span>
                            </div>

                    <p class="modal-section-label">PROGRAM DETAILS</p>
                    <div class="training-form-grid">
                        <div class="training-form-field training-form-full">
                            <label for="trainingTitle">Title of Seminar / Conference / Training Program <span class="req">*</span></label>
                            <input type="text" id="trainingTitle" name="title" required placeholder="e.g. Leadership and Governance Seminar">
                        </div>
                        <div class="training-form-field">
                            <label for="trainingPositionType">Type of Position <span class="req">*</span></label>
                            <select id="trainingPositionType" name="position_type" required>
                                <option value="">Select type</option>
                                <option value="Managerial">Managerial</option>
                                <option value="Supervisory">Supervisory</option>
                                <option value="Technical">Technical</option>
                                <option value="Clerical">Clerical</option>
                            </select>
                            </div>
                        <div class="training-form-field training-form-full">
                            <label for="trainingConductedBy">Conducted / Sponsored By <span class="req">*</span></label>
                            <input type="text" id="trainingConductedBy" name="conducted_by" required placeholder="Training agency or institution">
                            </div>
                        <div class="training-form-field training-form-full">
                            <label for="trainingVenue">Venue / Location</label>
                            <input type="text" id="trainingVenue" name="venue" placeholder="e.g. Laguna, Philippines">
                        </div>
                    </div>

                    <p class="modal-section-label modal-section-deductions">DATES &amp; HOURS</p>
                    <div class="training-form-grid">
                        <div class="training-form-field">
                            <label for="trainingDateFrom">Inclusive Date (From) <span class="req">*</span></label>
                            <input type="date" id="trainingDateFrom" name="date_from" required>
                        </div>
                        <div class="training-form-field">
                            <label for="trainingDateTo">Inclusive Date (To) <span class="req">*</span></label>
                            <input type="date" id="trainingDateTo" name="date_to" required>
                        </div>
                        <div class="training-form-field">
                            <label for="trainingHours">Number of Hours <span class="req">*</span></label>
                            <input type="number" id="trainingHours" name="hours" min="1" max="999" required placeholder="e.g. 8">
            </div>
        </div>

                    <p class="modal-section-label modal-section-deductions">DOCUMENTATION</p>
                    <div class="training-form-grid">
                        <div class="training-form-field">
                            <label for="trainingRefDoc">Reference Document Number <span class="req">*</span></label>
                            <input type="text" id="trainingRefDoc" name="ref_doc_no" required placeholder="Office Order No. or Travel Order No.">
                            <p class="training-field-hint">Enter the Office Order or Travel Order that authorized your attendance.</p>
                        </div>
                        <div class="training-form-field">
                            <label for="trainingCertNo">Certificate Number</label>
                            <input type="text" id="trainingCertNo" name="cert_no" placeholder="e.g. CERT-2025-001">
                        </div>
                        <div class="training-form-field training-form-full">
                            <label>Uploaded Certificate</label>
                            <div class="cert-file-preview" id="certFilePreview">
                                <div id="certFileIcon2"></div>
                                <div>
                                    <p id="certFileName2" style="font-size:13px; font-weight:600; color:#0b044d; margin:0;"></p>
                                    <p style="font-size:11px; color:#9999bb; margin:0;">Ready for submission</p>
                                </div>
                                <button type="button" onclick="resetToStep1()" style="margin-left:auto; background:none; border:none; color:#8e1e18; cursor:pointer; font-size:12px; font-weight:600;">Change</button>
                            </div>
                        </div>
</div>
    
                    <div class="training-enroll-note">
                        <svg width="16" height="16" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" class="training-enroll-note-icon"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p class="training-enroll-note-text">Submitted entries are marked <strong>Pending</strong> until HR verifies your certificate and reference document. Only verified hours count toward your annual L&amp;D total.</p>
            </div>
            </div>
        </div>

            <div class="modal-footer training-modal-footer-sticky">
                <button type="button" class="modal-btn-ghost" onclick="closeModal('addTrainingModal')">Cancel</button>
                <button type="submit" class="modal-btn-primary" id="certSubmitBtn" style="display:none;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    Submit for Verification
                </button>
        </div>
        </form>
    </div>
</div>

{{-- Flash success modal (after redirect) --}}
@if(session('success'))
<div class="modal-overlay training-modal-overlay" id="trainingFlashSuccessModal" onclick="closeModal('trainingFlashSuccessModal')">
    <div class="modal-box training-modal-sm" onclick="event.stopPropagation()">
        <div class="modal-body training-modal-center">
            <div class="training-modal-icon training-modal-icon-success">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-title training-modal-title-gap">Success</h3>
            <p class="training-modal-text">{{ session('success') }}</p>
        </div>
        <div class="modal-footer training-modal-footer-center">
            <button type="button" class="modal-btn-primary training-modal-btn-full" onclick="closeModal('trainingFlashSuccessModal')">Done</button>
        </div>
    </div>
</div>
@endif

{{-- Submit Success Modal --}}
<div class="modal-overlay training-modal-overlay" id="trainingSubmitModal" onclick="closeModal('trainingSubmitModal')">
    <div class="modal-box training-modal-sm" onclick="event.stopPropagation()">
        <div class="modal-body training-modal-center">
            <div class="training-modal-icon training-modal-icon-success">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-title training-modal-title-gap">Training Submitted</h3>
            <p class="training-modal-text">Your training record has been submitted and is <strong>Pending</strong> HR verification. You will be notified once it is approved or if corrections are needed.</p>
            <div class="training-modal-meta">
                <div class="modal-row"><span>Reference</span><strong id="tsRef">—</strong></div>
                <div class="modal-row training-modal-row-last"><span>Status</span><strong class="training-pending-strong">Pending</strong></div>
            </div>
        </div>
        <div class="modal-footer training-modal-footer-center">
            <button type="button" class="modal-btn-primary training-modal-btn-full" onclick="closeModal('trainingSubmitModal')">Done</button>
        </div>
    </div>
</div>

{{-- View Certificate Modal --}}
<div class="modal-overlay training-modal-overlay" id="viewCertModal" onclick="closeModal('viewCertModal')">
    <div class="modal-box training-view-cert-modal" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon training-hero-icon">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow">VERIFIED TRAINING RECORD</span>
                    <h3 class="modal-title" id="vcTitle">—</h3>
                    <p class="modal-sub" id="vcSubtitle">—</p>
                    <div class="pmodal-badges" id="vcBadges"></div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeModal('viewCertModal')" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <p class="modal-section-label">TRAINING DETAILS</p>
            <div class="training-modal-meta" id="vcDetails"></div>
            <p class="modal-section-label modal-section-deductions">CERTIFICATE FILE</p>
            <div class="training-cert-preview" id="vcPreview">
                <div class="training-cert-preview-icon" id="vcFileIcon"></div>
                <p class="training-cert-preview-name" id="vcFile">—</p>
                <p class="training-cert-preview-note">Certificate on file — verified by HRMO</p>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="modal-btn-ghost" onclick="closeModal('viewCertModal')">Close</button>
            <button type="button" class="modal-btn-primary" id="vcDownloadBtn">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Certificate
            </button>
        </div>
    </div>
</div>

{{-- Export PDS Success Modal --}}
<div class="modal-overlay training-modal-overlay" id="pdsExportModal" onclick="closeModal('pdsExportModal')">
    <div class="modal-box training-modal-sm" onclick="event.stopPropagation()">
        <div class="modal-body training-modal-center">
            <div class="training-modal-icon training-modal-icon-success">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </div>
            <h3 class="modal-title training-modal-title-gap">PDS Export Ready</h3>
            <p class="training-modal-text">Your verified training history has been prepared in the official CSC PDS Excel format (Section IV).</p>
            <div class="training-modal-meta">
                <div class="modal-row"><span>Format</span><strong>CSC PDS Excel</strong></div>
                <div class="modal-row training-modal-row-last"><span>Records</span><strong id="pdsRecordCount">—</strong></div>
            </div>
        </div>
        <div class="modal-footer training-modal-footer-center">
            <button type="button" class="modal-btn-ghost training-modal-btn-half" onclick="closeModal('pdsExportModal')">Close</button>
            <button type="button" class="modal-btn-primary training-modal-btn-half" onclick="closeModal('pdsExportModal')">Download File</button>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.1.0/dist/tesseract.min.js"></script>
<script src="{{ asset('js/training-certificate-parser.js') }}?v=2"></script>
<script>
    if (typeof pdfjsLib !== 'undefined') {
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    const mainEl = document.querySelector('.permanent-training');
    const FISCAL_YEAR = mainEl ? mainEl.dataset.fiscalYear : String(new Date().getFullYear());
    const GOAL_HOURS = 40;
    const MAX_CERT_BYTES = 5 * 1024 * 1024;
    let activeStatusFilter = 'all';

    const positionBadgeClass = {
        Managerial: 'managerial',
        Supervisory: 'supervisory',
        Technical: 'technical',
        Clerical: 'clerical',
    };

    function openModal(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
        const anyOpen = Array.from(document.querySelectorAll('.modal-overlay')).some(
            m => m.style.display === 'flex'
        );
        if (!anyOpen) document.body.style.overflow = '';
    }

    function showTrainingToast(msg) {
        const t = document.getElementById('trainingToast');
        if (!t) return;
        t.textContent = msg;
        t.classList.add('show');
        clearTimeout(showTrainingToast._timer);
        showTrainingToast._timer = setTimeout(() => t.classList.remove('show'), 3200);
    }

    function setScanLabel(text) {
        const el = document.getElementById('certScanLabel');
        if (el) el.textContent = text;
    }

    function openAddTrainingModal() {
        resetToStep1();
        const form = document.getElementById('addTrainingForm');
        if (form) form.reset();
        openModal('addTrainingModal');
    }

    function resetToStep1() {
        const step1 = document.getElementById('certStep1');
        const step2 = document.getElementById('certStep2');
        const scan = document.getElementById('certScanState');
        const submitBtn = document.getElementById('certSubmitBtn');
        const ind1 = document.getElementById('step1Indicator');
        const ind2 = document.getElementById('step2Indicator');
        const sub = document.getElementById('modalStepSub');
        if (step1) step1.style.display = '';
        if (step2) step2.style.display = 'none';
        if (scan) scan.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'none';
        if (ind1) { ind1.classList.add('active'); ind1.classList.remove('done'); }
        if (ind2) ind2.classList.remove('active', 'done');
        if (sub) sub.textContent = 'Step 1 of 2 — Upload your certificate to auto-fill details.';
        const fn = document.getElementById('trainingFileName');
        if (fn) { fn.hidden = true; fn.textContent = ''; }
        const zone = document.getElementById('trainingDropZone');
        if (zone) zone.classList.remove('has-file', 'dragover');
        const grid = document.querySelector('#certStep1 .training-form-grid');
        if (grid) grid.style.display = '';
        const certInput = document.getElementById('trainingCertificate');
        if (certInput) certInput.value = '';
        document.querySelectorAll('.cert-autofilled').forEach(el => el.classList.remove('cert-autofilled'));
    }

    async function handleTrainingFile(input) {
        const file = input.files ? input.files[0] : null;
        if (!file) return;

        const allowed = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
        const extOk = /\.(pdf|jpe?g|png)$/i.test(file.name);
        if (!allowed.includes(file.type) && !extOk) {
            showTrainingToast('Please upload a PDF, JPEG, or PNG file.');
            input.value = '';
            return;
        }
        if (file.size > MAX_CERT_BYTES) {
            showTrainingToast('File must be 5 MB or smaller.');
            input.value = '';
            return;
        }

        const fileNameEl = document.getElementById('trainingFileName');
        const dropZone = document.getElementById('trainingDropZone');
        if (fileNameEl) {
            fileNameEl.hidden = false;
            fileNameEl.textContent = file.name;
        }
        if (dropZone) dropZone.classList.add('has-file');

        document.getElementById('certStep1').querySelector('.training-form-grid').style.display = 'none';
        document.getElementById('certScanState').style.display = 'block';
        setScanLabel('Reading certificate...');

        let text = '';
        try {
            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            if (isPdf) {
                if (typeof pdfjsLib === 'undefined') {
                    throw new Error('PDF library not loaded');
                }
                text = await extractTextFromPdf(file);
            } else {
                text = await extractTextFromImage(file);
            }
        } catch (err) {
            console.error('Certificate extraction failed:', err);
            showTrainingToast('Could not read the certificate. You can still fill the form manually.');
            text = '';
        }

        const parsed = (typeof TrainingCertificateParser !== 'undefined')
            ? TrainingCertificateParser.parse(text || '')
            : {};
        autoFillForm(parsed, file.name);
        goToStep2(file.name);
    }

    window.openModal = openModal;
    window.closeModal = closeModal;
    window.openAddTrainingModal = openAddTrainingModal;
    window.resetToStep1 = resetToStep1;
    window.handleTrainingFile = handleTrainingFile;
    window.showTrainingToast = showTrainingToast;

    function setStatusFilter(status, btn) {
        activeStatusFilter = status;
        document.querySelectorAll('.training-filter-chip').forEach(c => {
            c.classList.toggle('active', c === btn);
        });
        filterPermanentTraining();
    }
    window.setStatusFilter = setStatusFilter;

    function filterPermanentTraining() {
        const q = (document.getElementById('permanentTrainingSearch')?.value || '').toLowerCase().trim();
        const posFilter = document.getElementById('trainingPositionFilter')?.value || '';
        const rows = document.querySelectorAll('#trainingHistoryBody tr[data-status]');
        let visible = 0;
        rows.forEach(row => {
            const status = row.dataset.status || '';
            const position = row.dataset.position || '';
            const text = row.textContent.toLowerCase();
            let show = true;
            if (activeStatusFilter !== 'all' && status !== activeStatusFilter) show = false;
            if (posFilter && position !== posFilter) show = false;
            if (q && !text.includes(q)) show = false;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const rc = document.getElementById('trainingRowCount');
        if (rc) rc.textContent = visible;
    }
    window.filterPermanentTraining = filterPermanentTraining;

    function recalcAnnualSummary() {
        const rows = document.querySelectorAll('#trainingHistoryBody tr[data-status]');
        let verifiedHours = 0, verified = 0, pending = 0, rejected = 0;
        const catHours = { leadership: 0, technical: 0, core: 0 };
        rows.forEach(row => {
            const status = row.dataset.status;
            const hours = parseInt(row.dataset.hours || '0', 10) || 0;
            const cat = row.dataset.category || 'core';
            if (status === 'verified') {
                verified++;
                verifiedHours += hours;
                if (catHours[cat] !== undefined) catHours[cat] += hours;
            } else if (status === 'pending') pending++;
            else if (status === 'rejected') rejected++;
        });
        const pct = Math.min(100, Math.round((verifiedHours / GOAL_HOURS) * 100));
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
        set('statTotalHours', verifiedHours);
        set('statVerifiedCount', verified);
        set('statPendingCount', pending);
        set('statRejectedCount', rejected);
        set('statGoalSub', verifiedHours + ' of ' + GOAL_HOURS + ' hrs · FY ' + FISCAL_YEAR);
        const fill = document.getElementById('trainingGoalFill');
        if (fill) fill.style.width = pct + '%';
        const bPct = document.getElementById('bannerGoalPct');
        if (bPct) bPct.textContent = pct + '%';
        const bVer = document.getElementById('bannerVerifiedCount');
        if (bVer) bVer.innerHTML = '<span class="banner-badge-dot banner-badge-dot-success"></span> ' + verified + ' Verified';
        const bPen = document.getElementById('bannerPendingCount');
        if (bPen) bPen.textContent = pending + ' Pending';
        const maxCat = Math.max(catHours.leadership, catHours.technical, catHours.core, 1);
        const setBar = (id, h) => {
            const el = document.getElementById(id);
            if (el) el.style.width = Math.round((h / maxCat) * 100) + '%';
        };
        set('hoursLeadership', catHours.leadership + ' hrs');
        set('hoursTechnical', catHours.technical + ' hrs');
        set('hoursCore', catHours.core + ' hrs');
        setBar('barLeadership', catHours.leadership);
        setBar('barTechnical', catHours.technical);
        setBar('barCore', catHours.core);
    }

    // ============================================================
    // TEXT EXTRACTION
    // ============================================================

    async function extractPageTextOrdered(pdf, pageNum) {
        const page = await pdf.getPage(pageNum);
        const content = await page.getTextContent();
        const items = content.items
            .filter(it => it.str && String(it.str).trim())
            .map(it => ({
                str: String(it.str).trim(),
                x: it.transform ? it.transform[4] : 0,
                y: it.transform ? it.transform[5] : 0,
            }));
        items.sort((a, b) => {
            const yDiff = b.y - a.y;
            if (Math.abs(yDiff) > 4) return yDiff;
            return a.x - b.x;
        });
        let text = '';
        let lastY = null;
        for (const item of items) {
            if (lastY !== null && Math.abs(item.y - lastY) > 4) {
                text += '\n';
            } else if (text.length && !text.endsWith('\n')) {
                text += ' ';
            }
            text += item.str;
            lastY = item.y;
        }
        return text;
    }

    async function ocrPdfPage(pdf, pageNum) {
        const page = await pdf.getPage(pageNum);
        const viewport = page.getViewport({ scale: 2.5 });
        const canvas = document.createElement('canvas');
        canvas.width = viewport.width;
        canvas.height = viewport.height;
        await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
        const blob = await new Promise(res => canvas.toBlob(res, 'image/png'));
        return extractTextFromImage(blob);
    }

    async function extractTextFromPdf(file) {
        const arrayBuffer = await file.arrayBuffer();
        const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
        const maxPages = Math.min(pdf.numPages, 3);
        let fullText = '';

        for (let i = 1; i <= maxPages; i++) {
            fullText += (await extractPageTextOrdered(pdf, i)) + '\n\n';
        }

        const textLen = fullText.replace(/\s/g, '').length;
        if (textLen < 35) {
            setScanLabel('No readable text — scanning certificate layout...');
            let ocrCombined = '';
            for (let i = 1; i <= maxPages; i++) {
                if (maxPages > 1) setScanLabel('Scanning page ' + i + ' of ' + maxPages + '...');
                ocrCombined += (await ocrPdfPage(pdf, i)) + '\n\n';
            }
            if (ocrCombined.replace(/\s/g, '').length > textLen) {
                fullText = ocrCombined;
            }
        }

        return fullText;
    }

    async function extractTextFromImage(file) {
        setScanLabel('Running OCR...');
        const url = URL.createObjectURL(file instanceof Blob ? file : new Blob([file]));
        try {
            // Tesseract.js v5 API
            const worker = await Tesseract.createWorker('eng', 1, {
                logger: m => {
                    if (m.status === 'recognizing text') {
                        setScanLabel('OCR: ' + Math.round(m.progress * 100) + '%');
                    }
                }
            });
            const { data: { text } } = await worker.recognize(url);
            await worker.terminate();
            return text;
        } catch (e) {
            // Fallback: try simple recognize API (v4 compat)
            try {
                const result = await Tesseract.recognize(url, 'eng');
                return result.data.text;
            } catch (e2) {
                console.error('OCR failed:', e2);
                return '';
            }
        } finally {
            URL.revokeObjectURL(url);
        }
    }

    function autoFillForm(data, filename) {
        const fields = [
            { id: 'trainingTitle',       val: data.title },
            { id: 'trainingConductedBy', val: data.conductedBy },
            { id: 'trainingVenue',       val: data.venue },
            { id: 'trainingHours',       val: data.hours },
            { id: 'trainingDateFrom',    val: data.dateFrom },
            { id: 'trainingDateTo',      val: data.dateTo },
            { id: 'trainingCertNo',      val: data.certNo },
            { id: 'trainingRefDoc',      val: data.refDoc },
        ];
        let filledCount = 0;
        fields.forEach(({ id, val }) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (val) {
                el.value = val;
                el.classList.add('cert-autofilled');
                filledCount++;
            } else {
                el.value = '';
                el.classList.remove('cert-autofilled');
            }
        });

        // Position type dropdown
        if (data.positionType) {
            const sel = document.getElementById('trainingPositionType');
            if (sel) {
                sel.value = data.positionType;
                sel.classList.add('cert-autofilled');
                filledCount++;
            }
        }

        // Update banner
        const banner = document.getElementById('certAutofillBanner');
        const msg    = document.getElementById('certAutofillMsg');
        const meta   = data._meta || {};
        if (filledCount > 0 && !meta.lowConfidence) {
            banner.className = 'cert-autofill-banner cert-autofill-success';
            msg.innerHTML = `<strong>${filledCount} field(s)</strong> auto-filled from your certificate. Review and correct if needed, then submit.`;
        } else if (filledCount > 0 && meta.lowConfidence) {
            banner.className = 'cert-autofill-banner cert-autofill-warn';
            msg.innerHTML = `<strong>${filledCount} field(s)</strong> detected with partial confidence. Please verify all fields before submitting.`;
        } else {
            banner.className = 'cert-autofill-banner cert-autofill-warn';
            msg.textContent = 'Could not extract details from this certificate layout. Please fill in the fields below manually.';
        }

        // File preview
        const isPdf = filename.toLowerCase().endsWith('.pdf');
        document.getElementById('certFileName2').textContent = filename;
        document.getElementById('certFileIcon2').innerHTML = isPdf
            ? '<svg width="32" height="32" fill="none" stroke="#8e1e18" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'
            : '<svg width="32" height="32" fill="none" stroke="#0369a1" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';
    }

    function goToStep2(filename) {
        document.getElementById('certScanState').style.display = 'none';
        document.getElementById('certStep1').style.display = 'none';
        document.getElementById('certStep2').style.display = '';
        document.getElementById('certSubmitBtn').style.display = '';
        document.getElementById('step1Indicator').classList.remove('active');
        document.getElementById('step1Indicator').classList.add('done');
        document.getElementById('step2Indicator').classList.add('active');
        document.getElementById('modalStepSub').textContent = 'Step 2 of 2 — Review extracted details and submit.';
    }

    (function initDropZone() {
        const zone = document.getElementById('trainingDropZone');
        if (!zone) return;
        ['dragenter', 'dragover'].forEach(evt => {
            zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.add('dragover'); });
        });
        ['dragleave', 'drop'].forEach(evt => {
            zone.addEventListener(evt, e => { e.preventDefault(); zone.classList.remove('dragover'); });
        });
        zone.addEventListener('drop', e => {
            const input = document.getElementById('trainingCertificate');
            if (e.dataTransfer.files.length) {
                const dt = e.dataTransfer;
                const fileInput = document.getElementById('trainingCertificate');
                // Use DataTransfer to set files
                try {
                    fileInput.files = dt.files;
                } catch(ex) {}
                handleTrainingFile({ files: dt.files });
            }
        });
    })();

    function submitTraining(e) {
        const step2 = document.getElementById('certStep2');
        if (step2 && step2.style.display === 'none') {
            e.preventDefault();
            showTrainingToast('Please upload your certificate first.');
            return;
        }
        const dateFrom     = document.getElementById('trainingDateFrom').value;
        const dateTo       = document.getElementById('trainingDateTo').value;
        const certInput    = document.getElementById('trainingCertificate');
        const positionType = document.getElementById('trainingPositionType').value;
        const refDoc       = document.getElementById('trainingRefDoc').value.trim();
        const title        = document.getElementById('trainingTitle').value.trim();

        if (!title) {
            e.preventDefault();
            showTrainingToast('Please enter the training title.');
            return;
        }
        if (!positionType) {
            e.preventDefault();
            showTrainingToast('Please select a Type of Position.');
            return;
        }
        if (!dateFrom || !dateTo) {
            e.preventDefault();
            showTrainingToast('Please enter inclusive dates.');
            return;
        }
        if (dateTo < dateFrom) {
            e.preventDefault();
            showTrainingToast('Inclusive Date (To) must be on or after the start date.');
            return;
        }
        if (!refDoc) {
            e.preventDefault();
            showTrainingToast('Please enter the Reference Document Number.');
            return;
        }
        if (!certInput || !certInput.files.length) {
            e.preventDefault();
            showTrainingToast('Please attach your Certificate of Completion.');
            return;
        }
        // Valid — allow form POST to backend
    }
    window.submitTraining = submitTraining;


    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    let _currentCertFile = '';

    function viewCertificate(btn) {
        const row = btn.closest('tr');
        if (!row) return;

        const title = row.querySelector('.training-title-text')?.textContent?.trim() || '—';
        const ref = row.dataset.ref || '—';
        const hours = row.querySelector('.training-hours-pill')?.textContent?.trim() || '—';
        const position = row.dataset.position || '—';
        const conductedBy = row.cells[4]?.textContent?.trim() || '—';
        const dateFrom = row.querySelector('.training-date-from')?.textContent?.trim() || '—';
        const dateTo = row.querySelector('.training-date-to')?.textContent?.trim() || '—';
        const filename = btn.dataset.certFile || 'certificate.pdf';
        const isPdf = filename.toLowerCase().endsWith('.pdf');
        const badgeClass = positionBadgeClass[position] || 'technical';

        _currentCertFile = filename;

        document.getElementById('vcTitle').textContent = title;
        document.getElementById('vcSubtitle').textContent = dateFrom + ' – ' + dateTo + ' · ' + hours + ' hrs';
        document.getElementById('vcBadges').innerHTML =
            '<span class="verify-badge verified">Verified</span>' +
            '<span class="type-badge ' + badgeClass + '">' + escapeHtml(position) + '</span>';

        document.getElementById('vcDetails').innerHTML =
            '<div class="modal-row"><span>Inclusive Dates</span><strong>' + escapeHtml(dateFrom) + ' – ' + escapeHtml(dateTo) + '</strong></div>' +
            '<div class="modal-row"><span>Number of Hours</span><strong>' + escapeHtml(hours) + '</strong></div>' +
            '<div class="modal-row"><span>Reference Document</span><strong>' + escapeHtml(ref) + '</strong></div>' +
            '<div class="modal-row training-modal-row-last"><span>Conducted / Sponsored By</span><strong>' + escapeHtml(conductedBy) + '</strong></div>';

        document.getElementById('vcFile').textContent = filename;
        document.getElementById('vcFileIcon').innerHTML = isPdf
            ? '<svg width="40" height="40" fill="none" stroke="#8e1e18" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'
            : '<svg width="40" height="40" fill="none" stroke="#0369a1" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';

        openModal('viewCertModal');
    }

    const vcDownloadBtn = document.getElementById('vcDownloadBtn');
    if (vcDownloadBtn) {
        vcDownloadBtn.addEventListener('click', () => {
            showTrainingToast('Downloading: ' + _currentCertFile);
            closeModal('viewCertModal');
        });
    }

    function exportToPds() {
        const verified = document.querySelectorAll('#trainingHistoryBody tr[data-status="verified"]').length;
        document.getElementById('pdsRecordCount').textContent = verified + ' verified record(s)';
        openModal('pdsExportModal');
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
            document.body.style.overflow = '';
        }
    });

    const goalFillEl = document.getElementById('trainingGoalFill');
    if (goalFillEl && goalFillEl.dataset.goalWidth) {
        goalFillEl.style.width = goalFillEl.dataset.goalWidth + '%';
    }

    ['barLeadership', 'barTechnical', 'barCore'].forEach(function (id) {
        const bar = document.getElementById(id);
        if (bar && bar.dataset.barWidth !== undefined) {
            bar.style.width = bar.dataset.barWidth + '%';
        }
    });

    if (mainEl && mainEl.dataset.flashSuccess === '1') {
        openModal('trainingFlashSuccessModal');
    }

    recalcAnnualSummary();
    filterPermanentTraining();
</script>

@include('permanent.chatbot.permanentChatbot')

<style>
/* ── Step indicator ── */
.cert-steps {
    display: flex;
    align-items: center;
    padding: 12px 24px 0;
    gap: 0;
}
.cert-step {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #b3b1c8;
}
.cert-step.active .cert-step-num {
    background: #0b044d;
    color: #fff;
}
.cert-step.active .cert-step-label { color: #0b044d; }
.cert-step.done .cert-step-num {
    background: #15803d;
    color: #fff;
}
.cert-step.done .cert-step-label { color: #15803d; }
.cert-step-num {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #e5e3f8;
    color: #9999bb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
    transition: background 0.2s, color 0.2s;
}
.cert-step-line {
    flex: 1;
    height: 2px;
    background: #e5e3f8;
    margin: 0 10px;
}
/* ── Scan spinner ── */
.cert-scan-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e3f8;
    border-top-color: #0b044d;
    border-radius: 50%;
    animation: certSpin 0.8s linear infinite;
    margin: 0 auto 12px;
}
@keyframes certSpin { to { transform: rotate(360deg); } }
.cert-scan-label {
    font-size: 13px;
    font-weight: 600;
    color: #0b044d;
    margin: 0;
}
/* ── Auto-fill banner ── */
.cert-autofill-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 16px;
}
.cert-autofill-success {
    background: #e8f5e9;
    border: 1px solid #a5d6a7;
    color: #1b5e20;
}
.cert-autofill-warn {
    background: #fff9e6;
    border: 1px solid #ffe9a3;
    color: #8b7500;
}
/* ── Auto-filled field highlight ── */
.cert-autofilled {
    border-color: #15803d !important;
    background: #f0fdf4 !important;
    box-shadow: 0 0 0 2px rgba(21,128,61,0.12) !important;
}
.cert-autofilled:focus {
    border-color: #0b044d !important;
    background: #fff !important;
    box-shadow: 0 0 0 3px rgba(11,4,77,0.1) !important;
}
/* ── File preview in step 2 ── */
.cert-file-preview {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    background: #f7f6ff;
    border: 1.5px solid #e5e3f8;
    border-radius: 8px;
}
</style>

@endsection



