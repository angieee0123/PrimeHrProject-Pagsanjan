<section class="table-section" id="monetization-tab" style="display: none; border: 1px solid var(--theme-neutral-300); border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, var(--gp-bg-tint-2) 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid var(--theme-neutral-300); align-items: center;">
        <div>
            <h3 class="table-title" style="color: var(--theme-neutral-950); font-size: 15px; font-weight: 800; margin: 0 0 4px;">Monetization Requests — {{ now()->format('F Y') }}</h3>
            <p class="table-sub" style="color: var(--theme-neutral-700); font-size: 12px; margin: 0;">Municipal Government of Pagsanjan · <span id="monetRequestCount">{{ $monetizationRequests->count() }}</span> records</p>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%; overflow: auto;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--theme-neutral-200);">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--theme-neutral-200);">Department</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--theme-neutral-200);">Request No.</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid var(--theme-neutral-200);">Days (VL / SL)</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: right; border-bottom: 1px solid var(--theme-neutral-200);">Amount</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid var(--theme-neutral-200);">Status</th>
                    <th class="row-menu-head" style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid var(--theme-neutral-200);">Actions</th>
                </tr>
            </thead>
            <tbody id="monetRequestsTableBody">
                @forelse($monetizationRequests as $monet)
                @php $totalDays = (float) $monet->vl_days + (float) $monet->sl_days; @endphp
                <tr data-department="{{ $monet->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}"
                    data-status="{{ ucfirst($monet->status) }}"
                    style="transition: all 0.15s ease;"
                    onmouseover="this.style.background='#f9fafb'"
                    onmouseout="this.style.background='#fff'">
                    <td style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200);">
                        <div class="emp-cell" style="display: flex; align-items: center; gap: 12px;">
                            @if($monet->employee->photo)
                                <img src="{{ $monet->employee->photo }}" alt="{{ $monet->employee->first_name }}" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 2px solid var(--theme-neutral-300); flex-shrink: 0;">
                            @else
                                <div style="background: {{ $avatarColors[($monet->employee->id ?? 0) % count($avatarColors)] }}; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; border: 2px solid var(--theme-neutral-300); flex-shrink: 0;">
                                    {{ strtoupper(substr($monet->employee->first_name ?? 'N', 0, 1) . substr($monet->employee->last_name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <div style="min-width: 0;">
                                <p style="margin: 0 0 2px; font-size: 13px; font-weight: 600; color: var(--theme-neutral-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $monet->employee->first_name ?? 'N/A' }} {{ $monet->employee->last_name ?? '' }}</p>
                                <p style="margin: 0; font-size: 11px; color: var(--theme-neutral-700); font-weight: 500;">{{ $monet->employee->employee_id ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200);">
                        <span style="display: inline-block; padding: 4px 10px; border-radius: 999px; font-size: 11px; font-weight: 700; background: var(--theme-neutral-100); color: var(--theme-neutral-700);">{{ $monet->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</span>
                    </td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200); font-size: 13px; color: var(--gp-pri); font-weight: 600;">{{ $monet->request_number }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200); text-align: center; font-weight: 700; color: var(--gp-pri); font-size: 13px;">{{ number_format((float) $monet->vl_days, 1) }} / {{ number_format((float) $monet->sl_days, 1) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200); text-align: right; font-weight: 700; color: var(--theme-neutral-950); font-size: 13px;">₱{{ number_format((float) $monet->computed_amount, 2) }}</td>
                    <td style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200); text-align: center;">
                        @if($monet->status === 'approved')
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f0fdf4; color: var(--theme-success);">Approved</span>
                        @elseif($monet->status === 'pending')
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #b45309;">Pending</span>
                        @elseif($monet->status === 'disapproved')
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #fef2f2; color: var(--theme-danger);">Disapproved</span>
                        @else
                            <span style="display: inline-block; padding: 5px 12px; border-radius: 999px; font-size: 11px; font-weight: 700; background: #f3f4f6; color: var(--theme-neutral-700);">Cancelled</span>
                        @endif
                    </td>
                    <td class="row-menu-cell" style="padding: 14px 16px; border-bottom: 1px solid var(--theme-neutral-200);">
                        <div style="position: relative; display: flex; justify-content: center;">
                            <button class="action-ellipsis-btn" onclick="toggleMonetActionMenu(event, this)" title="Actions" style="background: none; border: none; color: var(--gp-text-soft); cursor: pointer; padding: 6px 10px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='var(--gp-pri)'" onmouseout="this.style.background='none'; this.style.color='#8f8daf'">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="monet-action-menu" style="display: none; position: absolute; right: 0; top: 100%; background: #fff; border: 1px solid var(--theme-neutral-300); border-radius: 10px; box-shadow: 0 10px 24px rgba(15, 23, 42, 0.15); z-index: 100; min-width: 160px; margin-top: 6px; overflow: hidden;">
                                <button onclick="openAdminMonetDetailModal({{ $monet->id }})" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: var(--gp-pri); font-weight: 600; cursor: pointer; transition: all 0.2s; border-radius: 0;" onmouseover="this.style.background='#f2f1fb'" onmouseout="this.style.background='none'">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Details
                                </button>
                                @if($monet->status === 'pending')
                                    <button onclick="approveMonetizationRequest({{ $monet->id }}, '{{ $monet->request_number }}')" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #22c55e; font-weight: 600; cursor: pointer; border-top: 1px solid var(--theme-neutral-100); transition: all 0.2s; border-radius: 0;" onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background='none'">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display: inline; margin-right: 8px; vertical-align: middle;"><polyline points="20 6 9 17 4 12"/></svg>
                                        Approve
                                    </button>
                                    <button onclick="openMonetDisapproveModal({{ $monet->id }}, '{{ $monet->request_number }}')" style="width: 100%; padding: 11px 14px; border: none; background: none; text-align: left; font-size: 12px; color: #ef4444; font-weight: 600; cursor: pointer; border-top: 1px solid var(--theme-neutral-100); transition: all 0.2s; border-radius: 0;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
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
                        <div style="width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; background: var(--theme-neutral-100); display: flex; align-items: center; justify-content: center;">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p style="margin: 0 0 8px; font-size: 15px; color: var(--theme-neutral-700); font-weight: 600;">No monetization requests found</p>
                        <p style="margin: 0; font-size: 13px; color: var(--theme-neutral-600);">Employee monetization requests will appear here</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-top: 1px solid var(--theme-neutral-200); background: var(--theme-neutral-50);">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p id="monetRequestFooter" style="margin: 0; font-size: 12px; color: var(--theme-neutral-700); font-weight: 500;">Showing <strong style="color: var(--gp-pri); font-weight: 700;" id="monetRequestRowTotal">{{ $monetizationRequests->count() }}</strong> records</p>
        </div>
    </div>
</section>

@include('admin.leaveAndBenefits.modals.monetization-detail-modal')

{{-- Disapprove Modal --}}
<div class="modal-overlay" id="monetRejectModal" onclick="closeMonetDisapproveModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 500px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DISAPPROVE MONETIZATION REQUEST</span>
                <h3 class="modal-title" id="monetRejectModalTitle">Confirm Disapproval</h3>
                <p class="modal-sub">Please provide a reason for disapproving this monetization request</p>
            </div>
            <button class="modal-close" onclick="closeMonetDisapproveModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-field">
                <label style="display: block; font-weight: 600; color: var(--gp-pri); margin-bottom: 8px;">Disapproval Reason <span style="color: var(--theme-danger);">*</span></label>
                <textarea id="monetRejectionReason" rows="4" placeholder="Explain why this monetization request is being disapproved..." required style="width: 100%; padding: 12px; border: 2px solid var(--theme-neutral-300); border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical;"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeMonetDisapproveModal()">Cancel</button>
            <button class="modal-btn-primary" id="monetConfirmRejectBtn" style="background: var(--theme-danger);">
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
    @vite('resources/js/admin/leaveAndBenefits/monetization-requests-tab.js')
@endpush
