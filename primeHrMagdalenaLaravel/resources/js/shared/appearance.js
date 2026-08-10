// ══════════════════════════════════════════════════════════════════════
//  Appearance picker.
//
//  Binds one controller per [data-appearance] root, because the admin
//  settings page carries two of them (My Appearance and Global Appearance)
//  and they must not share state. Every URL, scope and token comes off the
//  root's data attributes, so this file names no route.
//
//  No colour is computed here. SystemTheme::derive() on the server clamps
//  lightness, measures WCAG contrast and picks label colours accordingly;
//  a second implementation in JavaScript would drift from it and the
//  preview would then promise a palette the server does not produce. The
//  preview asks the server to resolve whatever is selected.
// ══════════════════════════════════════════════════════════════════════

const PREVIEW_DEBOUNCE_MS = 140;

class AppearancePicker {
    constructor(root) {
        this.root = root;
        this.scope = root.dataset.appearance;
        this.cards = [...root.querySelectorAll('.theme-card')];
        this.preview = root.querySelector('[data-role="preview"]');
        this.hint = root.querySelector('[data-role="hint"]');
        this.message = root.querySelector('[data-role="message"]');
        this.warning = root.querySelector('[data-role="warning"]');
        this.applyBtn = root.querySelector('[data-role="apply"]');
        this.resetBtn = root.querySelector('[data-role="reset"]');
        this.customInput = root.querySelector('[data-role="customColor"]');
        this.overrides = [...root.querySelectorAll('[data-override]')];

        this.selected = root.querySelector('.theme-card.is-selected')?.dataset.themeKey || 'default';
        this.appliedKey = this.selected;
        this.customColor = this.customInput?.value || '#6366f1';
        this.surfaces = {};
        root.querySelectorAll('[data-surface]').forEach(group => {
            this.surfaces[group.dataset.surface] =
                group.querySelector('.theme-surface-btn.is-selected')?.dataset.surfaceStyle || 'brand';
        });
        this.timer = null;
        this.requestId = 0;

        this.appliedSurfaces = JSON.stringify(this.surfaces);

        this.bind();
        // Seed the fine-tune pickers and paint the preview from the palette
        // that is actually live.
        this.refresh(true);
    }

    bind() {
        this.cards.forEach(card => card.addEventListener('click', () => this.select(card.dataset.themeKey)));

        this.customInput?.addEventListener('input', e => {
            this.customColor = e.target.value;
            this.root.querySelector('[data-role="customHex"]').textContent = this.customColor.toUpperCase();
            // A new seed means new derived values, so the fine-tune pickers
            // are re-seeded even when custom is already selected.
            this.select('custom');
        });

        this.overrides.forEach(input => input.addEventListener('input', () => {
            this.syncOverrideLabels();
            this.refresh(false);
        }));

        this.root.querySelector('[data-role="resetOverrides"]')
            ?.addEventListener('click', () => this.refresh(true));

        this.root.querySelectorAll('[data-surface] .theme-surface-btn').forEach(btn => {
            btn.addEventListener('click', () => this.selectSurface(
                btn.closest('[data-surface]').dataset.surface,
                btn.dataset.surfaceStyle,
            ));
        });

        this.applyBtn?.addEventListener('click', () => this.apply());
        this.resetBtn?.addEventListener('click', () => this.reset());
    }

    select(key) {
        this.selected = key;
        this.cards.forEach(card => {
            const on = card.dataset.themeKey === key;
            card.classList.toggle('is-selected', on);
            card.setAttribute('aria-checked', on ? 'true' : 'false');
        });
        // Switching palette always re-seeds the fine-tune pickers: carrying
        // one palette's overrides onto another is what produces a mismatch.
        this.refresh(true);
    }

    selectSurface(surface, style) {
        this.surfaces[surface] = style;
        this.root.querySelectorAll(`[data-surface="${surface}"] .theme-surface-btn`).forEach(btn => {
            const on = btn.dataset.surfaceStyle === style;
            btn.classList.toggle('is-selected', on);
            btn.setAttribute('aria-checked', on ? 'true' : 'false');
        });
        // A surface change does not touch the palette, so the fine-tune
        // pickers keep whatever the user has set.
        this.refresh(false);
    }

