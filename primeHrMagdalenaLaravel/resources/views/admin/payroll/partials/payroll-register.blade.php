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

// Get deduction type names
$deductionTypeNames = [];
if (isset($deductionTypes) && $deductionTypes->isNotEmpty()) {
    $deductionTypeModels = \App\Models\DeductionType::whereIn('code', $deductionTypes)->get();
    foreach ($deductionTypeModels as $dt) {
        $deductionTypeNames[$dt->code] = $dt->name;
    }
}
@endphp

<div class="table-header">
    <div>
        <h3 class="table-title">Payroll Register — {{ $periodDisplay }}</h3>
        <p class="table-sub">Municipal Government of Pagsanjan · Pay Date: {{ date('M d, Y', strtotime($endDateDisplay)) }} · {{ $payrollRecords->count() }} records</p>
    </div>
    <div class="table-actions">
        <form method="GET" action="{{ route('admin.payroll') }}" id="filterForm" style="display: contents;">
            <input type="hidden" name="tab" value="register">
            <input type="date" class="filter-select" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
            <span style="font-size: 12px; color: #9999bb;">to</span>
            <input type="date" class="filter-select" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
            <select class="filter-select" name="employee_name">
                <option value="">All Employees</option>
                @foreach($employees as $emp)
                    <option value="{{ $emp }}" {{ request('employee_name') == $emp ? 'selected' : '' }}>{{ $emp }}</option>
                @endforeach
            </select>
            <select class="filter-select" name="department">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                @endforeach
            </select>
            <select class="filter-select" name="status">
                <option value="">All Status</option>
                <option value="Processed" {{ request('status') == 'Processed' ? 'selected' : '' }}>Processed</option>
                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="On Hold" {{ request('status') == 'On Hold' ? 'selected' : '' }}>On Hold</option>
            </select>
            <select class="filter-select" name="view_mode" style="background: #f7f6ff; border-color: #0b044d; color: #0b044d; font-weight: 600;">
                <option value="daily" {{ request('view_mode', 'daily') == 'daily' ? 'selected' : '' }}>Daily View</option>
                <option value="employee" {{ request('view_mode') == 'employee' ? 'selected' : '' }}>By Employee</option>
                <option value="monthly" {{ request('view_mode') == 'monthly' ? 'selected' : '' }}>Monthly Summary</option>
            </select>
            <button type="submit" class="btn-filter-main">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filter
            </button>
        </form>
        <button class="btn-export">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>

