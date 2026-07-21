<!-- QR Code Modal -->
<div id="qrCodeModal" style="display:none; position:fixed; inset:0; background:rgba(15,12,40,0.42); -webkit-backdrop-filter:blur(8px) saturate(150%); backdrop-filter:blur(8px) saturate(150%); z-index:2000; align-items:center; justify-content:center;">
    <div style="background:#fff; border-radius:18px; width:100%; max-width:500px; padding:32px; text-align:center; box-shadow:0 20px 50px rgba(15,23,42,.16), 0 2px 10px rgba(15,23,42,.05);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0; font-size:20px; font-weight:700; color:#0b044d;">Employee QR Code</h3>
            <button onclick="closeQRModal()" style="background:transparent; border:none; border-radius:9px; color:#56547a; font-size:24px; cursor:pointer; width:32px; height:32px; display:flex; align-items:center; justify-content:center; transition:background .2s cubic-bezier(.4,0,.2,1);">&times;</button>
        </div>

        <div style="background:#f7f6fc; border:2px solid #ecebf6; border-radius:14px; padding:24px; margin-bottom:20px;">
            <p style="margin:0 0 8px; font-size:14px; font-weight:600; color:#0b044d;" id="qrEmployeeName"></p>
            <p style="margin:0 0 16px; font-size:12px; color:#56547a;" id="qrEmployeeId"></p>
            <div id="qrCodeContainer" style="display:flex; justify-content:center; align-items:center; min-height:300px;">
                <p style="color:#56547a;">Generating QR Code...</p>
            </div>
        </div>

        <div style="display:flex; gap:10px;">
            <button onclick="downloadQRCode()" style="flex:1; padding:12px; background:#0b044d; color:#fff; border:none; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","Helvetica Neue",Arial,sans-serif; display:flex; align-items:center; justify-content:center; gap:8px; transition:background .2s cubic-bezier(.4,0,.2,1);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Download
            </button>
            <button onclick="printQRCode()" style="flex:1; padding:12px; background:#15803d; color:#fff; border:none; border-radius:9px; font-size:14px; font-weight:600; cursor:pointer; font-family:-apple-system,BlinkMacSystemFont,"SF Pro Display","SF Pro Text","Helvetica Neue",Arial,sans-serif; display:flex; align-items:center; justify-content:center; gap:8px; transition:background .2s cubic-bezier(.4,0,.2,1);">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print
            </button>
        </div>
    </div>
</div>
