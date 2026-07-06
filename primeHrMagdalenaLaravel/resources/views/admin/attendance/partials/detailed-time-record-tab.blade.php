<section class="table-section" id="detailed-tab" style="display: none;">
    @php
        $groupedByDate = collect($detailedRecords)->groupBy('date_key');
        $dayRangeLabel = null;
        if ($groupedByDate->isNotEmpty()) {
            $firstPageDate = \Carbon\Carbon::parse($groupedByDate->keys()->first());
            $lastPageDate = \Carbon\Carbon::parse($groupedByDate->keys()->last());
            if ($firstPageDate->isSameDay($lastPageDate)) {
                $dayRangeLabel = $firstPageDate->format('M d, Y');
            } elseif ($firstPageDate->isSameMonth($lastPageDate)) {
                $dayRangeLabel = $firstPageDate->format('M d') . '–' . $lastPageDate->format('d, Y');
            } else {
                $dayRangeLabel = $firstPageDate->format('M d') . ' – ' . $lastPageDate->format('M d, Y');
            }
        }
    @endphp
    <div class="table-header">
        <div>
            <h3 class="table-title">Detailed Time Record — {{ $periodDisplay }}</h3>
            <p class="table-sub">Daily attendance logs with timestamps · {{ $detailedPagination['total_days'] }} days · {{ $detailedPagination['total'] }} records</p>
        </div>
        <div class="table-actions">
            <form method="GET" action="{{ route('admin.attendance') }}" id="detailedFilterForm" style="display: contents;">
                <input type="hidden" name="tab" value="detailed">
                <select class="filter-select" name="employee_name" style="min-width: 200px;">
                    <option value="">All Employees</option>
                    @foreach(\App\Models\Employee::orderBy('first_name')->get() as $emp)
                        @php
                            $fullName = trim($emp->first_name . ' ' . ($emp->middle_name ? $emp->middle_name . ' ' : '') . $emp->last_name);
                        @endphp
                        <option value="{{ $fullName }}" {{ request('employee_name') == $fullName ? 'selected' : '' }}>
                            {{ $fullName }} ({{ $emp->employee_id }})
                        </option>
                    @endforeach
                </select>
                <input type="date" class="filter-select" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" class="filter-select" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                <select class="filter-select" name="department">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>{{ $dept }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-filter-main">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                @if(request('employee_name') || request('department'))
                    <a href="{{ route('admin.attendance', ['tab' => 'detailed', 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" 
                       class="btn-filter-main" 
                       style="background: #f7f6ff; color: #0b044d; border: 1px solid #e8e7f5;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        Clear
                    </a>
                @endif
            </form>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export All
            </button>
        </div>
    </div>

    <div class="dtl-timeline">
        @forelse($groupedByDate as $dateKey => $dayRecords)
            @php
                $first = $dayRecords->first();
                $isWeekendDay = in_array($first['day'], ['Saturday', 'Sunday']);
                $presentCount = $dayRecords->filter(fn($r) => !$r['is_absent'] && !$r['is_on_leave'] && $r['late_minutes'] == 0)->count();
                $lateCount    = $dayRecords->filter(fn($r) => !$r['is_absent'] && !$r['is_on_leave'] && $r['late_minutes'] > 0)->count();
                $absentCount  = $dayRecords->filter(fn($r) => $r['is_absent'])->count();
                $leaveCount   = $dayRecords->filter(fn($r) => $r['is_on_leave'])->count();

                // Tag each record with its status, then cluster same-status
                // avatars together (worst-first) so the day's mix is scannable
                // at a glance instead of just relying on ring color alone.
                $statusOrder = ['absent', 'late', 'review', 'leave', 'present', 'weekend'];
                $taggedRecords = $dayRecords->map(function ($record) use ($isWeekendDay) {
                    $needsReview = ($record['am_in'] && !$record['am_out']) || ($record['pm_in'] && !$record['pm_out']) || (!$record['am_in'] && $record['am_out']) || (!$record['pm_in'] && $record['pm_out']);
                    if ($isWeekendDay) {
                        $status = 'weekend';
                    } elseif ($record['is_on_leave']) {
                        $status = 'leave';
                    } elseif ($record['is_absent']) {
                        $status = 'absent';
                    } elseif ($needsReview) {
                        $status = 'review';
                    } elseif ($record['late_minutes'] > 0) {
                        $status = 'late';
                    } else {
                        $status = 'present';
                    }
                    $record['_status'] = $status;
                    return $record;
                });
                $recordsByStatus = $taggedRecords->groupBy('_status');
            @endphp
            <div class="dtl-day {{ $isWeekendDay ? 'is-weekend' : '' }}">
                <div class="dtl-day-track">
                    <div class="dtl-day-dot"></div>
                    <div class="dtl-day-line"></div>
                </div>
                <div class="dtl-day-card">
                    <div class="dtl-day-head">
                        <div class="dtl-day-date">
                            <span class="dtl-day-num">{{ \Carbon\Carbon::parse($dateKey)->format('d') }}</span>
                            <div class="dtl-day-meta">
                                <p class="dtl-day-month">{{ \Carbon\Carbon::parse($dateKey)->format('M Y') }}</p>
                                <p class="dtl-day-weekday">{{ $first['day'] }}</p>
                            </div>
                        </div>
                        <div class="dtl-day-stats">
                            @if($isWeekendDay)
                                <span class="dtl-stat weekend">Weekend</span>
                            @else
                                @if($presentCount > 0)<span class="dtl-stat present">{{ $presentCount }} Present</span>@endif
                                @if($lateCount > 0)<span class="dtl-stat late">{{ $lateCount }} Late</span>@endif
                                @if($absentCount > 0)<span class="dtl-stat absent">{{ $absentCount }} Absent</span>@endif
                                @if($leaveCount > 0)<span class="dtl-stat leave">{{ $leaveCount }} Leave</span>@endif
                            @endif
                        </div>
                    </div>
                    <div class="dtl-avatar-grid">
                        @foreach($statusOrder as $statusKey)
                            @if($recordsByStatus->has($statusKey))
                                <div class="dtl-avatar-group">
                                    @foreach($recordsByStatus[$statusKey] as $record)
                                        @php
                                            $timeLabel = match(true) {
                                                $record['is_on_leave'] => $record['leave_info']['leave_type'] ?? 'On Leave',
                                                $record['is_absent'] => 'Absent',
                                                $isWeekendDay => 'Weekend',
                                                ($record['am_in'] || $record['pm_out']) => trim(($record['am_in'] ?? '—') . ' – ' . ($record['pm_out'] ?? '—')),
                                                default => 'No record',
                                            };
                                            $tooltip = $record['employee_name'] . ' • ' . $timeLabel;
                                        @endphp
                                        <button type="button" class="dtl-avatar-chip status-{{ $record['_status'] }}" data-tooltip="{{ $tooltip }}"
                                            @if($record['attendance_id'])
                                                onclick="openCorrectModal({{ $record['attendance_id'] }}, '{{ $record['date'] }}')"
                                            @else
                                                onclick="openCorrectModal('new_{{ $record['employee_id'] }}_{{ $dateKey }}', '{{ $record['date'] }}')"
                                            @endif
                                        >
                                            @if(!empty($record['photo']))
                                                <img src="{{ $record['photo'] }}" alt="{{ $record['employee_name'] }}" class="dtl-avatar-img">
                                            @else
                                                <span class="dtl-avatar-img dtl-avatar-fallback" style="background: {{ $avatarColors[$record['employee_id'] % count($avatarColors)] }}">{{ getInitials($record['employee_name']) }}</span>
                                            @endif
                                            <span class="dtl-status-dot"></span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="dtl-empty">
                <p>No attendance records found for the selected period.</p>
            </div>
        @endforelse
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p>
                @if($dayRangeLabel)
                    Showing <strong>{{ $dayRangeLabel }}</strong> ({{ $groupedByDate->count() }} {{ Str::plural('day', $groupedByDate->count()) }}) ·
                @endif
                <strong>{{ $detailedPagination['total'] }}</strong> total records
            </p>
            <form method="GET" action="{{ route('admin.attendance') }}" style="display:inline;margin:0;">
                <input type="hidden" name="tab" value="detailed">
                <input type="hidden" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="hidden" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                @if(request('employee_name'))
                    <input type="hidden" name="employee_name" value="{{ request('employee_name') }}">
                @endif
                @if(request('department'))
                    <input type="hidden" name="department" value="{{ request('department') }}">
                @endif
                <select name="per_page" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 days</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 days</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 days</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 days</option>
                </select>
            </form>
        </div>
        
        @if($detailedPagination['last_page'] > 1)
        <div class="pagination" style="display: flex; gap: 8px; align-items: center;">
            @if($detailedPagination['current_page'] > 1)
                <a href="{{ route('admin.attendance', array_merge(request()->all(), ['page' => $detailedPagination['current_page'] - 1, 'tab' => 'detailed'])) }}" class="pagination-btn">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                    Previous
                </a>
            @endif
            
            @for($i = max(1, $detailedPagination['current_page'] - 2); $i <= min($detailedPagination['last_page'], $detailedPagination['current_page'] + 2); $i++)
                <a href="{{ route('admin.attendance', array_merge(request()->all(), ['page' => $i, 'tab' => 'detailed'])) }}" 
                   class="pagination-btn {{ $i == $detailedPagination['current_page'] ? 'active' : '' }}">
                    {{ $i }}
                </a>
            @endfor
            
            @if($detailedPagination['current_page'] < $detailedPagination['last_page'])
                <a href="{{ route('admin.attendance', array_merge(request()->all(), ['page' => $detailedPagination['current_page'] + 1, 'tab' => 'detailed'])) }}" class="pagination-btn">
                    Next
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </a>
            @endif
        </div>
        @endif
    </div>
