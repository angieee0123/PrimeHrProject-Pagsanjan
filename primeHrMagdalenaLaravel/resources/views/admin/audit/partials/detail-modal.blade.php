{{--
    The drawer that holds everything the table row does not.

    This is what buys the calm table: the full before/after diff, the URL, the
    IP, the raw user agent and the exact timestamp all live here, so the list
    can stay at one line per entry without any of it being lost.

    It slides in from the right rather than opening as a centred dialog because
    an auditor reads *down* a list and checks entries one after another — a
    side panel keeps the row you came from visible, and arrow keys walk to the
    next entry without closing anything.

    Every field is filled in by adminAudit.js with `textContent`, never
    innerHTML: an audit row is a verbatim copy of values a user typed, and this
    page is where all of them are put back on screen at once.
--}}
<div class="au-drawer-scrim" id="auditDrawerScrim" hidden></div>

<aside class="au-drawer" id="auditDrawer" role="dialog" aria-modal="true"
       aria-labelledby="auditDrawerTitle" hidden>

    <header class="au-drawer-head">
        <div>
            <span class="au-drawer-eyebrow">Audit entry <span id="auditDrawerId"></span></span>
            <h3 class="au-drawer-title" id="auditDrawerTitle"></h3>
            <p class="au-drawer-sub" id="auditDrawerSub"></p>
        </div>
        <button type="button" class="au-drawer-close" id="auditDrawerClose" aria-label="Close details">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </header>

    <div class="au-drawer-body">

        {{-- Who / when / what, as a summary before the detail. --}}
        <div class="au-facts">
            <div class="au-fact">
                <span class="au-fact-label">Performed by</span>
                <span class="au-fact-value au-fact-user">
                    {{-- Same avatar as the table row: the photo when there is one,
                         initials underneath it otherwise. adminAudit.js sets the
                         `src` and toggles `has-photo`; the tile keeps both children
                         so a photo that fails to load falls back in place. --}}
                    <span class="au-avatar au-avatar-sm" id="auditDrawerAvatar">
                        <img alt="" class="au-avatar-img" id="auditDrawerPhoto" hidden>
                        <span id="auditDrawerInitials"></span>
                    </span>
                    <span id="auditDrawerUser"></span>
                </span>
            </div>
            <div class="au-fact">
                <span class="au-fact-label">When</span>
                <span class="au-fact-value" id="auditDrawerWhen"></span>
                <span class="au-fact-note" id="auditDrawerRelative"></span>
            </div>
        </div>

        <section class="au-drawer-section">
            <h4 class="au-drawer-h">
                Field changes
                <span class="au-count" id="auditDrawerCount">0</span>
            </h4>

            {{-- Column headers render once, above the list, instead of the
                 old inline "old → new" repeated on every line. --}}
            <div class="au-diff" id="auditDrawerDiff">
                <div class="au-diff-head">
                    <span>Field</span>
                    <span>Before</span>
                    <span>After</span>
                </div>
                <div id="auditDrawerDiffRows"></div>
            </div>

            <p class="au-diff-none" id="auditDrawerNoDiff" hidden>No field values were recorded for this entry.</p>
        </section>

        <section class="au-drawer-section">
            <h4 class="au-drawer-h">Request details</h4>
            <dl class="au-meta">
                <div class="au-meta-row">
                    <dt>Page</dt>
                    <dd id="auditDrawerPath" class="au-mono"></dd>
                </div>
                <div class="au-meta-row">
                    <dt>Full URL</dt>
                    <dd id="auditDrawerUrl" class="au-mono au-wrap"></dd>
                </div>
                <div class="au-meta-row">
                    <dt>IP address</dt>
                    <dd id="auditDrawerIp" class="au-mono"></dd>
                </div>
                <div class="au-meta-row">
                    <dt>Device</dt>
                    <dd id="auditDrawerDevice"></dd>
                </div>
                <div class="au-meta-row">
                    <dt>User agent</dt>
                    <dd id="auditDrawerAgent" class="au-mono au-wrap au-agent"></dd>
                </div>
            </dl>
        </section>
    </div>

    <footer class="au-drawer-foot">
        <span class="au-drawer-hint">
            <kbd>↑</kbd><kbd>↓</kbd> move &nbsp;·&nbsp; <kbd>Esc</kbd> close
        </span>
        <button type="button" class="btn-ghost" id="auditDrawerDone">Close</button>
    </footer>
</aside>
