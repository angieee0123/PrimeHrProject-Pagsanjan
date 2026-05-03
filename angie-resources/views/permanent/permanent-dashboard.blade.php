@extends('layouts.app')

@section('title', 'Permanent Dashboard · PRIME HRIS')

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
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                </div>
                <div>
                    <h2>Welcome back, Ana!</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    June 2025 Payroll Active
                </span>
                <span class="banner-badge outline">Next Pay: Jun 30</span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Basic Pay</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value" style="font-size:20px">₱16,921.50</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">Jun 16-30, 2025</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Net Pay</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value" style="font-size:20px">₱13,537.50</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">After deductions</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Leave Credits</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                </div>
                </div>
                <p class="stat-value">22.5 days</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">12.5 vacation · 10 sick</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value">95%</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">20 days present</p>
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
                <table class="payroll-table">
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
                        @php
                        $payslips = [
                            ['period'=>'Jun 16-30, 2025','basic'=>'₱16,921.50','deduct'=>'₱3,384.00','net'=>'₱13,537.50','date'=>'Jun 30, 2025','status'=>'pending'],
                            ['period'=>'Jun 1-15, 2025','basic'=>'₱16,921.50','deduct'=>'₱3,384.00','net'=>'₱13,537.50','date'=>'Jun 15, 2025','status'=>'processed'],
                            ['period'=>'May 16-31, 2025','basic'=>'₱16,921.50','deduct'=>'₱3,384.00','net'=>'₱13,537.50','date'=>'May 31, 2025','status'=>'processed'],
                            ['period'=>'May 1-15, 2025','basic'=>'₱16,921.50','deduct'=>'₱3,384.00','net'=>'₱13,537.50','date'=>'May 15, 2025','status'=>'processed'],
                            ['period'=>'Apr 16-30, 2025','basic'=>'₱16,921.50','deduct'=>'₱3,384.00','net'=>'₱13,537.50','date'=>'Apr 30, 2025','status'=>'processed'],
                        ];
                        @endphp
                        @foreach($payslips as $p)
                        <tr>
                            <td style="font-weight:600;color:#0b044d;font-size:13px">{{ $p['period'] }}</td>
                            <td style="font-size:13px;color:#0b044d">{{ $p['basic'] }}</td>
                            <td style="font-size:13px;color:#8e1e18">{{ $p['deduct'] }}</td>
                            <td class="net-pay">{{ $p['net'] }}</td>
                            <td style="font-size:12.5px;color:#6b6a8a">{{ $p['date'] }}</td>
                            <td>
                                @if($p['status']==='pending')
                                    <span class="badge-status pending">Pending</span>
                                @else
                                    <span class="badge-status processed">Processed</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>1–5</strong> of <strong>24</strong> payslips</span>
                <div class="pagination">
                    <button class="page-btn">‹</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">›</button>
                </div>
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
                                    <div style="display:flex;align-items:flex-start;gap:10px">
                                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $n['status']==='unread' ? '#d9bb00' : '#e5e4f0' }};margin-top:5px;flex-shrink:0"></div>
                                        <div>
                                            <p style="font-size:13px;font-weight:600;color:#0b044d;margin:0 0 2px">{{ $n['title'] }}</p>
                                            <p style="font-size:12px;color:#6b6a8a;margin:0">{{ $n['desc'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td style="font-size:11px;color:#aaa8cc">{{ $n['time'] }}</td>
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
                    <div class="table-header" style="padding:16px 20px">
                        <p class="table-title" style="font-size:13px">Quick Actions</p>
                    </div>
                    <div style="padding:4px 20px 16px">
                        <button class="quick-action-btn" onclick="showPayslip()" style="width:100%;justify-content:flex-start;margin-bottom:8px">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                            View Payslip
                        </button>
                        <button class="quick-action-btn" style="width:100%;justify-content:flex-start;margin-bottom:8px">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            File Leave
                        </button>
                        <button class="quick-action-btn" style="width:100%;justify-content:flex-start;margin-bottom:8px">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/></svg>
                            View Attendance
                        </button>
                        <button class="quick-action-btn" style="width:100%;justify-content:flex-start">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            My Profile
                        </button>
                    </div>
                </div>

                <div class="stat-card no-margin">
                    <p class="stat-label" style="margin-bottom:12px">Leave Balance</p>
                    @php
                    $leaves = [
                        ['label'=>'Vacation Leave','balance'=>'12.5 days','color'=>'#0b044d','percent'=>62.5],
                        ['label'=>'Sick Leave','balance'=>'10 days','color'=>'#8e1e18','percent'=>50],
                        ['label'=>'Emergency Leave','balance'=>'3 days','color'=>'#a16207','percent'=>100],
                    ];
                    @endphp
                    @foreach($leaves as $l)
                    <div style="margin-bottom:10px">
                        <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px">
                            <span style="font-weight:600;color:#0b044d">{{ $l['label'] }}</span>
                            <span style="color:#9999bb">{{ $l['balance'] }}</span>
                        </div>
                        <div style="height:6px;background:#f0effe;border-radius:99px;overflow:hidden">
                            <div style="height:100%;width:{{ $l['percent'] }}%;background:{{ $l['color'] }};border-radius:99px"></div>
                        </div>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>

    </main>

</div>

{{-- Payslip Modal --}}
<div class="modal-overlay" id="payslipModal" style="display:none" onclick="closeModal('payslipModal')">
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
                <div class="emp-avatar" style="background:#8e1e18;width:48px;height:48px;border-radius:12px;font-size:16px">AR</div>
                <div>
                    <p class="modal-emp-id">PGS-0115</p>
                    <span class="badge-status pending">Pending</span>
                </div>
            </div>
            <div class="modal-section-label">EARNINGS</div>
            <div class="modal-row"><span>Basic Semi-Monthly Pay</span><strong>₱16,921.50</strong></div>
            <div class="modal-section-label" style="margin-top:16px">DEDUCTIONS</div>
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

@include('permanent.permanent-chatbot')

<style>
.quick-action-btn { display:flex; align-items:center; gap:9px; padding:10px 14px; border:1.5px solid #eceaf8; border-radius:10px; background:#fafafe; cursor:pointer; font-size:13px; font-weight:600; color:#0b044d; transition:border-color 0.18s; }
.quick-action-btn:hover { border-color:#0b044d; }
.modal-overlay { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(11,4,77,0.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:1000; padding:20px; }
.modal-box { background:#fff; border-radius:16px; width:100%; max-width:480px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.25); animation:slideUp 0.3s ease; }
@keyframes slideUp { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }
.modal-header { display:flex; justify-content:space-between; align-items:flex-start; padding:24px 24px 0; }
.modal-eyebrow { font-size:10.5px; color:#9999bb; font-weight:700; letter-spacing:1px; }
.modal-title { font-size:18px; font-weight:700; color:#0b044d; margin:4px 0 2px; }
.modal-sub { font-size:13px; color:#6b6a8a; margin:0; }
.modal-close { background:none; border:none; cursor:pointer; padding:4px; color:#9999bb; }
.modal-close:hover { color:#0b044d; }
.modal-body { padding:20px 24px; }
.modal-emp-row { display:flex; align-items:center; gap:16px; margin-bottom:20px; padding:16px; background:#f7f6ff; border-radius:12px; }
.modal-emp-id { font-size:11px; color:#9999bb; margin:0 0 4px; }
.modal-section-label { font-size:10.5px; font-weight:700; color:#9999bb; letter-spacing:1px; margin-bottom:12px; }
.modal-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0effe; }
.modal-row span { font-size:13px; color:#9999bb; font-weight:600; }
.modal-row strong { font-size:13px; color:#0b044d; font-weight:600; }
.modal-row.total { border-bottom:2px solid #e5e4f0; padding-top:14px; margin-top:6px; }
.modal-deduct { color:#8e1e18 !important; }
.modal-net-row { display:flex; justify-content:space-between; padding:14px 0; margin-top:10px; background:#f0fdf4; border-radius:10px; padding:14px 16px; }
.modal-net-row span { font-size:13px; color:#15803d; font-weight:700; }
.modal-net-row strong { font-size:18px; color:#15803d; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px 24px; }
.modal-btn-ghost { padding:9px 18px; border-radius:9px; border:1.5px solid #dddcf0; background:#fff; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; }
.modal-btn-ghost:hover { border-color:#0b044d; color:#0b044d; }
.modal-btn-primary { padding:9px 18px; border-radius:9px; border:none; background:linear-gradient(135deg,#0b044d,#1a0f6e); color:#fff; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; }
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
