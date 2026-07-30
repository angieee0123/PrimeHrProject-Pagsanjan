// Shared busy-aware date pickers, used by the employee filing modals (File
// Leave / Travel Order / Pass Slip) and the admin modals that pair an employee
// with a date. Replaces the native <input type="date"> pickers (whose browser
// calendar cannot be styled) with flatpickr, and paints each day the employee
// already has a leave or travel order on:
//   amber  = pending leave      green = approved leave      blue = travel order
//
// Two scopes:
//   scope 'self'  — the logged-in employee's own dates (/employee/busy-dates).
//   scope 'admin' — any employee's dates, fetched per employee_id. Nothing is
//                   fetched until setEmployee() names one, since admin modals
//                   pick the employee from a dropdown after the modal opens.
//
// blockKind makes matching days unselectable; omit it for informational marks
// only. Admin modals are informational: their dates are effectivity/ledger
// periods that legitimately span leave, so blocking would break them.
//
// From/To pairs share ONE range calendar (flatpickr rangePlugin): the user picks
// a start, sees the range preview live, picks the end — and range mode refuses
// to span a blocked day. Clicking a blocked day shows a notice naming the
// conflicting request.
import flatpickr from 'flatpickr';
import rangePlugin from 'flatpickr/dist/plugins/rangePlugin';
import 'flatpickr/dist/flatpickr.min.css';
import '../../css/busyDatesCalendar.css';

// date -> { kind: 'leave-pending' | 'leave-approved' | 'travel', label }
// Leaves win over travel when both cover a day, approved wins over pending.
const KIND_PRIORITY = { 'travel': 1, 'leave-pending': 2, 'leave-approved': 3 };

const ENDPOINTS = {
    self: '/employee/busy-dates',
    admin: '/admin/employee-busy-dates',
};

