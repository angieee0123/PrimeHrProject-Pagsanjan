<section class="table-section" id="leave-tab" style="display: block; border: 1px solid #e5e7eb; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, #f2f1fb 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid #e5e7eb; align-items: center;">
        <div>
            <h3 class="table-title" style="color: #111827; font-size: 15px; font-weight: 800; margin: 0 0 4px;">Leave Requests — {{ now()->format('F Y') }}</h3>
            <p class="table-sub" style="color: #667085; font-size: 12px; margin: 0;">Municipal Government of Pagsanjan · <span id="leaveRequestCount">{{ $leaveApplications->count() }}</span> records</p>
        </div>
        <div class="table-actions" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <button class="btn-export" style="background: #0b044d; color: #fff; border: 1px solid #0b044d; border-radius: 8px; font-size: 11px; padding: 0 14px; height: 34px; font-weight: 700; display: flex; align-items: center; gap: 6px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(11, 4, 77, 0.1);" onclick="openManualCreditModal('add')" onmouseover="this.style.background='#1b1464'; this.style.boxShadow='0 4px 8px rgba(11, 4, 77, 0.2)'" onmouseout="this.style.background='#0b044d'; this.style.boxShadow='0 2px 4px rgba(11, 4, 77, 0.1)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Manual Credits
            </button>
            <button class="btn-export" style="background: #dc2626; color: #fff; border: 1px solid #dc2626; border-radius: 8px; font-size: 11px; padding: 0 14px; height: 34px; font-weight: 700; display: flex; align-items: center; gap: 6px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);" onclick="openManualCreditModal('deduct')" onmouseover="this.style.background='#b91c1c'; this.style.boxShadow='0 4px 8px rgba(220, 38, 38, 0.2)'" onmouseout="this.style.background='#dc2626'; this.style.boxShadow='0 2px 4px rgba(220, 38, 38, 0.1)'">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Deduct Credits
            </button>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Department</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Leave Type</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid #eef2f6;">Period</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Days</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Status</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: #f8fafc; color: #667085; font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid #eef2f6;">Actions</th>
                </tr>
            </thead>
            <tbody id="leaveRequestsTableBody">
                @forelse($leaveApplications as $application)
                <tr data-department="{{ $application->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}"
                    data-type="{{ $application->leaveType->leave_name ?? 'N/A' }}"
                    data-status="{{ ucfirst($application->status) }}"
                    data-start-date="{{ $application->start_date->format('Y-m-d') }}"
                    data-end-date="{{ $application->end_date->format('Y-m-d') }}"
                    style="transition: all 0.15s ease;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @if($application->employee->photo)
                                <img src="{{ $application->employee->photo }}" alt="{{ $application->employee->first_name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; flex-shrink: 0;">
                            @else
                                <div style="background: {{ $avatarColors[($application->employee->id ?? 0) % count($avatarColors)] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid #e2e8f0; flex-shrink: 0;">
                                    {{ strtoupper(substr($application->employee->first_name ?? 'N', 0, 1) . substr($application->employee->last_name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $application->employee->first_name ?? 'N/A' }} {{ $application->employee->last_name ?? '' }}</p>
                                <p style="margin: 0; font-size: 11px; color: #64748b; font-weight: 500;">{{ $application->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569;">{{ $application->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 13px; color: #0b044d; font-weight: 600;">{{ $application->leaveType->leave_name ?? 'N/A' }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; font-size: 12px; color: #64748b;">
                        @php
                            $startMonth = $application->start_date->format('M');
                            $endMonth = $application->end_date->format('M');
                            $startDay = $application->start_date->format('d');
                            $endDay = $application->end_date->format('d');
                            $startYear = $application->start_date->format('Y');
                            $endYear = $application->end_date->format('Y');
                            
                            if ($startYear === $endYear && $startMonth === $endMonth) {
                                $period = $startMonth . ' ' . $startDay . ' – ' . $endDay . ', ' . $startYear;
                            } elseif ($startYear === $endYear) {
                                $period = $startMonth . ' ' . $startDay . ' – ' . $endMonth . ' ' . $endDay . ', ' . $startYear;
                            } else {
                                $period = $startMonth . ' ' . $startDay . ', ' . $startYear . ' – ' . $endMonth . ' ' . $endDay . ', ' . $endYear;
                            }
                        @endphp
                        <span style="font-weight: 600; color: #111827;">{{ $period }}</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center; font-weight: 700; color: #0b044d; font-size: 14px;">{{ number_format($application->number_of_days, 1) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6; text-align: center;">
                        @if($application->status === 'approved')
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f0fdf4; color: #15803d;">Approved</span>
                        @elseif($application->status === 'pending')
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #b45309;">Pending</span>
                        @elseif($application->status === 'rejected')
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #fef2f2; color: #dc2626;">Disapproved</span>
                        @else
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f3f4f6; color: #6b7280;">Cancelled</span>
                        @endif
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid #eef2f6;">
                        <div style="position: relative; display: flex; justify-content: center;">
                            <button class="action-ellipsis-btn" onclick="toggleLeaveActionMenu(event, this)" title="Actions" style="background: none; border: none; color: #8f8daf; cursor: pointer; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#0b044d'" onmouseout="this.style.background='none'; this.style.color='#8f8daf'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="leave-action-menu" style="display: none; position: absolute; right: 0; top: 100%; background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15); z-index: 100; min-width: 160px; margin-top: 6px; overflow: hidden;">
                                <button data-leave-app-id="{{ $application->id }}" onclick="openAdminLeaveDetailModal(
                                    {{ $application->id }},
                                    '{{ addslashes($application->employee->first_name ?? 'N/A') }} {{ addslashes($application->employee->last_name ?? '') }}',
                                    '{{ $application->employee->employee_id ?? 'N/A' }}',
                                    '{{ addslashes($application->leaveType->leave_name ?? 'N/A') }}',
                                    '{{ $application->start_date->format('M d, Y') }}',
                                    '{{ $application->end_date->format('M d, Y') }}',
                                    {{ $application->number_of_days }},
                                    '{{ addslashes($application->reason) }}',
                                    '{{ ucfirst($application->status) }}',
                                    '{{ $application->application_number }}',
                                    '{{ $application->attachment_path ? asset('storage/' . $application->attachment_path) : '' }}',
                                    '{{ addslashes($application->approver_remarks ?? '') }}'
                                )" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #0b044d; font-weight: 600; cursor: pointer; transition: all 0.2s; border-radius: 0;" onmouseover="this.style.background='#f2f1fb'" onmouseout="this.style.background='none'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Details
                                </button>
                                @if($application->status === 'pending')
                                    <button onclick="approveLeaveRequest({{ $application->id }}, '{{ $application->application_number }}')" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #22c55e; font-weight: 600; cursor: pointer; border-top: 1px solid #f1f5f9; transition: all 0.2s; border-radius: 0;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='none'">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><polyline points="20 6 9 17 4 12"/></svg>
                                        Approve
                                    </button>
                                    <button onclick="openRejectModal({{ $application->id }}, '{{ $application->application_number }}')" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #ef4444; font-weight: 600; cursor: pointer; border-top: 1px solid #f1f5f9; transition: all 0.2s; border-radius: 0;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        Disapprove
                                    </button>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 60px 20px; border-bottom: none;">
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: #475569; font-weight: 600;">No leave requests found</p>
                        <p style="margin: 0; font-size: 13px; color: #94a3b8;">Leave applications will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid #eef2f6; background: #f8fafc;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p id="leaveRequestFooter" style="margin: 0; font-size: 12px; color: #64748b; font-weight: 500;">Showing <strong style="color: #0b044d; font-weight: 700;" id="leaveRequestRowStart">1</strong>-<strong style="color: #0b044d; font-weight: 700;" id="leaveRequestRowEnd">{{ min(10, $leaveApplications->count()) }}</strong> of <strong style="color: #0b044d; font-weight: 700;" id="leaveRequestRowTotal">{{ $leaveApplications->count() }}</strong> records</p>
            <select id="leaveRequestRowsPerPage" onchange="changeLeaveRequestRowsPerPage()" style="width: auto; padding: 6px 10px; font-size: 12px; border: 1px solid #e5e7eb; border-radius: 6px; background: #fff; color: #56547a; font-weight: 600; cursor: pointer;">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="leaveRequestPaginationControls" style="display: flex; gap: 4px;"></div>
    </div>
</section>

{{-- Admin Leave Detail Modal — CS Form No. 6 Preview --}}
<div class="modal-overlay" id="adminLeaveDetailModal" onclick="closeAdminLeaveDetailModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 920px; width: 95vw;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow" id="adminLeaveAppNumber">CS FORM NO. 6 · LV-2025-001</span>
                <h3 class="modal-title" id="adminLeaveEmployeeName">Employee Name</h3>
                <p class="modal-sub">
                    <span id="adminLeaveEmployeeId">PGS-0115</span>
                    · <span id="adminLeaveType">Vacation Leave</span>
                    · <span id="adminLeaveStatus" class="badge-status pending" style="font-size: 11px;">Pending</span>
                </p>
            </div>
            <button class="modal-close" onclick="closeAdminLeaveDetailModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0; background: #e8e8f0;">
            <iframe id="adminLeaveFormFrame"
                title="CS Form No. 6 — Application for Leave"
                style="width: 100%; height: 65vh; border: none; display: block; background: #fff;"
                src="about:blank"></iframe>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeAdminLeaveDetailModal()">Close</button>
            <button class="modal-btn-primary" id="adminDownloadBtn" style="display: none; background: #6b7280;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Attachment
            </button>
            <div style="display: flex; gap: 8px; margin-left: auto;">
                <button class="modal-btn-primary" id="adminPrintFormBtn" onclick="printLeaveForm()" style="background: #6366f1;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Print Form
                </button>
                <button class="modal-btn-primary" id="adminDownloadFormBtn" onclick="downloadLeaveForm()" style="background: #10b981;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Download PDF
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal-overlay" id="rejectModal" onclick="closeRejectModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DISAPPROVE LEAVE REQUEST</span>
                <h3 class="modal-title" id="rejectModalTitle">Confirm Disapproval</h3>
                <p class="modal-sub">Please provide a reason for disapproving this leave request</p>
            </div>
            <button class="modal-close" onclick="closeRejectModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-field">
                <label style="display: block; font-weight: 600; color: #0b044d; margin-bottom: 8px;">Disapproval Reason <span style="color: #8e1e18;">*</span></label>
                <textarea id="rejectionReason" rows="4" placeholder="Explain why this leave request is being disapproved..." required style="width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeRejectModal()">Cancel</button>
            <button class="modal-btn-primary" id="confirmRejectBtn" style="background: #dc2626;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                Disapprove Request
            </button>
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/leaveAndBenefits/leave-requests-tab.js')
@endpush
