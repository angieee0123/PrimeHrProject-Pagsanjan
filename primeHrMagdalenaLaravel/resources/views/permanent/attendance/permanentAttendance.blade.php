@extends('layouts.permanent')

@section('title', 'Attendance · PRIME HRIS')

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
    <main class="main-content permanent-dashboard permanent-attendance">

        @include('permanent.notification.permanentNotification')

        {{-- Welcome Banner --}}
        <div class="welcome-banner">
            <div class="banner-left">
                <div class="banner-icon">
                    <svg width="22" height="22" fill="none" stroke="#d9bb00" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div>
                    <h2>My Attendance</h2>
                    <p><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; Nurse II · Municipal Health Office · PGS-0115</p>
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
                    <div class="stat-icon-wrap stat-icon-wrap-success">
                        <svg width="17" height="17" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <p class="stat-value">17</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-success"></span>
                    <p class="stat-sub">1 late arrival</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Days Absent</p>
                    <div class="stat-icon-wrap stat-icon-wrap-danger">
                        <svg width="17" height="17" fill="none" stroke="#8e1e18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                </div>
                <p class="stat-value">1</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-danger"></span>
                    <p class="stat-sub">This month</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Overtime Hours</p>
                    <div class="stat-icon-wrap stat-icon-wrap-primary">
                        <svg width="17" height="17" fill="none" stroke="#0b044d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    </div>
                </div>
                <p class="stat-value">3h</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-primary"></span>
                    <p class="stat-sub">2 leave days</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Attendance Rate</p>
                    <div class="stat-icon-wrap stat-icon-wrap-warning">
                        <svg width="17" height="17" fill="none" stroke="#a16207" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    </div>
                </div>
                <p class="stat-value">94%</p>
                <div class="stat-footer">
                    <span class="stat-dot stat-dot-amber"></span>
                    <p class="stat-sub">18 working days</p>
                </div>
            </div>

        </div>

        {{-- Summary Bar --}}
        <div class="attendance-summary-bar">
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Total Present</p>
                <p class="attendance-summary-value attendance-summary-value-success">17</p>
                <p class="attendance-summary-sub">days</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Total Absent</p>
                <p class="attendance-summary-value attendance-summary-value-danger">1</p>
                <p class="attendance-summary-sub">days</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Late Arrivals</p>
                <p class="attendance-summary-value attendance-summary-value-warning">1</p>
                <p class="attendance-summary-sub">times</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Overtime</p>
                <p class="attendance-summary-value attendance-summary-value-primary">3</p>
                <p class="attendance-summary-sub">hrs</p>
            </div>
            <div class="attendance-summary-divider"></div>
            <div class="attendance-summary-item">
                <p class="attendance-summary-label">Leave Days</p>
                <p class="attendance-summary-value attendance-summary-value-primary">2</p>
                <p class="attendance-summary-sub">days</p>
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
                    <button class="modal-btn-primary" onclick="showDetailedDTRModal()">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        View Detailed
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table attendance-history-table">
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
                            <td class="table-cell-period">{{ $r['date'] }}</td>
                            <td class="attendance-cell-day">{{ $r['day'] }}</td>
                            <td class="{{ $r['in'] === '—' ? 'attendance-cell-muted' : 'attendance-cell-time' }}">{{ $r['in'] }}</td>
                            <td class="{{ $r['out'] === '—' ? 'attendance-cell-muted' : 'attendance-cell-time' }}">{{ $r['out'] }}</td>
                            <td class="{{ $r['ot'] !== '—' ? 'attendance-cell-ot' : 'attendance-cell-muted' }}">{{ $r['ot'] }}</td>
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
<div class="modal-overlay" id="dtrModal" onclick="closeModal('dtrModal')">
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
                <div class="emp-avatar modal-emp-avatar">AR</div>
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
            <div class="modal-section-label modal-section-deductions">OVERTIME</div>
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

    function showDetailedDTRModal() {
        document.getElementById('detailedDTRModal').style.display = 'flex';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay').forEach(m => m.style.display = 'none');
        }
    });
</script>

@include('permanent.attendance.modals.detailedDtrModal')
@include('permanent.chatbot.permanentChatbot')

@endsection
