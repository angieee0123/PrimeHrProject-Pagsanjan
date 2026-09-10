/**
 * The Attendance Kiosk address modal.
 *
 * The kiosk is a public URL, so there is no page in the admin area to link to —
 * what staff need from here is the address itself, and a QR that opens it on
 * the tablet without anyone typing a 60-character token.
 *
 * The QR is rendered on first open rather than on page load: this modal sits on
 * the busiest admin page in the system, and most visits never open it.
 */

const kioskModal = document.getElementById('kioskLinkModal');
const kioskUrlField = document.getElementById('kioskUrlField');
const kioskQrTarget = document.getElementById('kioskQrTarget');
const kioskCopyBtn = document.getElementById('kioskCopyBtn');

let kioskQrRendered = false;

window.openKioskLinkModal = function () {
    if (!kioskModal) return;

    // qrcodejs is loaded from a CDN on this page. If it did not arrive, the URL
    // field and the copy button still work — the QR is the convenience, not the
    // feature, so a missing CDN must not leave an empty modal.
    if (!kioskQrRendered && typeof QRCode !== 'undefined' && kioskQrTarget) {
        new QRCode(kioskQrTarget, {
            text: kioskUrlField.value,
            width: 168,
            height: 168,
            correctLevel: QRCode.CorrectLevel.M,
        });

        kioskQrRendered = true;
    }

    kioskModal.hidden = false;
    document.body.style.overflow = 'hidden';
};

window.closeKioskLinkModal = function () {
    if (!kioskModal) return;

    kioskModal.hidden = true;
    document.body.style.overflow = '';
};

window.copyKioskUrl = async function () {
    if (!kioskUrlField) return;

    try {
        await navigator.clipboard.writeText(kioskUrlField.value);
        flashCopyButton('Copied');
    } catch {
        // The Clipboard API needs a secure context, and a municipal install may
        // well be reached over plain http on the LAN. Selecting the text is the
        // honest fallback: it does not claim to have copied anything.
        kioskUrlField.select();
        flashCopyButton('Press Ctrl+C');
    }
};

function flashCopyButton(label) {
    if (!kioskCopyBtn) return;

    const original = kioskCopyBtn.textContent;
    kioskCopyBtn.textContent = label;

    setTimeout(() => {
        kioskCopyBtn.textContent = original;
    }, 1800);
}

if (kioskModal) {
    // Clicking the backdrop closes; clicking the card must not.
    kioskModal.addEventListener('click', (event) => {
        if (event.target === kioskModal) window.closeKioskLinkModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !kioskModal.hidden) window.closeKioskLinkModal();
    });
}
