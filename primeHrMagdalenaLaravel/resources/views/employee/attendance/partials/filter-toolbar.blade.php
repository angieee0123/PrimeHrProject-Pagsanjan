                {{-- ── FILTER TOOLBAR ── --}}
                <div class="ddtr-toolbar">
                    <span class="ddtr-toolbar-label">Date Range</span>
                    <div class="ddtr-fld">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="detailedStartDate" class="ddtr-input" aria-label="Start date" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                    </div>
                    <span class="ddtr-sep">to</span>
                    <div class="ddtr-fld">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <input type="date" id="detailedEndDate" class="ddtr-input" aria-label="End date" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
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
                                <span class="ddtr-dd-dot" style="background:#2fa860"></span>Present
                            </button>
                            <button class="ddtr-dd-item" data-chip="absent">
                                <span class="ddtr-dd-dot" style="background:#e5484d"></span>Absent
                            </button>
                            <button class="ddtr-dd-item" data-chip="late">
                                <span class="ddtr-dd-dot" style="background:#eba417"></span>Late
                            </button>
                            <button class="ddtr-dd-item" data-chip="leave">
                                <span class="ddtr-dd-dot" style="background:#4F7CFF"></span>On Leave
                            </button>
                            <button class="ddtr-dd-item" data-chip="incomplete">
                                <span class="ddtr-dd-dot" style="background:#7C5CFF"></span>Incomplete
                            </button>
                        </div>
                    </div>

                    <div class="ddtr-toolbar-actions">
                        <button class="ddtr-btn-solid" onclick="loadDetailedDTR()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                            Apply
                        </button>
                        <button class="ddtr-btn-ghost" onclick="resetDetailedDTR()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            Reset
                        </button>
                        <button class="ddtr-btn-ghost" onclick="exportDetailedDTR()">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Export
                        </button>
                    </div>
                </div>
