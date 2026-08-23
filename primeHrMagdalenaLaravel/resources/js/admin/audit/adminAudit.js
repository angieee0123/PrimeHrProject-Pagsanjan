/**
 * Admin → Audit Trail.
 *
 * The page's filtering, sorting and paging are all server-side now, so almost
 * nothing is left for this file: it opens the detail drawer, walks between
 * entries, and forwards the topbar search box into the filter form below it.
 *
 * What it deliberately does *not* do is filter or paginate the rows on screen.
 * That is what the previous version did, over the fifteen rows the server had
 * already sent — so choosing a user filtered one page out of hundreds and the
 * footer then reported the filtered count as the table's total. Anything that
 * changes which records are shown belongs in a URL, not in this file.
 *
 * Every value written into the drawer goes in as `textContent`. An audit row
 * is a verbatim copy of whatever a user typed into a form, and this drawer is
 * where all of it is put back on screen at once.
 */

const dataEl = document.getElementById('auditData');
const drawer = document.getElementById('auditDrawer');
const scrim = document.getElementById('auditDrawerScrim');

if (dataEl && drawer && scrim) {
    const audits = JSON.parse(dataEl.textContent || '{}');
    const rows = Array.from(document.querySelectorAll('.au-row'));

    /** The row that opened the drawer — focus goes back to it on close. */
    let openRow = null;

    const el = (id) => document.getElementById(id);

    const set = (id, text) => {
        const node = el(id);
        if (node) node.textContent = text ?? '';
    };

    // ── Drawer ───────────────────────────────────────────────────────────

    function render(entry) {
        set('auditDrawerId', `Entry #${entry.id}`);
        set('auditDrawerSub', `${entry.change_count} ${entry.change_count === 1 ? 'field' : 'fields'} affected`);
        set('auditDrawerUser', entry.user_name);
        set('auditDrawerInitials', entry.user_initials);
        setAvatar(entry.user_photo);
        // The table shortens today's entries to "Today"; the drawer is where
        // the exact timestamp belongs, so it spells the whole thing out.
        set('auditDrawerWhen', entry.full);
        set('auditDrawerRelative', entry.relative);
        set('auditDrawerCount', entry.change_count);
        set('auditDrawerPath', entry.path);
        set('auditDrawerUrl', entry.url || '—');
        set('auditDrawerIp', entry.ip);
        set('auditDrawerDevice', entry.device);
        set('auditDrawerAgent', entry.user_agent);

        // The record the entry touched is the heading; the action is a status
        // beside the reference chip. They used to share the <h3>, which made a
        // badge part of a heading and left neither reading as what it is.
        set('auditDrawerTitle', `${entry.record_type} #${entry.record_id}`);

        const badge = el('auditDrawerBadge');
        badge.className = `badge-status ${entry.badge}`;
        badge.textContent = entry.event_label;

        renderDiff(entry.changes || []);
    }

    /**
     * The "Performed by" avatar: the employee's photo when the record has one,
     * their initials otherwise.
     *
     * Both children stay in the DOM and the class decides which one shows, so
     * stepping from an entry with a photo to one without cannot leave the
     * previous person's face beside the new name. `src` is *removed* rather than
     * set to '' — an empty src resolves against the current page and re-requests
     * it.
     */
    function setAvatar(photo) {
        const tile = el('auditDrawerAvatar');
        const img = el('auditDrawerPhoto');
        if (!tile || !img) return;

        tile.classList.toggle('has-photo', Boolean(photo));
        img.hidden = !photo;

        if (photo) {
            img.src = photo;
        } else {
            img.removeAttribute('src');
        }
    }

    // The stored URL can outlive the file it points at, so a photo that fails
    // to load hands the tile back to the initials underneath it.
    el('auditDrawerPhoto')?.addEventListener('error', () => setAvatar(null));

    function renderDiff(changes) {
        const host = el('auditDrawerDiffRows');
        const table = el('auditDrawerDiff');
        const none = el('auditDrawerNoDiff');

        host.textContent = '';
        table.hidden = changes.length === 0;
        none.hidden = changes.length > 0;

        changes.forEach((change) => {
            const row = document.createElement('div');
            row.className = 'au-diff-row';

            row.append(
                cell('au-diff-field', change.field),
                valueCell('au-diff-old', change.old),
                valueCell('au-diff-new', change.new),
            );

            host.appendChild(row);
        });
    }

    function cell(className, text) {
        const node = document.createElement('span');
        node.className = className;
        node.textContent = text;
        return node;
    }

    /**
     * A missing side — `created` has no before, `deleted` has no after — is a
     * muted dash, not a struck-through or green one. Styling an absent value
     * as a deletion is how a freshly created record reads as a change *from*
     * something.
     */
    function valueCell(className, value) {
        const missing = value === null || value === undefined;
        return cell(missing ? 'au-diff-empty' : className, missing ? '—' : value);
    }

    function open(row) {
        const entry = audits[row.dataset.audit];
        if (!entry) return;

        if (openRow) openRow.classList.remove('is-open');
        openRow = row;
        row.classList.add('is-open');

        render(entry);

        scrim.hidden = false;
        drawer.hidden = false;
        // Two frames: the element has to be laid out at translateX(100%)
        // before the class that animates it to 0 can take effect.
        requestAnimationFrame(() => {
            scrim.classList.add('is-open');
            drawer.classList.add('is-open');
        });

        document.body.style.overflow = 'hidden';
        el('auditDrawerClose')?.focus();
    }

    function close() {
        drawer.classList.remove('is-open');
        scrim.classList.remove('is-open');
        document.body.style.overflow = '';

        const returnTo = openRow;
        if (openRow) {
            openRow.classList.remove('is-open');
            openRow = null;
        }

        // Kept in the flow until the slide-out finishes, then removed from the
        // tab order entirely so its buttons are not reachable behind the page.
        window.setTimeout(() => {
            if (!drawer.classList.contains('is-open')) {
                drawer.hidden = true;
                scrim.hidden = true;
            }
        }, 260);

        returnTo?.focus();
    }

    const isOpen = () => drawer.classList.contains('is-open');

    /** Walk to the next/previous entry without closing — an auditor reads a run. */
    function step(offset) {
        if (!openRow) return;
        const next = rows[rows.indexOf(openRow) + offset];
        if (next) {
            open(next);
            next.scrollIntoView({ block: 'nearest' });
        }
    }

    rows.forEach((row) => {
        row.addEventListener('click', () => open(row));
        row.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                open(row);
            }
        });
    });

    scrim.addEventListener('click', close);
    el('auditDrawerClose')?.addEventListener('click', close);
    el('auditDrawerDone')?.addEventListener('click', close);

    document.addEventListener('keydown', (event) => {
        if (!isOpen()) return;

        if (event.key === 'Escape') {
            close();
        } else if (event.key === 'ArrowDown') {
            event.preventDefault();
            step(1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            step(-1);
        }
    });
}

