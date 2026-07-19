{{--
    Top Performers card — expects: $attendancePerformanceMonth, $attendancePerformanceWeek
    (collections with a 'tier' and 'rate' field per employee), $perfPeriodMonth, $perfPeriodWeek.

    Only the top 5 (excellent/good tier) are shown; the bottom-5 slices this used to also
    compute were dead (never rendered anywhere on this page) and were dropped during the
    2026-07 page split rather than carried along unused.
--}}
@php
    $top5Month = $attendancePerformanceMonth->whereIn('tier', ['excellent','good'])->take(5)->values();
    $top5Week  = $attendancePerformanceWeek->whereIn('tier',  ['excellent','good'])->take(5)->values();
@endphp
<div class="table-section" style="margin:0">
    <div class="table-header">
        <div>
            <p class="table-title">Top Performers</p>
            <p class="table-sub" id="perfPeriodSub">{{ $perfPeriodMonth }}</p>
        </div>
        <div style="display:flex;gap:6px">
            <button id="perfTabMonth" onclick="switchPerfTab('month')" class="chart-tab active">Prev Month</button>
            <button id="perfTabWeek" onclick="switchPerfTab('week')" class="chart-tab">Prev Week</button>
        </div>
    </div>
    <div class="enterprise-card-body">
        <div id="perfPanelMonth" class="enterprise-list">
            @forelse($top5Month as $i => $emp)
            <div class="enterprise-list-item" onclick='showPerformerDetails(@json($emp), "month", {{ $i + 1 }})' style="cursor:pointer">
                <span class="enterprise-rank">{{ $i + 1 }}</span>
                @if($emp['photo'])
                    <img src="{{ $emp['photo'] }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover">
                @else
                    <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px">{{ $emp['initials'] }}</div>
                @endif
                <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div class="enterprise-person" style="margin:0">
                            <strong>{{ $emp['name'] }}</strong>
                            <span>{{ $emp['position'] }}</span>
                        </div>
                        <span class="enterprise-metric">{{ $emp['rate'] }}%</span>
                    </div>
                    <div style="width:100%;height:6px;background:#eef2f6;border-radius:3px;overflow:hidden">
                        <div style="width:{{ $emp['rate'] }}%;height:100%;background:linear-gradient(90deg,#150c63,#0b044d);border-radius:3px;transition:width 0.3s ease"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No top performers yet</p>
            @endforelse
        </div>
        <div id="perfPanelWeek" class="enterprise-list" style="display:none">
            @forelse($top5Week as $i => $emp)
            <div class="enterprise-list-item" onclick='showPerformerDetails(@json($emp), "week", {{ $i + 1 }})' style="cursor:pointer">
                <span class="enterprise-rank">{{ $i + 1 }}</span>
                @if($emp['photo'])
                    <img src="{{ $emp['photo'] }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover">
                @else
                    <div class="emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:12px">{{ $emp['initials'] }}</div>
                @endif
                <div style="flex:1;min-width:0;display:flex;flex-direction:column;gap:6px">
                    <div style="display:flex;justify-content:space-between;align-items:center">
                        <div class="enterprise-person" style="margin:0">
                            <strong>{{ $emp['name'] }}</strong>
                            <span>{{ $emp['position'] }}</span>
                        </div>
                        <span class="enterprise-metric">{{ $emp['rate'] }}%</span>
                    </div>
                    <div style="width:100%;height:6px;background:#eef2f6;border-radius:3px;overflow:hidden">
                        <div style="width:{{ $emp['rate'] }}%;height:100%;background:linear-gradient(90deg,#150c63,#0b044d);border-radius:3px;transition:width 0.3s ease"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="table-sub" style="text-align:center;padding:28px 0;margin:0">No top performers yet</p>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/dashboard/top-performers-card.js')
@endpush
