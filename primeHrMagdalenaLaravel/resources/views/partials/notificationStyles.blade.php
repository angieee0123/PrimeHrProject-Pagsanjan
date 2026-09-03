{{-- Notification styling, for the bell, the dropdown and the history page.

     One stylesheet for all three surfaces, and no colour literals in it: every
     value is a theme variable, so the panel follows the palette the way the
     rest of the app does. The only fixed colours in this feature are the
     per-category badge gradients, which live on Notification::CATEGORIES — a
     category hue exists to tell leave from travel at a glance, and re-tinting
     it toward the theme is how that stops working. Same rule as the charts.

     Included via @once from the panel, and directly by the history page. --}}
<style>
/* ── the bell ─────────────────────────────────────────────────────────── */
.notif-wrap { position: fixed; top: 20px; right: 20px; z-index: 1000; }
.notif-btn {
    width: 46px; height: 46px; border-radius: 11px;
    background: var(--theme-bg, #fff); border: 1px solid var(--theme-neutral-300);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(var(--theme-shadow-rgb), .10);
    transition: border-color .2s, box-shadow .2s, transform .2s; position: relative;
}
.notif-btn:hover { border-color: var(--gp-pri); box-shadow: 0 4px 12px rgba(var(--theme-primary-rgb), .18); transform: translateY(-1px); }
.notif-btn:focus-visible { outline: 3px solid var(--theme-focus-ring); outline-offset: 2px; }
.notif-btn svg { color: var(--gp-pri); width: 22px; height: 22px; }

.notif-badge {
    position: absolute; top: -6px; right: -6px; min-width: 19px; height: 19px; padding: 0 5px;
    background: var(--theme-danger); color: var(--theme-danger-fg, #fff);
    font-size: 10px; font-weight: 700; line-height: 1; border-radius: 999px;
    border: 2px solid var(--theme-bg, #fff);
    box-shadow: 0 2px 6px rgba(var(--theme-shadow-rgb), .35);
    display: none; align-items: center; justify-content: center;
}
.notif-badge.active { display: inline-flex; }

/* ── the dropdown ─────────────────────────────────────────────────────── */
.notif-panel {
    position: absolute; top: 56px; right: 0; width: 400px;
    background: var(--theme-bg, #fff); border-radius: 16px;
    box-shadow: 0 12px 32px rgba(var(--theme-shadow-rgb), .16), 0 0 0 1px var(--theme-border);
    display: none; flex-direction: column; overflow: hidden;
}
.notif-panel.open { display: flex; animation: notifFadeIn .2s ease; }
@keyframes notifFadeIn { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

.notif-head { padding: 13px 14px 11px; border-bottom: 1px solid var(--theme-primary-light); display: flex; justify-content: space-between; align-items: center; gap: 10px; }
.notif-head h3 { font-size: 13px; font-weight: 700; color: var(--gp-pri); margin: 0 0 2px; letter-spacing: .01em; }
.notif-head p { font-size: 11px; color: var(--gp-text-soft); margin: 0; }

.notif-clear {
    display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0;
    padding: 6px 9px; border-radius: 8px; border: 1px solid var(--theme-primary-light);
    background: none; cursor: pointer; color: var(--gp-pri);
    font: 600 10.5px/1 'Poppins', system-ui, sans-serif; letter-spacing: .02em;
    transition: background .2s, border-color .2s, opacity .2s;
}
.notif-clear:hover:not(:disabled) { background: var(--theme-primary-soft); border-color: var(--theme-neutral-300); }
.notif-clear:disabled { opacity: .4; cursor: default; }

.notif-body { max-height: 400px; overflow-y: auto; padding: 8px; }

.notif-foot {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 11px; border-top: 1px solid var(--theme-primary-light);
    background: var(--theme-neutral-50); text-decoration: none;
    color: var(--gp-pri); font-size: 11.5px; font-weight: 600;
    transition: background .2s;
}
.notif-foot:hover { background: var(--theme-primary-soft); }

/* ── one card ─────────────────────────────────────────────────────────── */
.notif-card {
    position: relative; display: flex; gap: 10px; align-items: flex-start;
    padding: 10px 22px 10px 12px; margin-bottom: 6px;
    background: var(--theme-neutral-50); border: 1px solid var(--theme-primary-light);
    border-radius: 10px; text-decoration: none; cursor: pointer;
    transition: background .18s, border-color .18s;
}
.notif-card:last-child { margin-bottom: 0; }
.notif-card:hover { background: var(--theme-primary-soft); border-color: var(--theme-neutral-300); }
.notif-card:focus-visible { outline: 2px solid var(--theme-focus-ring); outline-offset: 1px; }

/* Unread is marked three ways — a left rule, a filled ground and a dot — so
   the distinction survives a monochrome screen and colour-vision deficiency,
   which a background tint alone does not. */
.notif-card.is-unread {
    background: var(--theme-primary-soft);
    border-color: var(--theme-neutral-400);
    box-shadow: inset 3px 0 0 0 var(--gp-pri);
}
.notif-card.is-unread .notif-title { font-weight: 700; }
.notif-card .notif-dot { display: none; }
.notif-card.is-unread .notif-dot {
    display: block; position: absolute; top: 13px; right: 10px;
    width: 7px; height: 7px; border-radius: 50%; background: var(--theme-danger);
}

.notif-avatar { flex-shrink: 0; width: 32px; height: 32px; border-radius: 9px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(var(--theme-shadow-rgb), .18); }
.notif-content { flex: 1; min-width: 0; display: block; }
.notif-meta { display: flex; align-items: center; gap: 6px; margin-bottom: 3px; }
.notif-chip {
    font-size: 9px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    color: var(--gp-text-soft); background: var(--theme-bg-tint);
    border-radius: 4px; padding: 2px 5px; line-height: 1;
}
.notif-time { font-size: 9.5px; color: var(--gp-text-soft); }
.notif-title { display: block; font-size: 12px; font-weight: 600; color: var(--gp-pri); margin-bottom: 2px; line-height: 1.35; }
.notif-msg { display: block; font-size: 11px; color: var(--gp-text-mid); line-height: 1.45; }

.notif-empty { padding: 38px 24px; text-align: center; display: flex; flex-direction: column; align-items: center; color: var(--gp-text-soft); }
.notif-empty svg { stroke: var(--theme-neutral-400); margin-bottom: 10px; }
.notif-empty p { font-size: 12.5px; font-weight: 600; color: var(--gp-text-mid); margin: 0 0 3px; }
.notif-empty span { font-size: 11px; }

/* ── history page ─────────────────────────────────────────────────────── */
.notif-page { padding: 4px 0 32px; }
.notif-page-bar { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.notif-filters { display: flex; flex-wrap: wrap; gap: 6px; }
.notif-filter {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 11px; border-radius: 999px; text-decoration: none;
    border: 1px solid var(--theme-primary-light); background: var(--theme-bg, #fff);
    color: var(--gp-text-mid); font-size: 11.5px; font-weight: 600;
    transition: background .18s, border-color .18s, color .18s;
}
.notif-filter:hover { background: var(--theme-primary-soft); }
.notif-filter.active { background: var(--gp-pri); border-color: var(--gp-pri); color: var(--theme-on-primary, #fff); }
.notif-filter-count { font-size: 10px; opacity: .75; }

.notif-list { display: flex; flex-direction: column; gap: 8px; }
.notif-list-row { display: flex; align-items: stretch; gap: 8px; }
.notif-list-row .notif-card { flex: 1; margin-bottom: 0; padding: 13px 26px 13px 14px; }
.notif-list-row .notif-title { font-size: 13px; }
.notif-list-row .notif-msg { font-size: 11.5px; }

.notif-row-actions { display: flex; flex-direction: column; gap: 5px; justify-content: center; flex-shrink: 0; }
.notif-row-btn {
    padding: 6px 10px; border-radius: 8px; cursor: pointer; white-space: nowrap;
    border: 1px solid var(--theme-primary-light); background: var(--theme-bg, #fff);
    color: var(--gp-text-mid); font: 600 10.5px/1 'Poppins', system-ui, sans-serif;
    transition: background .18s, color .18s, border-color .18s;
}
.notif-row-btn:hover { background: var(--theme-primary-soft); color: var(--gp-pri); }
.notif-row-btn.danger:hover { background: var(--theme-danger-subtle); border-color: var(--theme-danger-border); color: var(--theme-danger); }

.notif-pagination { margin-top: 18px; }
.notif-pagination svg { width: 16px; height: 16px; }

@media (max-width: 768px) {
    .notif-wrap { top: 12px; right: 12px; }
    .notif-panel {
        position: fixed; top: 64px; right: 12px; left: 12px;
        width: auto; max-height: calc(100vh - 84px); border-radius: 14px;
    }
    .notif-body { max-height: calc(100vh - 210px); }
    .notif-list-row { flex-direction: column; }
    .notif-row-actions { flex-direction: row; }
}

@media (prefers-reduced-motion: reduce) {
    .notif-panel.open { animation: none; }
    .notif-btn, .notif-card, .notif-filter, .notif-row-btn { transition: none; }
}
</style>
