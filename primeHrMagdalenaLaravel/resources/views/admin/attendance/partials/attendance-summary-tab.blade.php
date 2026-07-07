<section class="table-section" id="summary-tab">
    <div class="table-header">
        <div>
            <h3 class="table-title">Daily Time Record</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $periodDisplay }} · {{ count($attendanceRecords) }} records</p>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table attendance-summary-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Employee</th>
                    <th style="width: 15%;">Department</th>
                    <th style="width: 5%; text-align: center;">Present</th>
                    <th style="width: 5%; text-align: center;">Leave</th>
                    <th style="width: 5%; text-align: center;">Absent</th>
                    <th style="width: 5%; text-align: center;">Late</th>
                    <th style="width: 5%; text-align: center;">½ Day</th>
                    <th style="width: 5%; text-align: center;">OT</th>
                    <th style="width: 12%;">Rate</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 13%; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody id="attendanceSummaryBody">
                @foreach($attendanceRecords as $index => $record)
                <tr data-department="{{ $record['dept'] }}" data-status="{{ $record['status'] }}" data-name="{{ $record['name'] }}" data-id="{{ $record['id'] }}">
                    <td>
                        <div class="emp-cell">
                            @if(isset($record['photo']) && $record['photo'])
                                <img src="{{ $record['photo'] }}" alt="{{ $record['name'] }}" class="emp-avatar" style="width:42px; height:42px; border-radius:50%; object-fit:cover;">
                            @else
                                <div class="emp-avatar" style="background: {{ $avatarColors[$index % count($avatarColors)] }}; width:42px; height:42px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#fff; font-weight:600; font-size:13px;">
                                    {{ getInitials($record['name']) }}
                                </div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $record['name'] }}</p>
                                <p class="emp-id">{{ $record['id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="dept-tag" data-tooltip="{{ $record['dept'] }}">{{ $record['dept'] }}</span></td>
                    <td class="num-cell num-present">{{ $record['present'] }}</td>
                    <td class="num-cell">
                        @if(isset($record['on_leave']) && $record['on_leave'] > 0)
                            <span class="num-leave">{{ $record['on_leave'] }}</span>
                        @else
                            <span class="num-muted">—</span>
                        @endif
                    </td>
                    <td class="num-cell {{ $record['absent'] > 0 ? 'num-absent' : 'num-muted' }}">{{ $record['absent'] }}</td>
                    <td class="num-cell {{ $record['late'] > 0 ? 'num-late' : 'num-muted' }}">{{ $record['late'] }}</td>
                    <td class="num-cell {{ $record['halfday'] > 0 ? 'num-late' : 'num-muted' }}">{{ $record['halfday'] }}</td>
                    <td class="num-cell {{ $record['overtime'] > 0 ? 'num-ot' : 'num-muted' }}">{{ $record['overtime'] > 0 ? $record['overtime'] : '—' }}</td>
                    <td>
                        <div class="rate-cell">
                            <div class="rate-track">
                                <div class="rate-fill {{ $record['rate'] >= 90 ? 'good' : ($record['rate'] >= 75 ? 'mid' : 'low') }}" style="width: {{ $record['rate'] }}%;"></div>
                            </div>
                            <span class="rate-pct">{{ $record['rate'] }}%</span>
                        </div>
                    </td>
                    <td><span class="badge-status {{ $record['status'] === 'Complete' ? 'processed' : 'pending' }}">{{ $record['status'] }}</span></td>
                    <td style="text-align: center;">
                        <div class="row-actions" style="justify-content: center; position: relative;">
                            <button class="act-btn" title="Actions" onclick="toggleActionMenu(event, 'action-menu-{{ $index }}')">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                            </button>
                            <div id="action-menu-{{ $index }}" class="action-dropdown" style="display:none;">
                                <button class="action-dropdown-item" onclick='openDTRModal(@json($record), {{ $index }}); closeAllActionMenus()'>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Quick View
                                </button>
                                <button class="action-dropdown-item" onclick="openDetailedDTRModal({{ $record['employee_id'] }}, '{{ addslashes($record['name']) }}', '{{ $record['id'] }}'); closeAllActionMenus()">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                                    Detailed DTR
                                </button>
                                <button class="action-dropdown-item" onclick='openEditModal(@json($record)); closeAllActionMenus()'>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="attendanceSummaryFooter">Showing <strong id="attendanceRowStart">1</strong>-<strong id="attendanceRowEnd">{{ min(10, count($attendanceRecords)) }}</strong> of <strong id="attendanceRowTotal">{{ count($attendanceRecords) }}</strong> records</p>
            <select id="attendanceRowsPerPage" class="rows-select" onchange="changeAttendanceRowsPerPage()">
                <option value="10">10 per page</option>
                <option value="25">25 per page</option>
                <option value="50">50 per page</option>
                <option value="100">100 per page</option>
            </select>
        </div>
        <div class="pagination" id="attendancePaginationControls"></div>
    </div>
</section>

<style>
/* ══ Liquid Glass refinement for the Daily Time Record card ══
   Scoped to #summary-tab only — doesn't touch other tabs/pages
   that share .table-section / .payroll-table / .action-dropdown.
   Includes its own tinted backdrop so the frosted-glass blur has
   something colorful to show through (the page behind it is flat
   gray, so blur alone was invisible without this). */
#summary-tab {
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
    transition: box-shadow .3s ease;
}

#summary-tab::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -1;
    border-radius: 22px;
    background:
        radial-gradient(420px 260px at 4% -10%, rgba(99,102,241,.20), transparent 60%),
        radial-gradient(380px 240px at 100% 0%, rgba(56,189,248,.18), transparent 60%),
        radial-gradient(520px 320px at 60% 115%, rgba(79,124,255,.14), transparent 60%);
}

#summary-tab:hover {
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.9),
        0 26px 55px rgba(11,4,77,.16),
        0 4px 14px rgba(15,23,42,.07) !important;
}

