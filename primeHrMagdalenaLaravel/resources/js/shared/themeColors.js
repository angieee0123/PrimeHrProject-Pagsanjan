// ══════════════════════════════════════════════════════════════════════
//  Reading the active theme from JavaScript.
//
//  Most of the app follows the theme by writing `var(--theme-…)` straight
//  into CSS. Three places cannot:
//
//    • Chart.js — its options take colour *values*, not CSS declarations,
//      so `var(--theme-accent)` is passed through to the canvas verbatim
//      and silently draws nothing.
//    • Canvas 2D (`ctx.fillStyle`, the QR renderer) — same reason.
//    • Documents opened with `window.open()` — a popup is its own document
//      and never inherits the theme block from the page that spawned it,
//      so those need a literal baked into the markup they write.
//
//  Everything here resolves against the live `:root`, so a themed page and
//  its charts cannot disagree. The fallbacks are the default Municipal
//  Navy values, matching the `:root` block in glassSystem.css.
// ══════════════════════════════════════════════════════════════════════

/** Resolved value of a theme custom property, e.g. themeColor('--theme-accent'). */
// NOTE: every fallback in this file must stay a literal hex. They are what
// gets used when a custom property does *not* resolve, so a `var(…)` here
// would hand Chart.js a string it cannot paint — the exact failure this
// module exists to prevent.
export function themeColor(name, fallback = '#0b044d') {
    if (typeof window === 'undefined') return fallback;
    const value = getComputedStyle(document.documentElement).getPropertyValue(name).trim();

    return value || fallback;
}

/**
 * A translucent layer in a theme colour. Reads the `-rgb` triplet the theme
 * publishes for exactly this purpose, because a hex cannot carry an alpha
 * into a Chart.js gradient stop.
 */
export function themeRgba(name, alpha, fallbackTriplet = '11, 4, 77') {
    const triplet = themeColor(`${name}-rgb`, fallbackTriplet);

    return `rgba(${triplet}, ${alpha})`;
}

/**
 * A vertical Chart.js area-fill gradient in a theme colour — the pattern
 * every trend chart on the dashboards uses.
 */
export function themeGradient(ctx, height, name = '--theme-accent', from = 0.25, to = 0.01) {
    const gradient = ctx.createLinearGradient(0, 0, 0, height);
    gradient.addColorStop(0, themeRgba(name, from));
    gradient.addColorStop(1, themeRgba(name, to));

    return gradient;
}

/**
 * The chart chrome shared by every Chart.js instance in the app: gridlines,
 * ticks, tooltip surfaces. Kept in one place so a chart added later cannot
 * quietly reintroduce the hard-coded lavender gridline.
 */
export function chartChrome() {
    return {
        grid: themeColor('--theme-bg-tint-2', '#f0eefa'),
        border: themeColor('--theme-border', '#e8e7f6'),
        tick: themeColor('--theme-text-soft', '#918eae'),
        ink: themeColor('--theme-ink', '#221e48'),
        surface: '#ffffff',
    };
}
