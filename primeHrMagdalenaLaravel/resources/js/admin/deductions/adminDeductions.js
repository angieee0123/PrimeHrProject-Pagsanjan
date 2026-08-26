import { exportWithFilters } from './exportWithFilters.js';

function switchTab(tabName) {
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('[id$="-tab"]').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.filter-group').forEach(g => g.style.display = 'none');

    event.target.classList.add('active');
    document.getElementById(tabName + '-tab').style.display = 'block';
    const group = document.getElementById(tabName + '-filter-group');
    if (group) group.style.display = 'contents';
}

window.switchTab = switchTab;

// ── Deduction Types filters ──────────────────────────────────────────────
// The Category and Status selects existed in the toolbar with no `onchange`
// and no listener anywhere, so both were dead controls: changing either did
// nothing. The rows now carry data-category / data-status, so they can do
// the job the toolbar has always implied they do.
function filterDeductionTypes() {
    const category = document.getElementById('filterDeductionTypeCategory')?.value ?? '';
    const status   = document.getElementById('filterDeductionTypeStatus')?.value ?? '';
    const rows     = document.querySelectorAll('#deduction-types-tab tbody tr[data-category]');

    let visible = 0;
    rows.forEach(row => {
        const match = (!category || row.dataset.category === category)
            && (!status || row.dataset.status === status);
        row.style.display = match ? '' : 'none';
        if (match) visible++;
    });

    // The footer counts what is on screen, not what was loaded — otherwise a
    // filtered table reports a total that contradicts the rows beneath it.
    const footer = document.querySelector('#deduction-types-tab .table-footer p');
    if (footer) {
        footer.innerHTML = `Showing <strong>${visible}</strong> of <strong>${rows.length}</strong> deduction types`;
    }
}

['filterDeductionTypeCategory', 'filterDeductionTypeStatus'].forEach(id => {
    document.getElementById(id)?.addEventListener('change', filterDeductionTypes);
});

window.filterDeductionTypes = filterDeductionTypes;

// Deduction Types — this button had no handler at all until now.
window.exportDeductionTypes = (btn) => exportWithFilters(btn, {
    category: 'filterDeductionTypeCategory',
    status:   'filterDeductionTypeStatus',
});
