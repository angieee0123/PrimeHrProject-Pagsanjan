{{-- Recent Leave Filers card — expects: $recentLeaveFilers (collection with rank, photo, color, initials, name, position, leave_type, days, status, status_bg, status_color, start_date, end_date). --}}
<div class="table-section" style="margin:0">
    <div class="table-header">
        <div>
            <p class="table-title">Recent Leave Filers</p>
            <p class="table-sub">Latest 5 leave applications filed</p>
        </div>
        <button class="btn-export" style="font-size:11px;padding:6px 12px" onclick="window.location.href='/admin/leave'">View All</button>
    </div>
    <div style="padding:16px 20px 20px">
        <div style="display:flex;flex-direction:column;gap:12px">
            @forelse($recentLeaveFilers as $filer)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:{{ $filer['rank'] < 5 ? '1px solid #f1f5f9' : 'none' }}">
                    <div style="min-width:26px;height:26px;border-radius:8px;text-align:center;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;background:#f1f5f9;color:#64748b">{{ $filer['rank'] }}</div>
                    @if($filer['photo'])
                        <img src="{{ $filer['photo'] }}" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $filer['color'] }}" style="width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;border:2px solid #e2e8f0;flex-shrink:0">{{ $filer['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $filer['name'] }}</p>
                        <p style="font-size:11px;color:#64748b;margin:0 0 3px;font-weight:500">{{ $filer['position'] }}</p>
                        <p style="font-size:10px;color:#94a3b8;margin:0">{{ $filer['leave_type'] }} · {{ $filer['days'] }} day{{ $filer['days'] > 1 ? 's' : '' }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-size:10px;padding:4px 8px;border-radius:6px;font-weight:700;background:{{ $filer['status_bg'] }};color:{{ $filer['status_color'] }};margin-bottom:4px">{{ $filer['status'] }}</div>
                        <p style="font-size:10px;color:#94a3b8;margin:0;font-weight:500">{{ $filer['start_date'] }} - {{ $filer['end_date'] }}</p>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 24px">
                    <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </div>
                    <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Recent Leave Applications</p>
                    <p style="font-size:12px;color:#94a3b8;margin:0">Leave applications will appear here</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
