{{-- On Leave Today card — expects: $onLeaveTodayList (collection with id, photo, color, initials, name, position, dept, leave_type, start_date, end_date, is_last_day, back_on) and $stats.
     Uses the dashboard's shared list language (.enterprise-list / .enterprise-list-item /
     .enterprise-person) so it reads as a sibling of the Pending Requests card next to it,
     instead of the boxed-row treatment it had before. --}}
<div class="table-section enterprise-sidebar-card" id="on-leave-today" style="margin:0">
    <div class="table-header">
        <div>
            <p class="table-title" style="display:flex;align-items:center;gap:8px">
                On Leave Today
                <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;background:{{ $onLeaveTodayList->isEmpty() ? 'var(--theme-success-subtle)' : '#fbf6e3' }};color:{{ $onLeaveTodayList->isEmpty() ? 'var(--theme-success)' : '#8a6d1f' }}">{{ $onLeaveTodayList->count() }}</span>
            </p>
            <p class="table-sub">
                {{ $onLeaveTodayList->count() }} of {{ $stats['total_employees'] }} employees · {{ now()->format('M d') }}
            </p>
        </div>
        <button class="btn-export" style="font-size:11px;padding:6px 12px" onclick="window.location.href='/admin/leave'">View All</button>
    </div>

    <div class="enterprise-card-body">
        @if($onLeaveTodayList->isEmpty())
            <div style="text-align:center;padding:34px 12px">
                <div style="width:46px;height:46px;border-radius:50%;background:var(--theme-success-subtle);display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                    <svg width="21" height="21" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <p style="font-size:13px;font-weight:700;color:var(--gp-pri);margin:0 0 4px">Full attendance</p>
                <p style="font-size:11.5px;color:var(--gp-text-soft);margin:0;line-height:1.45">Nobody is on approved leave today</p>
            </div>
        @else
            <div class="enterprise-list on-leave-list">
                @foreach($onLeaveTodayList as $person)
                    {{-- Row opens this leave's CS Form No. 6 detail — the same modal
                         the Leave & Benefits requests tab opens from "View Details". --}}
                    <div class="enterprise-list-item on-leave-row"
                         onclick="openAdminLeaveDetailModal({{ $person['leave_id'] }}, @js($person['name']), @js($person['employee_code']), @js($person['leave_type']), @js($person['detail_start']), @js($person['detail_end']), {{ $person['days'] }}, @js($person['reason']), @js($person['status_label']), @js($person['application_number']), @js($person['attachment_url']), @js($person['approver_remarks']))"
                         title="View leave details — {{ $person['name'] }}, {{ $person['leave_type'] }}">
                        @if($person['photo'])
                            <img src="{{ $person['photo'] }}" style="width:38px;height:38px;border-radius:50%;object-fit:cover;flex-shrink:0">
                        @else
                            <div class="emp-avatar-dynamic" data-bg="{{ $person['color'] }}" style="width:38px;height:38px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0">{{ $person['initials'] }}</div>
                        @endif
                        <div class="enterprise-person" style="flex:1">
                            <strong>{{ $person['name'] }}</strong>
                            <span>{{ $person['leave_type'] }} · {{ $person['position'] }}</span>
                        </div>
                        @if($person['is_last_day'])
                            <span class="on-leave-chip is-last">Last day</span>
                        @else
                            <span class="on-leave-chip">Back {{ $person['back_on'] }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
