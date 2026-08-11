/*
    Payroll Register — expandable rows.

    This file used to build a "deduction breakdown" modal. That modal existed
    because the register was twelve columns wide: past 1920px the individual
    contribution columns were hidden by CSS, and the modal was the only way to
    reach the figures the hiding took away.

    The table is seven columns now and the breakdown is a row underneath the
    record it belongs to, so the modal has nothing left to do. Opening a
    breakdown no longer costs a fetch, a dialog, or a viewport check.
*/

const table = document.querySelector('.payroll-register-table.is-expandable');

if (table) {
    const detailFor = (key) => table.querySelector(`.pr-detail-row[data-detail-for="${key}"]`);
    const toggleFor = (key) => table.querySelector(`.pr-toggle[data-toggle-row="${key}"]`);

    function setRow(key, open) {
        const detail = detailFor(key);
        const toggle = toggleFor(key);
        const row = table.querySelector(`.pr-row[data-row-key="${key}"]`);
        if (!detail || !toggle || !row) return;

        detail.hidden = !open;
        toggle.setAttribute('aria-expanded', String(open));
        row.classList.toggle('is-open', open);
    }

    const isOpen = (key) => !detailFor(key)?.hidden;

    // One listener on the table rather than one per row: pagination hides and
    // shows rows, and rows-per-page can redraw them, so binding per row would
    // need re-binding every time.
    table.addEventListener('click', (e) => {
        const toggle = e.target.closest('.pr-toggle');
        if (!toggle) return;
        const key = toggle.dataset.toggleRow;
        setRow(key, !isOpen(key));
        syncExpandAll();
    });

    // ── Expand all / collapse all ───────────────────────────────────
    const expandAll = document.querySelector('[data-role="expand-all"]');

    /** Only rows the current page is actually showing count towards the label. */
    const visibleKeys = () =>
        [...table.querySelectorAll('.pr-row')]
            .filter((r) => r.style.display !== 'none')
            .map((r) => r.dataset.rowKey);

    function syncExpandAll() {
        if (!expandAll) return;
        const keys = visibleKeys();
        const allOpen = keys.length > 0 && keys.every(isOpen);
        expandAll.setAttribute('aria-expanded', String(allOpen));
        expandAll.querySelector('[data-label]').textContent = allOpen ? 'Collapse all' : 'Expand all';
        expandAll.classList.toggle('is-open', allOpen);
    }

    expandAll?.addEventListener('click', () => {
        const keys = visibleKeys();
        const open = !(keys.length > 0 && keys.every(isOpen));
        keys.forEach((k) => setRow(k, open));
        syncExpandAll();
    });

    // Pagination redraws which rows are on screen, so the label has to be
    // recomputed after it runs rather than only when a row is clicked.
    const paginate = window.updatePayrollPagination;
    if (typeof paginate === 'function') {
        window.updatePayrollPagination = function () {
            paginate.apply(this, arguments);
            syncExpandAll();
        };
    }

    syncExpandAll();
}
