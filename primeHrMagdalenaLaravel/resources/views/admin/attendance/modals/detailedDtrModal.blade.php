<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="modal-box modal-box-wide ddtr-modal" onclick="event.stopPropagation()">

        {{-- ── HEADER ── --}}
        <div class="ddtr-header">
            <div class="ddtr-header-left">
                <div class="ddtr-avatar" id="detailedAvatar"></div>
                <div>
                    <p class="ddtr-eyebrow">DETAILED TIME RECORD &nbsp;·&nbsp; <span id="detailedPeriod">{{ $periodDisplay }}</span></p>
                    <h3 class="ddtr-name" id="detailedName">—</h3>
                    <p class="ddtr-meta" id="detailedEmpId">—</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeDetailedDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- ── FILTER BAR ── --}}
        <div class="ddtr-filter-bar">
            <div class="ddtr-filter-group">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" id="detailedStartDate" class="filter-select-sm">
                <span class="ddtr-sep">to</span>
                <input type="date" id="detailedEndDate" class="filter-select-sm">
            </div>
            <div class="ddtr-filter-actions">
                <button class="btn-filter" onclick="loadDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                <button class="btn-export-sm" onclick="exportDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </button>
            </div>
        </div>

        {{-- ── TABLE ── --}}
        <div class="ddtr-table-wrap">
            <div id="detailedDTRLoading" class="ddtr-loading">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2.5" style="animation:spin 1s linear infinite">
                    <circle cx="12" cy="12" r="10" opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" opacity=".75"/>
                </svg>
                <p>Loading attendance records…</p>
            </div>

            <table class="detailed-dtr-table" id="detailedDTRTable" style="display:none;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>AM In</th>
                        <th>AM Out</th>
                        <th>PM In</th>
                        <th>PM Out</th>
                        <th>OT In</th>
                        <th>OT Out</th>
                        <th>Undertime</th>
                        <th>Late</th>
                        <th>Total Hrs</th>
                        <th>Accredited Hrs</th>
                        <th>Leave Deduction</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="detailedDTRBody"></tbody>
            </table>
        </div>

        {{-- ── FOOTER / SUMMARY ── --}}
        <div class="ddtr-footer">
            <div class="ddtr-stats">
                <div class="ddtr-stat">
                    <span class="ddtr-stat-label">Total Days</span>
                    <strong class="ddtr-stat-val" id="detailedTotalDays">0</strong>
                </div>
                <div class="ddtr-stat-divider"></div>
                <div class="ddtr-stat">
                    <span class="ddtr-stat-label">Present</span>
                    <strong class="ddtr-stat-val" style="color:#15803d;" id="detailedTotalPresent">0</strong>
                </div>
                <div class="ddtr-stat-divider"></div>
                <div class="ddtr-stat">
                    <span class="ddtr-stat-label">Absent</span>
                    <strong class="ddtr-stat-val" style="color:#dc2626;" id="detailedTotalAbsent">0</strong>
                </div>
                <div class="ddtr-stat-divider"></div>
                <div class="ddtr-stat">
                    <span class="ddtr-stat-label">Late</span>
                    <strong class="ddtr-stat-val" style="color:#d97706;" id="detailedTotalLate">0×</strong>
                </div>
                <div class="ddtr-stat-divider"></div>
                <div class="ddtr-stat">
                    <span class="ddtr-stat-label">Total Late</span>
                    <strong class="ddtr-stat-val" style="color:#d97706;" id="detailedTotalLateMinutes">0 min</strong>
                </div>
                <div class="ddtr-stat-divider"></div>
                <div class="ddtr-stat">
                    <span class="ddtr-stat-label">Undertime</span>
                    <strong class="ddtr-stat-val" style="color:#dc2626;" id="detailedTotalUndertime">0 min</strong>
                </div>
            </div>
            <button class="modal-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
        </div>
    </div>
</div>

<style>
/* ── DETAILED DTR MODAL ── */
.ddtr-modal { display: flex; flex-direction: column; max-height: 92vh; overflow: hidden; }

/* Header */
.ddtr-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f0effe;
    flex-shrink: 0;
}
.ddtr-header-left { display: flex; align-items: center; gap: 14px; }
.ddtr-avatar {
    width: 46px; height: 46px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, #0b044d, #1a0f6e);
    color: #fff; font-size: 15px; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    letter-spacing: .5px;
}
.ddtr-eyebrow { font-size: 10.5px; font-weight: 700; color: #9999bb; letter-spacing: .5px; text-transform: uppercase; margin: 0 0 3px; }
.ddtr-name { font-size: 18px; font-weight: 800; color: #0b044d; margin: 0 0 2px; letter-spacing: -.3px; }
.ddtr-meta { font-size: 12px; color: #6b6a8a; margin: 0; font-weight: 500; }

/* Filter bar */
.ddtr-filter-bar {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 10px 24px;
    background: #fafafe;
    border-bottom: 1px solid #f0effe;
    flex-shrink: 0;
}
.ddtr-filter-group { display: flex; align-items: center; gap: 8px; }
.ddtr-filter-group svg { color: #9999bb; flex-shrink: 0; }
.ddtr-sep { font-size: 11px; font-weight: 600; color: #9999bb; }
.ddtr-filter-actions { display: flex; align-items: center; gap: 8px; }

/* Table wrapper */
.ddtr-table-wrap { flex: 1; overflow-y: auto; overflow-x: auto; min-height: 200px; }
.ddtr-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; padding: 48px; color: #9999bb; font-size: 13px; }

/* Footer */
.ddtr-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 16px;
    padding: 12px 24px;
    border-top: 1px solid #f0effe;
    background: #fafafe;
    flex-shrink: 0;
    flex-wrap: wrap;
}
.ddtr-stats { display: flex; align-items: center; gap: 0; flex-wrap: wrap; }
.ddtr-stat { display: flex; flex-direction: column; align-items: center; padding: 0 16px; }
.ddtr-stat-label { font-size: 10px; font-weight: 700; color: #9999bb; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 2px; }
.ddtr-stat-val { font-size: 15px; font-weight: 800; color: #0b044d; }
.ddtr-stat-divider { width: 1px; height: 28px; background: #e8e7f5; flex-shrink: 0; }
</style>
