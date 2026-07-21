<!-- DTR Detail Modal -->
<x-modal id="dtrModal" close="closeDTRModal" title-id="dtrName">
    <x-slot:eyebrow>DTR · <span id="dtrPeriod"></span></x-slot:eyebrow>
    <x-slot:subtitle><span id="dtrPosition"></span> · <span id="dtrDept"></span></x-slot:subtitle>
    <div class="modal-body">
        <div class="modal-emp-row">
            <div class="emp-avatar lg" id="dtrAvatar"></div>
            <div>
                <p class="modal-emp-id" id="dtrEmpId"></p>
                <span class="badge-status" id="dtrStatus"></span>
            </div>
        </div>

        <div class="modal-section-label">SELECT DATE RANGE</div>
        <div class="dtr-date-grid">
            <div>
                <label class="dtr-field-label">Start Date</label>
                <input type="date" id="dtrStartDate" class="dtr-date-input">
            </div>
            <div>
                <label class="dtr-field-label">End Date</label>
                <input type="date" id="dtrEndDate" class="dtr-date-input">
            </div>
        </div>
        <button onclick="loadDTRSummary()" class="dtr-load-btn">Load Data</button>

        <div class="modal-section-label gp-mt-20">ATTENDANCE SUMMARY</div>
        <div class="modal-row"><span>Working Days</span><strong id="dtrWorkingDays"></strong></div>
        <div class="modal-row"><span>Days Present</span><strong class="text-success" id="dtrPresent"></strong></div>
        <div class="modal-row"><span>Days Absent</span><strong class="text-danger" id="dtrAbsent"></strong></div>
        <div class="modal-row"><span>Late Arrivals</span><strong class="text-warning" id="dtrLate"></strong></div>
        <div class="modal-row"><span>Half Days</span><strong class="text-warning" id="dtrHalfday"></strong></div>

        <div class="modal-section-label gp-mt-16">OVERTIME</div>
        <div class="modal-row"><span>Total OT Hours</span><strong class="text-primary-ink" id="dtrOT"></strong></div>

        <div class="modal-net-row gp-mt-16">
            <span>ATTENDANCE RATE</span>
            <strong id="dtrRate"></strong>
        </div>
    </div>
    <div class="modal-footer">
        <x-modal-btn variant="ghost" onclick="closeDTRModal()">Close</x-modal-btn>
        <x-modal-btn onclick="downloadDTR()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Download DTR
        </x-modal-btn>
    </div>
</x-modal>
