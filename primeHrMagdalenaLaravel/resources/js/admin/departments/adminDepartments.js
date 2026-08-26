// Admin Departments & Designations Dashboard — client-side sort/filter/paginate + modals
//
// Bundled as an ES module by Vite, so nothing here is implicitly global the way a
// classic inline <script> was. Every function invoked from an onclick="" / onchange=""
// attribute in the blade templates is assigned onto `window` explicitly.

const departments  = window.departmentsData;
const designations = window.designationsData;
const avatarColors = window.avatarColors;

// --- Sort state ---
let deptSort  = { col: null, dir: 'asc' };
let desigSort = { col: null, dir: 'asc' };

window.sortDept = function(col) {
    deptSort.dir = deptSort.col === col && deptSort.dir === 'asc' ? 'desc' : 'asc';
    deptSort.col = col;
    applySortDept();
    updateSortHeaders('#departments thead th', col, deptSort.dir);
    deptPage = 1;
    renderTable();
}

function applySortDept() {
    const { col, dir } = deptSort;
    filteredDepartments = [...filteredDepartments].sort((a, b) => {
        const av = col === 'personnel_count' ? +a[col] : (a[col] || '').toString().toLowerCase();
        const bv = col === 'personnel_count' ? +b[col] : (b[col] || '').toString().toLowerCase();
        return av < bv ? (dir === 'asc' ? -1 : 1) : av > bv ? (dir === 'asc' ? 1 : -1) : 0;
    });
}

window.sortDesig = function(col) {
    desigSort.dir = desigSort.col === col && desigSort.dir === 'asc' ? 'desc' : 'asc';
    desigSort.col = col;
    applyDesigSort();
    updateSortHeaders('#designations thead th', col, desigSort.dir);
    desigPage = 1;
    renderDesigTable();
}

let sortedDesignations = [...designations];

function applyDesigSort() {
    const { col, dir } = desigSort;
    sortedDesignations = [...filteredDesignations].sort((a, b) => {
        let av, bv;
        if (col === 'department')         { av = (a.department?.name || '').toLowerCase(); bv = (b.department?.name || '').toLowerCase(); }
        else if (col === 'dept_code')      { av = (a.department?.code || '').toLowerCase(); bv = (b.department?.code || '').toLowerCase(); }
        else if (col === 'monthly_rate' || col === 'salary_grade') { av = +(a[col] || 0); bv = +(b[col] || 0); }
        else { av = (a[col] || '').toString().toLowerCase(); bv = (b[col] || '').toString().toLowerCase(); }
        return av < bv ? (dir === 'asc' ? -1 : 1) : av > bv ? (dir === 'asc' ? 1 : -1) : 0;
    });
}

function updateSortHeaders(selector, activeCol, dir) {
    document.querySelectorAll(selector).forEach(th => {
        const icon = th.querySelector('.sort-icon');
        if (!icon) return;
        const col = th.dataset.col;
        icon.textContent = col === activeCol ? (dir === 'asc' ? '↑' : '↓') : '⇅';
        icon.style.color = col === activeCol ? 'var(--gp-pri)' : 'var(--theme-neutral-400)';
        th.classList.toggle('is-sorted', col === activeCol);
    });
}

// --- Filter state ---
let deptFilters  = { status: '' };
let desigFilters = { dept_id: '', type: '' };

window.applyDeptFilters = function() {
    deptFilters.status = document.getElementById('dept-filter-status').value;
    const q = document.getElementById('dept-search').value.toLowerCase();
    filteredDepartments = departments.filter(d => {
        const matchSearch = !q || d.name.toLowerCase().includes(q) || d.code.toLowerCase().includes(q) || d.head.toLowerCase().includes(q);
        const matchStatus = !deptFilters.status || d.status === deptFilters.status;
        return matchSearch && matchStatus;
    });
    if (deptSort.col) applySortDept();
    deptPage = 1;
    renderTable();
    const hasFilter = !!deptFilters.status;
    document.getElementById('dept-filter-clear').classList.toggle('visible', hasFilter);
    document.getElementById('dept-filter-status').classList.toggle('active-filter', hasFilter);
}

window.clearDeptFilters = function() {
    deptFilters = { status: '' };
    document.getElementById('dept-filter-status').value = '';
    applyDeptFilters();
}

