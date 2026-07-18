{{-- Top 5 Highest Earners card — expects: $topEarners (collection with rank, photo, color, initials, name, designation, avg_earnings). --}}
<div class="table-section enterprise-sidebar-card" style="margin:0">
    <div class="table-header">
        <div>
            <p class="table-title">Top 5 Highest Earners</p>
            <p class="table-sub">Based on average daily earnings this month</p>
        </div>
    </div>
    <div style="padding:16px 20px 20px">
        <div style="display:flex;flex-direction:column;gap:12px">
            @forelse($topEarners as $earner)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:{{ $earner['rank'] < 5 ? '1px solid #f1f5f9' : 'none' }}">
                    <div style="min-width:26px;height:26px;border-radius:9px;text-align:center;display:inline-flex;align-items:center;justify-content:center;font-size:{{ $earner['rank'] <= 3 ? '13px' : '12px' }};font-weight:{{ $earner['rank'] === 1 ? '800' : '700' }};background:{{ $earner['rank'] <= 3 ? '#fbf6e3' : '#f1f5f9' }};color:{{ $earner['rank'] <= 3 ? '#c9a227' : '#94a3b8' }}">{{ $earner['rank'] }}</div>
                    @if($earner['photo'])
                        <img src="{{ $earner['photo'] }}" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $earner['color'] }}" style="width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;border:2px solid #e2e8f0;flex-shrink:0">{{ $earner['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:13px;font-weight:600;color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $earner['name'] }}</p>
                        <p style="font-size:11px;color:#64748b;margin:0;font-weight:500">{{ $earner['designation'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0">
                        <p style="font-size:14px;font-weight:700;color:#0b044d;margin:0">₱{{ number_format($earner['avg_earnings'], 2) }}</p>
                        <p style="font-size:10px;color:#94a3b8;margin:2px 0 0;font-weight:500">avg/day</p>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 24px">
                    <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    </div>
                    <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Salary Data Available</p>
                    <p style="font-size:12px;color:#94a3b8;margin:0">Salary computations will appear here</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