// ── Toolbar ──────────────────────────────────────────────────────────────

const form = document.getElementById('auditFilterForm');

if (form) {
    const search = document.getElementById('auditSearchInput');
    const searchField = document.getElementById('auditSearchField');

    // The search box lives in the topbar and the filters live in the card
    // below it. One form, so the two cannot produce competing views of the
    // same list; the debounce is what keeps that from being a request per
    // keystroke.
    if (search && searchField) {
        let timer;

        search.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => {
                searchField.value = search.value;
                form.submit();
            }, 450);
        });

        search.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
                window.clearTimeout(timer);
                searchField.value = search.value;
                form.submit();
            }
        });
    }

    const perPage = document.getElementById('auditPerPage');
    const perPageField = document.getElementById('auditPerPageField');

    if (perPage && perPageField) {
        perPage.addEventListener('change', () => {
            perPageField.value = perPage.value;
            // No `page` field on the form, so this lands on page 1 — page 40
            // of a 15-row list is not page 40 of a 100-row one.
            form.submit();
        });
    }

    // A dropdown that visibly changes and then does nothing until a second
    // click on Apply reads as broken; the button stays for the date range,
    // where you are usually setting two fields before you want a reload.
    form.querySelectorAll('select[name]').forEach((select) => {
        select.addEventListener('change', () => form.submit());
    });
}
