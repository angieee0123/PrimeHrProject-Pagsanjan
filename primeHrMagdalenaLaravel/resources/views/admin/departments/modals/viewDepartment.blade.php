<x-adm-modal id="dept-modal" close="closeDeptModal" max-width="520px" eyebrow="DEPARTMENT DETAIL" eyebrow-id="modal-eyebrow" title-id="modal-title">
    <x-slot:icon>
        <div class="vdm-avatar" id="modal-avatar"></div>
    </x-slot:icon>

    <div class="vdm-stats">
        <div class="vdm-stat">
            <div class="vdm-stat-icon vdm-stat-icon-navy">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
                <p class="vdm-stat-label">Office Code</p>
                <p class="vdm-stat-val" id="modal-code"></p>
            </div>
        </div>
        <div class="vdm-stat">
            <div class="vdm-stat-icon vdm-stat-icon-green">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <p class="vdm-stat-label">Personnel</p>
                <p class="vdm-stat-val vdm-stat-val-green" id="modal-personnel-count"></p>
            </div>
        </div>
        <div class="vdm-stat">
            <div class="vdm-stat-icon vdm-stat-icon-gold">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <p class="vdm-stat-label">Status</p>
                <p class="vdm-stat-val" id="modal-status-badge"></p>
            </div>
        </div>
    </div>

    <div class="vdm-body">
        <p class="vdm-section-label">OFFICE INFORMATION</p>
        <div class="vdm-row">
            <span class="vdm-row-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8f8daf" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Department Head
            </span>
            <strong id="modal-head"></strong>
        </div>
        <div class="vdm-row">
            <span class="vdm-row-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8f8daf" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Municipality
            </span>
            <strong>Municipal Government of Pagsanjan</strong>
        </div>
        <div class="vdm-row" id="modal-desc-row">
            <span class="vdm-row-label">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#8f8daf" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Description
            </span>
            <span id="modal-desc" class="vdm-desc-val"></span>
        </div>
    </div>

    <div class="adm-footer">
        <button class="adm-btn-ghost" onclick="closeDeptModal()">Close</button>
    </div>
</x-adm-modal>