#summary-tab .table-header {
    background: transparent !important;
    border-bottom: 1px solid rgba(15,23,42,.07);
    border-radius: 22px 22px 0 0;
    padding: 20px 24px !important;
}

#summary-tab .table-title {
    letter-spacing: -.3px;
}

#summary-tab .payroll-table thead th {
    background: rgba(248,250,252,.65) !important;
    backdrop-filter: blur(14px) saturate(180%);
    -webkit-backdrop-filter: blur(14px) saturate(180%);
}

#summary-tab .payroll-table tbody tr:nth-child(even) { background: rgba(255,255,255,.3); }
#summary-tab .payroll-table tbody tr:hover { background: rgba(79,124,255,.1) !important; }

#summary-tab .emp-avatar {
    border: 2px solid rgba(255,255,255,.8);
    box-shadow: 0 4px 10px rgba(11,4,77,.14);
}

#summary-tab .dept-tag {
    background: rgba(227,236,255,.75) !important;
    border: 1px solid rgba(37,71,176,.12);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    max-width: 170px;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}

#summary-tab .badge-status {
    border: 1px solid transparent;
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
}
#summary-tab .badge-status.processed { background: rgba(233,249,239,.8) !important; border-color: rgba(35,135,90,.15); }
#summary-tab .badge-status.pending   { background: rgba(253,243,227,.8) !important; border-color: rgba(166,114,12,.15); }

#summary-tab .act-btn {
    border-radius: 999px !important;
    background: rgba(255,255,255,.65) !important;
    backdrop-filter: blur(10px) saturate(180%);
    -webkit-backdrop-filter: blur(10px) saturate(180%);
    border: 1px solid rgba(15,23,42,.08) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.8);
}
#summary-tab .act-btn:hover {
    background: linear-gradient(135deg, #1b1464, #0b044d) !important;
    color: #fff !important;
    border-color: transparent !important;
    box-shadow: 0 6px 16px rgba(11,4,77,.35);
    transform: translateY(-1px);
}

#summary-tab .action-dropdown {
    background: rgba(255,255,255,.88) !important;
    backdrop-filter: blur(28px) saturate(200%);
    -webkit-backdrop-filter: blur(28px) saturate(200%);
    border: 1px solid rgba(255,255,255,.75) !important;
    border-radius: 16px !important;
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.9),
        0 24px 50px rgba(15,23,42,.18),
        0 2px 8px rgba(15,23,42,.06) !important;
}
#summary-tab .action-dropdown-item { border-radius: 10px; }
#summary-tab .action-dropdown-item:hover { background: rgba(11,4,77,.08) !important; color: var(--pri); }

#summary-tab .table-footer {
    background: rgba(255,255,255,.4) !important;
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    border-top: 1px solid rgba(15,23,42,.07);
    border-radius: 0 0 22px 22px;
}

#summary-tab .rows-select {
    border-radius: 999px !important;
    background: rgba(255,255,255,.65) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(15,23,42,.08) !important;
}
</style>
