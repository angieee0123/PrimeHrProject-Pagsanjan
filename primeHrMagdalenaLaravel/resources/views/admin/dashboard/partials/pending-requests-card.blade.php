{{-- Pending Requests card (leave + pass slip tabs) — expects: $stats, $leaveRequests, $pendingPassSlips, $passSlipRequests. --}}
<div class="table-section enterprise-sidebar-card" style="margin-bottom:0">
    <div class="table-header">
        <div>
            <p class="table-title">Pending Requests</p>
            <p class="table-sub">Requires immediate approval</p>
        </div>
        <button class="btn-export" id="pendingRequestsViewAllBtn" style="font-size:11px;padding:6px 12px" onclick="window.location.href='/admin/leave'">View All</button>
    </div>
    <div style="display:flex;gap:6px;padding:0 20px 14px">
        <button type="button" class="chart-tab active" id="pendingTabLeaveBtn" onclick="switchPendingRequestsTab('leave')" style="font-size:11px">
            Leave{{ $stats['pending_leave'] > 0 ? ' (' . $stats['pending_leave'] . ')' : '' }}
        </button>
        <button type="button" class="chart-tab" id="pendingTabPassSlipBtn" onclick="switchPendingRequestsTab('passslip')" style="font-size:11px">
            Pass Slip{{ $pendingPassSlips > 0 ? ' (' . $pendingPassSlips . ')' : '' }}
        </button>
    </div>
    <div class="enterprise-card-body" id="pendingLeaveTabPanel">
        <div class="enterprise-list">
            @forelse($leaveRequests as $l)
            <div class="enterprise-list-item" style="cursor:default;padding:12px 0;position:relative">
                @if($l['photo'])
                    <img src="{{ $l['photo'] }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">
                @else
                    <div class="emp-avatar-dynamic" data-bg="{{ $l['color'] }}" style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px">{{ $l['initials'] }}</div>
                @endif
                <div class="enterprise-person" style="flex:1">
                    <strong>{{ $l['name'] }}</strong>
                    <span>{{ $l['type'] }} · {{ $l['days'] }}</span>
                </div>
                <button onclick="toggleLeaveMenu(event)" style="background:none;border:none;color:#8f8daf;cursor:pointer;padding:4px 8px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0" onmouseover="this.style.background='#f1f5f9';this.style.color='#0b044d'" onmouseout="this.style.background='none';this.style.color='#8f8daf'">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                </button>
                <div class="leave-action-menu" style="display:none;position:absolute;right:0;top:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(15,23,42,0.12);z-index:100;min-width:140px;margin-top:4px">
                    <button onclick="approveLeave(event)" style="width:100%;padding:10px 12px;border:none;background:#0b044d;color:#fff;text-align:left;font-size:12px;font-weight:600;cursor:pointer;border-bottom:1px solid #e5e7eb;transition:all 0.2s;border-radius:6px 6px 0 0" onmouseover="this.style.background='#1b1464'" onmouseout="this.style.background='#0b044d'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><polyline points="20 6 9 17 4 12"/></svg>
                        Approve
                    </button>
                    <button onclick="disapproveLeave(event)" style="width:100%;padding:10px 12px;border:none;background:none;text-align:left;font-size:12px;color:#8e1e18;font-weight:600;cursor:pointer;border-bottom:1px solid #e5e7eb;transition:all 0.2s" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='none'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Disapprove
                    </button>
                    <button onclick="viewLeaveDetails(event)" style="width:100%;padding:10px 12px;border:none;background:none;text-align:left;font-size:12px;color:#0b044d;font-weight:600;cursor:pointer;transition:all 0.2s;border-radius:0 0 6px 6px" onmouseover="this.style.background='#f2f1fb'" onmouseout="this.style.background='none'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View
                    </button>
                </div>
            </div>
            @empty
            <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No pending requests</p>
            @endforelse
        </div>
    </div>
    <div class="enterprise-card-body" id="pendingPassSlipTabPanel" style="display:none">
        <div class="enterprise-list">
            @forelse($passSlipRequests as $p)
            <div class="enterprise-list-item" style="cursor:default;padding:12px 0;position:relative">
                @if($p['photo'])
                    <img src="{{ $p['photo'] }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover">
                @else
                    <div class="emp-avatar-dynamic" data-bg="{{ $p['color'] }}" style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px">{{ $p['initials'] }}</div>
                @endif
                <div class="enterprise-person" style="flex:1">
                    <strong>{{ $p['name'] }}</strong>
                    <span>{{ $p['type_label'] }}{{ $p['destination'] ? ' · ' . $p['destination'] : '' }}</span>
                </div>
                <button onclick="togglePassSlipMenuDash(event)" style="background:none;border:none;color:#8f8daf;cursor:pointer;padding:4px 8px;border-radius:6px;display:flex;align-items:center;justify-content:center;transition:all 0.2s;flex-shrink:0" onmouseover="this.style.background='#f1f5f9';this.style.color='#0b044d'" onmouseout="this.style.background='none';this.style.color='#8f8daf'">
                    <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                </button>
                <div class="leave-action-menu" style="display:none;position:absolute;right:0;top:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 4px 12px rgba(15,23,42,0.12);z-index:100;min-width:160px;margin-top:4px">
                    <button onclick="window.location.href='/admin/passslip'" style="width:100%;padding:10px 12px;border:none;background:#0b044d;color:#fff;text-align:left;font-size:12px;font-weight:600;cursor:pointer;border-radius:6px 6px 0 0" onmouseover="this.style.background='#1b1464'" onmouseout="this.style.background='#0b044d'">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24" style="display:inline;margin-right:6px;vertical-align:middle"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Review in Pass Slip
                    </button>
                </div>
            </div>
            @empty
            <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No pending pass slips</p>
            @endforelse
        </div>
    </div>
</div>
