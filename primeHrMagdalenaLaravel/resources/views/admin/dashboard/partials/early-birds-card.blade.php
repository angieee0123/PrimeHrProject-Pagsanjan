{{--
    Top 5 Early Birds / Late Arrivals card — expects: $earlyBirds, $lateBirds (collections
    with rank-driving fields: photo, color, initials, name, position, time_in,
    late_minutes, schedule), $attendanceDate.

    Each row's `data-schedule` JSON is read by birdScheduleTooltip's hover script
    (see partials/bird-schedule-tooltip.blade.php), so it must stay included on the
    same page as that tooltip.
--}}
<div class="table-section" style="margin:0">
    <div class="table-header">
        <div>
            <p class="table-title" id="birdsTabTitle">Top 5 Early Birds</p>
            <p class="table-sub">{{ $attendanceDate->isToday() ? 'Today' : $attendanceDate->format('F d, Y') }} · Earliest clock-ins</p>
        </div>
        <div style="display:flex;gap:6px">
            <button id="tabEarly" onclick="switchBirdsTab('early')" class="chart-tab active">Earliest</button>
            <button id="tabLate" onclick="switchBirdsTab('late')" class="chart-tab">Late arrivals</button>
        </div>
    </div>
    <div id="panelEarly" style="padding:16px 20px 20px">
        <div style="display:flex;flex-direction:column;gap:12px">
            @forelse($earlyBirds as $index => $bird)
                @php
                    $rank = $index + 1;
                    $avatarSize = $rank === 1 ? 48 : ($rank === 2 ? 44 : ($rank === 3 ? 40 : ($rank === 4 ? 38 : 36)));
                    $nameSize = $rank === 1 ? '13.5px' : ($rank === 2 ? '13px' : '12.5px');
                    $positionSize = $rank === 1 ? '11.5px' : '11px';
                    $timeSize = $rank === 1 ? '13px' : ($rank === 2 ? '12.5px' : '12px');
                    $padding = $rank === 1 ? '12px 0' : ($rank === 2 ? '10px 0' : '8px 0');
                @endphp
                <div class="bird-item" data-schedule='@json($bird['schedule'])' style="display:flex;align-items:center;gap:12px;padding:{{ $padding }};border-bottom:{{ $rank < 5 ? '1px solid #f1f5f9' : 'none' }}">
                    <div style="min-width:26px;text-align:center;font-size:{{ $rank <= 3 ? '13px' : '12px' }};font-weight:{{ $rank === 1 ? '800' : '700' }};color:{{ $rank === 1 ? '#c9a227' : ($rank === 2 ? '#94a3b8' : ($rank === 3 ? '#b45309' : '#94a3b8')) }}">{{ $rank }}</div>
                    @if($bird['photo'])
                        <img src="{{ $bird['photo'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;object-fit:cover;border:2px solid #e2e8f0;flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $bird['color'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:{{ $rank === 1 ? '18px' : ($rank === 2 ? '16px' : '14px') }};border:2px solid #e2e8f0;flex-shrink:0">{{ $bird['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:{{ $nameSize }};font-weight:{{ $rank === 1 ? '700' : '600' }};color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $bird['name'] }}</p>
                        <p style="font-size:{{ $positionSize }};color:#64748b;margin:0;font-weight:500">{{ $bird['position'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0;margin-right:16px">
                        <p style="font-size:{{ $timeSize }};font-weight:700;color:#0b044d;margin:0">{{ $bird['time_in'] }}</p>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 24px">
                    <div style="width:48px;height:48px;border-radius:50%;background:#f1f5f9;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <svg width="22" height="22" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Attendance Records Today</p>
                    <p style="font-size:12px;color:#94a3b8;margin:0">Check back later as employees clock in</p>
                </div>
            @endforelse
        </div>
    </div>
    <div id="panelLate" style="display:none;padding:16px 20px 20px">
        <div style="display:flex;flex-direction:column;gap:12px">
            @forelse($lateBirds as $index => $bird)
                @php
                    $rank = $index + 1;
                    $avatarSize = $rank === 1 ? 48 : ($rank === 2 ? 44 : ($rank === 3 ? 40 : ($rank === 4 ? 38 : 36)));
                    $nameSize = $rank === 1 ? '13.5px' : ($rank === 2 ? '13px' : '12.5px');
                    $positionSize = $rank === 1 ? '11.5px' : '11px';
                    $timeSize = $rank === 1 ? '13px' : ($rank === 2 ? '12.5px' : '12px');
                    $padding = $rank === 1 ? '12px 0' : ($rank === 2 ? '10px 0' : '8px 0');
                @endphp
                <div class="bird-item" data-schedule='@json($bird['schedule'])' style="display:flex;align-items:center;gap:12px;padding:{{ $padding }};border-bottom:{{ $rank < 5 ? '1px solid #fee2e2' : 'none' }}">
                    <div style="min-width:26px;text-align:center;font-size:{{ $rank <= 3 ? '13px' : '12px' }};font-weight:700;color:#8e1e18">{{ $rank }}</div>
                    @if($bird['photo'])
                        <img src="{{ $bird['photo'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;object-fit:cover;border:2px solid #fecaca;flex-shrink:0">
                    @else
                        <div class="emp-avatar-dynamic" data-bg="{{ $bird['color'] }}" style="width:{{ $avatarSize }}px;height:{{ $avatarSize }}px;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:{{ $rank === 1 ? '18px' : ($rank === 2 ? '16px' : '14px') }};border:2px solid #fecaca;flex-shrink:0">{{ $bird['initials'] }}</div>
                    @endif
                    <div style="flex:1;min-width:0">
                        <p style="font-size:{{ $nameSize }};font-weight:{{ $rank === 1 ? '700' : '600' }};color:#1e293b;margin:0 0 2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $bird['name'] }}</p>
                        <p style="font-size:{{ $positionSize }};color:#64748b;margin:0;font-weight:500">{{ $bird['position'] }}</p>
                    </div>
                    <div style="text-align:right;flex-shrink:0;margin-right:16px">
                        <p style="font-size:{{ $timeSize }};font-weight:700;color:#0b044d;margin:0 0 3px">{{ $bird['time_in'] }}</p>
                        <p style="font-size:10px;color:#8e1e18;font-weight:600;margin:0">+{{ $bird['late_minutes'] }}m</p>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:32px 24px">
                    <div style="width:48px;height:48px;border-radius:50%;background:#f0fdf4;display:flex;align-items:center;justify-content:center;margin:0 auto 12px">
                        <svg width="22" height="22" fill="none" stroke="#15803d" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <p style="font-size:13px;font-weight:600;color:#475569;margin:0 0 4px">No Late Arrivals Today</p>
                    <p style="font-size:12px;color:#94a3b8;margin:0">All employees arrived on time! 🎉</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