window.applyDesigFilters = function() {
    desigFilters.dept_id = document.getElementById('desig-filter-dept').value;
    desigFilters.type    = document.getElementById('desig-filter-type').value;
    const q = document.getElementById('dept-search').value.toLowerCase();
    filteredDesignations = designations.filter(d => {
        const matchSearch = !q ||
            (d.title || '').toLowerCase().includes(q) ||
            (d.department?.name || '').toLowerCase().includes(q) ||
            (d.department?.code || '').toLowerCase().includes(q) ||
            (d.employment_type || '').toLowerCase().includes(q) ||
            (d.salary_grade || '').toString().includes(q);
        const matchDept = !desigFilters.dept_id || String(d.department_id) === desigFilters.dept_id;
        const matchType = !desigFilters.type    || d.employment_type === desigFilters.type;
        return matchSearch && matchDept && matchType;
    });
    if (desigSort.col) applyDesigSort();
    desigPage = 1;
    renderDesigTable();
    const hasFilter = !!(desigFilters.dept_id || desigFilters.type);
    document.getElementById('desig-filter-clear').classList.toggle('visible', hasFilter);
    document.getElementById('desig-filter-dept').classList.toggle('active-filter', !!desigFilters.dept_id);
    document.getElementById('desig-filter-type').classList.toggle('active-filter', !!desigFilters.type);
}

window.clearDesigFilters = function() {
    desigFilters = { dept_id: '', type: '' };
    document.getElementById('desig-filter-dept').value = '';
    document.getElementById('desig-filter-type').value = '';
    applyDesigFilters();
}

// --- Departments pagination ---
let deptPage = 1, deptRowsPerPage = 10, filteredDepartments = [...departments];

/* ── Row building ─────────────────────────────────────────────────────────
   Both tables build their rows as DOM nodes now, rather than concatenating
   into `tbody.innerHTML`. Two reasons:

   · A department name or designation title is admin-entered free text that
     arrives here through `@json`. Interpolated into an HTML string it is
     parsed as markup -- a name containing a tag would run as one. Text set
     with `textContent` cannot.
   · `innerHTML +=` re-parses and re-builds the entire tbody on every
     iteration of the loop, which is quadratic in the number of rows.
*/

/** <td> with optional class and plain text. */
function depCell(text, className) {
    const td = document.createElement('td');
    if (className) td.className = className;
    if (text !== undefined && text !== null) td.textContent = text;
    return td;
}

/** A pill (`.dept-tag`, `.badge-status`, …) inside its own <td>. */
function depPillCell(text, pillClass, cellClass) {
    const td = depCell(null, cellClass);
    const pill = document.createElement('span');
    pill.className = pillClass;
    pill.textContent = text;
    td.appendChild(pill);
    return td;
}

/**
 * The tile colour is derived from the department's own code, not from its
 * position in the list. Indexing the palette by row number meant the same
 * office was navy on page 1 and maroon on page 2, and changed colour again
 * whenever the table was re-sorted -- so the tile read as decoration rather
 * than as that department's mark. A hash of the code is stable across
 * sorting, paging and filtering.
 */
function depAvatarColor(code) {
    const key = String(code || '');
    let hash = 0;
    for (let i = 0; i < key.length; i++) hash = (hash * 31 + key.charCodeAt(i)) >>> 0;
    return avatarColors[hash % avatarColors.length];
}

/** The department's identity tile: its two-letter code on its own colour. */
function depAvatar(code) {
    const tile = document.createElement('span');
    tile.className = 'dep-avatar';
    tile.style.background = depAvatarColor(code);
    tile.textContent = String(code || '?').slice(0, 2).toUpperCase();
    tile.setAttribute('aria-hidden', 'true');
    return tile;
}

/** The shared "nothing to show" row, spanning the whole table. */
function depEmptyRow(title, text) {
    const tr = document.createElement('tr');
    tr.className = 'dep-empty-row';
    const td = document.createElement('td');
    td.colSpan = 6;

    const wrap = document.createElement('div');
    wrap.className = 'dep-empty';
    wrap.innerHTML =
        '<span class="dep-empty-icon" aria-hidden="true">' +
        '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg></span>';

    const h = document.createElement('p');
    h.className = 'dep-empty-title';
    h.textContent = title;

    const p = document.createElement('p');
    p.className = 'dep-empty-text';
    p.textContent = text;

    wrap.append(h, p);
    td.appendChild(wrap);
    tr.appendChild(td);
    return tr;
}