</section>

<style>
/* ══ Liquid Glass timeline for the Detailed Time Record tab ══
   Scoped to #detailed-tab only. Replaces the old per-row table
   with a day-grouped timeline: one node per date, with a cluster
   of employee avatars (ring-colored by attendance status) instead
   of a repeated row per employee. */
#detailed-tab {
    position: relative;
    isolation: isolate;
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif !important;
    background: linear-gradient(180deg, rgba(255,255,255,.78), rgba(255,255,255,.5)) !important;
    backdrop-filter: blur(28px) saturate(200%) !important;
    -webkit-backdrop-filter: blur(28px) saturate(200%) !important;
    border: 1px solid rgba(255,255,255,.75) !important;
    border-radius: 22px !important;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.9),
        0 20px 45px rgba(11,4,77,.12),
        0 4px 14px rgba(15,23,42,.06) !important;
}
#detailed-tab::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -1;
    border-radius: 22px;
    background:
        radial-gradient(420px 260px at 4% -10%, rgba(99,102,241,.20), transparent 60%),
        radial-gradient(380px 240px at 100% 0%, rgba(56,189,248,.18), transparent 60%);
}

#detailed-tab .table-header { background: transparent !important; border-bottom: 1px solid rgba(15,23,42,.07); border-radius: 22px 22px 0 0; }

