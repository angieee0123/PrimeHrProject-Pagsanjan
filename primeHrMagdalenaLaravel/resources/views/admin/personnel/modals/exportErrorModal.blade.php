<!-- Export Error Modal -->
<div id="exportErrorModal" style="display:none; position:fixed; inset:0; background:rgba(15,12,40,0.42); -webkit-backdrop-filter:blur(8px) saturate(150%); backdrop-filter:blur(8px) saturate(150%); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:18px; width:100%; max-width:450px; padding:32px; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,.16), 0 2px 10px rgba(15,23,42,.05);">
        <div style="width:64px; height:64px; background:#fdedec; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#8e1e18" stroke-width="2.5">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h3 style="margin:0 0 12px; font-size:20px; font-weight:700; color:#0b044d;">Export Failed</h3>
        <p id="exportErrorMessage" style="margin:0 0 24px; font-size:14px; color:#56547a; line-height:1.6;"></p>
        <button onclick="closeExportErrorModal()" style="padding:12px 32px; background:#8e1e18; color:#fff; border:none; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","Helvetica Neue",Arial,sans-serif; transition:transform .15s ease;">
            Close
        </button>
    </div>
</div>