    /** The body for a preview/apply request. */
    payload(seedPickers) {
        // "System theme" is not a palette of its own — previewing it means
        // asking for whatever the organisation currently has.
        const key = this.selected === 'inherit' ? this.root.dataset.globalTheme || 'default' : this.selected;
        const overrides = seedPickers ? {} : this.readOverrides();

        return {
            theme: key,
            custom_color: this.selected === 'custom' ? this.customColor : null,
            secondary: overrides.secondary || null,
            accent: overrides.accent || null,
            muted: overrides.muted || null,
            sidebar_style: this.surfaces.sidebar || 'brand',
            topbar_style: this.surfaces.topbar || 'brand',
        };
    }

    readOverrides() {
        return this.overrides.reduce((acc, input) => {
            acc[input.dataset.override] = input.value;
            return acc;
        }, {});
    }

    syncOverrideLabels() {
        this.root.querySelectorAll('[data-hex-for]').forEach(label => {
            const input = document.getElementById(label.dataset.hexFor);
            if (input) label.textContent = (input.value || '').toUpperCase();
        });
    }

    // Each fine-tune control edits one variable; these must be the same
    // variables AppearanceController writes an override onto, or a picker
    // would open on a colour its own override does not replace.
    static OVERRIDE_VARS = {
        secondary: '--theme-primary-2',
        accent: '--theme-accent',
        muted: '--theme-muted',
    };

    setOverrides(vars) {
        this.overrides.forEach(input => {
            const value = vars[AppearancePicker.OVERRIDE_VARS[input.dataset.override]];
            if (value) input.value = value;
        });
        this.syncOverrideLabels();
    }

