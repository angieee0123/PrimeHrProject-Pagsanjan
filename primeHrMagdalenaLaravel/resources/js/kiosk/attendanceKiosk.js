/**
 * Attendance kiosk — the public, self-service attendance terminal.
 *
 * Unlike the staffed scanner it replaces, a scan here does not immediately
 * record anything. The badge is read, the server is asked who it belongs to and
 * which punch the day's schedule implies, and that answer is put on screen for
 * the employee to confirm. Only the Confirm button writes.
 *
 * That two-step shape is the whole difference between an operator terminal and
 * this one. With an operator, a human chose the slot and the badge was not
 * allowed to move the buttons; with nobody there, the server's inference is the
 * default and the employee is the check on it.
 *
 * Every judgement still lives on the server. This file captures a code, shows
 * what it is told, and posts a confirmation — it decides nothing about
 * attendance.
 */

const shell = document.querySelector('.kiosk-shell');

if (shell) {
    const PEEK_URL = shell.dataset.peekUrl;
    const PUNCH_URL = shell.dataset.punchUrl;
    const SLOT_LABELS = JSON.parse(shell.dataset.slotLabels || '{}');

    /**
     * A camera decodes the same badge many times a second while it is held up.
     * Without this, one person standing still would generate a burst of peeks;
     * there is no reason to send them.
     */
    const RESCAN_COOLDOWN_MS = 3000;

    /** How long a result stays up before the kiosk returns to idle. */
    const RESULT_LINGER_MS = 8000;

    const els = {
        clock: document.getElementById('kioskClock'),
        clockDate: document.getElementById('kioskClockDate'),
        status: document.getElementById('kioskStatus'),
        viewportIdle: document.getElementById('kioskViewportIdle'),
        startBtn: document.getElementById('kioskStartBtn'),
        startLabel: document.getElementById('kioskStartLabel'),
        manualForm: document.getElementById('kioskManualForm'),
        manualInput: document.getElementById('kioskManualInput'),
        panel: document.getElementById('kioskPanel'),
        idle: document.getElementById('kioskIdle'),
        confirm: document.getElementById('kioskConfirm'),
        confirmSlot: document.getElementById('kioskConfirmSlot'),
        confirmBtn: document.getElementById('kioskConfirmBtn'),
        cancelBtn: document.getElementById('kioskCancelBtn'),
        doneBtn: document.getElementById('kioskDoneBtn'),
        avatar: document.getElementById('kioskAvatar'),
        name: document.getElementById('kioskName'),
        meta: document.getElementById('kioskMeta'),
        slots: Array.from(document.querySelectorAll('.kiosk-slot')),
        result: document.getElementById('kioskResult'),
        message: document.getElementById('kioskMessage'),
        resultIdentity: document.getElementById('kioskResultIdentity'),
        resultAvatar: document.getElementById('kioskResultAvatar'),
        resultName: document.getElementById('kioskResultName'),
        resultMeta: document.getElementById('kioskResultMeta'),
        day: document.getElementById('kioskDay'),
        dayStrip: document.getElementById('kioskDayStrip'),
    };

    /** The code awaiting confirmation, and the slot currently proposed for it. */
    let pendingCode = null;
    let pendingSlot = null;

    let reader = null;
    let busy = false;
    let lastCode = null;
    let lastCodeAt = 0;
    let lingerTimer = null;

    // ---------- clock ----------

    function tick() {
        const now = new Date();
        els.clock.textContent = now.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        els.clockDate.textContent = now.toLocaleDateString([], { weekday: 'long', month: 'long', day: 'numeric' });
    }

    tick();
    setInterval(tick, 15000);

    // ---------- panel state ----------

    function setPanel(state) {
        els.panel.dataset.state = state;
        els.idle.hidden = state !== 'idle';
        els.confirm.hidden = state !== 'confirm';
        els.result.hidden = state !== 'result';
    }

    function reset({ clearCode = true } = {}) {
        if (lingerTimer) {
            clearTimeout(lingerTimer);
            lingerTimer = null;
        }

        if (clearCode) {
            pendingCode = null;
            pendingSlot = null;
        }

        setPanel('idle');
        paintSlots(null);
    }

    // ---------- camera ----------

    els.startBtn.addEventListener('click', () => {
        // A running camera is restarted rather than double-started; html5-qrcode
        // throws if `start` is called on an instance that is already scanning.
        if (reader) {
            stopCamera().then(startCamera);
            return;
        }

        startCamera();
    });

    async function startCamera() {
        if (typeof Html5Qrcode === 'undefined') {
            setStatus('error', 'Decoder unavailable');
            setPanel('result');
            showMessage('error', 'The QR reader could not be loaded. Use the scanner gun box, or ask HR for help.');
            return;
        }

        try {
            reader = new Html5Qrcode('kioskReader', { verbose: false });
            await reader.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 260, height: 260 } },
                onDecoded,
                () => {}, // per-frame decode misses are normal; nothing to report
            );

            els.viewportIdle.hidden = true;
            els.startLabel.textContent = 'Restart camera';
            setStatus('live', 'Scanning');
        } catch (error) {
            reader = null;
            setStatus('error', 'Camera blocked');
            setPanel('result');
            showMessage('error', `The camera could not be started: ${error?.message ?? error}. Allow camera access, or use the scanner gun box.`);
        }
    }

    async function stopCamera() {
        if (!reader) return;

        try {
            await reader.stop();
            await reader.clear();
        } catch {
            // Already stopped — nothing to unwind.
        }

        reader = null;
        els.viewportIdle.hidden = false;
        setStatus('idle', 'Camera off');
    }

    function onDecoded(text) {
        const now = Date.now();

        if (text === lastCode && now - lastCodeAt < RESCAN_COOLDOWN_MS) return;

        lastCode = text;
        lastCodeAt = now;
        identify(text);
    }

    // ---------- handheld / manual ----------

    els.manualForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const code = els.manualInput.value.trim();
        if (!code) return;

        els.manualInput.value = '';
        identify(code);
    });

    // ---------- identify (read-only) ----------

    async function identify(code) {
        if (busy) return;
        busy = true;

        try {
            const response = await fetch(PEEK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ code }),
            });

            const data = await response.json();

            if (!response.ok || data.status !== 'ok') {
                beep(false);
                setPanel('result');
                els.resultIdentity.hidden = true;
                els.day.hidden = true;
                showMessage('error', data.message ?? 'That badge could not be read.');
                linger();
                return;
            }

            pendingCode = code;
            pendingSlot = data.suggested_slot;
            paintSlots(pendingSlot);
            renderIdentity(els.avatar, els.name, els.meta, data.employee);
            els.confirmSlot.textContent = SLOT_LABELS[pendingSlot] ?? pendingSlot;
            setPanel('confirm');
            beep(true);
        } catch (error) {
            setPanel('result');
            els.resultIdentity.hidden = true;
            els.day.hidden = true;
            showMessage('error', `The badge could not be sent: ${error?.message ?? error}`);
            beep(false);
            linger();
        } finally {
            busy = false;
        }
    }

    // ---------- confirm (the only write) ----------

    els.confirmBtn.addEventListener('click', () => {
        if (pendingCode && pendingSlot) commit(pendingCode, pendingSlot);
    });

    els.cancelBtn.addEventListener('click', () => {
        reset();
        setStatus(reader ? 'live' : 'idle', reader ? 'Scanning' : 'Camera off');
    });

    els.doneBtn.addEventListener('click', () => {
        reset();
        setStatus(reader ? 'live' : 'idle', reader ? 'Scanning' : 'Camera off');
    });

    // Choosing a different punch does not write either — it only moves the slot
    // the Confirm button will send, so a mis-tap is still recoverable.
    els.slots.forEach((btn) => {
        btn.addEventListener('click', () => {
            pendingSlot = btn.dataset.slot;
            paintSlots(pendingSlot);
            els.confirmSlot.textContent = SLOT_LABELS[pendingSlot] ?? pendingSlot;
        });
    });

    function paintSlots(active) {
        els.slots.forEach((btn) => {
            const on = btn.dataset.slot === active;
            btn.classList.toggle('is-active', on);
            btn.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
    }

    async function commit(code, slot) {
        if (busy) return;
        busy = true;
        els.confirmBtn.disabled = true;

        try {
            const response = await fetch(PUNCH_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF(),
                    Accept: 'application/json',
                },
                body: JSON.stringify({ code, slot }),
            });

            const data = await response.json();

            setPanel('result');
            paintSlots(null);
            renderDay(data.day, data.slot);

            if (data.employee) {
                renderIdentity(els.resultAvatar, els.resultName, els.resultMeta, data.employee);
                els.resultIdentity.hidden = false;
            } else {
                els.resultIdentity.hidden = true;
            }

            showMessage(data.status ?? 'error', data.message ?? 'The punch could not be recorded.');
            beep(response.ok);

            pendingCode = null;
            pendingSlot = null;

            // A refused punch (leave, travel order, holiday) is the employee's
            // to act on, so it stays up longer than a confirmation.
            linger(response.ok ? RESULT_LINGER_MS : RESULT_LINGER_MS * 2);
        } catch (error) {
            setPanel('result');
            els.resultIdentity.hidden = true;
            showMessage('error', `The punch could not be sent: ${error?.message ?? error}`);
            beep(false);
            linger();
        } finally {
            busy = false;
            els.confirmBtn.disabled = false;
        }
    }

    function linger(ms = RESULT_LINGER_MS) {
        if (lingerTimer) clearTimeout(lingerTimer);

        lingerTimer = setTimeout(() => {
            lingerTimer = null;
            reset({ clearCode: false });
            setStatus(reader ? 'live' : 'idle', reader ? 'Scanning' : 'Camera off');
        }, ms);
    }

    // ---------- rendering ----------

    function setStatus(state, text) {
        els.status.dataset.state = state;
        els.status.textContent = text;
    }

    function renderIdentity(avatarEl, nameEl, metaEl, employee) {
        avatarEl.innerHTML = employee.photo
            ? `<img src="${escapeAttr(employee.photo)}" alt="">`
            : escapeHtml((employee.name || '?').charAt(0).toUpperCase());

        nameEl.textContent = employee.name ?? '';

        const parts = [employee.employee_id, employee.department, employee.designation].filter(Boolean);
        metaEl.textContent = parts.join(' · ');
    }

    function renderDay(day, currentSlot) {
        if (!day) {
            els.day.hidden = true;
            return;
        }

        els.day.hidden = false;
        els.dayStrip.innerHTML = Object.entries(day)
            .map(([slot, value]) => {
                const classes = ['kiosk-day-cell'];
                if (!value) classes.push('is-empty');
                if (slot === currentSlot) classes.push('is-current');

                return `<div class="${classes.join(' ')}">
                    <span>${escapeHtml(SLOT_LABELS[slot] ?? slot)}</span>
                    <strong>${escapeHtml(value ?? '—')}</strong>
                </div>`;
            })
            .join('');
    }

    function showMessage(state, message) {
        els.result.dataset.state = state;
        els.message.textContent = message;
    }

    /**
     * A turnstile tone. The employee is pocketing their badge, not reading the
     * screen, so the outcome has to be audible.
     */
    function beep(ok) {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.frequency.value = ok ? 880 : 220;
            gain.gain.setValueAtTime(0.06, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.18);

            osc.connect(gain).connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.18);
            osc.onended = () => ctx.close();
        } catch {
            // Audio is a courtesy; the panel already shows the outcome.
        }
    }

    function CSRF() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    }

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function escapeAttr(value) {
        return String(value ?? '').replace(/"/g, '&quot;');
    }

    // ---------- boot ----------

    // A wall-mounted kiosk has nobody to tap "start", so the camera comes up on
    // its own. The button stays as the recovery path when permission is refused
    // or the device has no camera at all.
    startCamera();

    // Releases the camera when the tab is closed or navigated away from.
    window.addEventListener('beforeunload', () => {
        if (reader) reader.stop().catch(() => {});
    });

    // A kiosk is left focused on the scanner, so typing anywhere goes to the
    // handheld input and a scanner gun works without clicking the box first.
    document.addEventListener('keydown', (event) => {
        if (event.target === els.manualInput) return;
        if (event.target.matches('input, textarea, select, button')) return;
        if (event.key.length !== 1) return;

        els.manualInput.focus();
    });
}
