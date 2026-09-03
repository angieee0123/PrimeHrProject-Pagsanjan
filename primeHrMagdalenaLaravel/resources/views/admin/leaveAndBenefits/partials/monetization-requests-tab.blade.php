{{-- Monetization Requests tab.

     The table, its row menu and its two empty states are styled from
     `resources/css/admin/adminLeaveAndBenefits.css` rather than inline, so
     the control sizes here are the same ones the Leave Types and
     Transaction History tabs use instead of a second set that happens to
     look similar. See that file's monetization block for why.

     Every action a row can offer is rendered once, in full; `data-status`
     on the menu decides which of them show. That is what lets approving a
     request swap its menu in place — Print/Download in, Approve/Disapprove
     out — without a second copy of this markup living in JavaScript. Each
     endpoint re-checks the status server-side regardless, so the hiding is
     tidiness rather than the permission. --}}
<section class="table-section" id="monetization-tab" style="display: none; border: 1px solid var(--theme-neutral-300); border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, .04), 0 1px 3px rgba(15, 23, 42, .03); overflow: hidden;">
    <div class="table-header" style="background: linear-gradient(135deg, var(--gp-bg-tint-2) 0%, #fff 100%); padding: 18px 20px; border-bottom: 1px solid var(--theme-neutral-300); align-items: center;">
        <div>
            <h3 class="table-title" style="color: var(--theme-neutral-950); font-size: 15px; font-weight: 800; margin: 0 0 4px;">Monetization Requests — {{ now()->format('F Y') }}</h3>
            <p class="table-sub" style="color: var(--theme-neutral-700); font-size: 12px; margin: 0;">Municipal Government of Pagsanjan · <span id="monetRequestCount">{{ $monetizationRequests->count() }}</span> records</p>
        </div>
    </div>

    <div class="table-wrapper" style="max-width: 100%;">
        <table class="payroll-table" style="width: 100%; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--theme-neutral-200);">Employee</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--theme-neutral-200);">Department</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--theme-neutral-200);">Request</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid var(--theme-neutral-200);">Days Monetized</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: right; border-bottom: 1px solid var(--theme-neutral-200);">Amount</th>
                    <th style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid var(--theme-neutral-200);">Status</th>
                    <th class="row-menu-head" style="position: sticky; top: 0; z-index: 2; background: var(--theme-neutral-50); color: var(--theme-neutral-700); font-size: 10.5px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; padding: 12px 16px; text-align: center; border-bottom: 1px solid var(--theme-neutral-200); width: 72px;">Actions</th>
                </tr>
            </thead>
            <tbody id="monetRequestsTableBody">
                @forelse($monetizationRequests as $monet)
                @php
                    $monetEmployee = $monet->employee;
                    $monetEmployment = $monetEmployee?->employmentDetail;
                    $monetDepartment = $monetEmployment?->departmentRelation?->name ?? 'N/A';
                    $monetPosition = $monetEmployment?->designationRelation?->title;
                    $monetName = trim(($monetEmployee?->first_name ?? 'N/A') . ' ' . ($monetEmployee?->last_name ?? ''));
                    $monetTotalDays = (float) $monet->vl_days + (float) $monet->sl_days;
                    $monetStatusClass = match ($monet->status) {
                        'approved' => 'approved',
                        'pending' => 'pending',
                        'disapproved' => 'rejected',
                        default => 'cancelled',
                    };
                @endphp
                <tr class="monet-row"
                    data-monet-id="{{ $monet->id }}"
                    data-department="{{ $monetDepartment }}"
                    data-status="{{ ucfirst($monet->status) }}"
                    data-employee-name="{{ $monetName }}"
                    data-request-number="{{ $monet->request_number }}"
                    data-amount="{{ number_format((float) $monet->computed_amount, 2) }}"
                    data-days="{{ number_format($monetTotalDays, 1) }}">
                    <td>
                        <div class="monet-emp">
                            @if($monetEmployee?->photo)
                                <img class="monet-emp-avatar" src="{{ $monetEmployee->photo }}" alt="">
                            @else
                                <div class="monet-emp-avatar" style="background: {{ $avatarColors[($monetEmployee->id ?? 0) % count($avatarColors)] }};" aria-hidden="true">
                                    {{ strtoupper(substr($monetEmployee?->first_name ?? 'N', 0, 1) . substr($monetEmployee?->last_name ?? 'A', 0, 1)) }}
                                </div>
                            @endif
                            <div class="monet-emp-text">
                                <p class="monet-emp-name">{{ $monetName }}</p>
                                <p class="monet-emp-meta" title="{{ $monetEmployee?->employee_id ?? 'N/A' }}{{ $monetPosition ? ' · ' . $monetPosition : '' }}">
                                    <strong>{{ $monetEmployee?->employee_id ?? 'N/A' }}</strong>@if($monetPosition) · {{ $monetPosition }}@endif
                                </p>
                            </div>
                        </div>
                    </td>
                    <td><span class="monet-dept" title="{{ $monetDepartment }}">{{ $monetDepartment }}</span></td>
                    <td>
                        <p class="monet-reqno">{{ $monet->request_number }}</p>
                        <p class="monet-filed">Filed {{ $monet->created_at?->format('M d, Y') ?? '—' }}</p>
                    </td>
                    <td class="monet-days">
                        <span class="monet-days-total">{{ number_format($monetTotalDays, 1) }}</span>
                        <span class="monet-days-split">{{ number_format((float) $monet->vl_days, 1) }} VL · {{ number_format((float) $monet->sl_days, 1) }} SL</span>
                    </td>
                    <td class="monet-amount">₱{{ number_format((float) $monet->computed_amount, 2) }}</td>
                    <td class="monet-status-cell">
                        {{-- The app's one status-badge definition, so these pills
                             follow the active palette like every other badge
                             rather than the four hex literals they used to be. --}}
                        <span class="badge-status {{ $monetStatusClass }}" data-monet-badge>
                            {{ $monet->status === 'disapproved' ? 'Disapproved' : ucfirst($monet->status) }}
                        </span>
                    </td>
                    <td class="row-menu-cell">
                        <div class="monet-menu-wrap">
                            <button type="button" class="monet-ellipsis-btn"
                                    onclick="toggleMonetActionMenu(event, this)"
                                    aria-haspopup="true" aria-expanded="false"
                                    aria-label="Actions for {{ $monet->request_number }}" title="Actions">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                            </button>
                            <div class="monet-action-menu" role="menu" data-status="{{ ucfirst($monet->status) }}">
                                <button type="button" role="menuitem" onclick="openAdminMonetDetailModal({{ $monet->id }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    View Details
                                </button>
                                {{-- Print Sheet only on an approved request: the
                                     office's Monetization form carries no status
                                     anywhere on it, so a pending one would print
                                     as an authorised computation of money owed.
                                     `renderForm()` refuses it server-side too. --}}
                                <button type="button" role="menuitem" data-monet-item="print" onclick="printMonetizationSheet({{ $monet->id }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    Print Sheet
                                </button>
                                <button type="button" role="menuitem" data-monet-item="download" onclick="downloadMonetizationSheet({{ $monet->id }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Download PDF
                                </button>
                                <button type="button" role="menuitem" class="is-approve" data-monet-item="approve" onclick="approveMonetizationRequest({{ $monet->id }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                    Approve
                                </button>
                                <button type="button" role="menuitem" class="is-disapprove" data-monet-item="disapprove" onclick="openMonetDisapproveModal({{ $monet->id }})">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    Disapprove
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr id="monetNoRecordsRow">
                    <td colspan="7" class="monet-empty-td">
                        <div class="monet-empty-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <p class="monet-empty-title">No Monetization Requests Found</p>
                        <p class="monet-empty-sub">Requests filed by employees to convert leave credits into cash will appear here once submitted.</p>
                    </td>
                </tr>
                @endforelse

                {{-- Shown by applyMonetizationAdminFilters() when the status
                     filter hides every row. Without it, narrowing to a status
                     nobody holds left a blank strip that reads as a broken
                     table rather than as an empty result. --}}
                <tr id="monetNoMatchesRow" style="display: none;">
                    <td colspan="7" class="monet-empty-td">
                        <div class="monet-empty-icon">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                            </svg>
                        </div>
                        <p class="monet-empty-title">No Requests Match This Filter</p>
                        <p class="monet-empty-sub">No monetization request currently holds the selected status. <button type="button" class="btn-link-reset" onclick="clearMonetizationAdminFilters()" style="background: none; border: none; padding: 0; font: inherit; color: var(--gp-pri); font-weight: 700; cursor: pointer; text-decoration: underline;">Show all requests</button></p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="table-footer" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; padding: 16px 20px; border-top: 1px solid var(--theme-neutral-200); background: var(--theme-neutral-50);">
        <p id="monetRequestFooter" style="margin: 0; font-size: 12px; color: var(--theme-neutral-700); font-weight: 500;">Showing <strong style="color: var(--gp-pri); font-weight: 700;" id="monetRequestRowTotal">{{ $monetizationRequests->count() }}</strong> of <strong style="color: var(--gp-pri); font-weight: 700;">{{ $monetizationRequests->count() }}</strong> records</p>
        <p style="margin: 0; font-size: 11.5px; color: var(--theme-text-soft);">Approving a request deducts the monetized days from the employee's VL/SL balances.</p>
    </div>
