{{-- Where staff get the kiosk's address.

     The attendance terminal is no longer a page in this area — it is a public
     URL that needs no login, because the person using it is an employee with no
     account in front of them. That makes this modal the handover point: the
     address to paste into the tablet, and a QR to open it on the tablet without
     typing a 60-character token by hand.

     The token is derived from APP_KEY rather than stored, so it is regenerated
     — and this URL invalidated — by `php artisan key:generate`. That is the
     intended revocation path, and the same one that invalidates every printed
     QR badge, so the note below says so where somebody will actually read it. --}}
<div id="kioskLinkModal" class="kiosk-modal" hidden>
    <div class="kiosk-modal-card">
        <div class="kiosk-modal-head">
            <div>
                <h3>Attendance Kiosk</h3>
                <p>The self-service terminal. No login required.</p>
            </div>
            <button type="button" class="kiosk-modal-close" onclick="closeKioskLinkModal()" aria-label="Close">&times;</button>
        </div>

        <div class="kiosk-modal-qr">
            <div id="kioskQrTarget"></div>
        </div>

        <label class="kiosk-modal-label" for="kioskUrlField">Kiosk address</label>
        <div class="kiosk-modal-url">
            <input type="text" id="kioskUrlField" value="{{ $kioskUrl }}" readonly spellcheck="false">
            <button type="button" id="kioskCopyBtn" onclick="copyKioskUrl()">Copy</button>
        </div>

        <p class="kiosk-modal-note">
            <strong>Treat this address as a credential.</strong> Anyone who has it can open the terminal,
            though recording a punch still needs a real QR badge. Regenerating the application key
            (<code>php artisan key:generate</code>) replaces it — and reissues every employee badge at the same time.
        </p>

        <a href="{{ $kioskUrl }}" target="_blank" rel="noopener" class="kiosk-modal-open">Open the kiosk in a new tab</a>
    </div>
</div>

@push('styles')
<style>
    /* Scoped to #kioskLinkModal. Colours are theme variables, so the modal
       follows Settings → Appearance like everything else. */
    .kiosk-modal {
        position: fixed;
        inset: 0;
        z-index: 2000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        background: rgba(var(--theme-shadow-rgb), .42);
        -webkit-backdrop-filter: blur(8px) saturate(150%);
        backdrop-filter: blur(8px) saturate(150%);
    }

    .kiosk-modal[hidden] { display: none; }

    .kiosk-modal-card {
        width: 100%;
        max-width: 460px;
        padding: 26px;
        border-radius: var(--gp-radius-card);
        background: var(--gp-glass-strong);
        border: 1px solid var(--theme-border);
        box-shadow: 0 20px 50px rgba(var(--theme-shadow-rgb), .18);
    }

    .kiosk-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 18px;
    }

    .kiosk-modal-head h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 700;
        color: var(--theme-ink);
    }

    .kiosk-modal-head p {
        margin: 3px 0 0;
        font-size: 12.5px;
        color: var(--theme-text-soft);
    }

    .kiosk-modal-close {
        flex: 0 0 32px;
        width: 32px;
        height: 32px;
        border: none;
        border-radius: 9px;
        background: transparent;
        color: var(--theme-text-mid);
        font-size: 24px;
        line-height: 1;
        cursor: pointer;
    }

    .kiosk-modal-close:hover { background: var(--theme-neutral-100); color: var(--theme-ink); }

    .kiosk-modal-qr {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 180px;
        padding: 18px;
        margin-bottom: 18px;
        border-radius: 14px;
        background: var(--theme-bg-tint);
        border: 1px solid var(--theme-border);
    }

    /* qrcodejs emits an <img> or a <canvas>; either way it should not overflow. */
    .kiosk-modal-qr img,
    .kiosk-modal-qr canvas { display: block; max-width: 100%; height: auto; }

    .kiosk-modal-label {
        display: block;
        margin-bottom: 6px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
        color: var(--theme-text-soft);
    }

    .kiosk-modal-url { display: flex; gap: 8px; }

    .kiosk-modal-url input {
        flex: 1;
        min-width: 0;
        padding: 11px 13px;
        border-radius: 11px;
        border: 1px solid var(--theme-border);
        background: var(--gp-glass-strong);
        color: var(--theme-ink);
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 12.5px;
    }

    .kiosk-modal-url button {
        flex: 0 0 auto;
        padding: 11px 17px;
        border-radius: 11px;
        border: 1px solid var(--theme-btn-primary-bg);
        background: var(--theme-btn-primary-bg);
        color: var(--theme-btn-primary-fg);
        font-family: inherit;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
    }

    .kiosk-modal-note {
        margin: 14px 0 0;
        padding: 11px 13px;
        border-radius: 11px;
        background: var(--theme-warning-subtle);
        border: 1px solid var(--theme-warning-border);
        color: var(--theme-warning-emphasis);
        font-size: 12.5px;
        line-height: 1.5;
    }

    .kiosk-modal-note code {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-size: 11.5px;
    }

    .kiosk-modal-open {
        display: block;
        margin-top: 16px;
        text-align: center;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--theme-link);
    }
</style>
@endpush