#detailed-tab .filter-select,
#detailed-tab input[type="date"].filter-select {
    border-radius: 999px !important;
    background: rgba(255,255,255,.6) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(15,23,42,.08) !important;
}
#detailed-tab .btn-filter-main {
    border-radius: 999px !important;
    background: linear-gradient(135deg, #1b1464, #0b044d) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.25), 0 8px 20px rgba(11,4,77,.3);
    border: none !important;
}
#detailed-tab .btn-export {
    border-radius: 999px !important;
    background: rgba(255,255,255,.6) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(15,23,42,.08) !important;
}

/* ── Timeline ── */
#detailed-tab .dtl-timeline {
    padding: 24px 28px;
    display: flex;
    flex-direction: column;
}
#detailed-tab .dtl-day {
    display: flex;
    gap: 16px;
}
#detailed-tab .dtl-day-track {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 14px;
    flex-shrink: 0;
}
#detailed-tab .dtl-day-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--pri);
    box-shadow: 0 0 0 4px rgba(11,10,77,.1);
    flex-shrink: 0;
    margin-top: 22px;
}
#detailed-tab .dtl-day.is-weekend .dtl-day-dot { background: #b9bed0; box-shadow: 0 0 0 4px rgba(185,190,208,.15); }
#detailed-tab .dtl-day-line {
    flex: 1;
    width: 2px;
    background: linear-gradient(180deg, rgba(11,10,77,.16), rgba(11,10,77,.04));
    margin: 6px 0;
    min-height: 24px;
}
#detailed-tab .dtl-day:last-child .dtl-day-line { display: none; }

#detailed-tab .dtl-day-card {
    flex: 1;
    min-width: 0;
    margin-bottom: 20px;
    padding: 16px 20px;
    background: rgba(255,255,255,.55);
    backdrop-filter: blur(16px) saturate(160%);
    -webkit-backdrop-filter: blur(16px) saturate(160%);
    border: 1px solid rgba(255,255,255,.7);
    border-radius: 16px;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.8), 0 4px 14px rgba(15,23,42,.04);
    transition: box-shadow .25s ease, transform .25s ease;
}
#detailed-tab .dtl-day-card:hover {
    box-shadow: inset 0 1px 0 rgba(255,255,255,.9), 0 12px 28px rgba(11,4,77,.1);
    transform: translateY(-2px);
}

#detailed-tab .dtl-day-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 14px;
}
#detailed-tab .dtl-day-date { display: flex; align-items: center; gap: 10px; }
#detailed-tab .dtl-day-num {
    font-size: 22px;
    font-weight: 800;
    color: var(--pri);
    letter-spacing: -.5px;
    line-height: 1;
}
#detailed-tab .dtl-day-month { font-size: 11px; font-weight: 700; color: var(--ink); margin: 0; }
#detailed-tab .dtl-day-weekday { font-size: 10.5px; font-weight: 500; color: var(--muted); margin: 2px 0 0; text-transform: uppercase; letter-spacing: .4px; }

