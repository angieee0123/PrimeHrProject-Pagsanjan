/*
    Website Content editor.

    Each section is one <form> that posts to /admin/website/{section}. The only
    interesting part is the repeaters.

    Rows carry `data-name` rather than a finished `name`, and this file writes
    `name="items[3][title]"` from the row's position in the DOM. Doing it here
    instead of in Blade means one rule for indices: a row added in the browser
    is numbered by the same code that renumbers everything after a delete, so
    removing a middle row cannot leave `items[0], items[2]` behind — which PHP
    would happily accept and the validator would then index differently from
    what the admin saw.

    A repeater with an empty `data-name` is a list of plain strings (tags,
    features, paragraphs) and indexes to `tags[2]` rather than `tags[2][...]`.

    Repeaters nest: the services section has service rows inside category rows.
    reindex() walks a repeater's *own* rows only — it finds them by climbing
    from each row back up to the nearest repeater — so adding a category cannot
    renumber another category's services.
*/

const editors = document.querySelectorAll('[data-website-editor]');

editors.forEach((root) => {
    const updateUrl = root.dataset.updateUrl;
    const csrf = root.dataset.csrf;

    // ── section rail ────────────────────────────────────────────────
    root.querySelectorAll('.wc-nav-item').forEach((btn) => {
        btn.addEventListener('click', () => {
            root.querySelectorAll('.wc-nav-item').forEach((b) => b.classList.remove('active'));
            root.querySelectorAll('.wc-panel').forEach((p) => p.classList.remove('active'));
            btn.classList.add('active');
            root.querySelector(`.wc-panel[data-panel="${btn.dataset.target}"]`)?.classList.add('active');
        });
    });

    // ── repeaters ───────────────────────────────────────────────────

    /** The repeater a row belongs to — the nearest one above it. */
    const ownerOf = (row) => row.parentElement.closest('[data-repeat]');

    /** Rows that belong to this repeater directly, not to a nested one. */
    const rowsOf = (repeat) =>
        [...repeat.querySelectorAll('[data-row]')].filter((row) => ownerOf(row) === repeat);

    /**
     * The name prefix for a repeater, built from its ancestors so a nested
     * list lands at `categories[0][items][2]`.
     */
    function prefixOf(repeat) {
        const parentRow = repeat.closest('[data-row]');
        if (!parentRow) return repeat.dataset.repeat;

        const parentRepeat = ownerOf(parentRow);
        const index = rowsOf(parentRepeat).indexOf(parentRow);
        return `${prefixOf(parentRepeat)}[${index}][${repeat.dataset.repeat}]`;
    }

    function reindex(repeat) {
        const prefix = prefixOf(repeat);

        rowsOf(repeat).forEach((row, i) => {
            row.querySelectorAll('[data-name]').forEach((field) => {
                // Only fields belonging to this row, not to a nested repeater.
                if (field.closest('[data-row]') !== row) return;

                const leaf = field.dataset.name;
                field.name = leaf ? `${prefix}[${i}][${leaf}]` : `${prefix}[${i}]`;
            });

            // A nested repeater's own prefix just changed with its parent index.
            row.querySelectorAll('[data-repeat]').forEach((nested) => {
                if (nested.closest('[data-row]') === row) reindex(nested);
            });
        });

        const max = parseInt(repeat.dataset.max || '20', 10);
        const add = repeat.querySelector(':scope > .wc-repeat-head > [data-add]');
        if (add) add.disabled = rowsOf(repeat).length >= max;
    }

    /** Every repeater, outermost first, so nested prefixes resolve correctly. */
    const allRepeats = () => [...root.querySelectorAll('[data-repeat]')];

    allRepeats().forEach(reindex);

    root.addEventListener('click', (e) => {
        const add = e.target.closest('[data-add]');
        if (add) {
            e.preventDefault();
            const repeat = add.closest('[data-repeat]');
            const tpl = repeat.querySelector(':scope > template[data-template]');
            if (!tpl) return;

            const clone = tpl.content.firstElementChild.cloneNode(true);
            repeat.querySelector(':scope > [data-rows]').appendChild(clone);

            // A cloned row may itself contain repeaters that were never indexed.
            reindex(repeat);
            clone.querySelectorAll('[data-repeat]').forEach(reindex);
            clone.querySelector('input, textarea, select')?.focus();
            return;
        }

        const remove = e.target.closest('[data-remove]');
        if (remove) {
            e.preventDefault();
            const row = remove.closest('[data-row]');
            const repeat = ownerOf(row);
            row.remove();
            reindex(repeat);
        }
    });

    // ── logo ────────────────────────────────────────────────────────

    const logo = root.querySelector('[data-logo]');

    if (logo) {
        const img = logo.querySelector('[data-logo-img]');
        const input = logo.querySelector('[data-logo-input]');
        const state = logo.querySelector('[data-logo-state]');
        const msg = logo.querySelector('[data-logo-msg]');
        const resetBtn = logo.querySelector('[data-logo-reset]');
        const url = logo.dataset.uploadUrl;

        const say = (text, kind) => {
            msg.textContent = text;
            msg.className = 'wc-msg is-' + kind;
            msg.hidden = false;
            if (kind === 'ok') setTimeout(() => { msg.hidden = true; }, 4000);
        };

        /* The filename is content-hashed, so a new upload is a new URL and the
           <img> refreshes on its own. Going back to the shipped logo returns a
           URL the browser has cached, so that one case needs a cache-buster. */
        const show = (next, isDefault) => {
            img.src = isDefault ? `${next}?v=${Date.now()}` : next;
            logo.dataset.default = isDefault ? '1' : '0';
            resetBtn.disabled = isDefault;
            state.textContent = isDefault
                ? 'Currently using the logo shipped with the system.'
                : 'Using an uploaded logo.';
        };

        input.addEventListener('change', async () => {
            const file = input.files?.[0];
            if (!file) return;

            const body = new FormData();
            body.append('logo', file);

            say('Uploading…', 'ok');

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    body,
                });
                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    show(data.url, false);
                    say(data.message || 'Logo updated.', 'ok');
                } else if (res.status === 422) {
                    say(validationText(data), 'err');
                } else {
                    say(data.message || `Could not upload (${res.status}).`, 'err');
                }
            } catch {
                say('Could not reach the server. The logo was not changed.', 'err');
            } finally {
                // Clear it so choosing the same file twice fires change again.
                input.value = '';
            }
        });

        resetBtn.addEventListener('click', async () => {
            if (!confirm('Go back to the logo that came with the system? The uploaded image will be deleted.')) {
                return;
            }

            try {
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                });
                const data = await res.json().catch(() => ({}));

                if (res.ok) {
                    show(data.url, true);
                    say(data.message || 'Logo reset.', 'ok');
                } else {
                    say(data.message || `Could not reset (${res.status}).`, 'err');
                }
            } catch {
                say('Could not reach the server.', 'err');
            }
        });
    }

    // ── save / reset ────────────────────────────────────────────────

    function flash(panel, text, kind) {
        const msg = panel.querySelector('[data-role="msg"]');
        if (!msg) return;
        msg.textContent = text;
        msg.className = 'wc-msg is-' + kind;
        msg.hidden = false;
        if (kind === 'ok') {
            setTimeout(() => { msg.hidden = true; }, 4000);
        }
    }

    /** Laravel's 422 body, flattened to something an admin can act on. */
    function validationText(body) {
        const errors = body?.errors || {};
        const first = Object.values(errors)[0];
        return Array.isArray(first) ? first[0] : (body?.message || 'Please check the fields and try again.');
    }

    root.querySelectorAll('.wc-panel').forEach((panel) => {
        const section = panel.dataset.section;

        panel.addEventListener('submit', async (e) => {
            e.preventDefault();
            const save = panel.querySelector('[data-role="save"]');
            save.disabled = true;

            try {
                const res = await fetch(`${updateUrl}/${section}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                    body: new FormData(panel),
                });
                const body = await res.json().catch(() => ({}));

                if (res.ok) {
                    flash(panel, body.message || 'Saved.', 'ok');
                    const meta = panel.querySelector('[data-role="meta"]');
                    if (meta) meta.textContent = 'Last edited just now';
                    root.querySelector(`.wc-nav-item[data-target="${section}"]`)?.classList.add('is-edited');
                } else if (res.status === 422) {
                    flash(panel, validationText(body), 'err');
                } else {
                    flash(panel, body.message || `Could not save (${res.status}).`, 'err');
                }
            } catch {
                flash(panel, 'Could not reach the server. Your changes were not saved.', 'err');
            } finally {
                save.disabled = false;
            }
        });

        panel.querySelector('[data-role="reset"]')?.addEventListener('click', async () => {
            const label = panel.querySelector('.wc-panel-title')?.textContent?.trim() || 'this section';
            if (!confirm(`Put "${label}" back to the original wording? Anything you have written here will be replaced.`)) {
                return;
            }

            try {
                const res = await fetch(`${updateUrl}/${section}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' },
                });
                const body = await res.json().catch(() => ({}));

                if (res.ok) {
                    // The form is rebuilt from the defaults server-side, so a
                    // reload is the honest way to show what is now stored —
                    // repopulating by hand would be a second renderer to keep
                    // in step with the Blade.
                    location.reload();
                } else {
                    flash(panel, body.message || `Could not reset (${res.status}).`, 'err');
                }
            } catch {
                flash(panel, 'Could not reach the server.', 'err');
            }
        });
    });
});
