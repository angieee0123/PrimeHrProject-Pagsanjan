<div id="dept-modal" class="adm-overlay" onclick="closeDeptModal()">
    <div class="adm-box" style="max-width:520px;" onclick="event.stopPropagation()">

        <div class="adm-header">
            <div class="adm-header-left">
                <div class="vdm-avatar" id="modal-avatar"></div>
                <div>
                    <span class="adm-eyebrow" id="modal-eyebrow">DEPARTMENT DETAIL</span>
                    <h3 class="adm-title" id="modal-title"></h3>
                </div>
            </div>
            <button class="adm-close" onclick="closeDeptModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="vdm-stats">
            <div class="vdm-stat">
                <div class="vdm-stat-icon" style="background:linear-gradient(135deg,#0b044d,#2d1a8e)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                </div>
                <div>
                    <p class="vdm-stat-label">Office Code</p>
                    <p class="vdm-stat-val" id="modal-code"></p>
                </div>
            </div>
            <div class="vdm-stat">
                <div class="vdm-stat-icon" style="background:linear-gradient(135deg,#15803d,#22c55e)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <p class="vdm-stat-label">Personnel</p>
                    <p class="vdm-stat-val" style="color:#15803d" id="modal-personnel-count"></p>
                </div>
            </div>
            <div class="vdm-stat">
                <div class="vdm-stat-icon" style="background:linear-gradient(135deg,#d9bb00,#fbbf24)">
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
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Department Head
                </span>
                <strong id="modal-head"></strong>
            </div>
            <div class="vdm-row">
                <span class="vdm-row-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Municipality
                </span>
                <strong>Municipal Government of Pagsanjan</strong>
            </div>
            <div class="vdm-row" id="modal-desc-row">
                <span class="vdm-row-label">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Description
                </span>
                <span id="modal-desc" style="color:#6b6a8a;font-size:12.5px;text-align:right;max-width:60%;"></span>
            </div>
        </div>

        <div class="adm-footer">
            <button class="adm-btn-ghost" onclick="closeDeptModal()">Close</button>
        </div>
    </div>
</div>