#detailed-tab .dtl-day-stats { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; }
#detailed-tab .dtl-stat {
    font-size: 10.5px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 999px;
    white-space: nowrap;
    border: 1px solid transparent;
}
#detailed-tab .dtl-stat.present { background: rgba(233,249,239,.8); color: #23875a; border-color: rgba(35,135,90,.15); }
#detailed-tab .dtl-stat.late    { background: rgba(253,243,227,.8); color: #a6720c; border-color: rgba(166,114,12,.15); }
#detailed-tab .dtl-stat.absent  { background: rgba(253,237,236,.8); color: #d5433c; border-color: rgba(213,67,60,.15); }
#detailed-tab .dtl-stat.leave   { background: rgba(234,241,255,.8); color: #2547b0; border-color: rgba(37,71,176,.15); }
#detailed-tab .dtl-stat.weekend { background: rgba(241,242,246,.8); color: #7c839d; border-color: rgba(124,131,157,.15); }

#detailed-tab .dtl-avatar-grid {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 18px;
}
#detailed-tab .dtl-avatar-group {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
    padding-right: 18px;
    border-right: 1px solid rgba(15,23,42,.08);
}
#detailed-tab .dtl-avatar-group:last-child {
    padding-right: 0;
    border-right: none;
}
#detailed-tab .dtl-avatar-chip {
    position: relative;
    width: 30px;
    height: 30px;
    padding: 0;
    border: none;
    background: none;
    cursor: pointer;
    border-radius: 50%;
    transition: transform .18s cubic-bezier(.4,0,.2,1);
}
#detailed-tab .dtl-avatar-chip:hover { transform: translateY(-3px) scale(1.1); z-index: 5; }
#detailed-tab .dtl-avatar-img {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    box-sizing: border-box;
}
#detailed-tab .dtl-avatar-fallback { display: flex; }

#detailed-tab .status-present .dtl-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px var(--success); }
#detailed-tab .status-late    .dtl-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px var(--warning); }
#detailed-tab .status-absent  .dtl-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px var(--danger); opacity: .7; }
#detailed-tab .status-leave   .dtl-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px var(--blue); }
#detailed-tab .status-review  .dtl-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px var(--purple); }
#detailed-tab .status-weekend .dtl-avatar-img { box-shadow: 0 0 0 2px #fff, 0 0 0 3.5px #cbd5e1; opacity: .6; }

#detailed-tab .dtl-status-dot {
    position: absolute;
    bottom: -1px;
    right: -1px;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    border: 1.5px solid #fff;
}
#detailed-tab .status-present .dtl-status-dot { background: var(--success); }
#detailed-tab .status-late    .dtl-status-dot { background: var(--warning); }
#detailed-tab .status-absent  .dtl-status-dot { background: var(--danger); }
#detailed-tab .status-leave   .dtl-status-dot { background: var(--blue); }
#detailed-tab .status-review  .dtl-status-dot { background: var(--purple); }
#detailed-tab .status-weekend .dtl-status-dot { background: #cbd5e1; }

/* custom tooltip, matching .dept-tag / .acc-pill pattern used elsewhere */
#detailed-tab .dtl-avatar-chip:hover::after {
    content: attr(data-tooltip);
    position: absolute; bottom: calc(100% + 10px); left: 50%; transform: translateX(-50%);
    background: #1a1f36; color: #fff; font-size: 11px; font-weight: 500;
    padding: 6px 10px; border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,.18); z-index: 20; pointer-events: none;
    max-width: 240px; white-space: normal; text-align: center; line-height: 1.4;
}
#detailed-tab .dtl-avatar-chip:hover::before {
    content: '';
    position: absolute; bottom: calc(100% + 4px); left: 50%; transform: translateX(-50%);
    border: 5px solid transparent; border-top-color: #1a1f36; z-index: 20;
}

#detailed-tab .dtl-empty {
    padding: 60px 24px;
    text-align: center;
    color: var(--muted);
    font-size: 13px;
}

#detailed-tab .table-footer {
    background: rgba(255,255,255,.4) !important;
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-top: 1px solid rgba(15,23,42,.07) !important;
    border-radius: 0 0 22px 22px;
}

@media (max-width: 640px) {
    #detailed-tab .dtl-timeline { padding: 16px; }
    #detailed-tab .dtl-day { gap: 10px; }
    #detailed-tab .dtl-day-card { padding: 14px; }
}
</style>
