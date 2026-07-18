{{-- Designations Tab --}}
<section class="table-section tab-content" id="designations">
    <div class="table-header">
        <div>
            <h3 class="table-title">Designations</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · {{ $designations->count() }} designations</p>
        </div>
        <div class="table-actions">
            <button class="btn-export" style="color:#15803d;border-color:#15803d;" onclick="openBulkImportDesignationModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                Bulk Import
            </button>
            <button class="modal-btn-primary" style="padding:7px 16px;font-size:12.5px;background:#150c63;" onclick="openAddDesignationModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Designation
            </button>
        </div>
    </div>
    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th class="sortable-th" onclick="sortDesig('title')" data-col="title">Designation Title <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('department')" data-col="department">Department <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('dept_code')" data-col="dept_code">Code <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('salary_grade')" data-col="salary_grade">Salary Grade <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('monthly_rate')" data-col="monthly_rate">Monthly Rate <span class="sort-icon">⇅</span></th>
                    <th class="sortable-th" onclick="sortDesig('employment_type')" data-col="employment_type">Employment Type <span class="sort-icon">⇅</span></th>
                </tr>
            </thead>
            <tbody id="desig-tbody"></tbody>
        </table>
    </div>
    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:8px;">
            <p>Showing <strong><span id="desig-showing-start">1</span>–<span id="desig-showing-end">10</span></strong> of <strong><span id="desig-showing-total">{{ $designations->count() }}</span></strong> designations</p>
            <select id="desig-rows-select" onchange="changeRowsDesig(this.value)" style="font-size:13px;padding:6px 12px;border:none;border-radius:8px;color:#0b044d;background:#f7f6fc;font-family:'Poppins',sans-serif;outline:none;cursor:pointer;">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="desig-pagination"></div>
    </div>
</section>
