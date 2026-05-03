@extends('layouts.app')

@section('title', 'Training · PRIME HRIS')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endpush

@section('content')
<div class="app-layout">

    {{-- Mobile Menu Button --}}
    <button class="mobile-menu-btn" id="mobile-menu-btn" aria-label="Toggle menu">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('permanent.permanent-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('permanent.permanent-notification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                    <h2>My Training</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    3 Active Trainings
                </span>
                <span class="banner-badge outline">2 Available</span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Trainings</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    </div>
                </div>
                <p class="stat-value">3</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">All programs</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Completed</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">2</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">Finished programs</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Enrolled</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    </div>
                </div>
                <p class="stat-value">1</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">Currently active</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Available</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">2</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">Open for enrollment</p>
                </div>
            </div>

        </div>

        {{-- My Trainings Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">My Trainings</p>
                    <p class="table-sub">Your enrolled and completed training programs</p>
                </div>
            </div>
                
                <div class="table-wrapper">
                    <table class="payroll-table">
                        <thead>
                            <tr>
                                <th>Training ID</th>
                                <th>Program Title</th>
                                <th>Type</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">TRN-001</td>
                                <td class="position-cell">Leadership Development Program</td>
                                <td><span class="type-badge">Leadership</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Jun 15, 2025</td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Jul 15, 2025</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="flex:1;height:6px;background:#f0effe;border-radius:99px;min-width:60px;"><div style="height:100%;width:65%;background:#d9bb00;border-radius:99px;"></div></div>
                                        <span style="font-size:12;font-weight:600;color:#6b6a8a;min-width:35px;">65%</span>
                                    </div>
                                </td>
                                <td><span class="badge-status processed">Enrolled</span></td>
                                <td>
                                    <button class="btn-view" onclick="openMyTraining('Leadership Development Program', 'Leadership', 65, 'Jun 15, 2025', 'Jul 15, 2025', 'Municipal Hall Conference Room', 'Enrolled', false)">View</button>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">TRN-003</td>
                                <td class="position-cell">Customer Service Excellence</td>
                                <td><span class="type-badge">Soft Skills</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">May 10, 2025</td>
                                <td style="font-size:12.5px;color:#6b6a8a;">May 20, 2025</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="flex:1;height:6px;background:#f0effe;border-radius:99px;min-width:60px;"><div style="height:100%;width:100%;background:#15803d;border-radius:99px;"></div></div>
                                        <span style="font-size:12;font-weight:600;color:#6b6a8a;min-width:35px;">100%</span>
                                    </div>
                                </td>
                                <td><span class="badge-status on-hold">Completed</span></td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn-view" onclick="openMyTraining('Customer Service Excellence', 'Soft Skills', 100, 'May 10, 2025', 'May 20, 2025', 'Municipal Hall Conference Room', 'Completed', 'CERT-2025-003')">View</button>
                                        <button class="btn-certificate" onclick="downloadCertificate('CERT-2025-003')">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size:12.5px;color:#6b6a8a;font-weight:500;">TRN-002</td>
                                <td class="position-cell">Digital Literacy Training</td>
                                <td><span class="type-badge technical">Technical</span></td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Apr 5, 2025</td>
                                <td style="font-size:12.5px;color:#6b6a8a;">Apr 15, 2025</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="flex:1;height:6px;background:#f0effe;border-radius:99px;min-width:60px;"><div style="height:100%;width:100%;background:#15803d;border-radius:99px;"></div></div>
                                        <span style="font-size:12;font-weight:600;color:#6b6a8a;min-width:35px;">100%</span>
                                    </div>
                                </td>
                                <td><span class="badge-status on-hold">Completed</span></td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn-view" onclick="openMyTraining('Digital Literacy Training', 'Technical', 100, 'Apr 5, 2025', 'Apr 15, 2025', 'IT Training Center', 'Completed', 'CERT-2025-002')">View</button>
                                        <button class="btn-certificate" onclick="downloadCertificate('CERT-2025-002')">
                                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <div class="table-footer">
                <span>Showing <strong>3</strong> training programs</span>
            </div>
        </div>

        {{-- Available Trainings --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Available Trainings</p>
                    <p class="table-sub">Open programs you can enroll in</p>
                </div>
            </div>
            
            <div class="training-cards">
                    <div class="training-card">
                        <span class="type-badge technical">Technical</span>
                        <div class="card-header">
                            <div class="card-icon" style="background:linear-gradient(135deg, #15803d, #166534);">13</div>
                            <div>
                                <p class="card-id">TRN-004</p>
                                <h4 class="card-title">Financial Management Workshop</h4>
                            </div>
                        </div>
                        <p class="card-venue">Treasurer Office Training Room</p>
                        <div class="capacity-bar">
                            <div class="capacity-label">
                                <span>CAPACITY</span>
                                <span style="color:#15803d">48% filled</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:48%;background:linear-gradient(90deg, #15803d, #166534);"></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div>
                                <p>Start Date</p>
                                <p>Jul 5, 2025</p>
                            </div>
                            <div class="card-actions">
                                <button class="btn-view" onclick="openAvailableTraining('Financial Management Workshop', 'Technical', 13, 25, 'Jul 5, 2025', 'Jul 10, 2025', 'Treasurer Office Training Room')">View</button>
                                <button class="btn-enroll" onclick="enrollTraining('TRN-004')">Enroll</button>
                            </div>
                        </div>
                    </div>
                    <div class="training-card">
                        <span class="type-badge safety">Safety</span>
                        <div class="card-header">
                            <div class="card-icon" style="background:linear-gradient(135deg, #8e1e18, #5a0f0b);">10</div>
                            <div>
                                <p class="card-id">TRN-005</p>
                                <h4 class="card-title">Emergency Response Training</h4>
                            </div>
                        </div>
                        <p class="card-venue">MDRRM Office</p>
                        <div class="capacity-bar">
                            <div class="capacity-label">
                                <span>CAPACITY</span>
                                <span style="color:#8e1e18">80% filled</span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="width:80%;background:linear-gradient(90deg, #8e1e18, #5a0f0b);"></div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <div>
                                <p>Start Date</p>
                                <p>Jul 20, 2025</p>
                            </div>
                            <div class="card-actions">
                                <button class="btn-view" onclick="openAvailableTraining('Emergency Response Training', 'Safety', 10, 50, 'Jul 20, 2025', 'Jul 22, 2025', 'MDRRM Office')">View</button>
                                <button class="btn-enroll" onclick="enrollTraining('TRN-005')">Enroll</button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>

    </main>

</div>
    
{{-- Fully Booked Modal --}}
<div class="modal-overlay" id="fullyBookedModal" style="display:none" onclick="closeModal('fullyBookedModal')">
    <div class="modal-box" style="max-width:400px" onclick="event.stopPropagation()">
        <div class="modal-body" style="text-align:center;padding-top:28px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#fdf0ef;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <svg width="28" height="28" fill="none" stroke="#8e1e18" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <h3 class="modal-title" style="margin-bottom:6px;">Training Fully Booked</h3>
            <p style="font-size:13px;color:#6b6a8a;margin-bottom:18px;">Sorry, this training program has <strong>no available slots</strong>. Please check back later or choose another program.</p>
            <div style="text-align:left;background:#fdf0ef;border-radius:12px;padding:14px 16px;border:1px solid #f5d0ce;">
                <div class="modal-row" style="border-color:#f5d0ce"><span>Status</span><strong style="color:#8e1e18;">Fully Booked</strong></div>
                <div class="modal-row" style="border:none"><span>Suggestion</span><strong>Try another program</strong></div>
            </div>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="modal-btn-primary" style="width:100%;justify-content:center;" onclick="closeModal('fullyBookedModal')">Got It</button>
        </div>
    </div>
</div>

{{-- Certificate Download Modal --}}
<div class="modal-overlay" id="certDownloadModal" style="display:none" onclick="closeModal('certDownloadModal')">
    <div class="modal-box" style="max-width:400px" onclick="event.stopPropagation()">
        <div class="modal-body" style="text-align:center;padding-top:28px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#e8f9ef;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            </div>
            <h3 class="modal-title" style="margin-bottom:6px;">Certificate Downloaded!</h3>
            <p style="font-size:13px;color:#6b6a8a;margin-bottom:18px;">Your certificate of completion has been successfully downloaded as a PDF file.</p>
            <div style="text-align:left;background:#f7f6ff;border-radius:12px;padding:14px 16px;">
                <div class="modal-row"><span>Certificate No.</span><strong id="cdCertNo">&mdash;</strong></div>
                <div class="modal-row"><span>Format</span><strong>PDF Document</strong></div>
                <div class="modal-row" style="border:none"><span>Downloaded</span><strong id="cdDate">&mdash;</strong></div>
            </div>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="modal-btn-ghost" onclick="closeModal('certDownloadModal')" style="flex:1;justify-content:center;">Close</button>
            <button class="modal-btn-primary" style="flex:1;justify-content:center;" onclick="closeModal('certDownloadModal')">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Done
            </button>
        </div>
    </div>
</div>
<div class="modal-overlay" id="enrollConfirmModal" style="display:none" onclick="closeModal('enrollConfirmModal')">
    <div class="modal-box" style="max-width:420px" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon" style="background:linear-gradient(135deg,#0b044d,#1a0f6e)">
                    <svg width="22" height="22" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow">ENROLLMENT REQUEST</span>
                    <h3 class="modal-title">Confirm Enrollment</h3>
                    <p class="modal-sub">Please review the training details before confirming.</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('enrollConfirmModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div style="background:#f7f6ff;border-radius:12px;padding:16px;margin-bottom:16px;">
                <p style="font-size:10.5px;font-weight:700;color:#9999bb;letter-spacing:1px;margin:0 0 10px;">TRAINING DETAILS</p>
                <div class="modal-row"><span>Program</span><strong id="ecTitle">—</strong></div>
                <div class="modal-row"><span>Type</span><strong id="ecType">—</strong></div>
                <div class="modal-row"><span>Start Date</span><strong id="ecStart">—</strong></div>
                <div class="modal-row"><span>End Date</span><strong id="ecEnd">—</strong></div>
                <div class="modal-row" style="border:none"><span>Venue</span><strong id="ecVenue">—</strong></div>
            </div>
            <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;background:#fefce8;border-radius:10px;border:1px solid #fde68a;">
                <svg width="16" height="16" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <p style="font-size:12.5px;color:#a16207;margin:0;line-height:1.5">Once submitted, your enrollment request will be reviewed by the HRMO. You will be notified upon approval.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('enrollConfirmModal')">Cancel</button>
            <button class="modal-btn-primary" id="enrollConfirmBtn" onclick="submitEnrollment()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                Confirm Enrollment
            </button>
        </div>
    </div>
</div>

{{-- Enroll Success Modal --}}
<div class="modal-overlay" id="enrollSuccessModal" style="display:none" onclick="closeModal('enrollSuccessModal')">
    <div class="modal-box" style="max-width:400px" onclick="event.stopPropagation()">
        <div class="modal-body" style="text-align:center;padding-top:28px;">
            <div style="width:56px;height:56px;border-radius:50%;background:#e8f9ef;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <svg width="28" height="28" fill="none" stroke="#15803d" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="modal-title" style="margin-bottom:6px;">Enrollment Submitted!</h3>
            <p style="font-size:13px;color:#6b6a8a;margin-bottom:18px;">Your enrollment request for <strong id="esTitle">—</strong> has been submitted and is pending HRMO approval.</p>
            <div style="text-align:left;background:#f7f6ff;border-radius:12px;padding:14px 16px;">
                <div class="modal-row"><span>Reference No.</span><strong id="esRef">—</strong></div>
                <div class="modal-row"><span>Status</span><strong style="color:#d9bb00;">Pending Approval</strong></div>
                <div class="modal-row" style="border:none"><span>Submitted</span><strong id="esDate">—</strong></div>
            </div>
        </div>
        <div class="modal-footer" style="justify-content:center;">
            <button class="modal-btn-primary" style="width:100%;justify-content:center;" onclick="closeModal('enrollSuccessModal')">Done</button>
        </div>
    </div>
</div>

{{-- Training Modal --}}
<div class="modal-overlay" id="trainingModal" style="display:none" onclick="closeModal('trainingModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="pmodal-hero">
                <div class="pmodal-hero-icon" id="modalIcon" style="background:linear-gradient(135deg, #6b3fa0, #7c4fc0);">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                </div>
                <div>
                    <span class="modal-eyebrow" id="modalId">TRAINING PROGRAM</span>
                    <h3 class="modal-title" id="modalTitle">Leadership Development Program</h3>
                    <p class="modal-sub" id="modalSub">Leadership Training · Municipal Hall Conference Room</p>
                    <div class="pmodal-badges">
                        <span class="badge-status" id="modalStatus">Enrolled</span>
                        <span class="badge-emptype" id="modalType">Leadership</span>
                    </div>
                </div>
            </div>
            <button class="modal-close" onclick="closeModal('trainingModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
        </div>
        <div class="modal-footer" id="modalFooter">
            <button class="modal-btn-ghost" onclick="closeModal('trainingModal')">Close</button>
            <button class="modal-btn-primary" id="modalAction">Enroll Now</button>
        </div>
    </div>
</div>

<style>
.quick-action-btn { display:flex; align-items:center; gap:9px; padding:10px 14px; border:1.5px solid #eceaf8; border-radius:10px; background:#fafafe; cursor:pointer; font-size:13px; font-weight:600; color:#0b044d; transition:border-color 0.18s; }
.quick-action-btn:hover { border-color:#0b044d; }
.modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(11,4,77,0.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px; }
.modal-box { background:#fff; border-radius:16px; width:100%; max-width:480px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:slideUp 0.3s ease; }
@keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
.modal-header { display:flex; justify-content:space-between; align-items:flex-start; padding:24px 24px 0; }
.pmodal-hero { display:flex; gap:14px; align-items:flex-start; }
.pmodal-hero-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.pmodal-badges { display:flex; gap:6px; margin-top:8px; }
.modal-eyebrow { font-size:10.5px; color:#9999bb; font-weight:700; letter-spacing:1px; }
.modal-title { font-size:18px; font-weight:700; color:#0b044d; margin:4px 0 2px; }
.modal-sub { font-size:13px; color:#6b6a8a; margin:0 0 8px; }
.modal-close { background:none; border:none; cursor:pointer; padding:4px; color:#9999bb; }
.modal-close:hover { color:#0b044d; }
.modal-body { padding:20px 24px; }
.modal-progress { margin-bottom:20px; padding:16px; background:#f7f6ff; border-radius:12px; }
.modal-progress-label { display:flex; justify-content:space-between; font-size:10.5px; font-weight:700; letter-spacing:1px; margin-bottom:8px; }
.modal-progress-label span:first-child { color:#9999bb; }
.modal-section-label { font-size:10.5px; font-weight:700; color:#9999bb; letter-spacing:1px; margin-bottom:12px; }
.modal-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0effe; }
.modal-row span { font-size:13px; color:#9999bb; font-weight:600; }
.modal-row strong { font-size:13px; color:#0b044d; font-weight:600; }
.slots-card { display:flex; align-items:center; gap:14px; padding:16px; background:#f7f6ff; border-radius:12px; margin-bottom:20px; }
.slots-icon { width:44px; height:44px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.slots-card p { font-size:11px; color:#9999bb; font-weight:600; margin:0 0 4px; }
.slots-card span { font-size:22px; font-weight:800; color:#0b044d; }
.slots-card small { font-size:14px; color:#9999bb; font-weight:600; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px 24px; }
.modal-btn-ghost { padding:9px 18px; border-radius:9px; border:1.5px solid #dddcf0; background:#fff; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; }
.modal-btn-ghost:hover { border-color:#0b044d; color:#0b044d; }
.modal-btn-primary { padding:9px 18px; border-radius:9px; border:none; background:linear-gradient(135deg,#0b044d,#1a0f6e); color:#fff; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; }
.training-cards { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:16px; padding:16px 20px; }
.training-card { background:#fff; border-radius:14px; border:1.5px solid #e5e4f0; padding:20px; position:relative; }
.training-card .type-badge { position:absolute; top:16px; right:16px; font-size:10px; font-weight:600; padding:4px 10px; border-radius:20px; background:#f0effe; color:#6b3fa0; }
.training-card .type-badge.technical { background:#fefce8; color:#a16207; }
.training-card .type-badge.safety { background:#fef3c7; color:#92400e; }
table .type-badge { font-size:10px; font-weight:600; padding:4px 10px; border-radius:20px; background:#f0effe; color:#6b3fa0; display:inline-block; }
table .type-badge.technical { background:#fefce8; color:#a16207; }
table .type-badge.safety { background:#fef3c7; color:#92400e; }
table .type-badge.leadership { background:#f0effe; color:#6b3fa0; }
table .type-badge.soft-skills { background:#fefce8; color:#a16207; }
.card-header { display:flex; align-items:center; gap:12px; margin-bottom:10px; }
.card-icon { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:18px; font-weight:800; flex-shrink:0; }
.card-id { font-size:10px; color:#9999bb; font-weight:600; margin:0 0 2px; }
.card-title { font-size:14px; font-weight:700; color:#0b044d; margin:0; }
.card-venue { font-size:12px; color:#6b6a8a; margin:0 0 14px; }
.capacity-bar { margin-bottom:14px; }
.capacity-label { display:flex; justify-content:space-between; font-size:10px; font-weight:700; letter-spacing:0.5px; margin-bottom:6px; }
.capacity-label span:first-child { color:#9999bb; }
.progress-bar { height:6px; background:#f0effe; border-radius:99px; overflow:hidden; }
.progress-fill { height:100%; border-radius:99px; }
.card-footer { display:flex; justify-content:space-between; align-items:center; padding-top:14px; border-top:1px solid #f0effe; }
.card-footer > div:first-child p:first-child { font-size:10px; color:#9999bb; font-weight:600; margin:0 0 2px; }
.card-footer > div:first-child p:last-child { font-size:12.5px; color:#0b044d; font-weight:600; margin:0; }
.card-actions { display:flex; gap:6px; }
.btn-enroll { padding:7px 14px; border-radius:8px; border:none; background:linear-gradient(135deg,#0b044d,#1a0f6e); font-size:12px; font-weight:600; color:#fff; cursor:pointer; }
.btn-enroll:hover { background:linear-gradient(135deg,#1a0f6e,#0b044d); }
.btn-certificate { padding:7px 10px; border-radius:8px; border:none; background:#15803d; font-size:12px; font-weight:600; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; }
.btn-certificate:hover { background:#166534; }
.btn-certificate svg { stroke:#fff; }
</style>

<script>
    const sidebar      = document.getElementById('sidebar');
    const toggleBtn    = document.getElementById('toggle-btn');
    const logoText     = document.getElementById('logo-text');
    const navLabel     = document.getElementById('nav-label');
    const userInfo     = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn    = document.getElementById('mobile-menu-btn');
    const overlay      = document.getElementById('mobile-overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = collapsed ? '›' : '‹';
            if (logoText) logoText.style.display  = collapsed ? 'none' : '';
            if (navLabel) navLabel.style.display  = collapsed ? 'none' : '';
            if (userInfo) userInfo.style.display  = collapsed ? 'none' : '';
            if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
            document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
                el.style.display = collapsed ? 'none' : '';
            });
        });
    }

    if (mobileBtn) {
        mobileBtn.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }

    const typeColors = { Leadership: '#0b044d', Technical: '#15803d', 'Soft Skills': '#d9bb00', Safety: '#8e1e18', Compliance: '#6b3fa0' };
        
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }
        
        function openMyTraining(title, type, progress, startDate, endDate, venue, status, certificate) {
            const color = typeColors[type] || '#6b3fa0';
            document.getElementById('modalId').textContent = 'TRAINING PROGRAM';
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalSub').textContent = type + ' Training · ' + venue;
            document.getElementById('modalIcon').style.background = 'linear-gradient(135deg, ' + color + ', ' + color + '99)';
            document.getElementById('modalStatus').textContent = status;
            document.getElementById('modalStatus').className = 'badge-status ' + (status === 'Enrolled' ? 'processed' : 'on-hold');
            document.getElementById('modalStatus').style.display = 'inline-block';
            document.getElementById('modalType').textContent = type;
            
            let progressHtml = '';
            if (progress !== undefined && progress < 100) {
                progressHtml = '<div class="modal-progress"><div class="modal-progress-label"><span>TRAINING PROGRESS</span><span style="color:' + color + '">' + progress + '%</span></div><div style="height:8px;background:#f0effe;border-radius:99px;"><div style="height:100%;width:' + progress + '%;background:linear-gradient(90deg,' + color + ',' + color + '99);border-radius:99px;"></div></div></div>';
            }
            
            let certRow = '';
            if (certificate) {
                certRow = '<div class="modal-row"><span>Certificate No.</span><strong>' + certificate + '</strong></div>';
            }
            
            document.getElementById('modalBody').innerHTML = progressHtml + '<div class="modal-section-label">SCHEDULE & DETAILS</div><div class="modal-row"><span>Start Date</span><strong>' + startDate + '</strong></div><div class="modal-row"><span>End Date</span><strong>' + endDate + '</strong></div><div class="modal-row"><span>Venue</span><strong>' + venue + '</strong></div>' + certRow;
            
            if (certificate) {
                document.getElementById('modalAction').innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>Download Certificate';
                document.getElementById('modalAction').onclick = function() {
                    closeModal('trainingModal');
                    downloadCertificate(certificate);
                };
            } else {
                document.getElementById('modalAction').textContent = 'Continue Training';
                document.getElementById('modalAction').onclick = function() {
                    closeModal('trainingModal');
                };
            }
            
            document.getElementById('modalAction').style.display = 'flex';
            document.getElementById('trainingModal').style.display = 'flex';
        }
        
        function openAvailableTraining(title, type, slots, capacity, startDate, endDate, venue) {
            const color = typeColors[type] || '#6b3fa0';
            document.getElementById('modalId').textContent = 'AVAILABLE TRAINING';
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalSub').textContent = type + ' Training · ' + venue;
            document.getElementById('modalIcon').style.background = 'linear-gradient(135deg, ' + color + ', ' + color + '99)';
            document.getElementById('modalStatus').style.display = 'none';
            document.getElementById('modalType').textContent = type;
            
            const fillPct = Math.round(((capacity - slots) / capacity) * 100);
            const fillColor = fillPct > 75 ? '#dc2626' : (fillPct > 50 ? '#d97706' : '#15803d');
            
            document.getElementById('modalBody').innerHTML = '<div class="slots-card" style="background:' + (fillPct > 75 ? '#fef2f2' : '#f7f6ff') + ';"><div class="slots-icon" style="background:linear-gradient(135deg, ' + fillColor + ', ' + fillColor + 'dd);"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div><div style="flex:1;"><p style="font-size:11px;color:#9999bb;font-weight:600;margin:0 0 4px;">AVAILABLE SLOTS</p><div style="display:flex;align-items:baseline;gap:4px;"><span style="font-size:28px;font-weight:800;color:' + fillColor + ';">' + slots + '</span><span style="font-size:16px;color:#9999bb;font-weight:600;">/ ' + capacity + '</span></div><div style="margin-top:8px;"><div style="height:6px;background:#e5e4f0;border-radius:99px;overflow:hidden;"><div style="height:100%;width:' + fillPct + '%;background:' + fillColor + ';border-radius:99px;"></div></div></div></div></div><div class="modal-section-label">SCHEDULE & DETAILS</div><div class="modal-row"><span>Start Date</span><strong>' + startDate + '</strong></div><div class="modal-row"><span>End Date</span><strong>' + endDate + '</strong></div><div class="modal-row"><span>Venue</span><strong>' + venue + '</strong></div><div class="modal-row"><span>Training Type</span><strong>' + type + '</strong></div>';
            
            document.getElementById('modalAction').innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>Enroll Now';
            document.getElementById('modalAction').onclick = function() {
                if (slots > 0) {
                    openEnrollConfirm(null, title, type, startDate, endDate, venue);
                } else {
                    showFullyBookedModal();
                }
            };
            document.getElementById('modalAction').style.display = 'flex';
            document.getElementById('modalFooter').style.display = 'flex';
            document.getElementById('trainingModal').style.display = 'flex';
        }
    
    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
        const ms = document.getElementById('modalStatus');
        if (ms) ms.style.display = 'inline-block';
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        }
    });
    
    function showFullyBookedModal() {
        document.getElementById('fullyBookedModal').style.display = 'flex';
    }

    function downloadCertificate(certNo) {
        const now = new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        document.getElementById('cdCertNo').textContent = certNo;
        document.getElementById('cdDate').textContent   = now;
        document.getElementById('certDownloadModal').style.display = 'flex';
    }
    
    let _pendingEnroll = null;

    function enrollTraining(trainingId) {
        // Find card data from the DOM
        const card = document.querySelector('[onclick*="\'' + trainingId + '\'"]').closest('.training-card');
        const title  = card.querySelector('.card-title').textContent;
        const type   = card.querySelector('.type-badge').textContent.trim();
        const start  = card.querySelector('.card-footer p:last-child').textContent;
        const venue  = card.querySelector('.card-venue').textContent.trim();
        openEnrollConfirm(trainingId, title, type, start, '—', venue);
    }

    function openEnrollConfirm(id, title, type, start, end, venue) {
        _pendingEnroll = { id, title };
        document.getElementById('ecTitle').textContent  = title;
        document.getElementById('ecType').textContent   = type;
        document.getElementById('ecStart').textContent  = start;
        document.getElementById('ecEnd').textContent    = end !== '—' ? end : start;
        document.getElementById('ecVenue').textContent  = venue;
        document.getElementById('enrollConfirmModal').style.display = 'flex';
    }

    function submitEnrollment() {
        if (!_pendingEnroll) return;
        const ref  = 'ENR-' + new Date().getFullYear() + '-' + String(Math.floor(Math.random() * 9000) + 1000);
        const now  = new Date().toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
        document.getElementById('esTitle').textContent = _pendingEnroll.title;
        document.getElementById('esRef').textContent   = ref;
        document.getElementById('esDate').textContent  = now;
        closeModal('enrollConfirmModal');
        closeModal('trainingModal');
        document.getElementById('enrollSuccessModal').style.display = 'flex';
        _pendingEnroll = null;
    }
</script>

@include('permanent.permanent-chatbot')

@endsection