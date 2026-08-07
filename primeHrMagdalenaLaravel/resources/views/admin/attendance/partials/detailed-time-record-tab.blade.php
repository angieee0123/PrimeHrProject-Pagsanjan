<section class="table-section" id="detailed-tab">
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
            <form method="GET" action="{{ route('admin.attendance') }}" id="detailedFilterForm">
                <input type="hidden" name="tab" value="detailed">
                <select class="filter-select employee-filter-select" name="employee_name">
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
                <span class="dtr-filter-sep">to</span>
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
                       class="btn-filter-main ghost">
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
                // A weekend day only means "nobody was expected in" — it must not
                // override someone who actually has real punches that day (e.g.
                // special/OT duty), which is what was hiding Jeremy Pogi's Jul 11
                // attendance behind a blanket "Weekend" tag despite him clocking in.
                $hasAttendance = fn($r) => $r['am_in'] || $r['am_out'] || $r['pm_in'] || $r['pm_out'];

                $presentCount  = $dayRecords->filter(fn($r) => $hasAttendance($r) && !$r['is_absent'] && !$r['is_on_leave'] && !$r['is_on_pass_slip'] && $r['late_minutes'] == 0)->count();
                $lateCount     = $dayRecords->filter(fn($r) => $hasAttendance($r) && !$r['is_absent'] && !$r['is_on_leave'] && !$r['is_on_pass_slip'] && $r['late_minutes'] > 0)->count();
                $absentCount   = $dayRecords->filter(fn($r) => $r['is_absent'])->count();
                $leaveCount    = $dayRecords->filter(fn($r) => $r['is_on_leave'])->count();
                $passSlipCount = $dayRecords->filter(fn($r) => $r['is_on_pass_slip'])->count();

                // Tag each record with its status, then cluster same-status
                // avatars together (worst-first) so the day's mix is scannable
                // at a glance instead of just relying on ring color alone.
                // An approved Pass Slip explains a missing/mismatched punch the
                // same way an approved Leave does, so it's checked before the
                // Absent/Needs Review classification, not layered on top of it.
                $statusOrder = ['absent', 'late', 'review', 'leave', 'passslip', 'present', 'weekend'];
                $taggedRecords = $dayRecords->map(function ($record) use ($isWeekendDay, $hasAttendance) {
                    $attended = $hasAttendance($record);
                    $needsReview = !$record['is_on_pass_slip'] && (($record['am_in'] && !$record['am_out']) || ($record['pm_in'] && !$record['pm_out']) || (!$record['am_in'] && $record['am_out']) || (!$record['pm_in'] && $record['pm_out']));
                    if ($record['is_on_leave']) {
                        $status = 'leave';
                    } elseif ($record['is_absent']) {
                        $status = 'absent';
                    } elseif ($needsReview) {
                        $status = 'review';
                    } elseif ($record['is_on_pass_slip']) {
                        $status = 'passslip';
                    } elseif ($isWeekendDay && !$attended) {
                        $status = 'weekend';
                    } elseif ($record['late_minutes'] > 0) {
                        $status = 'late';
                    } else {
                        $status = 'present';
                    }
                    $record['_status'] = $status;
                    return $record;
                });
                $recordsByStatus = $taggedRecords->groupBy('_status')->map(function ($records, $status) {
                    if ($status === 'present') {
                        return $records->sortBy(fn($r) => $r['am_in'] ?? '99:99')->values();
                    }
                    return $records;
                });
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
                            @endif
                            @if($presentCount > 0)<span class="dtl-stat present">{{ $presentCount }} Present</span>@endif
                            @if($lateCount > 0)<span class="dtl-stat late">{{ $lateCount }} Late</span>@endif
                            @if($absentCount > 0)<span class="dtl-stat absent">{{ $absentCount }} Absent</span>@endif
                            @if($leaveCount > 0)<span class="dtl-stat leave">{{ $leaveCount }} Leave</span>@endif
                            @if($passSlipCount > 0)<span class="dtl-stat passslip">{{ $passSlipCount }} Pass Slip</span>@endif
                        </div>
                    </div>
                    <div class="dtl-avatar-grid">
                        @foreach($statusOrder as $statusKey)
                            @if($recordsByStatus->has($statusKey))
                                <div class="dtl-avatar-group">
                                    @foreach($recordsByStatus[$statusKey] as $record)
                                        @php
                                            // First and last real punch of the day, across every slot.
                                            // Pairing am_in with pm_out (as this did) drops am_out and
                                            // pm_in entirely, so a scanned half-day — in at 16:00, out at
                                            // 16:12 — rendered as "16:00 – —" and the time-out was
                                            // invisible. Each column can also hold a marker such as
                                            // ABSENT / ON LEAVE / ON TRAVEL instead of a time, so only
                                            // values that actually look like a clock time count here.
                                            $punchTimes = collect([
                                                $record['am_in'] ?? null, $record['am_out'] ?? null,
                                                $record['pm_in'] ?? null, $record['pm_out'] ?? null,
                                                $record['ot_in'] ?? null, $record['ot_out'] ?? null,
                                            ])->filter(fn($t) => $t && preg_match('/^\d{1,2}:\d{2}$/', $t))->values();

                                            $timeIn  = $punchTimes->min();
                                            $timeOut = $punchTimes->count() > 1 ? $punchTimes->max() : null;

                                            // A day with real punches shows them even when it is also
                                            // flagged absent (a lone time-in is classed ABSENT): the ring
                                            // colour and the day's stat chips already carry the status,
                                            // and hiding the punch makes a scan look like it never landed.
                                            $statusNote = match(true) {
                                                $record['is_on_leave'] => $record['leave_info']['leave_type'] ?? 'On Leave',
                                                (bool) $punchTimes->count() => null,
                                                $record['is_absent'] => 'Absent',
                                                $isWeekendDay => 'Weekend',
                                                default => 'No record',
                                            };

                                            $timeLabel = $statusNote ?? ($timeIn . ' – ' . ($timeOut ?? 'no time out yet'));
                                            if ($record['is_absent'] && $punchTimes->count()) {
                                                $timeLabel .= ' • Absent (incomplete punches)';
                                            }
                                            if ($record['is_on_pass_slip'] && $record['pass_slip_info']) {
                                                $psLabels = collect($record['pass_slip_info'])->map(fn($s) => ($s['type'] === 'official_activity' ? 'Official Activity' : 'Personal Reason') . ($s['destination'] ? ' – ' . $s['destination'] : ''))->join(', ');
                                                $timeLabel .= ' • Pass Slip: ' . $psLabels;
                                            }
                                            $tooltip = $record['employee_name'] . ' • ' . $timeLabel;
                                        @endphp
                                        <button type="button" class="dtl-avatar-chip status-{{ $record['_status'] }}" data-tooltip="{{ $tooltip }}"
                                            @if($record['attendance_id'])
                                                onclick="openCorrectModal({{ $record['attendance_id'] }}, '{{ $record['date'] }}')"
                                            @else
                                                onclick="openCorrectModal('new_{{ $record['employee_id'] }}_{{ $dateKey }}', '{{ $record['date'] }}')"
                                            @endif
                                        >
                                            <span class="dtl-avatar-face">
                                                @if(!empty($record['photo']))
                                                    <img src="{{ $record['photo'] }}" alt="{{ $record['employee_name'] }}" class="dtl-avatar-img">
                                                @else
                                                    <span class="dtl-avatar-img dtl-avatar-fallback" style="background: {{ $avatarColors[$record['employee_id'] % count($avatarColors)] }}">{{ getInitials($record['employee_name']) }}</span>
                                                @endif
                                                <span class="dtl-status-dot"></span>
                                            </span>
                                            {{-- The times were previously only inside data-tooltip, so
                                                 reading a day's actual in/out meant hovering every
                                                 avatar one at a time. --}}
                                            <span class="dtl-chip-time">
                                                @if($statusNote)
                                                    <span class="dtl-chip-note">{{ $statusNote }}</span>
                                                @else
                                                    <span class="dtl-chip-in">{{ $timeIn }}</span>
                                                    <span class="dtl-chip-out {{ $timeOut ? '' : 'is-pending' }}">{{ $timeOut ?? '—' }}</span>
                                                @endif
                                            </span>
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
        <div class="table-footer-left">
            <p>
                @if($dayRangeLabel)
                    Showing <strong>{{ $dayRangeLabel }}</strong> ({{ $groupedByDate->count() }} {{ Str::plural('day', $groupedByDate->count()) }}) ·
                @endif
                <strong>{{ $detailedPagination['total'] }}</strong> total records
            </p>
            <form method="GET" action="{{ route('admin.attendance') }}" class="table-footer-form">
                <input type="hidden" name="tab" value="detailed">
                <input type="hidden" name="start_date" value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                <input type="hidden" name="end_date" value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                @if(request('employee_name'))
                    <input type="hidden" name="employee_name" value="{{ request('employee_name') }}">
                @endif
                @if(request('department'))
                    <input type="hidden" name="department" value="{{ request('department') }}">
                @endif
                <select name="per_page" class="filter-select per-page-select" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 days</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 days</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 days</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 days</option>
                </select>
            </form>
        </div>
        
        @if($detailedPagination['last_page'] > 1)
        <div class="pagination">
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