function isoOf(dateObj) {
    const y = dateObj.getFullYear();
    const m = String(dateObj.getMonth() + 1).padStart(2, '0');
    const d = String(dateObj.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function eachDate(startIso, endIso, cb) {
    // Format in LOCAL time (isoOf), never toISOString(): UTC conversion shifts
    // dates back a day in timezones ahead of UTC (e.g. Philippine time).
    const start = new Date(startIso + 'T00:00:00');
    const end = new Date(endIso + 'T00:00:00');
    for (let d = start; d <= end; d.setDate(d.getDate() + 1)) {
        cb(isoOf(d));
    }
}

async function fetchBusyMap(endpoint, employeeId) {
    const url = employeeId
        ? `${endpoint}?employee_id=${encodeURIComponent(employeeId)}`
        : endpoint;
    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) throw new Error('busy-dates fetch failed: ' + res.status);
    const data = await res.json();

    const map = new Map();
    const put = (iso, kind, label) => {
        const prev = map.get(iso);
        if (!prev || KIND_PRIORITY[kind] > KIND_PRIORITY[prev.kind]) {
            map.set(iso, { kind, label });
        }
    };

    (data.leaves || []).forEach(l =>
        eachDate(l.start, l.end, iso => put(iso, l.status === 'approved' ? 'leave-approved' : 'leave-pending', l.label)));
    (data.travel_orders || []).forEach(t =>
        eachDate(t.start, t.end, iso => put(iso, 'travel', t.label)));

    return map;
}

// busyRef wraps the map so config closures see data that arrives after init.
function baseConfig(busyRef, { blockKind, muteWeekends, minDate }) {
    // blockKind null = purely informational marks, nothing disabled.
    const isBlocked = (dateObj) => {
        if (!blockKind) return false;
        const info = busyRef.map.get(isoOf(dateObj));
        if (!info) return false;
        return blockKind === 'leave' ? info.kind.startsWith('leave') : info.kind === 'travel';
    };

    return {
        dateFormat: 'Y-m-d',
        // null = no floor. Admin records are routinely backdated (a correction,
        // a retroactive effectivity), so those callers pass null rather than
        // the employee side's 'today'.
        minDate: minDate,
        disableMobile: true,
        // Anchor the calendar to the input instead of appending it to <body>.
        // Every one of these pickers lives in a position:fixed modal, and a
        // body-appended calendar is positioned absolutely against the document
        // — so scrolling the page slid the calendar away from its field and
        // right out of the modal. Static mode renders it inside the field's own
        // wrapper, so it can never drift.
        static: true,
        onOpen: (selected, dateStr, fp) => {
            requestAnimationFrame(() => {
                keepCalendarInsideContainer(fp);
                // Static calendars sit inside the modal's scrollable body, so the
                // lower fields' calendars can open partly below the fold. Nudge the
                // minimum amount needed to bring the whole calendar into view.
                fp.calendarContainer.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            });
        },
        disable: [isBlocked],
        onDayCreate: (dObj, dStr, fp, dayElem) => {
            const day = dayElem.dateObj;
            if (muteWeekends && (day.getDay() === 0 || day.getDay() === 6)) {
                dayElem.classList.add('busy-weekend');
            }
            const info = busyRef.map.get(isoOf(day));
            if (!info) return;
            dayElem.classList.add('busy-day', 'busy-' + info.kind);
            // Instant CSS tooltip (see busyDatesCalendar.css) — no native title,
            // whose one-second hover delay makes it easy to miss.
            dayElem.dataset.busyLabel = info.label;
        },
    };
}

/**
 * A static calendar is anchored to its field's LEFT edge and has a fixed width
 * (~308px). Where the field is narrower than that — e.g. a date sitting in a
 * two-column form row inside a modal — the calendar would stick out past the
 * container and put the whole modal into horizontal scroll. Flip the anchor to
 * the field's right edge in that case so the calendar grows inward instead.
 */
function keepCalendarInsideContainer(fp) {
    const cal = fp.calendarContainer;
    cal.style.left = '';
    cal.style.right = '';

    const wrapper = cal.parentElement;
    if (!wrapper) return;
    const bounds = wrapper.closest('.modal-body, .modal-container, form') || document.body;
    const b = bounds.getBoundingClientRect();

    if (cal.getBoundingClientRect().right > b.right - 2) {
        cal.style.left = 'auto';
        cal.style.right = '0';
        // Too narrow for either edge to work: sit flush left and let the
        // container scroll rather than hiding part of the calendar.
        if (cal.getBoundingClientRect().left < b.left) {
            cal.style.left = '0';
            cal.style.right = 'auto';
        }
    }
}

// "Unavailable — FL leave (pending)" strip when a blocked day is clicked;
// without it, clicking a disabled day silently does nothing.
function attachBlockedNotice(fp, busyRef) {
    const notice = document.createElement('div');
    notice.className = 'busy-cal-notice';
    fp.calendarContainer.appendChild(notice);
    let timer;
    // Must listen on daysContainer, not calendarContainer: flatpickr's own
    // day-click handler stopPropagation()s, but same-element listeners still run.
    fp.daysContainer.addEventListener('click', (e) => {
        const day = e.target.closest('.flatpickr-day.flatpickr-disabled');
        if (!day || !day.dateObj) return;
        const info = busyRef.map.get(isoOf(day.dateObj));
        notice.textContent = info ? 'Unavailable — already booked: ' + info.label : 'This date is unavailable.';
        notice.classList.add('show');
        clearTimeout(timer);
        timer = setTimeout(() => notice.classList.remove('show'), 2600);
    });
}

function loadBusy(busyRef, fp, state) {
    // Admin pickers have nothing to show until an employee is chosen.
    if (state.scope === 'admin' && !state.employeeId) {
        busyRef.map = new Map();
        fp.redraw();
        return;
    }
    // Render immediately; paint marks when the data arrives.
    fetchBusyMap(ENDPOINTS[state.scope], state.employeeId).then(map => {
        busyRef.map = map;
        fp.redraw();
    }).catch(err => console.error(err));
}

/**
 * Handle returned by the init functions.
 * setEmployee() repaints for a different employee — admin modals call it from
 * their employee dropdown's change handler.
 */
function controller(fp, busyRef, state) {
    return {
        setEmployee(employeeId) {
            state.employeeId = employeeId || null;
            loadBusy(busyRef, fp, state);
        },
        reload() {
            loadBusy(busyRef, fp, state);
        },
        clear() {
            fp.clear();
        },
        flatpickr: fp,
    };
}

/**
 * Wire a From/To input pair to ONE busy-aware range calendar.
 *
 * @param {object} opts
 * @param {string} opts.fromId          id of the start-date input
 * @param {string} opts.toId            id of the end-date input (filled by rangePlugin)
 * @param {string} [opts.blockKind]     'leave' blocks leave-busy days; 'travel' blocks travel days; omit for marks only
 * @param {function} [opts.onChange]    called after the selection changes
 * @param {boolean} [opts.muteWeekends] grey out Sat/Sun (leave excludes them from day counts)
 * @param {string} [opts.scope]         'self' (default) or 'admin'
 * @param {*} [opts.employeeId]         admin scope: employee to show, or null until setEmployee()
 * @param {string|null} [opts.minDate]  'today' (default) or null to allow backdating
 */
export function initBusyDateRange({
    fromId, toId, blockKind = null, onChange, muteWeekends = false,
    scope = 'self', employeeId = null, minDate = 'today',
}) {
    const fromEl = document.getElementById(fromId);
    const toEl = document.getElementById(toId);
    if (!fromEl || !toEl) return null;

    const busyRef = { map: new Map() };
    const state = { scope, employeeId };
    const fp = flatpickr(fromEl, {
        ...baseConfig(busyRef, { blockKind, muteWeekends, minDate }),
        mode: 'range',
        plugins: [new rangePlugin({ input: '#' + toId })],
        onChange: () => { if (onChange) onChange(); },
    });
    attachBlockedNotice(fp, busyRef);
    loadBusy(busyRef, fp, state);
    return controller(fp, busyRef, state);
}

/**
 * Busy-aware calendar for a single date input (e.g. the Pass Slip modal, or an
 * admin modal's transaction date). Marks are informational by default.
 * Options are the same as initBusyDateRange, with `inputId` instead of from/to.
 */
export function initBusySingleDate({
    inputId, blockKind = null, onChange, muteWeekends = false,
    scope = 'self', employeeId = null, minDate = 'today',
}) {
    const el = document.getElementById(inputId);
    if (!el) return null;

    const busyRef = { map: new Map() };
    const state = { scope, employeeId };
    const fp = flatpickr(el, {
        ...baseConfig(busyRef, { blockKind, muteWeekends, minDate }),
        onChange: () => { if (onChange) onChange(); },
    });
    attachBlockedNotice(fp, busyRef);
    loadBusy(busyRef, fp, state);
    return controller(fp, busyRef, state);
}
