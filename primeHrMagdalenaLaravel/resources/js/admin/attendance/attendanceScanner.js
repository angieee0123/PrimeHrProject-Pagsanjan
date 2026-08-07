/**
 * Attendance scanner kiosk.
 *
 * Reads an employee QR badge from the camera (or a USB scanner gun, which
 * types the payload and presses Enter), then posts it with the slot the
 * operator selected. All the judgement lives on the server — this file decides
 * nothing about attendance, it only captures and displays.
 */

const page = document.querySelector('.scan-page');

if (page) {
    const PUNCH_URL = page.dataset.punchUrl;
    const SUGGEST_URL = page.dataset.suggestUrl;
    const RECENT_URL = page.dataset.recentUrl;
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const SLOT_LABELS = {
        am_in: 'AM In',
        am_out: 'AM Out',
        pm_in: 'PM In',
        pm_out: 'PM Out',
        ot_in: 'OT In',
        ot_out: 'OT Out',
    };

    /**
     * A camera decodes the same badge many times a second while it is held up.
     * Without this, one person standing still would generate a burst of
     * requests; the server rejects them as duplicates, but there is no reason
     * to send them.
     */
    const RESCAN_COOLDOWN_MS = 3000;

    const els = {
        status: document.getElementById('scanStatus'),
        slots: Array.from(document.querySelectorAll('.scan-slot')),
        slotHint: document.getElementById('scanSlotHint'),
        viewportIdle: document.getElementById('scanViewportIdle'),
        startBtn: document.getElementById('scanStartBtn'),
        stopBtn: document.getElementById('scanStopBtn'),
        manualForm: document.getElementById('scanManualForm'),
        manualInput: document.getElementById('scanManualInput'),
        result: document.getElementById('scanResult'),
        resultEmpty: document.getElementById('scanResultEmpty'),
        resultBody: document.getElementById('scanResultBody'),
        avatar: document.getElementById('scanAvatar'),
        name: document.getElementById('scanName'),
        meta: document.getElementById('scanMeta'),
        message: document.getElementById('scanMessage'),
        dayStrip: document.getElementById('scanDayStrip'),
        feed: document.getElementById('scanFeed'),
        feedCount: document.getElementById('scanFeedCount'),
    };

    let selectedSlot = 'am_in';
    let reader = null;
    let busy = false;
    let lastCode = null;
    let lastCodeAt = 0;

    // ---------- slot selection ----------

    els.slots.forEach((btn) => {
        btn.addEventListener('click', () => selectSlot(btn.dataset.slot));
    });

    function selectSlot(slot) {
        selectedSlot = slot;
        els.slots.forEach((btn) => {
            const active = btn.dataset.slot === slot;
            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    /**
     * Mark the slot the server would have chosen. Deliberately does not change
     * the selection — the operator picked it, and a badge should not be able to
     * move the button out from under them between aiming and scanning.
     */
    function markSuggested(slot) {
        els.slots.forEach((btn) => btn.classList.toggle('is-suggested', btn.dataset.slot === slot));

        if (slot && slot !== selectedSlot) {
            els.slotHint.textContent = `Based on the schedule and today's record, this scan looks like ${SLOT_LABELS[slot]}.`;
        }
    }

    // ---------- camera ----------

    els.startBtn.addEventListener('click', startCamera);
    els.stopBtn.addEventListener('click', stopCamera);

    async function startCamera() {
        if (typeof Html5Qrcode === 'undefined') {
            setStatus('error', 'Decoder unavailable');
            showMessage('error', 'The QR decoder could not be loaded. Check the internet connection, or use the handheld scanner box below.');
            return;
        }

        try {
            reader = new Html5Qrcode('scanReader', { verbose: false });
            await reader.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 260, height: 260 } },
                onDecoded,
                () => {}, // per-frame decode misses are normal; nothing to report
            );

            els.viewportIdle.hidden = true;
            els.startBtn.disabled = true;
            els.stopBtn.disabled = false;
            setStatus('live', 'Scanning');
        } catch (error) {
            reader = null;
            setStatus('error', 'Camera blocked');
            showMessage('error', `The camera could not be started: ${error?.message ?? error}. Allow camera access for this site, or use the handheld scanner box below.`);
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
        els.startBtn.disabled = false;
        els.stopBtn.disabled = true;
        setStatus('idle', 'Camera off');
    }

    function onDecoded(text) {
        const now = Date.now();

        if (text === lastCode && now - lastCodeAt < RESCAN_COOLDOWN_MS) return;

        lastCode = text;
        lastCodeAt = now;
        submit(text);
    }

    // ---------- handheld / manual ----------

    els.manualForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const code = els.manualInput.value.trim();
        if (!code) return;

        els.manualInput.value = '';
        submit(code);
    });

    // ---------- submitting ----------

    async function submit(code) {
        if (busy) return;
        busy = true;

        // Preview who this is and which punch the day expects, so a mis-set
        // slot is visible before it is committed.
        peek(code);

        try {
            const response = await fetch(PUNCH_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ code, slot: selectedSlot, device_label: 'Front desk kiosk' }),
            });

            const data = await response.json();

            if (data.employee) {
                renderIdentity(data.employee);
                renderDay(data.day, data.slot);
            }

            showMessage(data.status ?? 'error', data.message ?? 'The scan could not be recorded.');
            beep(response.ok);

            if (data.recent) renderFeed(data.recent);
        } catch (error) {
            showMessage('error', `The scan could not be sent: ${error?.message ?? error}`);
            beep(false);
        } finally {
            busy = false;
        }
    }

    async function peek(code) {
        try {
            const response = await fetch(SUGGEST_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ code }),
            });

            if (!response.ok) return;

            const data = await response.json();
            markSuggested(data.suggested_slot);
        } catch {
            // The punch request carries the real answer; a failed hint is silent.
        }
    }

    // ---------- rendering ----------

    function setStatus(state, text) {
        els.status.dataset.state = state;
        els.status.textContent = text;
    }

    function renderIdentity(employee) {
        els.resultEmpty.hidden = true;
        els.resultBody.hidden = false;

        els.avatar.innerHTML = employee.photo
            ? `<img src="${escapeAttr(employee.photo)}" alt="">`
            : escapeHtml((employee.name || '?').charAt(0).toUpperCase());

        els.name.textContent = employee.name ?? '';

        const parts = [employee.employee_id, employee.department, employee.designation].filter(Boolean);
        els.meta.textContent = parts.join(' · ');
    }

    function renderDay(day, currentSlot) {
        if (!day) {
            els.dayStrip.innerHTML = '';
            return;
        }

        els.dayStrip.innerHTML = Object.entries(day)
            .map(([slot, value]) => {
                const classes = ['scan-day-cell'];
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
        els.resultEmpty.hidden = true;
        els.resultBody.hidden = false;
        els.message.textContent = message;

        // An invalid badge resolves to no employee, so the previous person's
        // card must not stay on screen under someone else's error.
        if (state === 'invalid') {
            els.avatar.textContent = '?';
            els.name.textContent = 'Unrecognised badge';
            els.meta.textContent = '';
            els.dayStrip.innerHTML = '';
            markSuggested(null);
        }
    }

    function renderFeed(recent) {
        els.feedCount.textContent = recent.length;

        if (!recent.length) {
            els.feed.innerHTML = '<li class="scan-feed-empty">No punches recorded today.</li>';
            return;
        }

        els.feed.innerHTML = recent
            .map((punch) => {
                const avatar = punch.photo
                    ? `<img src="${escapeAttr(punch.photo)}" alt="">`
                    : `<span>${escapeHtml((punch.name || '?').charAt(0).toUpperCase())}</span>`;

                return `<li class="scan-feed-item">
                    <div class="scan-feed-avatar">${avatar}</div>
                    <div class="scan-feed-text">
                        <p class="scan-feed-name">${escapeHtml(punch.name)}</p>
                        <p class="scan-feed-sub">${escapeHtml(punch.slot_label)} · ${escapeHtml(punch.time)}</p>
                    </div>
                </li>`;
            })
            .join('');
    }

    /**
     * A turnstile tone. The operator is looking at the person, not the screen,
     * so the outcome has to be audible.
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

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function escapeAttr(value) {
        return String(value ?? '').replace(/"/g, '&quot;');
    }

    // ---------- background refresh ----------

    // Keeps the feed current when another terminal is scanning too.
    setInterval(async () => {
        if (busy) return;

        try {
            const response = await fetch(RECENT_URL, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;

            const data = await response.json();
            renderFeed(data.recent ?? []);
        } catch {
            // Transient; the next tick tries again.
        }
    }, 30000);

    // Releases the camera when the kiosk tab is closed or navigated away from.
    window.addEventListener('beforeunload', () => {
        if (reader) reader.stop().catch(() => {});
    });

    // A kiosk is left focused on the scanner; typing anywhere goes to the
    // handheld input so a scanner gun works without clicking the box first.
    document.addEventListener('keydown', (event) => {
        if (event.target === els.manualInput) return;
        if (event.target.matches('input, textarea, select, button')) return;
        if (event.key.length !== 1) return;

        els.manualInput.focus();
    });
}
