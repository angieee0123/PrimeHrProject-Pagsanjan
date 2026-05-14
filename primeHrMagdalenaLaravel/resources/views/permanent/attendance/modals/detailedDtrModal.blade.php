<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="modal-box" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DTR · <span id="detailedPeriod">JUNE 2025</span></span>
                <h3 class="modal-title" id="detailedName">Ana R. Reyes</h3>
                <p class="modal-sub"><span id="detailedPosition">Nurse II</span> · <span id="detailedDept">Municipal Health Office</span></p>
            </div>
            <button class="modal-close" onclick="closeDetailedDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="modal-emp-row">
                <div class="emp-avatar lg" id="detailedAvatar" style="width: 60px; height: 60px; font-size: 20px;">AR</div>
                <div>
                    <p class="modal-emp-id" id="detailedEmpId">PGS-0115</p>
                    <span class="badge-status processed" id="detailedStatus">Complete</span>
                </div>
            </div>

            <div class="modal-section-label">ATTENDANCE SUMMARY</div>
            <div class="modal-row"><span>Working Days</span><strong id="detailedWorkingDays">18 days</strong></div>
            <div class="modal-row"><span>Days Present</span><strong style="color: #15803d;" id="detailedPresent">17 days</strong></div>
            <div class="modal-row"><span>Days Absent</span><strong style="color: #8e1e18;" id="detailedAbsent">1 day</strong></div>
            <div class="modal-row"><span>Late Arrivals</span><strong style="color: #a16207;" id="detailedLate">1 time</strong></div>
            <div class="modal-row"><span>Half Days</span><strong style="color: #a16207;" id="detailedHalfday">0</strong></div>

            <div class="modal-section-label" style="margin-top: 16px;">OVERTIME</div>
            <div class="modal-row"><span>Total OT Hours</span><strong style="color: #0b044d;" id="detailedOT">3 hrs</strong></div>

            <div class="modal-net-row" style="margin-top: 16px;">
                <span>ATTENDANCE RATE</span>
                <strong id="detailedRate">94%</strong>
            </div>
        </div>
        <div class="modal-footer">
            <button class="modal-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
            <button class="modal-btn-primary" onclick="downloadDetailedDTR()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download DTR
            </button>
        </div>
    </div>
</div>

<script>
function closeDetailedDTRModal() {
    document.getElementById('detailedDTRModal').style.display = 'none';
}

function downloadDetailedDTR() {
    alert('Download DTR functionality - to be implemented');
}
</script>
