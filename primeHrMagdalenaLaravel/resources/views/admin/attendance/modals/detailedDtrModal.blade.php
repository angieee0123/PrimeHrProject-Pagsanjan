<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="modal-box modal-box-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DETAILED DTR · <span id="detailedPeriod">{{ $periodDisplay }}</span></span>
                <h3 class="modal-title" id="detailedName"></h3>
                <p class="modal-sub" id="detailedEmpId"></p>
            </div>
            <button class="modal-close" onclick="closeDetailedDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="detailed-dtr-filters" style="padding: 16px 24px; border-bottom: 1px solid #f0effe; display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 12px; font-weight: 600; color: #6b6a8a;">Date Range:</span>
                <input type="date" id="detailedStartDate" class="filter-select-sm" style="width: auto;">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" id="detailedEndDate" class="filter-select-sm" style="width: auto;">
                <button class="btn-filter" onclick="loadDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                <div style="flex: 1;"></div>
                <button class="btn-export-sm" onclick="exportDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </button>
            </div>
            <div style="max-height: 500px; overflow-y: auto; padding: 24px;">
                <div id="detailedDTRLoading" style="text-align: center; padding: 40px; color: #9999bb;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin: 0 auto;">
                        <circle cx="12" cy="12" r="10" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/>
                    </svg>
                    <p style="margin-top: 12px;">Loading attendance records...</p>
                </div>
                <table class="detailed-dtr-table" id="detailedDTRTable" style="display: none;">
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
                            <th>Total Hours</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="detailedDTRBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer" style="background: #fafafe; border-top: 1px solid #f0effe;">
            <div style="flex: 1; display: flex; gap: 16px; font-size: 12px;">
                <div><span style="color: #9999bb;">Total Days:</span> <strong id="detailedTotalDays" style="color: #0b044d;">0</strong></div>
                <div><span style="color: #9999bb;">Present:</span> <strong id="detailedTotalPresent" style="color: #15803d;">0</strong></div>
                <div><span style="color: #9999bb;">Absent:</span> <strong id="detailedTotalAbsent" style="color: #8e1e18;">0</strong></div>
                <div><span style="color: #9999bb;">Late:</span> <strong id="detailedTotalLate" style="color: #a16207;">0 times</strong></div>
                <div><span style="color: #9999bb;">Total Late:</span> <strong id="detailedTotalLateMinutes" style="color: #a16207;">0 min</strong></div>
                <div><span style="color: #9999bb;">Total Undertime:</span> <strong id="detailedTotalUndertime" style="color: #8e1e18;">0 min</strong></div>
            </div>
            <button class="modal-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
        </div>
    </div>
</div>