</section>

@include('admin.leaveAndBenefits.modals.monetization-detail-modal')

{{-- Disapprove Modal. The reason is required by the controller, so the field
     is the confirmation: the request being refused is named above it rather
     than left to the admin's memory of which row they opened. --}}
<div class="modal-overlay" id="monetRejectModal" onclick="closeMonetDisapproveModal()" style="display: none;">
    <div class="modal-box" onclick="event.stopPropagation()" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DISAPPROVE MONETIZATION REQUEST</span>
                <h3 class="modal-title" id="monetRejectModalTitle">Disapprove Monetization Request?</h3>
                <p class="modal-sub" id="monetRejectModalSub">You are about to disapprove this monetization request.</p>
            </div>
            <button class="modal-close" onclick="closeMonetDisapproveModal()" aria-label="Close">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="monet-reject-summary" id="monetRejectSummary"></div>
            <div class="form-field">
                <label for="monetRejectionReason" style="display: block; font-weight: 600; color: var(--gp-pri); margin-bottom: 8px; font-size: 13px;">Disapproval Reason <span style="color: var(--theme-danger);">*</span></label>
                <textarea id="monetRejectionReason" rows="4" placeholder="Explain why this monetization request is being disapproved..." required maxlength="500" style="width: 100%; padding: 12px; border: 1px solid var(--theme-neutral-300); border-radius: 8px; font-family: inherit; font-size: 13px; resize: vertical;"></textarea>
                <p id="monetRejectionError" style="display: none; margin: 6px 0 0; font-size: 12px; font-weight: 600; color: var(--theme-danger);">A reason is required — the employee is shown this on their own request.</p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeMonetDisapproveModal()">Cancel</button>
            <button class="modal-btn-danger" id="monetConfirmRejectBtn">
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
