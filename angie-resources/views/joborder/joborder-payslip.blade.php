@extends('layouts.app')

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

    @include('joborder.joborder-sidebarnav')

    {{-- Main Content --}}
    <main class="main-content">

        @include('joborder.joborder-notification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <h2>My Payslips</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Utility Worker I · General Services Office · JO-0042</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    June 2025 Payroll Active
                </span>
                <span class="banner-badge outline">Pay Date: Jun 30</span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Latest Net Pay</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value" style="font-size:20px">₱10,235.45</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">Jun 16–30, 2025</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Daily Rate</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    </div>
                </div>
                <p class="stat-value" style="font-size:20px">₱1,136.36</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">Monthly ÷ 22 days</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Deductions</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value" style="font-size:20px">₱1,273.15</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">PhilHealth, Pag-IBIG, Tax</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Total Payslips</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                </div>
                <p class="stat-value">6</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">Since Jan 2025</p>
                </div>
            </div>

        </div>

        {{-- Payslip History Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Payslip History</p>
                    <p class="table-sub">Juan D. Cruz · JO-0042 · Recent payroll records</p>
                </div>
                <div class="table-actions">
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" onclick="openModal(0)">
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
                            <th>Days Worked</th>
                            <th>Gross Pay</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Pay Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $payslips = [
                            ['period'=>'Jun 16–30, 2025','days'=>10,'gross'=>'₱11,363.64','deduct'=>'₱1,273.15','net'=>'₱10,235.45','date'=>'Jun 30, 2025','status'=>'pending','idx'=>0,'latest'=>true],
                            ['period'=>'Jun 1–15, 2025','days'=>11,'gross'=>'₱12,500.00','deduct'=>'₱1,296.30','net'=>'₱11,203.70','date'=>'Jun 15, 2025','status'=>'processed','idx'=>1,'latest'=>false],
                            ['period'=>'May 16–31, 2025','days'=>11,'gross'=>'₱12,500.00','deduct'=>'₱1,250.00','net'=>'₱11,250.00','date'=>'May 31, 2025','status'=>'processed','idx'=>2,'latest'=>false],
                            ['period'=>'May 1–15, 2025','days'=>10,'gross'=>'₱11,363.64','deduct'=>'₱1,284.72','net'=>'₱10,078.91','date'=>'May 15, 2025','status'=>'processed','idx'=>3,'latest'=>false],
                            ['period'=>'Apr 16–30, 2025','days'=>11,'gross'=>'₱12,500.00','deduct'=>'₱1,250.00','net'=>'₱11,250.00','date'=>'Apr 30, 2025','status'=>'processed','idx'=>4,'latest'=>false],
                            ['period'=>'Apr 1–15, 2025','days'=>11,'gross'=>'₱12,500.00','deduct'=>'₱1,250.00','net'=>'₱11,250.00','date'=>'Apr 15, 2025','status'=>'processed','idx'=>5,'latest'=>false],
                        ];
                        @endphp
                        @foreach($payslips as $p)
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px">
                                    <div style="width:8px;height:8px;border-radius:50%;background:{{ $p['latest'] ? '#d9bb00' : '#e4e3f0' }};flex-shrink:0"></div>
                                    <span style="font-weight:600;color:#0b044d;font-size:13px">{{ $p['period'] }}</span>
                                    @if($p['latest'])
                                        <span style="font-size:10px;font-weight:700;background:#fefce8;color:#a16207;padding:2px 8px;border-radius:20px;border:1px solid #fde68a">LATEST</span>
                                    @endif
                                </div>
                            </td>
                            <td style="font-weight:600;color:#0b044d">{{ $p['days'] }} days</td>
                            <td style="font-size:13px;color:#0b044d;font-weight:600">{{ $p['gross'] }}</td>
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
                            <td>
                                <button class="btn-view" onclick="openModal({{ $p['idx'] }})">View</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>1–6</strong> of <strong>6</strong> payslips</span>
                <div class="pagination">
                    <button class="page-btn">‹</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">›</button>
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
                <span class="modal-eyebrow" id="modalPeriod">OFFICIAL PAYSLIP</span>
                <h3 class="modal-title">Juan D. Cruz</h3>
                <p class="modal-sub">Utility Worker I · General Services Office</p>
            </div>
            <button class="modal-close" onclick="closeModal('payslipModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar" style="background:#1a6e3c;width:48px;height:48px;border-radius:12px;font-size:16px">JD</div>
                <div>
                    <p class="modal-emp-id">JO-0042 &nbsp;·&nbsp; <span id="modalPayDate"></span></p>
                    <span class="badge-status" id="modalStatus">Pending</span>
                </div>
            </div>
            <div class="modal-section-label">EARNINGS</div>
            <div class="modal-row"><span>Daily Rate (Monthly ÷ 22)</span><strong>₱1,136.36</strong></div>
            <div class="modal-row"><span>Days Worked</span><strong id="modalDays"></strong></div>
            <div class="modal-row"><span>Gross Pay</span><strong id="modalGross"></strong></div>
            <div class="modal-section-label" style="margin-top:16px">DEDUCTIONS</div>
            <div class="modal-row" id="lateRow"><span>Late Deduction</span><span class="modal-deduct" id="lateAmt"></span></div>
            <div class="modal-row"><span>PhilHealth</span><span class="modal-deduct">₱375.00</span></div>
            <div class="modal-row"><span>Pag-IBIG</span><span class="modal-deduct">₱100.00</span></div>
            <div class="modal-row"><span>Withholding Tax</span><span class="modal-deduct">₱775.00</span></div>
            <div class="modal-row total"><span>Total Deductions</span><span class="modal-deduct" id="modalTotalDed"></span></div>
            <div class="modal-net-row">
                <span>NET PAY</span>
                <strong id="modalNet"></strong>
            </div>
            <p style="font-size:11px;color:#aaa8cc;text-align:center;margin-top:14px;line-height:1.6">Municipal Government of Pagsanjan · Human Resource Management Office<br>This is a system-generated payslip. No signature required.</p>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('payslipModal')">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download PDF
            </button>
        </div>
    </div>
</div>

<style>
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
.modal-section-label { font-size:10.5px; font-weight:700; color:#9999bb; letter-spacing:1px; margin-bottom:12px; display:block; }
.modal-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0effe; }
.modal-row span { font-size:13px; color:#9999bb; font-weight:600; }
.modal-row strong { font-size:13px; color:#0b044d; font-weight:600; }
.modal-row.total { border-bottom:2px solid #e5e4f0; padding-top:14px; margin-top:6px; }
.modal-deduct { color:#8e1e18 !important; }
.modal-net-row { display:flex; justify-content:space-between; margin-top:10px; background:#f0fdf4; border-radius:10px; padding:14px 16px; }
.modal-net-row span { font-size:13px; color:#15803d; font-weight:700; }
.modal-net-row strong { font-size:18px; color:#15803d; }
.modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px 24px; }
.modal-btn-ghost { padding:9px 18px; border-radius:9px; border:1.5px solid #dddcf0; background:#fff; font-size:13px; font-weight:600; color:#6b6a8a; cursor:pointer; }
.modal-btn-ghost:hover { border-color:#0b044d; color:#0b044d; }
.modal-btn-primary { padding:9px 18px; border-radius:9px; border:none; background:linear-gradient(135deg,#0b044d,#1a0f6e); color:#fff; font-size:13px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px; }
</style>

<script>
    const DAILY_RATE = 1136.36;
    const PER_MIN = DAILY_RATE / 8 / 60;

    const payslips = [
        { period:'Jun 16–30, 2025', payDate:'Jun 30, 2025', status:'Pending',   days:10, lateMin:10 },
        { period:'Jun 1–15, 2025',  payDate:'Jun 15, 2025', status:'Processed', days:11, lateMin:20 },
        { period:'May 16–31, 2025', payDate:'May 31, 2025', status:'Processed', days:11, lateMin:0  },
        { period:'May 1–15, 2025',  payDate:'May 15, 2025', status:'Processed', days:10, lateMin:15 },
        { period:'Apr 16–30, 2025', payDate:'Apr 30, 2025', status:'Processed', days:11, lateMin:0  },
        { period:'Apr 1–15, 2025',  payDate:'Apr 15, 2025', status:'Processed', days:11, lateMin:0  },
    ];

    const fmt = n => '₱' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    function openModal(i) {
        const s = payslips[i];
        const gross = DAILY_RATE * s.days;
        const late  = PER_MIN * s.lateMin;
        const totalDed = 375 + 100 + 775 + late;
        const net = gross - totalDed;

        document.getElementById('modalPeriod').textContent   = 'OFFICIAL PAYSLIP · ' + s.period.toUpperCase();
        document.getElementById('modalPayDate').textContent  = s.payDate;
        document.getElementById('modalStatus').textContent   = s.status;
        document.getElementById('modalStatus').className     = 'badge-status ' + (s.status === 'Processed' ? 'processed' : 'pending');
        document.getElementById('modalDays').textContent     = s.days + ' days';
        document.getElementById('modalGross').textContent    = fmt(gross);
        document.getElementById('modalTotalDed').textContent = fmt(totalDed);
        document.getElementById('modalNet').textContent      = fmt(net);

        const lateRow = document.getElementById('lateRow');
        if (s.lateMin > 0) {
            lateRow.style.display = 'flex';
            document.getElementById('lateAmt').textContent = fmt(late);
        } else {
            lateRow.style.display = 'none';
        }

        document.getElementById('payslipModal').style.display = 'flex';
    }

    function closeModal(id) {
        document.getElementById(id).style.display = 'none';
    }

    const sidebar       = document.getElementById('sidebar');
    const toggleBtn     = document.getElementById('toggle-btn');
    const logoText      = document.getElementById('logo-text');
    const navLabel      = document.getElementById('nav-label');
    const userInfo      = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn     = document.getElementById('mobile-menu-btn');
    const overlay       = document.getElementById('mobile-overlay');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const collapsed = sidebar.classList.toggle('collapsed');
            toggleBtn.textContent = collapsed ? '›' : '‹';
            if (logoText) logoText.style.display = collapsed ? 'none' : '';
            if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
            if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
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

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
    });
</script>

@include('joborder.joborder-chatbot')

@endsection
