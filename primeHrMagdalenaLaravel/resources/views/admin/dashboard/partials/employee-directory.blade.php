{{-- Employee Directory table (full width) — expects: $employees (paginator). --}}
<div class="table-section" id="employee-directory">
    <div class="table-header" style="background:linear-gradient(135deg,#f2f1fb 0%,#fff 100%)">
        <div>
            <p class="table-title" style="display:flex;align-items:center;gap:8px">
                Employee Directory
            </p>
            <p class="table-sub">All active government personnel | Real-time data</p>
        </div>
        <div class="table-actions">
            <div style="position:relative;margin-right:8px">
                <svg width="14" height="14" fill="none" stroke="#8f8daf" stroke-width="2" stroke-linecap="round" viewBox="0 0 24 24" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" id="searchEmployee" placeholder="Search employees..." style="font-size:11px;padding:6px 12px 6px 32px;border-radius:6px;border:1.5px solid #e5e4f0;width:200px;font-family:inherit" oninput="searchEmployees(this.value)">
            </div>
            <select class="filter-select" id="filterDept" onchange="applyFilters()" style="font-size:11px">
                <option value="">All Departments</option>
                <option>Administration</option>
                <option>Engineering</option>
                <option>Health</option>
                <option>Finance</option>
                <option>HRMO</option>
            </select>
            <select class="filter-select" id="filterType" onchange="applyFilters()" style="font-size:11px">
                <option value="">All Types</option>
                <option>Permanent</option>
                <option>Job Order</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </button>
            <x-modal-btn onclick="openAddEmployee()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Add Employee
            </x-modal-btn>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="payroll-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Position</th>
                    <th>Department</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr data-dept="{{ $emp['dept'] }}" data-type="{{ $emp['type'] }}">
                    <td>
                        <div class="emp-cell">
                            @if($emp['photo'])
                                <img src="{{ $emp['photo'] }}" style="width:40px; height:40px; border-radius:50%; object-fit:cover; border:2px solid #ecebf6;">
                            @else
                                <div class="emp-avatar emp-avatar-dynamic" data-bg="{{ $emp['color'] }}" style="width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; color:white; font-weight:600; font-size:13px; border:2px solid #ecebf6;">{{ $emp['initials'] }}</div>
                            @endif
                            <div>
                                <p class="emp-name">{{ $emp['name'] }}</p>
                                <p class="emp-id">{{ $emp['employee_id'] }}</p>
                            </div>
                        </div>
                    </td>
                    <td><span class="position-cell">{{ $emp['position'] }}</span></td>
                    <td><span class="dept-tag">{{ $emp['dept'] }}</span></td>
                    <td><span class="dept-tag emp-type-tag {{ $emp['type']==='Permanent' ? 'is-permanent' : 'is-joborder' }}">{{ $emp['type'] }}</span></td>
                    <td>
                        @if($emp['status']==='active')
                            <span class="badge-status processed">Active</span>
                        @else
                            <span class="badge-status pending">Inactive</span>
                        @endif
                    </td>
                    <td><button class="btn-view" onclick='viewEmployeeDashboard({{ $emp["id"] ?? 0 }})'>View</button></td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:40px;color:#8f8daf;">No employees found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <span id="filterCount">Showing <strong>{{ $employees->firstItem() ?? 0 }}–{{ $employees->lastItem() ?? 0 }}</strong> of <strong>{{ $employees->total() }}</strong> employees</span>
        <div class="pagination">
            @if($employees->onFirstPage())
                <button class="page-btn" disabled>‹</button>
            @else
                <a href="{{ $employees->previousPageUrl() }}" class="page-btn">‹</a>
            @endif

            @foreach($employees->getUrlRange(1, $employees->lastPage()) as $page => $url)
                @if($page == $employees->currentPage())
                    <button class="page-btn active">{{ $page }}</button>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach

            @if($employees->hasMorePages())
                <a href="{{ $employees->nextPageUrl() }}" class="page-btn">›</a>
            @else
                <button class="page-btn" disabled>›</button>
            @endif
        </div>
    </div>
</div>

@push('scripts')
    @vite('resources/js/admin/dashboard/employee-directory.js')
@endpush
