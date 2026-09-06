// Month-grid fit pass — shared by BOTH Leave & Travel calendars.
//
//   admin / mayor  → resources/views/partials/leaveCalendar/calendar.blade.php
//   employee       → resources/views/employee/leaveCalendar/leaveCalendar.blade.php
//
// The month grid has to show the whole month at one glance: no scrollbar
// inside the calendar, nothing cropped against a cell edge, and a row that
// grows taller when the busiest date in it needs the room. Those pull against
// each other the moment the calendar is drawn into a box of fixed height —
// the floating-button modal (layouts/calendarEmbed) — so which one gives way
// is decided here, by measurement, instead of by a hard-coded cap in CSS:
//
// Both surfaces are fitted; they differ only in where the height comes from:
//
//   · inside the modal (.lc-embed-wrap) the grid is a flex item, so the height
//     it has been given IS the budget.
//   · on the full page there is no such box, so the budget is what is left of
//     the viewport below the grid's own top — because "see the whole month at
//     one glance" is not satisfied by a calendar you have to scroll the *page*
//     to reach the end of. The page may still scroll past the calendar; the
//     month itself is what has to be whole on screen.
//
// In both, the number of records a cell shows is the largest that still lets
// *every* week row fit that budget. The remainder is named by the "+N" chip,
// never cropped. With JavaScript off the CSS default is "show everything and
// grow", which scrolls rather than hides — the safe direction to fail in.
//
// The cap this replaces was `nth-child(n+4)` in CSS: a fixed number that knew
// nothing about how tall the window was, so it hid a fourth person on a screen
// with room for eight and still overflowed a short one.
//
// Hidden records STAY IN THE DOM (`hidden`, not removed) — the admin tooltip,
// the day count badge and the employee's day popover are all built from them,
// so "+3" has to open a list that really does hold three more.

const RESERVE = 4;   // px of slack per row, so a sub-pixel height never overflows

// The shortest a week row may be squeezed to — a compact cell's own date plus
// one record. Below this, squeezing further would buy the "no scrollbar"
// promise with an empty-looking calendar, so the scroll valve in
// layouts/calendarEmbed is the honest answer instead.
const MIN_ROW = 46;

// Breathing room under the grid on the full page, so the last week's bottom
// border is not flush against the fold.
const PAGE_BOTTOM_GAP = 24;

// Put on the grid when not even one record a date will fit: it stops the grid
// being pinned to the modal's height so its rows can size to their content
// again. See the branch that adds it.
const OVERFLOW_CLASS = 'is-cal-overflowing';

// How hard the cell itself is squeezed, tried in order and only ever as far as
// the next one does not fit. Each is a look the calendar already has: `compact`
// is a smaller date badge and marker, `dense` is the type-mark treatment the
// ≤768px (admin) and ≤560px (employee) rules already use — applied here by the
// height a row can have rather than only by the width it has, because a cell
// out of height has the same problem as one out of width. Both are pulled only
// when the alternative is a scrollbar.
const DENSITY = ['', 'is-cal-compact', 'is-cal-dense'];

function px(value) {
    const n = parseFloat(value);
    return Number.isFinite(n) ? n : 0;
}

// A cell's fixed cost: its own padding, its date row, and the flex gap under it.
function chromeHeight(cell, head) {
    const cs = getComputedStyle(cell);
    return px(cs.paddingTop) + px(cs.paddingBottom)
        + (head ? head.offsetHeight : 0)
        + (head ? px(cs.rowGap) : 0);
}

