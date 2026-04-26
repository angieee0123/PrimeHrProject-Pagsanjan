<!-- DTR Detail Modal -->
<div id="dtrModal" class="modal-overlay" style="display: none;" onclick="closeDTRModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DTR · <span id="dtrPeriod"></span></span>
                <h3 class="modal-title" id="dtrName"></h3>
                <p class="modal-sub"><span id="dtrPosition"></span> · <span id="dtrDept"></span></p>
            </div>
            <button class="modal-close" onclick="closeDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar lg" id="dtrAvatar" style="width: 60px; height: 60px; font-size: 20px;"></div>
                <div>
                    <p class="modal-emp-id" id="dtrEmpId"></p>
                    <span class="badge-status" id="dtrStatus"></span>
                </div>
            </div>

            <div class="modal-section-label">ATTENDANCE SUMMARY</div>
            <div class="modal-row"><span>Working Days</span><strong id="dtrWorkingDays"></strong></div>
            <div class="modal-row"><span>Days Present</span><strong style="color: #15803d;" id="dtrPresent"></strong></div>
            <div class="modal-row"><span>Days Absent</span><strong style="color: #8e1e18;" id="dtrAbsent"></strong></div>
            <div class="modal-row"><span>Late Arrivals</span><strong style="color: #a16207;" id="dtrLate"></strong></div>
            <div class="modal-row"><span>Half Days</span><strong style="color: #a16207;" id="dtrHalfday"></strong></div>

            <div class="modal-section-label" style="margin-top: 16px;">OVERTIME</div>
            <div class="modal-row"><span>Total OT Hours</span><strong style="color: #0b044d;" id="dtrOT"></strong></div>

            <div class="modal-net-row" style="margin-top: 16px;">
                <span>ATTENDANCE RATE</span>
                <strong id="dtrRate"></strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeDTRModal()">Close</button>
            <button class="modal-btn-primary" onclick="downloadDTR()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download DTR
            </button>
        </div>
    </div>
</div>
