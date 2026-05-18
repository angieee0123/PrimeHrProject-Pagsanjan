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
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">No payslip records found</td>
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

@endsection
