<section class="table-section" id="accrual-tab" style="display: none;">
    <div class="table-header">
        <div>
            <h3 class="table-title">CSC Daily Accrual Configuration</h3>
            <p class="table-sub">Configure leave credit earning rates for all accrual-based leave types · {{ $accrualRates->total() }} records</p>
        </div>
        <div class="table-actions">
            <select class="filter-select" id="filterAccrualStatus" onchange="filterAccrualRates()">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <select class="filter-select" id="filterAccrualFrequency" onchange="filterAccrualRates()">
                <option value="all">All Frequencies</option>
                <option value="daily">Daily</option>
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
            <button class="btn-export" style="background: #0b044d; color: #fff; border-color: #0b044d;" onclick="openAddAccrualRateModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Accrual Rate
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
                    <th style="text-align: left;">Leave Type</th>
                    <th style="text-align: center;">Accrual Frequency</th>
                    <th style="text-align: center;">Days of Service Required</th>
                    <th style="text-align: center;">Credits Earned Per Period</th>
                    <th style="text-align: center;">Effective Date</th>
                    <th style="text-align: center;">End Date</th>
                    <th style="text-align: center;">Status</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="accrualRatesTableBody">
                @forelse($accrualRates as $rate)
                <tr class="accrual-rate-row" data-status="{{ $rate->is_active ? 'active' : 'inactive' }}" data-frequency="{{ $rate->accrual_frequency }}">
                    <td data-label="Leave Type" style="text-align: left;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="emp-avatar" style="background: {{ ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b'][$loop->index % 4] }}; margin-left: 0;">
                                {{ $rate->leave_code }}
                            </div>
                            <div>
                                <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">{{ $rate->leaveType->leave_name ?? $rate->leave_code }}</p>
                                <p style="color: #6b6a8a; margin: 0; font-size: 12px;">{{ $rate->leaveType->is_accrued ? 'Accrued Leave Type' : 'Fixed Leave Type' }}</p>
                            </div>
                        </div>
                    </td>
                    <td data-label="Accrual Frequency" style="text-align: center;">
                        <span class="badge-status {{ $rate->accrual_frequency === 'daily' ? 'processed' : ($rate->accrual_frequency === 'monthly' ? 'pending' : 'on-hold') }}">
                            {{ ucfirst($rate->accrual_frequency) }}
                        </span>
                    </td>
                    <td data-label="Days of Service" style="text-align: center; font-weight: 600; color: #0b044d;">
                        {{ number_format($rate->days_of_service_required, 2) }} {{ $rate->days_of_service_required == 1 ? 'day' : 'days' }}
                    </td>
                    <td data-label="Credits Earned" style="text-align: center; font-weight: 600; color: #15803d;">
                        {{ number_format($rate->credits_earned_per_period, 4) }} credits
                    </td>
                    <td data-label="Effective Date" style="text-align: center; color: #6b6a8a;">
                        {{ $rate->effective_date->format('M d, Y') }}
                    </td>
                    <td data-label="End Date" style="text-align: center; color: #6b6a8a;">
                        {{ $rate->end_date ? $rate->end_date->format('M d, Y') : '—' }}
                    </td>
                    <td data-label="Status" style="text-align: center;">
                        <span class="badge-status {{ $rate->is_active ? 'processed' : 'on-hold' }}">
                            {{ $rate->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td data-label="Actions" style="text-align: center;">
                        <div class="row-actions">
                            <button class="btn-view" onclick="viewAccrualRate({{ $rate->id }})">View</button>
                            <button class="btn-edit" onclick="editAccrualRate({{ $rate->id }})">Edit</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 40px; color: #6b6a8a;">
                        No accrual rates found. Click "Add Accrual Rate" to create one.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="margin: 0;">Showing <strong>{{ $accrualRates->firstItem() ?? 0 }}</strong> to <strong>{{ $accrualRates->lastItem() ?? 0 }}</strong> of <strong>{{ $accrualRates->total() }}</strong> accrual rates</p>
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

<script>
function filterAccrualRates() {
    const statusFilter = document.getElementById('filterAccrualStatus').value;
    const frequencyFilter = document.getElementById('filterAccrualFrequency').value;
    const rows = document.querySelectorAll('.accrual-rate-row');

    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        const frequency = row.getAttribute('data-frequency');

        const matchesStatus = statusFilter === 'all' || status === statusFilter;
        const matchesFrequency = frequencyFilter === 'all' || frequency === frequencyFilter;

        if (matchesStatus && matchesFrequency) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function navigateToAccrualPage(url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'accrual');
    window.location.href = urlObj.toString();
}

function viewAccrualRate(id) {
    alert('View Accrual Rate #' + id + ' - To be implemented');
}

function editAccrualRate(id) {
    alert('Edit Accrual Rate #' + id + ' - To be implemented');
}
</script>
