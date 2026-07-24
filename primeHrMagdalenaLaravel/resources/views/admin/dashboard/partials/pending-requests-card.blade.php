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
                <button type="button" class="pr-action-btn" onclick="toggleLeaveMenu(event)" aria-haspopup="menu" aria-expanded="false" aria-label="Actions for {{ $l['name'] }}">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                </button>
                <div class="leave-action-menu pr-menu" role="menu" style="display:none">
                    <button type="button" role="menuitem" class="pr-menu-item is-approve" onclick="approveLeave(event)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Approve
                    </button>
                    <button type="button" role="menuitem" class="pr-menu-item is-disapprove" onclick="disapproveLeave(event)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Disapprove
                    </button>
                    <div class="pr-menu-sep"></div>
                    <button type="button" role="menuitem" class="pr-menu-item" onclick="viewLeaveDetails(event)">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        View details
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
                <button type="button" class="pr-action-btn" onclick="togglePassSlipMenuDash(event)" aria-haspopup="menu" aria-expanded="false" aria-label="Actions for {{ $p['name'] }}">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="5" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="12" cy="19" r="2"/></svg>
                </button>
                <div class="leave-action-menu pr-menu" role="menu" style="display:none">
                    <button type="button" role="menuitem" class="pr-menu-item" onclick="window.location.href='/admin/passslip'">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
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

@push('scripts')
    @vite('resources/js/admin/dashboard/pending-requests-card.js')
@endpush
