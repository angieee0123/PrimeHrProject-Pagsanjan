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
        icon.style.color = col === activeCol ? '#0b044d' : '#bbb';
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

function renderTable() {
    const total = filteredDepartments.length;
    const totalPages = Math.ceil(total / deptRowsPerPage) || 1;
    if (deptPage > totalPages) deptPage = totalPages;
    const start = (deptPage - 1) * deptRowsPerPage;
    const end   = Math.min(start + deptRowsPerPage, total);
    const tbody = document.getElementById('dept-tbody');
    tbody.innerHTML = '';

    if (total === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="dep-empty-td">No departments found.</td></tr>';
    } else {
        filteredDepartments.slice(start, end).forEach((dept, i) => {
            const idx = start + i;
            tbody.innerHTML += `
                <tr>
                    <td>
                        <div class="emp-cell">
                            <div class="emp-avatar dep-avatar-sm" style="background:${avatarColors[idx % avatarColors.length]};">${dept.code.slice(0,2)}</div>
                            <p class="emp-name">${dept.name}</p>
                        </div>
                    </td>
                    <td><span class="dept-tag">${dept.code}</span></td>
                    <td class="position-cell">${dept.head}</td>
                    <td class="pay-cell">${dept.personnel_count}</td>
                    <td><span class="badge-status ${dept.status === 'Active' ? 'processed' : 'on-hold'}">${dept.status}</span></td>
                    <td><button class="btn-view" onclick="showDeptModal(${departments.indexOf(dept)})">View</button></td>
                </tr>`;
        });
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
    tbody.innerHTML = '';

    if (total === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="dep-empty-td">No designations added yet.</td></tr>';
    } else {
        source.slice(start, end).forEach(d => {
            const rate = d.monthly_rate ? '₱' + parseFloat(d.monthly_rate).toLocaleString('en-PH', {minimumFractionDigits:2}) : '—';
            const type = d.employment_type || '—';
            const deptCode = d.department?.code || '—';
            tbody.innerHTML += `
                <tr>
                    <td><p class="emp-name">${d.title}</p></td>
                    <td><span class="dept-tag">${d.department ? d.department.name : 'N/A'}</span></td>
                    <td><span class="dept-tag dept-tag-purple">${deptCode}</span></td>
                    <td class="dep-td-accent">${d.salary_grade || '—'}</td>
                    <td class="dep-td-green">${rate}</td>
                    <td><span class="badge-status ${type === 'Permanent' ? 'processed' : 'pending'}">${type}</span></td>
                </tr>`;
        });
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
    sb.style.color = dept.status === 'Active' ? '#15803d' : '#8e1e18';
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

window.exportDepartments = function() {
    try {
        window.location.href = window.exportRoutes.departments;
        setTimeout(() => openExportSuccessModal('Departments'), 1000);
    } catch (e) {
        openExportErrorModal(e.message);
    }
}

window.exportDesignations = function() {
    try {
        window.location.href = window.exportRoutes.designations;
        setTimeout(() => openExportSuccessModal('Designations'), 1000);
    } catch (e) {
        openExportErrorModal(e.message);
    }
}