function renderTable() {
    const total = filteredDepartments.length;
    const totalPages = Math.ceil(total / deptRowsPerPage) || 1;
    if (deptPage > totalPages) deptPage = totalPages;
    const start = (deptPage - 1) * deptRowsPerPage;
    const end   = Math.min(start + deptRowsPerPage, total);
    const tbody = document.getElementById('dept-tbody');
    tbody.textContent = '';

    if (total === 0) {
        tbody.appendChild(depEmptyRow(
            'No departments found',
            'No office matches the filters and search terms currently applied.'
        ));
    } else {
        const frag = document.createDocumentFragment();

        filteredDepartments.slice(start, end).forEach(dept => {
            const tr = document.createElement('tr');

            const nameTd = depCell(null);
            const cell = document.createElement('div');
            cell.className = 'emp-cell';
            const name = document.createElement('p');
            name.className = 'emp-name';
            name.textContent = dept.name;
            name.title = dept.name;
            cell.append(depAvatar(dept.code), name);
            nameTd.appendChild(cell);
            tr.appendChild(nameTd);

            tr.appendChild(depPillCell(dept.code, 'dept-tag'));
            tr.appendChild(depCell(dept.head, 'position-cell'));
            tr.appendChild(depCell(dept.personnel_count, 'dep-count-cell'));
            tr.appendChild(depPillCell(
                dept.status,
                'badge-status ' + (dept.status === 'Active' ? 'processed' : 'on-hold')
            ));

            const actionTd = depCell(null);
            const view = document.createElement('button');
            view.type = 'button';
            view.className = 'btn-view';
            view.textContent = 'View';
            const originalIndex = departments.indexOf(dept);
            view.onclick = () => window.showDeptModal(originalIndex);
            actionTd.appendChild(view);
            tr.appendChild(actionTd);

            frag.appendChild(tr);
        });

        tbody.appendChild(frag);
    }

    document.getElementById('showing-start').textContent  = total ? start + 1 : 0;
    document.getElementById('showing-end').textContent    = end;
    document.getElementById('showing-total').textContent  = total;
    renderPagination('dept-pagination', 'dept', deptPage, totalPages);
}

window.changeRowsDept = function(val) { deptRowsPerPage = parseInt(val); deptPage = 1; renderTable(); }

// --- Designations pagination ---
let desigPage = 1, desigRowsPerPage = 10;

function renderDesigTable() {
    const source = desigSort.col ? sortedDesignations : filteredDesignations;
    const total = source.length;
    const totalPages = Math.ceil(total / desigRowsPerPage) || 1;
    if (desigPage > totalPages) desigPage = totalPages;
    const start = (desigPage - 1) * desigRowsPerPage;
    const end   = Math.min(start + desigRowsPerPage, total);
    const tbody = document.getElementById('desig-tbody');
    tbody.textContent = '';

    if (total === 0) {
        tbody.appendChild(depEmptyRow(
            'No designations yet',
            'Designations added against a department appear here.'
        ));
    } else {
        const frag = document.createDocumentFragment();

        source.slice(start, end).forEach(d => {
            const rate = d.monthly_rate
                ? '₱' + parseFloat(d.monthly_rate).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                : '—';
            const type = d.employment_type || '—';
            const deptCode = d.department?.code || '—';

            const tr = document.createElement('tr');

            const titleTd = depCell(null);
            const title = document.createElement('p');
            title.className = 'emp-name';
            title.textContent = d.title;
            title.title = d.title;
            titleTd.appendChild(title);
            tr.appendChild(titleTd);

            const deptName = d.department ? d.department.name : 'N/A';
            const deptTd = depPillCell(deptName, 'dept-tag');
            deptTd.firstChild.title = deptName;
            tr.appendChild(deptTd);

            tr.appendChild(depPillCell(deptCode, 'dept-tag dept-tag-purple'));
            tr.appendChild(depCell(d.salary_grade || '—', 'dep-td-accent'));
            tr.appendChild(depCell(rate, 'dep-td-rate'));
            tr.appendChild(depPillCell(
                type,
                'badge-status ' + (type === 'Permanent' ? 'processed' : 'pending')
            ));

            frag.appendChild(tr);
        });

        tbody.appendChild(frag);
    }

    document.getElementById('desig-showing-start').textContent = total ? start + 1 : 0;
    document.getElementById('desig-showing-end').textContent   = end;
    document.getElementById('desig-showing-total').textContent = total;
    renderPagination('desig-pagination', 'desig', desigPage, totalPages);
}

