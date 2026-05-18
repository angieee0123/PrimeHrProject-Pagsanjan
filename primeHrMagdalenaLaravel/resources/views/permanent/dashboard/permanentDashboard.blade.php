@extends('layouts.permanent')

@section('title', 'Permanent Dashboard · PRIME HRIS')

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
    <main class="main-content permanent-dashboard">

        @include('permanent.notification.permanentNotification')

        @include('permanent.topbar.permanentTopbar')

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Basic Pay</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($basicPay, 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">{{ $startDate->format('M d') }}-{{ $endDate->format('d, Y') }}</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Net Pay</p>
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value stat-value-compact">₱{{ number_format($netPay, 2) }}</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">After deductions</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Leave Credits</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                </div>
                <p class="stat-value">{{ number_format($leaveBalances->sum('available_credits'), 1) }} days</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">{{ $leaveBalances->count() }} leave type(s)</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">{{ $attendanceRate }}%</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">{{ $presentDays }} days present</p>
                </div>
            </div>

        </div>

        {{-- Payslip History Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">My Payslips</p>
                    <p class="table-sub">Recent payroll history</p>
                </div>
                <div class="table-actions">
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" onclick="showPayslip()">
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
                        @forelse($payslips as $p)
                        <tr>
                            <td class="table-cell-period">{{ $p->period_label }}</td>
                            <td class="table-cell-basic">₱{{ number_format($p->basic_pay, 2) }}</td>
                            <td class="table-cell-deduct">₱{{ number_format($p->deductions, 2) }}</td>
                            <td class="net-pay">₱{{ number_format($p->net_pay, 2) }}</td>
                            <td class="table-cell-date">{{ \Carbon\Carbon::parse($p->pay_date)->format('M d, Y') }}</td>
                            <td>
                                <span class="badge-status processed">Processed</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 40px; color: #6b6a8a;">No payslip records found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>1–{{ $payslips->count() }}</strong> of <strong>{{ $payslips->count() }}</strong> payslips</span>
            </div>
        </div>

        {{-- Bottom Row: Notifications + Quick Actions --}}
        <div class="bottom-row">

            {{-- Notifications --}}
            <div class="table-section mb-0">
                <div class="table-header">
                    <div>
                        <p class="table-title">Notifications</p>
                        <p class="table-sub">2 unread messages</p>
                    </div>
                    <button class="btn-export">Mark all read</button>
                </div>
                <div class="table-wrapper">
                    <table class="payroll-table">
                        <thead>
                            <tr><th>Message</th><th>Date</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            @php
                            $notifs = [
                                ['title'=>'Leave Request Reminder','desc'=>'Your vacation leave request is pending approval.','time'=>'2 hours ago','status'=>'unread'],
                                ['title'=>'Payroll Updated','desc'=>'June 16-30, 2025 payroll is now available.','time'=>'Yesterday','status'=>'unread'],
                                ['title'=>'Training Schedule','desc'=>'CSC training scheduled for June 18, 2025.','time'=>'2 days ago','status'=>'read'],
                            ];
                            @endphp
                            @foreach($notifs as $n)
                            <tr>
                                <td>
                                    <div class="dashboard-notif-item">
                                        <div class="dashboard-notif-dot {{ $n['status']==='unread' ? 'dashboard-notif-dot-unread' : 'dashboard-notif-dot-read' }}"></div>
                                        <div>
                                            <p class="dashboard-notif-title">{{ $n['title'] }}</p>
                                            <p class="dashboard-notif-desc">{{ $n['desc'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="dashboard-notif-time">{{ $n['time'] }}</td>
                                <td>
                                    @if($n['status']==='unread')
                                        <span class="badge-status pending">Unread</span>
                                    @else
                                        <span class="badge-status processed">Read</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="side-col">

                <div class="table-section mb-0">
                    <div class="table-header table-header-compact">
                        <p class="table-title table-title-sm">Quick Actions</p>
                    </div>
                    <div class="quick-actions-body">
                        <button class="quick-action-btn quick-action-btn-block" onclick="showPayslip()">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            View Payslip
                        </button>
                        <button class="quick-action-btn quick-action-btn-block">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            File Leave
                        </button>
                        <button class="quick-action-btn quick-action-btn-block">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                            View Attendance
                        </button>
                        <button class="quick-action-btn quick-action-btn-block quick-action-btn-last">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            My Profile
                        </button>
                    </div>
                </div>

                <div class="stat-card no-margin">
                    <p class="stat-label leave-balance-label">Leave Balance</p>
                    @forelse($leaveBalances->take(3) as $balance)
                    @php
                        $percent = $balance->total_credits > 0 ? ($balance->available_credits / $balance->total_credits) * 100 : 0;
                        $colors = ['#0b044d', '#8e1e18', '#a16207'];
                        $color = $colors[$loop->index % 3];
                    @endphp
                    <div class="leave-row">
                        <div class="leave-row-top">
                            <span class="leave-row-name">{{ $balance->leaveType->leave_name ?? 'Unknown' }}</span>
                            <span class="leave-row-balance">{{ number_format($balance->available_credits, 1) }} days</span>
                        </div>
                        <div class="leave-progress-track">
                            <div class="leave-progress {{ $color == '#0b044d' ? 'leave-color-primary' : ($color == '#8e1e18' ? 'leave-color-danger' : 'leave-color-warning') }}" style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p style="text-align: center; color: #6b6a8a; padding: 20px 0;">No leave balances available</p>
                    @endforelse
                </div>

            </div>
        </div>

    </main>

</div>

{{-- Payslip Modal --}}
<div class="modal-overlay" id="payslipModal" onclick="closeModal('payslipModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">PAYSLIP · JUN 16-30, 2025</span>
                <h3 class="modal-title">Ana R. Reyes</h3>
                <p class="modal-sub">Nurse II · Municipal Health Office</p>
            </div>
            <button class="modal-close" onclick="closeModal('payslipModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar modal-emp-avatar">AR</div>
                <div>
                    <p class="modal-emp-id">PGS-0115</p>
                    <span class="badge-status pending">Pending</span>
                </div>
            </div>
            <div class="modal-section-label">EARNINGS</div>
            <div class="modal-row"><span>Basic Semi-Monthly Pay</span><strong>₱16,921.50</strong></div>
            <div class="modal-section-label modal-section-deductions">DEDUCTIONS</div>
            <div class="modal-row"><span>GSIS Premium</span><span class="modal-deduct">₱1,523.00</span></div>
            <div class="modal-row"><span>PhilHealth</span><span class="modal-deduct">₱425.00</span></div>
            <div class="modal-row"><span>Pag-IBIG</span><span class="modal-deduct">₱50.00</span></div>
            <div class="modal-row"><span>Withholding Tax</span><span class="modal-deduct">₱1,386.00</span></div>
            <div class="modal-row total"><span>Total Deductions</span><span class="modal-deduct">₱3,384.00</span></div>
            <div class="modal-net-row">
                <span>NET PAY</span>
                <strong>₱13,537.50</strong>
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

@include('permanent.chatbot.permanentChatbot')


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

    function showPayslip() {
        document.getElementById('payslipModal').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        }
    });
</script>
@endsection
