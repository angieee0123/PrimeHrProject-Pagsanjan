@extends('layouts.permanent')

@section('title', 'Payslip · PRIME HRIS')

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

    @include('permanent.sidebar.permanentSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-payslip">

        @include('permanent.notification.permanentNotification')

        @include('permanent.topbar.payslipTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Latest Net Pay</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($stats['latest_net_pay'], 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">{{ $latestPayslip ? $latestPayslip->period_start->format('M d') . '-' . $latestPayslip->period_end->format('d, Y') : 'No data' }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Basic Pay</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($stats['basic_pay'], 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">Semi-monthly rate</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Deductions</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($stats['total_deductions'], 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">Late, Undertime, Others</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Payslips</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $stats['total_payslips'] }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">All time</p>
                </div>
            </div>

        </div>

        {{-- Payslip History Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Payslip History</p>
                    <p class="table-sub">Recent payroll records</p>
                </div>
                <div class="table-actions">
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" onclick="openModal()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Latest
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table payslip-history-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Basic Pay</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Pay Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payslips as $payslip)
                        <tr>
                            <td class="table-cell-period">{{ $payslip->period_start->format('M d') }}-{{ $payslip->period_end->format('d, Y') }}</td>
                            <td class="table-cell-basic">₱{{ number_format($payslip->basic_pay, 2) }}</td>
                            <td class="table-cell-deduct">₱{{ number_format($payslip->late_deduction + $payslip->undertime_deduction + $payslip->other_deductions, 2) }}</td>
                            <td class="net-pay">₱{{ number_format($payslip->net_pay, 2) }}</td>
                            <td class="table-cell-date">{{ $payslip->period_end->format('M d, Y') }}</td>
                            <td>
                                @if($payslip->status === 'pending')
                                    <span class="badge-status pending">Pending</span>
                                @else
                                    <span class="badge-status processed">Processed</span>
                                @endif
                            </td>
                            <td>
                                <div class="row-actions">
                                    <button class="btn-action btn-view" onclick="viewPayslipDetail({{ $payslip->id }})" title="View Details">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </button>
                                    <button class="btn-action btn-print" onclick="printPayslipDirect({{ $payslip->id }})" title="Print">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">No payslip records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>{{ $payslips->firstItem() ?? 0 }}–{{ $payslips->lastItem() ?? 0 }}</strong> of <strong>{{ $payslips->total() }}</strong> payslips</span>
                <div class="pagination">
                    @if($payslips->onFirstPage())
                        <button class="page-btn" disabled>‹</button>
                    @else
                        <a href="{{ $payslips->previousPageUrl() }}" class="page-btn">‹</a>
                    @endif
                    
                    @foreach($payslips->getUrlRange(1, $payslips->lastPage()) as $page => $url)
                        <a href="{{ $url }}" class="page-btn {{ $page == $payslips->currentPage() ? 'active' : '' }}">{{ $page }}</a>
                    @endforeach
                    
                    @if($payslips->hasMorePages())
                        <a href="{{ $payslips->nextPageUrl() }}" class="page-btn">›</a>
                    @else
                        <button class="page-btn" disabled>›</button>
                    @endif
                </div>
            </div>
        </div>

    </main>

</div>

{{-- Payslip Modal --}}
@if($latestPayslip)
<div class="modal-overlay" id="payslipModal" onclick="closeModal('payslipModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">PAYSLIP · {{ strtoupper($latestPayslip->period_start->format('M d') . '-' . $latestPayslip->period_end->format('d, Y')) }}</span>
                <h3 class="modal-title">{{ Auth::user()->employee->first_name ?? '' }} {{ Auth::user()->employee->last_name ?? '' }}</h3>
                <p class="modal-sub">{{ Auth::user()->employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ Auth::user()->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</p>
            </div>
            <button class="modal-close" onclick="closeModal('payslipModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">{{ strtoupper(substr(Auth::user()->employee->first_name ?? 'U', 0, 1) . substr(Auth::user()->employee->last_name ?? 'N', 0, 1)) }}</div>
                <div>
                    <p class="modal-emp-id">{{ Auth::user()->employee->employee_id ?? 'N/A' }}</p>
                    <span class="badge-status {{ $latestPayslip->status === 'pending' ? 'pending' : 'processed' }}">{{ ucfirst($latestPayslip->status) }}</span>
                </div>
            </div>
            <div class="modal-section-label">EARNINGS</div>
            <div class="modal-row"><span>Basic Semi-Monthly Pay</span><strong>₱{{ number_format($latestPayslip->basic_pay, 2) }}</strong></div>
            @if($latestPayslip->ot_pay > 0)
            <div class="modal-row"><span>Overtime Pay</span><strong>₱{{ number_format($latestPayslip->ot_pay, 2) }}</strong></div>
            @endif
            <div class="modal-section-label modal-section-deductions">DEDUCTIONS</div>
            @if($latestPayslip->late_deduction > 0)
            <div class="modal-row"><span>Late Deduction</span><span class="modal-deduct">₱{{ number_format($latestPayslip->late_deduction, 2) }}</span></div>
            @endif
            @if($latestPayslip->undertime_deduction > 0)
            <div class="modal-row"><span>Undertime Deduction</span><span class="modal-deduct">₱{{ number_format($latestPayslip->undertime_deduction, 2) }}</span></div>
            @endif
            @if($latestPayslip->other_deductions > 0)
            <div class="modal-row"><span>Other Deductions</span><span class="modal-deduct">₱{{ number_format($latestPayslip->other_deductions, 2) }}</span></div>
            @endif
            <div class="modal-row total"><span>Total Deductions</span><span class="modal-deduct">₱{{ number_format($latestPayslip->late_deduction + $latestPayslip->undertime_deduction + $latestPayslip->other_deductions, 2) }}</span></div>
            <div class="modal-net-row">
                <span>NET PAY</span>
                <strong>₱{{ number_format($latestPayslip->net_pay, 2) }}</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('payslipModal')">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download Payslip
            </button>
        </div>
    </div>
</div>
@endif

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

    function openModal() {
        document.getElementById('payslipModal').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    function filterPermanentPayslip(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('.payroll-table tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        }
    });
</script>

@include('permanent.chatbot.permanentChatbot')

{{-- Payslip Detail Modal --}}
<div id="payslipDetailModal" class="modal-overlay" style="display: none;">
    <div class="modal-container" style="max-width: 800px;">
        <div class="modal-header">
            <h3 class="modal-title">Payslip Details</h3>
            <button type="button" class="modal-close" onclick="closePayslipDetailModal()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Employee Info -->
            <div class="payslip-header">
                <div class="payslip-logo">
                    <img src="{{ asset('municipal-of-pagsanjan-logo.jpg') }}" alt="Pagsanjan Logo" class="logo-image">
                    <h2>MUNICIPAL GOVERNMENT OF PAGSANJAN</h2>
                    <p>Province of Laguna</p>
                    <h3 class="payslip-title">PAYSLIP</h3>
                </div>
            </div>

            <div class="payslip-info-grid">
                <div class="info-group">
                    <label>Employee Name:</label>
                    <strong id="modalEmployeeName">-</strong>
                </div>
                <div class="info-group">
                    <label>Employee ID:</label>
                    <strong id="modalEmployeeId">-</strong>
                </div>
                <div class="info-group">
                    <label>Department:</label>
                    <strong id="modalDepartment">-</strong>
                </div>
                <div class="info-group">
                    <label>Position:</label>
                    <strong id="modalPosition">-</strong>
                </div>
                <div class="info-group">
                    <label>Period:</label>
                    <strong id="modalPeriod">-</strong>
                </div>
                <div class="info-group">
                    <label>Pay Date:</label>
                    <strong id="modalPayDate">-</strong>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Earnings Section -->
            <div class="payslip-section">
                <h4 class="section-title">Earnings</h4>
                <div class="payslip-table">
                    <div class="table-row">
                        <span>Monthly Rate:</span>
                        <strong id="modalMonthlyRate">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Daily Rate:</span>
                        <strong id="modalDailyRate">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Days Worked:</span>
                        <strong id="modalDaysWorked">0</strong>
                    </div>
                    <div class="table-row highlight">
                        <span>Basic Pay:</span>
                        <strong id="modalBasicPay">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Overtime Pay:</span>
                        <strong id="modalOtPay">₱0.00</strong>
                    </div>
                    <div class="table-row total">
                        <span>Gross Pay:</span>
                        <strong id="modalGrossPay">₱0.00</strong>
                    </div>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Deductions Section -->
            <div class="payslip-section">
                <h4 class="section-title">Deductions</h4>
                <div class="payslip-table">
                    <div class="table-row">
                        <span>Late Deduction:</span>
                        <strong class="deduction-amount" id="modalLateDeduction">₱0.00</strong>
                    </div>
                    <div class="table-row">
                        <span>Undertime Deduction:</span>
                        <strong class="deduction-amount" id="modalUndertimeDeduction">₱0.00</strong>
                    </div>
                    <div id="modalDeductionBreakdown">
                        <!-- Dynamic deduction breakdown will be inserted here -->
                    </div>
                    <div class="table-row total">
                        <span>Total Deductions:</span>
                        <strong class="deduction-amount" id="modalTotalDeductions">₱0.00</strong>
                    </div>
                </div>
            </div>

            <div class="payslip-divider"></div>

            <!-- Net Pay Section -->
            <div class="payslip-section">
                <div class="net-pay-box">
                    <span>NET PAY</span>
                    <strong id="modalNetPay">₱0.00</strong>
                </div>
            </div>

            <!-- Status and Notes -->
            <div class="payslip-footer">
                <div class="status-info">
                    <label>Status:</label>
                    <span id="modalStatus" class="badge-status">-</span>
                </div>
                <div class="notes-info" id="modalNotesSection" style="display: none;">
                    <label>Notes:</label>
                    <p id="modalNotes">-</p>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-row">
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Employee Signature</p>
                        <p class="signature-date">Date: <span id="employeeSignDate">__________</span></p>
                    </div>
                    <div class="signature-box">
                        <div class="signature-line"></div>
                        <p class="signature-label">Prepared By</p>
                        <p class="signature-name" id="preparedByName">HR Department</p>
                        <p class="signature-date">Date Released: <span id="releaseDate">{{ date('M d, Y') }}</span></p>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="closePayslipDetailModal()">Close</button>
            <button type="button" class="btn-primary" onclick="printPayslip()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print Payslip
            </button>
        </div>
    </div>
</div>

<style>
.row-actions {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-action {
    padding: 6px 8px;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-view {
    color: #0b044d;
}

.btn-view:hover {
    background: #f7f6ff;
    border-color: #0b044d;
}

.btn-print {
    color: #15803d;
}

.btn-print:hover {
    background: #f0fdf4;
    border-color: #15803d;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.modal-container {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
    max-height: 90vh;
    overflow: hidden;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #e8e7f5;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
}

.modal-title {
    font-size: 18px;
    font-weight: 700;
    color: #0b044d;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: #f7f6ff;
    color: #0b044d;
    font-size: 24px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.modal-close:hover {
    background: #e8e7f5;
}

.modal-body {
    padding: 24px;
    overflow-y: auto;
    background: #fff;
}

.modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #e8e7f5;
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    background: #fff;
}

.btn-secondary {
    padding: 10px 20px;
    background: #f7f6ff;
    color: #0b044d;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
}

.btn-secondary:hover {
    background: #e8e7f5;
}

.btn-primary {
    padding: 10px 20px;
    background: #0b044d;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-primary:hover {
    background: #1a0f6e;
}

.payslip-header {
    text-align: center;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #0b044d;
}

.payslip-logo {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.logo-image {
    width: 80px;
    height: 80px;
    object-fit: contain;
    margin-bottom: 8px;
}

.payslip-logo h2 {
    font-size: 18px;
    font-weight: 700;
    color: #0b044d;
    margin: 0;
    text-transform: uppercase;
}

.payslip-logo p {
    font-size: 13px;
    color: #6b6a8a;
    margin: 0;
}

.payslip-title {
    font-size: 16px;
    font-weight: 700;
    color: #0b044d;
    margin: 8px 0 0 0;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.payslip-info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 20px;
    border: 1px solid #e8e7f5;
    padding: 16px;
    border-radius: 6px;
    background: #fafafe;
}

.info-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-group label {
    font-size: 11px;
    color: #6b6a8a;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-group strong {
    font-size: 14px;
    color: #0b044d;
    font-weight: 600;
}

.payslip-divider {
    height: 1px;
    background: #e8e7f5;
    margin: 20px 0;
}

.payslip-section {
    margin-bottom: 20px;
}

.section-title {
    font-size: 14px;
    font-weight: 700;
    color: #0b044d;
    margin: 0 0 12px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.payslip-table {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.table-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 12px;
    background: #fafafe;
    border-radius: 6px;
}

.table-row span {
    font-size: 13px;
    color: #6b6a8a;
}

.table-row strong {
    font-size: 13px;
    color: #0b044d;
    font-weight: 600;
}

.table-row.highlight {
    background: #f7f6ff;
    border: 1px solid #e8e7f5;
}

.table-row.total {
    background: #0b044d;
    color: #fff;
    margin-top: 8px;
}

.table-row.total span,
.table-row.total strong {
    color: #fff;
    font-size: 14px;
}

.deduction-amount {
    color: #dc2626 !important;
}

.net-pay-box {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%);
    border-radius: 8px;
    color: #fff;
}

.net-pay-box span {
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 1px;
}

.net-pay-box strong {
    font-size: 24px;
    font-weight: 700;
}

.payslip-footer {
    margin-top: 24px;
    padding-top: 16px;
    border-top: 1px solid #e8e7f5;
}

.status-info {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
}

.status-info label {
    font-size: 12px;
    color: #6b6a8a;
    font-weight: 600;
}

.notes-info {
    margin-top: 12px;
}

.notes-info label {
    font-size: 12px;
    color: #6b6a8a;
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
}

.notes-info p {
    font-size: 13px;
    color: #0b044d;
    background: #f7f6ff;
    padding: 12px;
    border-radius: 6px;
    margin: 0;
}

.notes-info p {
    font-size: 13px;
    color: #0b044d;
    background: #f7f6ff;
    padding: 12px;
    border-radius: 6px;
    margin: 0;
}

.signature-section {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e8e7f5;
}

.signature-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.signature-box {
    text-align: center;
}

.signature-line {
    width: 100%;
    height: 60px;
    border-bottom: 2px solid #0b044d;
    margin-bottom: 8px;
}

.signature-label {
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.signature-name {
    font-size: 13px;
    font-weight: 600;
    color: #0b044d;
    margin: 0 0 4px 0;
}

.signature-date {
    font-size: 11px;
    color: #6b6a8a;
    margin: 0;
}

@media print {
    body * {
        visibility: hidden;
    }
    
    #payslipDetailModal,
    #payslipDetailModal * {
        visibility: visible;
    }
    
    #payslipDetailModal {
        position: fixed;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: white;
        z-index: 9999;
    }
    
    .modal-overlay {
        position: static;
        background: none;
        padding: 0;
    }
    
    .modal-container {
        box-shadow: none;
        max-width: 100%;
        max-height: none;
        border-radius: 0;
    }
    
    .modal-header,
    .modal-footer {
        display: none !important;
    }
    
    .modal-body {
        padding: 15mm;
        overflow: visible;
        font-size: 11px;
    }
    
    .payslip-header {
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid #000;
        page-break-after: avoid;
    }
    
    .payslip-logo {
        flex-direction: row;
        justify-content: center;
        align-items: center;
        gap: 15px;
    }
    
    .logo-image {
        width: 100px;
        height: 100px;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .payslip-logo h2 {
        font-size: 20px;
        margin: 0;
    }
    
    .payslip-logo p {
        font-size: 13px;
        margin: 0;
    }
    
    .payslip-title {
        font-size: 18px;
        margin: 5px 0 0 0;
        letter-spacing: 3px;
    }
    
    .payslip-info-grid {
        display: table;
        width: 100%;
        margin-bottom: 10px;
        border: 1px solid #000;
    }
    
    .info-group {
        display: table-row;
    }
    
    .info-group label {
        display: table-cell;
        padding: 3px 8px;
        font-size: 9px;
        font-weight: 600;
        border-bottom: 1px solid #ddd;
        width: 30%;
        background: #f5f5f5;
    }
    
    .info-group strong {
        display: table-cell;
        padding: 3px 8px;
        font-size: 10px;
        border-bottom: 1px solid #ddd;
        border-left: 1px solid #ddd;
    }
    
    .payslip-divider {
        height: 0;
        margin: 8px 0;
        border: none;
    }
    
    .payslip-section {
        page-break-inside: avoid;
        margin-bottom: 8px;
    }
    
    .section-title {
        font-size: 11px;
        margin: 0 0 5px 0;
        padding: 3px 8px;
        background: #000;
        color: white;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    .payslip-table {
        display: table;
        width: 100%;
        border: 1px solid #000;
        border-collapse: collapse;
    }
    
    .table-row {
        display: table-row;
        padding: 0;
        margin: 0;
        background: white;
        border-radius: 0;
    }
    
    .table-row span {
        display: table-cell;
        padding: 3px 8px;
        font-size: 10px;
        border-bottom: 1px solid #ddd;
        width: 70%;
    }
    
    .table-row strong {
        display: table-cell;
        padding: 3px 8px;
        font-size: 10px;
        border-bottom: 1px solid #ddd;
        border-left: 1px solid #ddd;
        text-align: right;
        width: 30%;
    }
    
    .table-row.highlight {
        background: #f9f9f9;
        border: none;
    }
    
    .table-row.total {
        background: #000 !important;
        border-bottom: 2px solid #000;
    }
    
    .table-row.total span,
    .table-row.total strong {
        color: white !important;
        font-size: 11px;
        font-weight: 700;
        border-bottom: none;
        padding: 5px 8px;
    }
    
    .net-pay-box {
        display: table;
        width: 100%;
        padding: 8px 12px;
        background: #000 !important;
        border: 2px solid #000;
        border-radius: 0;
        margin: 8px 0;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .net-pay-box span {
        display: table-cell;
        font-size: 12px;
        font-weight: 700;
        color: white;
        width: 70%;
    }
    
    .net-pay-box strong {
        display: table-cell;
        font-size: 16px;
        font-weight: 700;
        color: white;
        text-align: right;
        width: 30%;
    }
    
    .payslip-footer {
        margin-top: 10px;
        padding-top: 8px;
        border-top: 1px solid #000;
        font-size: 9px;
    }
    
    .status-info {
        margin-bottom: 5px;
    }
    
    .status-info label {
        font-size: 9px;
    }
    
    .badge-status {
        padding: 2px 8px;
        font-size: 9px;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
    
    .notes-info {
        margin-top: 5px;
    }
    
    .notes-info label {
        font-size: 9px;
    }
    
    .notes-info p {
        font-size: 9px;
        padding: 5px;
        background: #f5f5f5;
    }
    
    .signature-section {
        margin-top: 20px;
        padding-top: 10px;
        border-top: 1px solid #000;
        page-break-inside: avoid;
    }
    
    .signature-row {
        display: table;
        width: 100%;
    }
    
    .signature-box {
        display: table-cell;
        width: 50%;
        text-align: center;
        padding: 0 10px;
    }
    
    .signature-line {
        width: 100%;
        height: 40px;
        border-bottom: 1px solid #000;
        margin-bottom: 5px;
    }
    
    .signature-label {
        font-size: 9px;
        font-weight: 600;
        color: #000;
        margin: 0 0 3px 0;
        text-transform: uppercase;
    }
    
    .signature-name {
        font-size: 10px;
        font-weight: 600;
        color: #000;
        margin: 0 0 3px 0;
    }
    
    .signature-date {
        font-size: 8px;
        color: #000;
        margin: 0;
    }
    
    @page {
        margin: 15mm;
        size: A4 portrait;
    }
}
</style>

<script>
let currentPayslipData = null;

function viewPayslipDetail(id) {
    fetch(`/permanent/payslip/${id}/details`, {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            currentPayslipData = data.payslip;
            populatePayslipDetailModal(data.payslip);
            document.getElementById('payslipDetailModal').style.display = 'flex';
        } else {
            alert('Error: ' + (data.message || 'Failed to load payslip details'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load payslip details');
    });
}

function populatePayslipDetailModal(payslip) {
    // Employee Info
    document.getElementById('modalEmployeeName').textContent = payslip.employee_name;
    document.getElementById('modalEmployeeId').textContent = payslip.employee_id;
    document.getElementById('modalDepartment').textContent = payslip.department;
    document.getElementById('modalPosition').textContent = payslip.position;
    document.getElementById('modalPeriod').textContent = payslip.period;
    document.getElementById('modalPayDate').textContent = payslip.pay_date || 'Not set';
    
    // Earnings
    document.getElementById('modalMonthlyRate').textContent = '₱' + parseFloat(payslip.monthly_rate || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDailyRate').textContent = '₱' + parseFloat(payslip.daily_rate || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalDaysWorked').textContent = payslip.total_days_present || 0;
    document.getElementById('modalBasicPay').textContent = '₱' + parseFloat(payslip.basic_pay).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalOtPay').textContent = '₱' + parseFloat(payslip.ot_pay || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalGrossPay').textContent = '₱' + parseFloat(payslip.gross_pay || (payslip.basic_pay + (payslip.ot_pay || 0))).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // Deductions
    document.getElementById('modalLateDeduction').textContent = '₱' + parseFloat(payslip.late_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    document.getElementById('modalUndertimeDeduction').textContent = '₱' + parseFloat(payslip.undertime_deduction || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // Deduction Breakdown
    const breakdownContainer = document.getElementById('modalDeductionBreakdown');
    breakdownContainer.innerHTML = '';
    
    // Parse deduction_breakdown if it exists
    let deductionBreakdown = payslip.deduction_breakdown;
    if (typeof deductionBreakdown === 'string') {
        try {
            deductionBreakdown = JSON.parse(deductionBreakdown);
        } catch (e) {
            console.error('Error parsing deduction_breakdown:', e);
            deductionBreakdown = {};
        }
    }
    
    // Check if deductionBreakdown is valid and has entries
    if (deductionBreakdown && typeof deductionBreakdown === 'object' && Object.keys(deductionBreakdown).length > 0) {
        Object.entries(deductionBreakdown).forEach(([code, deduction]) => {
            if (deduction && deduction.name && deduction.amount !== undefined && !isNaN(deduction.amount)) {
                const row = document.createElement('div');
                row.className = 'table-row';
                row.innerHTML = `
                    <span>${deduction.name}:</span>
                    <strong class="deduction-amount">₱${parseFloat(deduction.amount).toLocaleString('en-US', {minimumFractionDigits: 2})}</strong>
                `;
                breakdownContainer.appendChild(row);
            }
        });
    }
    
    document.getElementById('modalTotalDeductions').textContent = '₱' + parseFloat(payslip.total_deductions || 0).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // Net Pay
    document.getElementById('modalNetPay').textContent = '₱' + parseFloat(payslip.net_pay).toLocaleString('en-US', {minimumFractionDigits: 2});
    
    // Status
    const statusBadge = document.getElementById('modalStatus');
    statusBadge.textContent = payslip.status.charAt(0).toUpperCase() + payslip.status.slice(1);
    statusBadge.className = 'badge-status ' + (payslip.status === 'pending' ? 'pending' : 'processed');
    
    // Notes
    if (payslip.notes) {
        document.getElementById('modalNotesSection').style.display = 'block';
        document.getElementById('modalNotes').textContent = payslip.notes;
    } else {
        document.getElementById('modalNotesSection').style.display = 'none';
    }
}

function closePayslipDetailModal() {
    document.getElementById('payslipDetailModal').style.display = 'none';
    currentPayslipData = null;
}

function printPayslip() {
    window.print();
}

function printPayslipDirect(id) {
    viewPayslipDetail(id);
    setTimeout(() => {
        window.print();
    }, 500);
}
</script>

@endsection