    /**
     * Ask the server to resolve the current selection and paint the mock.
     * Custom properties set on the preview element scope the unapplied
     * palette to that subtree, so nothing outside the preview repaints.
     */
    refresh(seedPickers) {
        if (!this.preview) return;
        clearTimeout(this.timer);

        // Dragging a colour input fires continuously; coalesce to one request.
        this.timer = setTimeout(() => {
            const id = ++this.requestId;
            this.preview.classList.add('is-loading');

            fetch(this.root.dataset.previewUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': this.root.dataset.csrf,
                },
                body: JSON.stringify(this.payload(seedPickers)),
            })
                .then(r => r.json())
                .then(json => {
                    // A slower earlier request must not overwrite a newer palette.
                    if (id !== this.requestId) return;
                    if (!json?.vars) throw new Error('no vars');

                    Object.entries(json.vars).forEach(([name, value]) => this.preview.style.setProperty(name, value));
                    if (seedPickers) this.setOverrides(json.vars);
                    this.checkContrast(json.vars, seedPickers);
                    this.updateHint();
                })
                .catch(() => {
                    // Never fake a palette locally: a wrong preview is worse
                    // than none.
                    if (id === this.requestId && this.hint) this.hint.textContent = 'Preview unavailable';
                })
                .finally(() => {
                    if (id === this.requestId) this.preview.classList.remove('is-loading');
                });
        }, PREVIEW_DEBOUNCE_MS);
    }

    updateHint() {
        if (!this.hint) return;
        const unchanged = this.selected === this.appliedKey
            && JSON.stringify(this.surfaces) === this.appliedSurfaces;
        this.hint.textContent = unchanged ? 'Currently applied' : 'Not applied yet — press Apply';
    }

    /**
     * Warn about a fine-tune override that hurts readability.
     *
     * Only overrides are checked. Every generated palette already passes a
     * server-side contrast walk, so checking those too made the warning fire
     * on Crimson and Amber — a warning that appears on the presets we ship
     * teaches people to ignore it. It warns; it never blocks.
     */
    checkContrast(vars, seedPickers) {
        if (!this.warning) return;

        // Right after re-seeding, the pickers hold the palette's own values;
        // there is nothing the user has overridden yet.
        if (seedPickers || !this.hasEdits(vars)) {
            this.warning.hidden = true;
            return;
        }

        const pairs = [
            [vars['--theme-btn-primary-bg'], vars['--theme-btn-primary-fg'], 4.5, 'Button labels'],
            ['#ffffff', vars['--theme-link'], 4.5, 'Links'],
            // Muted is small secondary text used at label size; AA-large is
            // the honest bar for it rather than 4.5.
            [vars['--theme-bg'], vars['--theme-muted'], 3, 'Muted text'],
        ];
        const weak = pairs
            .filter(([bg, fg, min]) => bg && fg && contrastRatio(bg, fg) < min)
            .map(([, , , what]) => what);

        this.warning.hidden = weak.length === 0;
        if (weak.length) {
            this.root.querySelector('[data-role="warningText"]').textContent =
                `${weak.join(' and ')} may be hard to read with these colours.`;
        }
    }

    /** True when a fine-tune picker differs from the palette's own value. */
    hasEdits(vars) {
        return this.overrides.some(input => {
            const generated = vars[AppearancePicker.OVERRIDE_VARS[input.dataset.override]];

            return generated && input.value.toLowerCase() !== generated.toLowerCase();
        });
    }

    setBusy(button, busy, label) {
        if (!button) return;
        button.disabled = busy;
        if (busy) {
            button.dataset.label = button.textContent;
            button.textContent = label;
        } else if (button.dataset.label) {
            button.textContent = button.dataset.label;
        }
    }

    showError(text) {
        if (!this.message) return;
        this.message.textContent = text;
        this.message.classList.remove('hidden');
    }

    send(url, method, body) {
        this.message?.classList.add('hidden');

        return fetch(url, {
            method,
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': this.root.dataset.csrf,
            },
            body: body ? JSON.stringify(body) : undefined,
        }).then(async r => {
            const json = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(json.message || 'That did not save. Please try again.');
            return json;
        });
    }

    apply() {
        // Choosing "System theme" and applying is the same act as resetting;
        // the user should not have to know which button expresses it.
        if (this.selected === 'inherit') return this.reset();

        // Only the organisation-wide change asks. A personal palette affects
        // nobody else and is one click to undo, so confirming it would be
        // friction for its own sake.
        if (this.scope === 'global' && !window.confirm(
            'Apply this to everyone?\n\n'
            + 'This changes the default look for people who are using the system theme.\n'
            + 'Anyone who has chosen their own appearance keeps it.',
        )) return;

        this.setBusy(this.applyBtn, true, 'Applying…');
        this.send(this.root.dataset.applyUrl, 'POST', this.payload(false))
            // One reload, only after a successful save: the palette lives in
            // a <style> block rendered server-side, so the rest of the page
            // cannot repaint without one.
            .then(() => window.location.reload())
            .catch(err => {
                this.showError(err.message);
                this.setBusy(this.applyBtn, false);
            });
    }

    reset() {
        if (this.scope === 'global' && !window.confirm(
            'Restore the PRIME HRIS default look?\n\n'
            + 'People using the system theme go back to Municipal Navy.\n'
            + 'Personal appearances are not deleted.',
        )) return;

        this.setBusy(this.resetBtn, true, 'Resetting…');
        this.send(this.root.dataset.resetUrl, 'DELETE')
            .then(() => window.location.reload())
            .catch(err => {
                this.showError(err.message);
                this.setBusy(this.resetBtn, false);
            });
    }
}

/** WCAG contrast ratio between two #rrggbb colours. */
function contrastRatio(a, b) {
    const lum = hex => {
        const c = [1, 3, 5].map(i => parseInt(hex.slice(i, i + 2), 16) / 255)
            .map(v => (v <= 0.03928 ? v / 12.92 : ((v + 0.055) / 1.055) ** 2.4));

        return 0.2126 * c[0] + 0.7152 * c[1] + 0.0722 * c[2];
    };

    if (!/^#[0-9a-f]{6}$/i.test(a) || !/^#[0-9a-f]{6}$/i.test(b)) return 21;
    const [x, y] = [lum(a), lum(b)];

    return (Math.max(x, y) + 0.05) / (Math.min(x, y) + 0.05);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-appearance]').forEach(root => new AppearancePicker(root));
});