window.changeRowsDesig = function(val) { desigRowsPerPage = parseInt(val); desigPage = 1; renderDesigTable(); }

// --- Shared pagination renderer ---
// `kind` is 'dept' or 'desig' — page-button clicks are dispatched through the single
// window.handlePageClick() below rather than embedding a closure in the onclick string,
// since a serialized closure can't see this module's scope once clicked from raw HTML.
function renderPagination(containerId, kind, current, total) {
    const container = document.getElementById(containerId);
    let html = `<button class="page-btn" ${current===1?'disabled':''} onclick="handlePageClick('${kind}', ${current-1})">‹</button>`;
    const pages = [];
    if (total <= 7) { for (let i=1;i<=total;i++) pages.push(i); }
    else {
        pages.push(1);
        if (current > 3) pages.push('...');
        for (let i=Math.max(2,current-1); i<=Math.min(total-1,current+1); i++) pages.push(i);
        if (current < total-2) pages.push('...');
        pages.push(total);
    }
    pages.forEach(p => {
        if (p === '...') { html += `<span class="dep-page-ellipsis">…</span>`; }
        else { html += `<button class="page-btn ${p===current?'active':''}" onclick="handlePageClick('${kind}', ${p})">${p}</button>`; }
    });
    html += `<button class="page-btn" ${current===total?'disabled':''} onclick="handlePageClick('${kind}', ${current+1})">›</button>`;
    container.innerHTML = html;
}

window.handlePageClick = function(kind, page) {
    if (kind === 'dept') { deptPage = page; renderTable(); }
    else { desigPage = page; renderDesigTable(); }
}

window.goToPage = function(page)  { deptPage = page; renderTable(); }
window.changePage = function(dir) { goToPage(dir === 'prev' ? deptPage - 1 : deptPage + 1); }

// --- Modal & UI helpers ---
window.showDeptModal = function(index) {
    const dept  = departments[index];
    const color = avatarColors[index % avatarColors.length];
    const avatar = document.getElementById('modal-avatar');
    avatar.style.background = color;
    avatar.textContent       = dept.code.slice(0, 2);
    document.getElementById('modal-eyebrow').textContent         = 'DEPARTMENTS · ' + dept.code;
    document.getElementById('modal-title').textContent           = dept.name;
    document.getElementById('modal-code').textContent            = dept.code;
    document.getElementById('modal-personnel-count').textContent = dept.personnel_count;
    const sb = document.getElementById('modal-status-badge');
    sb.textContent = dept.status;
    sb.style.color = dept.status === 'Active' ? 'var(--theme-success)' : 'var(--theme-danger)';
    document.getElementById('modal-head').textContent = dept.head;
    const descRow = document.getElementById('modal-desc-row');
    const descEl  = document.getElementById('modal-desc');
    if (dept.description) { descEl.textContent = dept.description; descRow.style.display = 'flex'; }
    else                  { descRow.style.display = 'none'; }
    document.getElementById('dept-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
window.closeDeptModal = function()    { document.getElementById('dept-modal').classList.remove('open');      document.body.style.overflow = ''; }
window.openAddModal = function()             { document.getElementById('add-dept-modal').classList.add('open');          document.body.style.overflow = 'hidden'; }
window.closeAddModal = function()            { document.getElementById('add-dept-modal').classList.remove('open');       document.body.style.overflow = ''; }
window.openAddDesignationModal = function()  { document.getElementById('add-designation-modal').classList.add('open');   document.body.style.overflow = 'hidden'; }
window.closeAddDesignationModal = function() { document.getElementById('add-designation-modal').classList.remove('open'); document.body.style.overflow = ''; }
window.openSuccessModal = function()  { document.getElementById('success-modal').classList.add('open');      document.body.style.overflow = 'hidden'; }
window.closeSuccessModal = function() { document.getElementById('success-modal').classList.remove('open');   document.body.style.overflow = ''; }
window.openFailedModal = function(msg){ if (msg) document.getElementById('failed-msg').textContent = msg; document.getElementById('failed-modal').classList.add('open'); document.body.style.overflow = 'hidden'; }
window.closeFailedModal = function()  { document.getElementById('failed-modal').classList.remove('open');    document.body.style.overflow = ''; openAddModal(); }
window.closeImportSummaryModal = function() { document.getElementById('import-summary-modal').classList.remove('open'); document.body.style.overflow = ''; }

const searchPlaceholders = {
    departments:  'Search department, code, or head...',
    designations: 'Search designation, department, or type...'
};

document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.filter-group').forEach(g => g.style.display = 'none');
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
        const group = document.getElementById(this.dataset.tab + '-filter-group');
        if (group) group.style.display = 'contents';
        const searchEl = document.getElementById('dept-search');
        searchEl.value = '';
        searchEl.placeholder = searchPlaceholders[this.dataset.tab] || 'Search...';
        filteredDepartments  = [...departments];
        filteredDesignations = [...designations];
        clearDeptFilters();
        clearDesigFilters();
    });
});