// Everything measured once per pass, with every record visible — a height read
// off a record that is `hidden` is zero, which is how a fit pass talks itself
// into believing anything fits.
function measure(entry, layout) {
    const { cell, head, list, items, more } = entry;
    const listStyle = getComputedStyle(list);
    const cellStyle = getComputedStyle(cell);

    entry.chrome = chromeHeight(cell, head);
    entry.itemGap = px(listStyle.rowGap);
    entry.moreGap = px(cellStyle.rowGap);
    // A chip taken out of flow — the dense employee cell puts it in the date
    // row's spare corner — costs the cell no height and no width. Read rather
    // than assumed, so the CSS stays the one place that decides where it sits.
    const inFlow = more && getComputedStyle(more).position !== 'absolute';
    entry.moreHeight = inFlow ? more.offsetHeight : 0;
    entry.moreWidth = inFlow ? more.offsetWidth : 0;

    if (layout === 'wrap') {
        // A row of avatars: its height is how many rows they wrap onto, not
        // how many of them there are.
        const first = items[0];
        entry.itemWidth = first ? first.offsetWidth : 0;
        entry.itemHeight = first ? first.offsetHeight : 0;
        entry.colGap = px(listStyle.columnGap);
        // The CELL's content width, not the marker row's: the marker row is a
        // flex item that has already shrunk to make room for the chip beside
        // it, so measuring that would count the chip's width twice and then
        // still miss the line it takes when it does not fit.
        entry.listWidth = cell.clientWidth - px(cellStyle.paddingLeft) - px(cellStyle.paddingRight);
    } else {
        // A stack of pills: each is measured on its own, because a two-line
        // label is genuinely taller than a one-line one.
        entry.itemHeights = items.map(el => el.offsetHeight);
    }
}

// How tall this cell has to be to show `visible` of its records honestly.
function requiredHeight(entry, visible, layout) {
    const total = entry.items.length;
    const shown = Math.min(visible, total);
    const truncated = shown < total;
    let body = 0;

    if (layout === 'wrap') {
        // Avatars are uniform, so how tall the row is comes down to how many
        // wrap onto each line — and to whether the "+N" chip still fits at the
        // end of the last one. It is `flex-wrap: wrap` in a container it does
        // not always fit: costing the chip as free is what let a cell claim a
        // height that the chip then sat 6px below.
        const width = entry.listWidth;
        const step = entry.itemWidth + entry.colGap;
        const perRow = step > 0 ? Math.max(1, Math.floor((width + entry.colGap) / step)) : 1;
        const lines = Math.max(1, Math.ceil(shown / perRow));
        body = lines * entry.itemHeight + (lines - 1) * entry.itemGap;
        if (truncated && entry.moreWidth) {
            const onLast = shown - (lines - 1) * perRow;
            const usedOnLast = onLast * entry.itemWidth + (onLast - 1) * entry.colGap;
            if (usedOnLast + entry.colGap + entry.moreWidth > width) {
                body += entry.itemGap + entry.moreHeight;          // its own line
            } else {
                body += Math.max(0, entry.moreHeight - entry.itemHeight);
            }
        }
    } else {
        for (let i = 0; i < shown; i++) body += entry.itemHeights[i] || 0;
        body += Math.max(0, shown - 1) * entry.itemGap;
        // The employee's chip sits under the stack rather than beside it.
        if (truncated) body += entry.moreHeight + entry.moreGap;
    }

    return entry.chrome + body;
}

// A week row is as tall as the busiest date in it — which is the whole point —
// but never shorter than `floor`, because a week holding no records at all
// still costs a full track. Starting an empty row at zero is how a fit pass
// convinces itself there is a floor's worth more room per quiet week than
// there is, and then shows a record the modal has no height for.
function rowHeight(row, visible, layout, floor) {
    let tallest = floor;
    row.forEach(entry => {
        tallest = Math.max(tallest, requiredHeight(entry, visible, layout));
    });
    return tallest + RESERVE;
}

// What the whole grid costs when each row shows `counts[i]` of its records.
//
// The comparison against the grid's height is not a rule of thumb — it is the
// browser's own. `minmax(floor, auto)` tracks only grow to their content while
// there is free space to grow into: give the grid less height than the sum of
// those contents and every track stays at `floor` and the content spills out
// of the cell instead. So "does the sum fit" is exactly the question that
// decides whether a cell can show another record without clipping it.
function gridHeight(rows, counts, layout, rowGap, floor) {
    let total = Math.max(0, rows.length - 1) * rowGap;
    for (let i = 0; i < rows.length; i++) {
        total += rowHeight(rows[i], counts[i], layout, floor);
    }
    return total;
}

