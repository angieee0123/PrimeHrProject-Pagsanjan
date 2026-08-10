<section class="table-section" id="accrual-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">CSC Daily Accrual Configuration</h3>
            <p class="table-sub">Configure leave credit earning rates for all accrual-based leave types · {{ $accrualRates->total() }} records</p>
        </div>
        <div class="table-actions">
            <button class="btn-export" style="background: var(--gp-pri); color: #fff; border-color: var(--gp-pri);" onclick="openAddAccrualRateModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Accrual Rate
            </button>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th style="text-align: left;">Leave Type</th>
                    <th style="text-align: center;">Accrual Frequency</th>
                    <th style="text-align: center;">Days of Service Required</th>
                    <th style="text-align: center;">Credits Earned Per Period</th>
                    <th style="text-align: center;">Effective Date</th>
                    <th style="text-align: center;">End Date</th>
                    <th style="text-align: center;">Status</th>
                    <th class="row-menu-head">Actions</th>
                </tr>
            </thead>
            <tbody id="accrualRatesTableBody">
                @forelse($accrualRates as $rate)
                <tr class="accrual-rate-row" data-status="{{ $rate->is_active ? 'active' : 'inactive' }}" data-frequency="{{ $rate->accrual_frequency }}">
                    <td data-label="Leave Type" style="text-align: left;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="emp-avatar" style="background: {{ ['var(--gp-pri)', 'var(--theme-danger)', 'var(--gp-pri-2)', '#a52820'][$loop->index % 4] }}; margin-left: 0;">
                                {{ $rate->leave_code }}
                            </div>
                            <div>
                                <p style="font-weight: 600; color: var(--gp-pri); margin: 0; font-size: 13px;">{{ $rate->leaveType->leave_name ?? $rate->leave_code }}</p>
                                <p style="color: var(--gp-text-mid); margin: 0; font-size: 12px;">{{ $rate->leaveType->is_accrued ? 'Accrued Leave Type' : 'Fixed Leave Type' }}</p>
                            </div>
                        </div>
                    </td>
                    <td data-label="Accrual Frequency" style="text-align: center;">
                        <span class="badge-status {{ $rate->accrual_frequency === 'daily' ? 'processed' : ($rate->accrual_frequency === 'monthly' ? 'pending' : 'on-hold') }}">
                            {{ ucfirst($rate->accrual_frequency) }}
                        </span>
                    </td>
                    <td data-label="Days of Service" style="text-align: center; font-weight: 600; color: var(--gp-pri);">
                        {{ number_format($rate->days_of_service_required, 2) }} {{ $rate->days_of_service_required == 1 ? 'day' : 'days' }}
                    </td>
                    <td data-label="Credits Earned" style="text-align: center; font-weight: 600; color: var(--theme-success);">
                        {{ number_format($rate->credits_earned_per_period, 4) }} credits
                    </td>
                    <td data-label="Effective Date" style="text-align: center; color: var(--gp-text-mid);">
                        {{ $rate->effective_date->format('M d, Y') }}
                    </td>
                    <td data-label="End Date" style="text-align: center; color: var(--gp-text-mid);">
                        {{ $rate->end_date ? $rate->end_date->format('M d, Y') : '—' }}
                    </td>
                    <td data-label="Status" style="text-align: center;">
                        <span class="badge-status {{ $rate->is_active ? 'processed' : 'on-hold' }}">
                            {{ $rate->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td data-label="Actions" class="row-menu-cell">
                        <button type="button" class="row-menu-btn" data-menu="accrualMenu{{ $rate->id }}"
                                onclick="toggleRowMenu(event)" aria-haspopup="menu" aria-expanded="false"
                                title="Actions" aria-label="Actions for this accrual rate">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/>
                            </svg>
                        </button>
                        <div class="row-menu" id="accrualMenu{{ $rate->id }}" role="menu" aria-label="Accrual rate actions">
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); viewAccrualRate({{ $rate->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                View rate
                            </button>
                            <button type="button" role="menuitem" class="row-menu-item" onclick="closeRowMenu(); editAccrualRate({{ $rate->id }})">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit rate
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: var(--gp-text-mid);">
                        No accrual rates found. Click "Add Accrual Rate" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p style="margin: 0;" id="accrualFooter">
                Showing <strong id="accrualRowStart">{{ $accrualRates->firstItem() ?? 0 }}</strong>-<strong id="accrualRowEnd">{{ $accrualRates->lastItem() ?? 0 }}</strong> of <strong id="accrualRowTotal">{{ $accrualRates->total() }}</strong> records
            </p>
            <select id="accrualRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeAccrualRowsPerPage()">
                <option value="10" {{ request('accrual_per_page', 10) == 10 ? 'selected' : '' }}>10 rows</option>
                <option value="25" {{ request('accrual_per_page', 10) == 25 ? 'selected' : '' }}>25 rows</option>
                <option value="50" {{ request('accrual_per_page', 10) == 50 ? 'selected' : '' }}>50 rows</option>
                <option value="100" {{ request('accrual_per_page', 10) == 100 ? 'selected' : '' }}>100 rows</option>
            </select>
        </div>
        <div class="pagination">
            @if ($accrualRates->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $accrualRates->previousPageUrl() }}#accrual-tab" class="page-btn" onclick="event.preventDefault(); navigateToAccrualPage('{{ $accrualRates->previousPageUrl() }}');">‹</a>
            @endif

            @foreach ($accrualRates->getUrlRange(1, $accrualRates->lastPage()) as $page => $url)
                @if ($page == $accrualRates->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $url }}#accrual-tab" class="page-btn" onclick="event.preventDefault(); navigateToAccrualPage('{{ $url }}');">{{ $page }}</a>
                @endif
            @endforeach

            @if ($accrualRates->hasMorePages())
                <a href="{{ $accrualRates->nextPageUrl() }}#accrual-tab" class="page-btn" onclick="event.preventDefault(); navigateToAccrualPage('{{ $accrualRates->nextPageUrl() }}');">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>

    <!-- Info Box -->
    <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; margin-top: 20px;">
        <div style="display: flex; gap: 12px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2" style="flex-shrink: 0;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="16" x2="12" y2="12"/>
                <line x1="12" y1="8" x2="12.01" y2="8"/>
            </svg>
            <div>
                <h4 style="margin: 0 0 8px 0; color: #0369a1; font-size: 14px; font-weight: 600;">CSC Accrual Rate Information</h4>
                <p style="margin: 0; color: #075985; font-size: 13px; line-height: 1.6;">
                    <strong>Current CSC Standard:</strong> VL and SL accrue at 1.25 days per month (15 days annually).<br>
                    <strong>Daily Calculation:</strong> 1.25 ÷ 30 = 0.042 credits per day of service (Official CSC Rate).<br>
                    <strong>Example:</strong> An employee with 30 days of service earns 30 × 0.042 = 1.26 leave credits.<br>
                    <strong>Future-Ready:</strong> Add new accrual rates here if CSC updates their policies.
                </p>
            </div>
        </div>
    </div>
</section>

@include('admin.leaveAndBenefits.modals.view-accrual-rate-modal')
@include('admin.leaveAndBenefits.modals.edit-accrual-rate-modal')

@push('scripts')
    @vite('resources/js/admin/leaveAndBenefits/csc-daily-accrual-tab.js')
@endpush
