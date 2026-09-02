<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay ddtr-overlay" onclick="closeDetailedDTRModal()">
    <div class="ddtr-modal" onclick="event.stopPropagation()" role="dialog" aria-modal="true" aria-labelledby="ddtrModalTitle">

        {{-- ── HEADER ── --}}
        <div class="ddtr-header">
            {{-- Left: branding --}}
            <div class="ddtr-header-brand">
                <div class="ddtr-header-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <div class="ddtr-header-titles">
                    <span class="ddtr-title" id="ddtrModalTitle">Detailed Time Record</span>
                    <span class="ddtr-subtitle">PRIME HRIS · Attendance</span>
                </div>
                <span class="ddtr-period-pill" id="detailedPeriod">{{ $periodDisplay }}</span>
            </div>

            {{-- Right: close --}}
            <button class="ddtr-close" onclick="closeDetailedDTRModal()" aria-label="Close">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- ── SCROLLABLE BODY ── --}}
        <div class="ddtr-body">

            {{-- ── KPI CARDS ── --}}
            <section class="ddtr-kpis">
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon green">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Present</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiPresent">0</strong>
                        <span class="ddtr-kpi-sub" id="detailedKpiPresentSub">0 days</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon red">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Absent</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiAbsent">0</strong>
                        <span class="ddtr-kpi-sub">days missed</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon amber">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Late</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiLate">0</strong>
                        <span class="ddtr-kpi-sub" id="detailedKpiLateSub">0 min total</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon blue">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Leave</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiLeave">0</strong>
                        <span class="ddtr-kpi-sub">approved days</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon rose">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Undertime</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiUndertime">0</strong>
                        <span class="ddtr-kpi-sub">total</span>
                    </div>
                </div>
                <div class="ddtr-kpi">
                    <div class="ddtr-kpi-icon purple">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/><line x1="22" y1="2" x2="18" y2="6"/></svg>
                    </div>
                    <div class="ddtr-kpi-text">
                        <span class="ddtr-kpi-label">Overtime</span>
                        <strong class="ddtr-kpi-val" id="detailedKpiOvertime">0</strong>
                        <span class="ddtr-kpi-sub">extra hours</span>
                    </div>
                </div>
            </section>

            {{-- ── FILTER TOOLBAR ── --}}
            <div class="ddtr-toolbar">
                <span class="ddtr-toolbar-label">Date Range</span>
                <div class="ddtr-fld">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <input type="date" id="detailedStartDate" class="ddtr-input" aria-label="Start date">
                </div>
                <span class="ddtr-sep">to</span>
                <div class="ddtr-fld">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <input type="date" id="detailedEndDate" class="ddtr-input" aria-label="End date">
                </div>

                <div class="ddtr-toolbar-divider"></div>

                {{-- View dropdown --}}
                <div class="ddtr-view-wrap" id="ddtrViewWrap">
                    <button class="ddtr-view-btn" id="ddtrViewBtn" onclick="toggleDdtrDropdown()" aria-haspopup="true">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                        <span id="ddtrViewLabel">All Records</span>
                        <svg class="ddtr-caret" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>

                    <div class="ddtr-dropdown" id="ddtrDropdown">
                        <div class="ddtr-dd-section">Show</div>
                        <button class="ddtr-dd-item active" data-chip="all">All Records</button>
                        <div class="ddtr-dd-sep"></div>
                        <div class="ddtr-dd-section">By Day</div>
                        <button class="ddtr-dd-item" data-chip="mon">Mondays only</button>
                        <button class="ddtr-dd-item" data-chip="tue">Tuesdays only</button>
                        <button class="ddtr-dd-item" data-chip="wed">Wednesdays only</button>
                        <button class="ddtr-dd-item" data-chip="thu">Thursdays only</button>
                        <button class="ddtr-dd-item" data-chip="fri">Fridays only</button>
                        <button class="ddtr-dd-item" data-chip="weekdays">Weekdays (Mon–Fri)</button>
                        <button class="ddtr-dd-item" data-chip="weekend">Weekends only</button>
                        <div class="ddtr-dd-sep"></div>
                        <div class="ddtr-dd-section">By Status</div>
                        <button class="ddtr-dd-item" data-chip="present">
                            <span class="ddtr-dd-dot present"></span>Present
                        </button>
                        <button class="ddtr-dd-item" data-chip="absent">
                            <span class="ddtr-dd-dot absent"></span>Absent
                        </button>
                        <button class="ddtr-dd-item" data-chip="late">
                            <span class="ddtr-dd-dot late"></span>Late
                        </button>
                        <button class="ddtr-dd-item" data-chip="leave">
                            <span class="ddtr-dd-dot leave"></span>On Leave
                        </button>
                        <button class="ddtr-dd-item" data-chip="incomplete">
                            <span class="ddtr-dd-dot incomplete"></span>Incomplete
                        </button>
                    </div>

                </div>

                <div class="ddtr-toolbar-actions">
                    <button class="ddtr-btn-solid" onclick="loadDetailedDTR()">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        Apply
                    </button>
                    {{-- Both buttons produce the office's own "Employee
                         Attendance Logs" sheet for the range and View above
                         them — the same document, streamed for printing or
                         saved as a file. They replaced a CSV, which is not a
                         form anyone can sign or file. --}}
                    <button class="ddtr-btn-ghost" onclick="printDetailedDTR()" title="Open the printable DTR form">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                        Print Form
                    </button>
                    <button class="ddtr-btn-ghost" onclick="downloadDetailedDTR()" title="Download the DTR form as a PDF">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download PDF
                    </button>
                </div>
            </div>

            {{-- ── TABLE ── --}}
            <div class="ddtr-table-card">
                <div id="detailedDTRLoading" class="ddtr-loading">
                    <svg class="spin-icon" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#9aa1b5" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10" opacity=".25"/><path d="M12 2a10 10 0 0 1 10 10" opacity=".75"/>
                    </svg>
                    <p>Loading attendance records…</p>
                </div>

                <div class="ddtr-table-scroll">
                    <table class="detailed-dtr-table" id="detailedDTRTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>AM <span class="ddtr-th-hint">8:00 – 12:00</span></th>
                                <th>PM <span class="ddtr-th-hint">1:00 – 5:00</span></th>
                                <th>OT</th>
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
            </div>
        </div>

        {{-- ── FOOTER ── --}}
        <div class="ddtr-footer">
            <button class="ddtr-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
        </div>
    </div>
</div>