function apply(rows, counts) {
    rows.forEach((row, week) => row.forEach(entry => {
        const total = entry.items.length;
        const shown = Math.min(counts[week], total);
        entry.items.forEach((el, index) => {
            if (index < shown) el.removeAttribute('hidden');
            else el.setAttribute('hidden', '');
        });
        if (!entry.more) return;

        const rest = total - shown;
        if (rest > 0) {
            entry.more.removeAttribute('hidden');
            const count = entry.more.querySelector('[data-cal-more-count]');
            if (count) count.textContent = '+' + rest;
            const label = entry.more.getAttribute('data-cal-more-label');
            if (label) entry.more.setAttribute('aria-label', label.replace('%d', String(rest)));
        } else {
            entry.more.setAttribute('hidden', '');
        }
    }));
}

/**
 * @param {object} opts
 * @param {string} opts.grid    selector for the month grid (the 7-column container)
 * @param {string} opts.cell    selector for one day cell
 * @param {string} opts.head    selector for the date row inside a cell
 * @param {string} opts.list    selector for the record container inside a cell
 * @param {string} opts.item    selector for one record
 * @param {string} opts.more    selector for the "+N" chip
 * @param {'stack'|'wrap'} opts.layout   how a cell lays its records out
 */
export function initCalendarFit(opts) {
    const grid = document.querySelector(opts.grid);
    if (!grid) return;

    // Grouped by week, because a week is the unit that has to fit: only cells
    // holding records are collected, but a week with none of them still costs a
    // full track, which is why the empty weeks are kept as empty rows rather
    // than dropped.
    const rows = [];   // one entry per WEEK, empty weeks included
    const entries = [];
    Array.from(grid.querySelectorAll(opts.cell)).forEach((cell, i) => {
        if (i % 7 === 0) rows.push([]);
        const list = cell.querySelector(opts.list);
        const items = list ? Array.from(list.querySelectorAll(opts.item)) : [];
        if (!items.length) return;
        const entry = {
            cell,
            head: cell.querySelector(opts.head),
            list,
            items,
            more: cell.querySelector(opts.more),
        };
        entries.push(entry);
        rows[rows.length - 1].push(entry);
    });
    // NOT `if (!entries.length) return`. A month with no leave and no travel in
    // it at all — October, in this data — still has five or six week rows to
    // fit, and they are what overflowed: the floor below is computed whether or
    // not there is a single record to hang off it. Bailing out here is why an
    // empty month was the one month that still scrolled.
    if (!rows.length) return;

    // Height-constrained only inside the modal iframe. On the full page the
    // grid may grow as tall as the month needs, so there is nothing to fit and
    // every record is shown.
    const constrained = grid.closest('.lc-embed-wrap');

    // Inside the modal the grid is `flex: 1`, so its height is handed to it and
    // does not depend on its content. On the full page nothing bounds it, so
    // the bound is the viewport: everything from the grid's own top down to the
    // fold. Measured from the document, not the viewport, so the answer does
    // not change with how far the page happens to be scrolled.
    function budgetFor() {
        if (constrained) return grid.clientHeight;
        const top = grid.getBoundingClientRect().top + window.scrollY;
        const view = document.documentElement.clientHeight || window.innerHeight;
        return Math.max(0, view - top - PAGE_BOTTOM_GAP);
    }
    const busiest = entries.reduce((max, e) => Math.max(max, e.items.length), 0);
    let applied = null;

    // Every week's own ceiling: no row is ever asked to show more records than
    // its busiest date actually holds.
    const maxima = rows.map(row => row.reduce((m, e) => Math.max(m, e.items.length), 0));

    // Everything visible and the stylesheet's own sizes in force: a height read
    // off a record that is `hidden` is zero, which is how a fit pass talks
    // itself into believing anything fits — and a floor read back off a
    // previous pass is how a window dragged smaller ratchets the rows down and
    // never lets them back up.
    function measureAll() {
        entries.forEach(entry => {
            entry.items.forEach(el => el.removeAttribute('hidden'));
            if (entry.more) entry.more.removeAttribute('hidden');
        });
        grid.style.removeProperty('--cal-row-min');
        entries.forEach(entry => measure(entry, opts.layout));
    }

    function run() {
        grid.classList.remove(OVERFLOW_CLASS, ...DENSITY.filter(Boolean));
        measureAll();

        const budget = budgetFor();
        const rowGap = px(getComputedStyle(grid).rowGap);
        const asked = px(getComputedStyle(grid).getPropertyValue('--cal-row-min'));

        // `--cal-row-min` is a comfortable floor for a full page, and six of it
        // is taller than the modal's grid on an ordinary laptop — so the month
        // overflowed before a single record had been counted. Capping how many
        // records a cell shows cannot fix that: the floor is what overflows. So
        // six weeks get a shorter floor than five, and a short window a shorter
        // one than a tall one, derived from the height there actually is rather
        // than typed out per breakpoint. It is only ever lowered — a roomy
        // window keeps the stylesheet's own figure.
        const affordable = Math.floor((budget - (rows.length - 1) * rowGap) / rows.length) - RESERVE;

        // One record per date is the least this may show: a date that is not
        // empty must never be drawn as though it were. So "does it fit" is
        // asked at one record a date, and the cell is squeezed a step at a time
        // until it does — measured at each step rather than adjusted by a
        // guess, because those sizes live in CSS beside the rest of the
        // calendar and not in a constant here.
        const counts = maxima.map(m => (m ? 1 : 0));
        let floor = Math.max(MIN_ROW, Math.min(asked, affordable));

        for (let step = 1; step < DENSITY.length; step++) {
            if (gridHeight(rows, counts, opts.layout, rowGap, floor) <= budget) break;
            grid.classList.remove(...DENSITY.filter(Boolean));
            grid.classList.add(DENSITY[step]);
            measureAll();
            floor = Math.max(MIN_ROW, Math.min(
                px(getComputedStyle(grid).getPropertyValue('--cal-row-min')), affordable));
        }

        if (floor < asked) grid.style.setProperty('--cal-row-min', floor + 'px');

        if (gridHeight(rows, counts, opts.layout, rowGap, floor) > budget) {
            // Not even one record a date fits — a six-week month in a short
            // window, say. Nothing is gained by hiding more, so the grid stops
            // being pinned to the modal's height and takes its content's
            // instead: the rows go back to sizing themselves, nothing is
            // cropped, and the wrapper's scrollbar is what gives. A scroll is
            // an honest failure here; half a marker under a cell border is not.
            if (constrained) grid.classList.add(OVERFLOW_CLASS);
            applied = 'overflow';
            apply(rows, counts);
            return;
        }

        // Raise the rows together, one record at a time, keeping only what the
        // grid as a whole can still pay for. Round-robin rather than cheapest
        // first: a quiet week reaches its own total and stops, which hands the
        // slack it did not need to the busy week beside it — so a date with two
        // records shows both even in a month where one date has fourteen. A
        // single count for the whole month would have cut that date to one.
        let spent = gridHeight(rows, counts, opts.layout, rowGap, floor);
        let raised = true;
        while (raised) {
            raised = false;
            for (let i = 0; i < rows.length; i++) {
                if (counts[i] >= maxima[i]) continue;
                const before = rowHeight(rows[i], counts[i], opts.layout, floor);
                const after = rowHeight(rows[i], counts[i] + 1, opts.layout, floor);
                if (spent + (after - before) > budget) continue;
                spent += after - before;
                counts[i]++;
                raised = true;
            }
        }

        applied = counts.join(',');
        apply(rows, counts);
    }

    run();

    // The budget moves with the window, and inside the modal it moves again
    // whenever the toolbar rows wrap. rAF-coalesced, so dragging the window is
    // one pass rather than sixty.
    let queued = false;
    const schedule = () => {
        if (queued) return;
        queued = true;
        requestAnimationFrame(() => { queued = false; run(); });
    };
    window.addEventListener('resize', schedule);
    // Only the modal is observed, and only its wrapper — a box whose height is
    // the iframe's and so cannot move in response to this pass. Observing
    // anything on the full page would mean observing something the fit itself
    // resizes, and a fit that re-triggers itself is a loop. The page's budget
    // is a function of the grid's *top*, which only the window moving changes,
    // and `resize` above already covers that.
    if (constrained && 'ResizeObserver' in window) {
        new ResizeObserver(schedule).observe(constrained);
    }
    // Web fonts land after first paint and change every height measured above.
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(schedule);
}