<div class="payroll-summary-bar" style="margin-top: 0; margin-bottom: 16px;">
    <div class="psummary-item">
        <span>Gross Total</span>
        <strong>{{ peso($grossPayroll) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Total Deductions</span>
        <strong class="deduction">{{ peso($totalDeductions) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Total Net Pay</span>
        <strong class="net-pay">{{ peso($totalNet) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Pay Date</span>
        <strong>{{ date('M d, Y', strtotime($endDateDisplay)) }}</strong>
    </div>
    <div class="psummary-divider"></div>
    <div class="psummary-item">
        <span>Records</span>
        <strong>{{ $payrollRecords->count() }}</strong>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table payroll-register-table">
        <thead>
            <tr>
                <th style="width: 20%;">Employee</th>
                <th style="width: 13%;">Department</th>
                @if($viewMode === 'daily')
                    <th style="width: 10%; text-align: center;">Date</th>
                    <th style="width: 8%; text-align: right;">Rate</th>
                @else
                    <th style="width: 8%; text-align: center;">Days</th>
                    <th style="width: 8%; text-align: right;">Rate</th>
                @endif
                <th style="width: 9%; text-align: right;">Basic</th>
                <th style="width: 7%; text-align: right;">OT</th>
                <th style="width: 7%; text-align: right;">Late</th>
                <th style="width: 7%; text-align: right;">UT</th>
                @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                    @foreach($deductionTypes as $code)
                        <th class="deduction-col-hide" style="width: 7%; text-align: right;">{{ $deductionTypeNames[$code] ?? $code }}</th>
                    @endforeach
                @endif
                <th class="deduction-col-show" style="display: none; width: 5%; text-align: center;">Ded.</th>
                <th style="width: 9%; text-align: right;">Total Ded.</th>
                <th style="width: 10%; text-align: right;">Net Pay</th>
            </tr>
        </thead>
        <tbody id="payrollRegisterBody">
            @foreach($payrollRecords as $index => $record)
            @php
                $basicPay = $record['basic'];
                $otPay = $record['ot_pay'];
                $lateDeduction = $record['late_deduction'];
                $undertimeDeduction = $record['undertime_deduction'];
                $grossPay = $basicPay + $otPay;
                
                // Calculate total deductions from all sources
                $totalDeductionsRow = $lateDeduction + $undertimeDeduction;
                if (isset($record['deductions'])) {
                    foreach ($record['deductions'] as $deductionAmount) {
                        $totalDeductionsRow += $deductionAmount;
                    }
                }
                
                $netPay = $grossPay - $totalDeductionsRow;
            @endphp
            <tr data-name="{{ $record['name'] }}" data-id="{{ $record['id'] }}" data-dept="{{ $record['dept'] }}" data-status="{{ $record['status'] }}">
                <td>
                    <div class="emp-cell">
                        <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }};">
                            {{ getInitials($record['name']) }}
                        </div>
                        <div>
                            <p class="emp-name">{{ $record['name'] }}</p>
                            <p class="emp-id">{{ $record['id'] }}</p>
                        </div>
                    </div>
                </td>
                <td><span class="dept-tag">{{ $record['dept'] }}</span></td>
                @if($viewMode === 'daily')
                    <td class="work-date" style="text-align: center;">{{ date('M d, Y', strtotime($record['work_date'])) }}</td>
                    <td class="daily-rate" style="text-align: right;">{{ peso($record['daily_rate']) }}</td>
                @else
                    <td style="text-align: center;"><span class="days-count">{{ $record['days_count'] }}</span></td>
                    <td class="daily-rate" style="text-align: right;">{{ peso($record['daily_rate']) }}</td>
                @endif
                <td class="pay-cell" style="text-align: right;">{{ peso($basicPay) }}</td>
                <td class="ot-pay" style="text-align: right;">{{ peso($otPay) }}</td>
                <td class="deduction" style="text-align: right;">{{ peso($lateDeduction) }}</td>
                <td class="deduction" style="text-align: right;">{{ peso($undertimeDeduction) }}</td>
                @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                    @foreach($deductionTypes as $code)
                        <td class="deduction deduction-col-hide" style="text-align: right;">{{ peso($record['deductions'][$code] ?? 0) }}</td>
                    @endforeach
                @endif
                <td class="deduction-col-show" style="display: none; text-align: center;">
                    <button class="btn-deductions-modal" onclick="showDeductionsModal({{ $index }})">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                    </button>
                    <div class="deductions-data" data-index="{{ $index }}" style="display: none;">
                        @if(isset($deductionTypes) && $deductionTypes->isNotEmpty())
                            @foreach($deductionTypes as $code)
                                <span data-type="{{ $deductionTypeNames[$code] ?? $code }}" data-amount="{{ peso($record['deductions'][$code] ?? 0) }}"></span>
                            @endforeach
                        @endif
                    </div>
                </td>
                <td style="text-align: right;">
                    <span class="badge-deduction">{{ peso($totalDeductionsRow) }}</span>
                </td>
                <td style="text-align: right;">
                    <span class="badge-netpay">{{ peso($netPay) }}</span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="table-footer">
    <div style="display:flex;align-items:center;gap:12px;">
        <p id="payrollRegisterFooter">Showing <strong id="payrollRowStart">1</strong>-<strong id="payrollRowEnd">{{ min(10, $payrollRecords->count()) }}</strong> of <strong id="payrollRowTotal">{{ $payrollRecords->count() }}</strong> records</p>
        <select id="payrollRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changePayrollRowsPerPage()">
            <option value="10">10 rows</option>
            <option value="25">25 rows</option>
            <option value="50">50 rows</option>
            <option value="100">100 rows</option>
        </select>
    </div>
    <div class="pagination" id="payrollPaginationControls"></div>
</div>

<!-- Deductions Modal -->
<div id="deductionsModal" class="adm-overlay" onclick="closeDeductionsModal()">
    <div class="adm-box" style="max-width:480px;" onclick="event.stopPropagation()">
        <div class="adm-header" style="background: linear-gradient(135deg, #8e1e18, #dc2626); border-bottom: none;">
            <div class="adm-header-left">
                <div class="vdm-avatar" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                </div>
                <div>
                    <span class="adm-eyebrow">PAYROLL DEDUCTIONS</span>
                    <h3 class="adm-title">Deduction Breakdown</h3>
                </div>
            </div>
            <button class="adm-close" style="color: rgba(255,255,255,0.8);" onmouseover="this.style.background='rgba(255,255,255,0.2)'; this.style.color='#fff'" onmouseout="this.style.background='transparent'; this.style.color='rgba(255,255,255,0.8)'" onclick="closeDeductionsModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="vdm-body" style="padding: 24px;" id="deductionsModalBody"></div>
        <div class="adm-footer" style="background: #fafafe;">
            <button class="adm-btn-primary" onclick="closeDeductionsModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                Got it
            </button>
        </div>
    </div>
</div>

<style>
/* Responsive deduction columns */
@media screen and (max-width: 1920px) {
    .deduction-col-hide { display: none !important; }
    .deduction-col-show { display: table-cell !important; }
}

.btn-deductions-modal {
    background: #f0eeff;
    border: 1px solid #d0c9ff;
    border-radius: 6px;
    padding: 6px 10px;
    cursor: pointer;
    color: #0b044d;
    transition: all 0.2s;
}

.btn-deductions-modal:hover {
    background: #e0d9ff;
    border-color: #0b044d;
}

.adm-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(11, 4, 77, 0.4);
    backdrop-filter: blur(4px);
}

.adm-overlay.active {
    display: flex;
    align-items: center;
    justify-content: center;
}

.adm-box {
    background: white;
    border-radius: 16px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(11, 4, 77, 0.3);
    animation: modalSlideIn 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.adm-header {
    padding: 24px;
    border-bottom: 1px solid #e8e6f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.adm-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.vdm-avatar {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.adm-eyebrow {
    font-size: 10px;
    font-weight: 700;
    color: #ffffff !important;
    letter-spacing: 0.5px;
    display: block;
    margin-bottom: 2px;
}

.adm-title {
    margin: 0;
    color: #ffffff !important;
    font-size: 19px;
    font-weight: 600;
    line-height: 1.2;
}

.adm-close {
    background: none;
    border: none;
    color: #9999bb;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.adm-close:hover {
    background: #f0eeff;
    color: #0b044d;
}

.vdm-body {
    padding: 20px 24px;
}

.vdm-section-label {
    font-size: 11px;
    font-weight: 700;
    color: #9999bb;
    letter-spacing: 0.8px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.vdm-section-label::before {
    content: '';
    width: 3px;
    height: 14px;
    background: linear-gradient(135deg, #8e1e18, #dc2626);
    border-radius: 2px;
}

.vdm-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 18px;
    background: #ffffff;
    border: 1px solid #f0effe;
    border-radius: 10px;
    margin-bottom: 10px;
    transition: all 0.2s;
}

.vdm-row:hover {
    background: #fef8f8;
    border-color: #fdd;
    transform: translateX(2px);
}

.vdm-row:last-child {
    margin-bottom: 0;
}

.vdm-row-label {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #5a5a7a !important;
    font-size: 13.5px;
    font-weight: 500;
}

.vdm-row-label svg {
    flex-shrink: 0;
}

.vdm-row-amount {
    color: #8e1e18 !important;
    font-size: 15px;
    font-weight: 700;
    font-family: 'Poppins', monospace;
}

.adm-footer {
    padding: 20px 24px;
    border-top: 1px solid #e8e6f5;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.adm-btn-ghost {
    padding: 8px 20px;
    background: transparent;
    color: #6b6a8a;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}

.adm-btn-ghost:hover {
    background: #f7f6ff;
    color: #0b044d;
    border-color: #d0c9ff;
}

.adm-btn-primary {
    padding: 10px 24px;
    background: linear-gradient(135deg, #0b044d, #2d1a8e);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(11, 4, 77, 0.2);
}

.adm-btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(11, 4, 77, 0.3);
}

.adm-btn-primary:active {
    transform: translateY(0);
}

.badge-deduction {
    display: inline-block;
    padding: 5px 12px;
    background: #fee;
    color: #8e1e18;
    border: 1px solid #fcc;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
}

.badge-netpay {
    display: inline-block;
    padding: 5px 12px;
    background: #e7f5e9;
    color: #15803d;
    border: 1px solid #b7e4c7;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
}
</style>

<script>
function showDeductionsModal(index) {
    const dataContainer = document.querySelector(`.deductions-data[data-index="${index}"]`);
    const modal = document.getElementById('deductionsModal');
    const modalBody = document.getElementById('deductionsModalBody');
    
    if (!dataContainer) return;
    
    const deductions = dataContainer.querySelectorAll('span[data-type]');
    let html = '<p class="vdm-section-label">BREAKDOWN</p>';
    
    let totalAmount = 0;
    deductions.forEach(deduction => {
        const type = deduction.getAttribute('data-type');
        const amountStr = deduction.getAttribute('data-amount');
        const amount = parseFloat(amountStr.replace(/[₱,]/g, '')) || 0;
        totalAmount += amount;
        
        html += `
            <div class="vdm-row">
                <span class="vdm-row-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="8" y1="12" x2="16" y2="12"/>
                    </svg>
                    ${type}
                </span>
                <strong class="vdm-row-amount">${amountStr}</strong>
            </div>
        `;
    });
    
    if (deductions.length === 0) {
        html = '<div style="text-align: center; padding: 40px 20px;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#d0c9ff" stroke-width="1.5" style="margin-bottom: 12px;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg><p style="color: #9999bb; font-size: 14px; margin: 0;">No deductions found</p></div>';
    } else {
        // Add total row
        html += `
            <div style="margin-top: 16px; padding-top: 16px; border-top: 2px solid #f0effe;">
                <div class="vdm-row" style="background: linear-gradient(135deg, #fef8f8, #fff); border: 2px solid #fdd;">
                    <span class="vdm-row-label" style="font-weight: 700; color: #0b044d !important;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <line x1="3" y1="9" x2="21" y2="9"/>
                        </svg>
                        Total Deductions
                    </span>
                    <strong class="vdm-row-amount" style="font-size: 16px;">₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</strong>
                </div>
            </div>
        `;
    }
    
    modalBody.innerHTML = html;
    modal.classList.add('active');
}

function closeDeductionsModal() {
    const modal = document.getElementById('deductionsModal');
    modal.classList.remove('active');
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeductionsModal();
    }
});
</script>
