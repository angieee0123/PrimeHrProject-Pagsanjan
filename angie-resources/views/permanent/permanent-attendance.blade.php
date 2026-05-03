@extends('layouts.app')

@section('title', 'Attendance · PRIME HRIS')

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
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h2>My Attendance</h2>
                    <p>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
                </div>
            </div>
            <div class="banner-right">
                <span class="banner-badge">
                    <span class="banner-badge-dot"></span>
                    Schedule: 8:00 AM - 5:00 PM
                </span>
                <span class="banner-badge outline">June 2025</span>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="stats-grid stats-grid-4">

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Days Present</p>
                    <div class="stat-icon-wrap" style="background:#e8f9ef">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">17</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#22c55e"></span>
                    <p class="stat-sub">1 late arrival</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Days Absent</p>
                    <div class="stat-icon-wrap" style="background:#fdf0ef">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value">1</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#8e1e18"></span>
                    <p class="stat-sub">This month</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Overtime Hours</p>
                    <div class="stat-icon-wrap" style="background:#f0effe">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                </div>
                <p class="stat-value">3h</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#0b044d"></span>
                    <p class="stat-sub">2 leave days</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance Rate</p>
                    <div class="stat-icon-wrap" style="background:#fefce8">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                </div>
                <p class="stat-value">94%</p>
                <div class="stat-footer">
                    <span class="stat-dot" style="background:#f59e0b"></span>
                    <p class="stat-sub">18 working days</p>
                </div>
            </div>

        </div>

        {{-- Summary Bar --}}
        <div style="background: linear-gradient(135deg, #fafafe 0%, #f7f6ff 100%); border: 1.5px solid #e4e3f0; border-radius: 12px; padding: 20px 24px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(11, 4, 77, 0.04);">
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Total Present</p>
                <p style="font-size: 20px; font-weight: 700; color: #15803d; margin: 0; line-height: 1;">17</p>
                <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">days</p>
            </div>
            <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Total Absent</p>
                <p style="font-size: 20px; font-weight: 700; color: #8e1e18; margin: 0; line-height: 1;">1</p>
                <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">days</p>
            </div>
            <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Late Arrivals</p>
                <p style="font-size: 20px; font-weight: 700; color: #a16207; margin: 0; line-height: 1;">1</p>
                <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">times</p>
            </div>
            <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Overtime</p>
                <p style="font-size: 20px; font-weight: 700; color: #0b044d; margin: 0; line-height: 1;">3</p>
                <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">hrs</p>
            </div>
            <div style="width: 1px; height: 40px; background: linear-gradient(to bottom, transparent, #e4e3f0, transparent);"></div>
            <div style="text-align: center; flex: 1;">
                <p style="font-size: 9.5px; font-weight: 600; color: #9999bb; letter-spacing: 1px; margin: 0 0 8px; text-transform: uppercase;">Leave Days</p>
                <p style="font-size: 20px; font-weight: 700; color: #0b044d; margin: 0; line-height: 1;">2</p>
                <p style="font-size: 11px; font-weight: 500; color: #9999bb; margin: 4px 0 0;">days</p>
            </div>
        </div>

        {{-- Daily Time Record Table --}}
        <div class="table-section">
            <div class="table-header">
                <div>
                    <p class="table-title">Daily Time Record</p>
                    <p class="table-sub">June 2025 attendance records</p>
                </div>
                <div class="table-actions">
                    <button class="btn-export">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Export
                    </button>
                    <button class="modal-btn-primary" onclick="showDTRModal()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View Summary
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>OT Hours</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                        $records = [
                            ['date'=>'Jun 27','day'=>'Fri','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                            ['date'=>'Jun 26','day'=>'Thu','in'=>'—','out'=>'—','ot'=>'—','status'=>'absent'],
                            ['date'=>'Jun 25','day'=>'Wed','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                            ['date'=>'Jun 24','day'=>'Tue','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                            ['date'=>'Jun 23','day'=>'Mon','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                            ['date'=>'Jun 20','day'=>'Fri','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                            ['date'=>'Jun 19','day'=>'Thu','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                            ['date'=>'Jun 18','day'=>'Wed','in'=>'7:59 AM','out'=>'6:00 PM','ot'=>'+1h','status'=>'present'],
                            ['date'=>'Jun 17','day'=>'Tue','in'=>'8:05 AM','out'=>'5:00 PM','ot'=>'—','status'=>'late'],
                            ['date'=>'Jun 16','day'=>'Mon','in'=>'8:00 AM','out'=>'5:00 PM','ot'=>'—','status'=>'present'],
                        ];
                        @endphp
                        @foreach($records as $r)
                        <tr>
                            <td style="font-weight:600;color:#0b044d;font-size:13px">{{ $r['date'] }}</td>
                            <td style="font-size:12.5px;color:#6b6a8a">{{ $r['day'] }}</td>
                            <td style="font-size:13px;color:{{ $r['in'] === '—' ? '#9999bb' : '#0b044d' }}">{{ $r['in'] }}</td>
                            <td style="font-size:13px;color:{{ $r['out'] === '—' ? '#9999bb' : '#0b044d' }}">{{ $r['out'] }}</td>
                            <td style="font-size:13px;color:{{ $r['ot'] !== '—' ? '#0b044d' : '#9999bb' }};font-weight:{{ $r['ot'] !== '—' ? '600' : '400' }}">{{ $r['ot'] }}</td>
                            <td>
                                @if($r['status']==='present')
                                    <span class="badge-status processed">Present</span>
                                @elseif($r['status']==='absent')
                                    <span class="badge-status on-hold">Absent</span>
                                @elseif($r['status']==='late')
                                    <span class="badge-status pending">Late</span>
                                @else
                                    <span class="badge-status pending">{{ ucfirst($r['status']) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <span>Showing <strong>1–10</strong> of <strong>19</strong> records</span>
                <div class="pagination">
                    <button class="page-btn">‹</button>
                    <button class="page-btn active">1</button>
                    <button class="page-btn">2</button>
                    <button class="page-btn">3</button>
                    <button class="page-btn">›</button>
                </div>
            </div>
        </div>

    </main>

</div>

{{-- DTR Modal --}}
<div class="modal-overlay" id="dtrModal" style="display:none" onclick="closeModal('dtrModal')">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DAILY TIME RECORD · JUNE 2025</span>
                <h3 class="modal-title">Ana R. Reyes</h3>
                <p class="modal-sub">Nurse II · Municipal Health Office</p>
            </div>
            <button class="modal-close" onclick="closeModal('dtrModal')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar" style="background:#8e1e18;width:48px;height:48px;border-radius:12px;font-size:16px">AR</div>
                <div>
                    <p class="modal-emp-id">PGS-0115</p>
                    <span class="badge-status processed">Complete</span>
                </div>
            </div>
            <div class="modal-section-label">ATTENDANCE SUMMARY</div>
            <div class="modal-row"><span>Working Days</span><strong>18 days</strong></div>
            <div class="modal-row"><span>Days Present</span><strong>17 days</strong></div>
            <div class="modal-row"><span>Days Absent</span><strong>1 day</strong></div>
            <div class="modal-row"><span>Late Arrivals</span><strong>1 time</strong></div>
            <div class="modal-row"><span>Leave Days</span><strong>2 days</strong></div>
            <div class="modal-section-label" style="margin-top:16px">OVERTIME</div>
            <div class="modal-row"><span>Total OT Hours</span><strong>3 hrs</strong></div>
            <div class="modal-net-row">
                <span>ATTENDANCE RATE</span>
                <strong>94%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeModal('dtrModal')">Close</button>
            <button class="modal-btn-primary">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download DTR
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
.modal-section-label { font-size:10.5px; font-weight:700; color:#9999bb; letter-spacing:1px; margin-bottom:12px; }
.modal-row { display:flex; justify-content:space-between; padding:10px 0; border-bottom:1px solid #f0effe; }
.modal-row span { font-size:13px; color:#9999bb; font-weight:600; }
.modal-row strong { font-size:13px; color:#0b044d; font-weight:600; }
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

    function showDTRModal() {
        document.getElementById('dtrModal').style.display = 'flex';
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

@include('permanent.permanent-chatbot')

@endsection