// Topbar search filter
let filteredDesignations = [...designations];

document.getElementById('dept-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    const activeTab = document.querySelector('.tab-btn.active')?.dataset.tab;

    if (activeTab === 'designations') {
        applyDesigFilters();
    } else {
        applyDeptFilters();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('dept-search').placeholder = searchPlaceholders.departments;
    renderTable();
    renderDesigTable();

    const flash = window.deptFlash || {};
    if (flash.success) openSuccessModal();
    if (flash.errorFirst) openFailedModal(flash.errorFirst);
    if (flash.exportError) openExportErrorModal(flash.exportError);

    if (flash.importImported !== null && flash.importImported !== undefined) {
        const imported = flash.importImported;
        const skipped  = flash.importSkipped || [];
        const type     = flash.importType || 'record';

        document.getElementById('import-summary-title').textContent =
            type === 'department' ? 'Department Import Summary' : 'Designation Import Summary';
        document.getElementById('import-count').textContent  = imported;
        document.getElementById('skipped-count').textContent = skipped.length;

        if (skipped.length > 0) {
            const wrap = document.getElementById('skipped-list-wrap');
            const list = document.getElementById('skipped-list');
            wrap.style.display = 'block';
            list.innerHTML = skipped.map(r =>
                `<p class="ism-skipped-item">⚠ ${r}</p>`
            ).join('');
        }

        document.getElementById('import-summary-modal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }
});
window.openExportSuccessModal = function(type) {
    document.getElementById('export-success-type').textContent = type;
    document.getElementById('export-success-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
window.closeExportSuccessModal = function() {
    document.getElementById('export-success-modal').classList.remove('open');
    document.body.style.overflow = '';
}
window.openExportErrorModal = function(msg) {
    document.getElementById('export-error-msg').textContent = msg || 'An unexpected error occurred during export.';
    document.getElementById('export-error-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
window.closeExportErrorModal = function() {
    document.getElementById('export-error-modal').classList.remove('open');
    document.body.style.overflow = '';
}

/**
 * The two tabs export two different documents.
 *
 * Departments & Offices exports the office directory with its headcount;
 * Designations exports the plantilla of positions with its salary grades.
 * Both are built by DepartmentExportController so they carry the same
 * letterhead as every other CSV this system hands out -- these functions only
 * hand the endpoint whichever filters the tab currently has set, so the
 * parameter block in the file describes the table the user was looking at
 * rather than the whole database.
 */
function exportWithFilters(url, params, label, visibleCount) {
    try {
        if (!url) throw new Error('Export endpoint is unavailable');

        if (visibleCount === 0) {
            openExportErrorModal(`No ${label.toLowerCase()} match the current filters, so there is nothing to export.`);
            return;
        }

        const query = new URLSearchParams(
            Object.entries(params).filter(([, value]) => value)
        ).toString();

        window.location.href = query ? `${url}?${query}` : url;
        setTimeout(() => openExportSuccessModal(label), 1000);
    } catch (e) {
        openExportErrorModal(e.message);
    }
}

window.exportDepartments = function() {
    exportWithFilters(window.exportRoutes?.departments, {
        status: document.getElementById('dept-filter-status')?.value || '',
        search: document.getElementById('dept-search')?.value.trim() || '',
    }, 'Departments', filteredDepartments.length);
}

window.exportDesignations = function() {
    exportWithFilters(window.exportRoutes?.designations, {
        department_id:   document.getElementById('desig-filter-dept')?.value || '',
        employment_type: document.getElementById('desig-filter-type')?.value || '',
        search:          document.getElementById('dept-search')?.value.trim() || '',
    }, 'Designations', filteredDesignations.length);
}
