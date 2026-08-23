{{-- Departments Tab --}}
<section class="table-section tab-content active" id="departments">
    <div class="table-header">
        <div>
            <h3 class="table-title">Departments & Offices</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Province of Laguna · {{ $departments->count() }} offices</p>
        </div>
        <div class="table-actions">
            <button class="btn-export btn-export-green" onclick="openBulkImportModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk Import
            </button>
            {{-- Same navy pill as "File Travel Order". --}}
            <button class="btn-export adm-btn-primary-solid" onclick="openAddModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Department
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        {{--
            Widths are declared here and the table is `table-layout: fixed`,
            so the columns stay put as you sort and page. Alignment is set on
            `th` and `td` together in adminDepartment.css -- the counts and
            badges are centred under centred headings rather than sitting
            left under a left heading while the eye looks for a column.
        --}}
        <table class="payroll-table dep-table">
            <colgroup>
                <col class="dcol-name">
                <col class="dcol-code">
                <col class="dcol-head">
                <col class="dcol-count">
                <col class="dcol-status">
                <col class="dcol-action">
            </colgroup>
            <thead>
                <tr>
                    <th scope="col" class="sortable-th" onclick="sortDept('name')" data-col="name">Department / Office <span class="sort-icon">⇅</span></th>
                    <th scope="col" class="sortable-th" onclick="sortDept('code')" data-col="code">Code <span class="sort-icon">⇅</span></th>
                    <th scope="col" class="sortable-th" onclick="sortDept('head')" data-col="head">Department Head <span class="sort-icon">⇅</span></th>
                    <th scope="col" class="sortable-th" onclick="sortDept('personnel_count')" data-col="personnel_count">Personnel <span class="sort-icon">⇅</span></th>
                    <th scope="col" class="sortable-th" onclick="sortDept('status')" data-col="status">Status <span class="sort-icon">⇅</span></th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody id="dept-tbody"></tbody>
        </table>
    </div>
    <div class="table-footer">
        <div class="dep-footer-inner">
            <p>Showing <strong><span id="showing-start">1</span>–<span id="showing-end">10</span></strong> of <strong><span id="showing-total">{{ $departments->count() }}</span></strong> offices</p>
            <select id="dept-rows-select" onchange="changeRowsDept(this.value)" class="dep-rows-select">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="dept-pagination"></div>
    </div>
</section>
