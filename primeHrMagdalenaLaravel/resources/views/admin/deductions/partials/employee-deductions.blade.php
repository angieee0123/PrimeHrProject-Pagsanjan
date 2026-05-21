<div id="employee-deductions-tab" style="display: none;">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Employee Deductions</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Assign and manage deductions for employees</p>
        </div>
        <div class="table-actions">
            <input type="text" id="searchEmployee" class="filter-select" placeholder="Search employee..." style="width: 200px;" onkeyup="filterEmployeeDeductions()">
            <select id="filterType" class="filter-select" onchange="filterEmployeeDeductions()">
                <option value="">All Types</option>
                <option value="MANDATORY">Mandatory</option>
                <option value="LOAN">Loans</option>
                <option value="OTHER">Other</option>
            </select>
            <select id="filterStatus" class="filter-select" onchange="filterEmployeeDeductions()">
                <option value="">All Status</option>
                <option value="ACTIVE">Active</option>
                <option value="COMPLETED">Completed</option>
                <option value="SUSPENDED">Suspended</option>
            </select>
            <button class="btn-export" onclick="exportEmployeeDeductions()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAssignDeductionModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Assign Deduction
            </button>
        </div>
    </div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Deduction Type</th>
                <th>Category</th>
                <th>Amount/Balance</th>
                <th>Cutoff Schedule</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="employeeDeductionsTableBody">
            @forelse($employeeDeductions as $deduction)
                <tr data-employee="{{ strtolower($deduction->employee->first_name . ' ' . $deduction->employee->last_name) }}" 
                    data-type="{{ $deduction->deductionType->category }}" 
                    data-status="{{ $deduction->status }}">
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="background: {{ $avatarColors[($deduction->employee_id ?? 0) % count($avatarColors)] }};">
                                {{ getInitials($deduction->employee->first_name . ' ' . $deduction->employee->last_name) }}
                            </div>
                            <div>
                                <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">
                                    {{ $deduction->employee->first_name }} {{ $deduction->employee->last_name }}
                                </p>
                                <p style="color: #9999bb; margin: 0; font-size: 11px;">ID: {{ $deduction->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ $deduction->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">{{ $deduction->deductionType->name }}</p>
                            <p style="color: #9999bb; margin: 0; font-size: 11px;">{{ $deduction->deductionType->code }}</p>
                        </div>
                    </td>
                    <td>
                        @php
                            $categoryColors = [
                                'MANDATORY' => ['bg' => '#0b044d18', 'text' => '#0b044d'],
                                'LOAN' => ['bg' => '#d9bb0018', 'text' => '#d9bb00'],
                                'OTHER' => ['bg' => '#6b6a8a18', 'text' => '#6b6a8a'],
                            ];
                            $colors = $categoryColors[$deduction->deductionType->category] ?? $categoryColors['OTHER'];
                        @endphp
                        <span class="badge" style="background: {{ $colors['bg'] }}; color: {{ $colors['text'] }};">
                            {{ $deduction->deductionType->category }}
                        </span>
                    </td>
                    <td>
                        @if($deduction->deductionType->category === 'LOAN')
                            <div>
                                <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">
                                    ₱{{ number_format($deduction->remaining_balance ?? 0, 2) }}
                                </p>
                                <p style="color: #9999bb; margin: 0; font-size: 11px;">
                                    of ₱{{ number_format($deduction->total_amount ?? 0, 2) }}
                                </p>
                            </div>
                        @elseif($deduction->deductionType->computation_type === 'PERCENTAGE')
                            <span style="font-size: 13px; color: #6b6a8a;">
                                {{ $deduction->deductionType->percentage_rate }}% 
                                @if($deduction->deductionType->max_amount)
                                    (max ₱{{ number_format($deduction->deductionType->max_amount, 2) }})
                                @endif
                            </span>
                        @elseif($deduction->amount)
                            <span style="font-size: 13px; color: #6b6a8a;">₱{{ number_format($deduction->amount, 2) }}</span>
                        @else
                            <span style="font-size: 13px; color: #9999bb;">Auto-computed</span>
                        @endif
                    </td>
                    <td>
                        @php
                            // Get the deduction schedule
                            $schedule = $deduction->deductionType->schedules->first();
                            $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                            
                            // Display cutoff schedule
                            if ($cutoffSchedule === '1ST_ONLY') {
                                $scheduleDisplay = '1st Cutoff Only';
                                $scheduleColor = '#0b044d';
                            } elseif ($cutoffSchedule === '2ND_ONLY') {
                                $scheduleDisplay = '2nd Cutoff Only';
                                $scheduleColor = '#15803d';
                            } elseif ($cutoffSchedule === 'BOTH_FULL') {
                                $scheduleDisplay = 'Both (Full Each)';
                                $scheduleColor = '#d9bb00';
                            } else { // BOTH_SPLIT
                                $scheduleDisplay = 'Both (Split 50-50)';
                                $scheduleColor = '#6b6a8a';
                            }
                        @endphp
                        <div>
                            <p style="font-weight: 600; color: {{ $scheduleColor }}; margin: 0; font-size: 13px;">
                                {{ $scheduleDisplay }}
                            </p>
                            <p style="color: #9999bb; margin: 0; font-size: 11px;">{{ $cutoffSchedule }}</p>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ \Carbon\Carbon::parse($deduction->start_date)->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ $deduction->end_date ? \Carbon\Carbon::parse($deduction->end_date)->format('M d, Y') : 'Ongoing' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'ACTIVE' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'SUSPENDED' => ['bg' => '#d9bb0018', 'text' => '#d9bb00'],
                                'COMPLETED' => ['bg' => '#6b6a8a18', 'text' => '#6b6a8a'],
                            ];
                            $statusColor = $statusColors[$deduction->status] ?? $statusColors['ACTIVE'];
                        @endphp
                        <span class="badge" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                            {{ $deduction->status }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button class="action-btn" onclick="editEmployeeDeduction({{ $deduction->id }})" title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="action-btn" onclick="deleteEmployeeDeduction({{ $deduction->id }}, '{{ $deduction->employee->first_name }} {{ $deduction->employee->last_name }}', '{{ $deduction->deductionType->name }}')" title="Delete" style="color: #8e1e18;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noDataRow">
                    <td colspan="10" style="text-align: center; padding: 40px; color: #9999bb;">
                        No employee deductions found. Click "Assign Deduction" to add.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <div style="display:flex;align-items:center;gap:12px;">
            <p id="employeeDeductionsFooter">Showing <strong id="deductionRowStart">1</strong>-<strong id="deductionRowEnd">{{ min(10, $employeeDeductions->count()) }}</strong> of <strong id="deductionRowTotal">{{ $employeeDeductions->count() }}</strong> records</p>
            <select id="deductionRowsPerPage" class="filter-select" style="width:auto;padding:6px 10px;font-size:13px;" onchange="changeDeductionRowsPerPage()">
                <option value="10">10 rows</option>
                <option value="25">25 rows</option>
                <option value="50">50 rows</option>
                <option value="100">100 rows</option>
            </select>
        </div>
        <div class="pagination" id="deductionPaginationControls"></div>
    </div>
</section>

<script>
window._deductionCurrentPage = 1;
window._deductionRowsPerPage = 10;

function filterEmployeeDeductions() {
    const searchTerm = document.getElementById('searchEmployee').value.toLowerCase();
    const typeFilter = document.getElementById('filterType').value;
    const statusFilter = document.getElementById('filterStatus').value;
    const rows = document.querySelectorAll('#employeeDeductionsTableBody tr:not(#noDataRow)');
    
    const filtered = [];
    
    rows.forEach(row => {
        const employeeName = row.dataset.employee || '';
        const type = row.dataset.type || '';
        const status = row.dataset.status || '';
        
        const matchesSearch = employeeName.includes(searchTerm);
        const matchesType = !typeFilter || type === typeFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesType && matchesStatus) {
            filtered.push(row);
        }
    });
    
    window._deductionFilteredRows = filtered;
    window._deductionCurrentPage = 1;
    updateDeductionPagination();
}

window.updateDeductionPagination = function () {
    const rows = window._deductionFilteredRows || [];
    const total = rows.length;
    const perPage = window._deductionRowsPerPage;
    const totalPages = Math.ceil(total / perPage) || 1;
    const page = Math.min(window._deductionCurrentPage, totalPages);
    window._deductionCurrentPage = page;
    
    const start = (page - 1) * perPage;
    const end = Math.min(start + perPage, total);
    
    document.querySelectorAll('#employeeDeductionsTableBody tr:not(#noDataRow)').forEach(row => row.style.display = 'none');
    rows.forEach((row, i) => { if (i >= start && i < end) row.style.display = ''; });
    
    document.getElementById('deductionRowStart').textContent = total ? start + 1 : 0;
    document.getElementById('deductionRowEnd').textContent = end;
    document.getElementById('deductionRowTotal').textContent = total;
    
    // Show/hide no data row
    const noDataRow = document.getElementById('noDataRow');
    if (noDataRow) {
        noDataRow.style.display = total === 0 ? '' : 'none';
    }
    
    const controls = document.getElementById('deductionPaginationControls');
    if (totalPages <= 1) { controls.innerHTML = ''; return; }
    
    let html = '';
    const maxVisible = 5;
    let startPage = Math.max(1, page - Math.floor(maxVisible / 2));
    let endPage = Math.min(totalPages, startPage + maxVisible - 1);
    if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);
    
    if (page > 1) html += '<button class="page-btn" onclick="goToDeductionPage(' + (page - 1) + ')">‹</button>';
    if (startPage > 1) {
        html += '<button class="page-btn" onclick="goToDeductionPage(1)">1</button>';
        if (startPage > 2) html += '<span style="padding:0 8px;color:#9999bb;">...</span>';
    }
    for (let i = startPage; i <= endPage; i++) {
        html += '<button class="page-btn' + (i === page ? ' active' : '') + '" onclick="goToDeductionPage(' + i + ')">' + i + '</button>';
    }
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) html += '<span style="padding:0 8px;color:#9999bb;">...</span>';
        html += '<button class="page-btn" onclick="goToDeductionPage(' + totalPages + ')">' + totalPages + '</button>';
    }
    if (page < totalPages) html += '<button class="page-btn" onclick="goToDeductionPage(' + (page + 1) + ')">›</button>';
    
    controls.innerHTML = html;
};

window.goToDeductionPage = function (page) {
    window._deductionCurrentPage = page;
    updateDeductionPagination();
};

window.changeDeductionRowsPerPage = function () {
    window._deductionRowsPerPage = parseInt(document.getElementById('deductionRowsPerPage').value) || 10;
    window._deductionCurrentPage = 1;
    updateDeductionPagination();
};

// Initialize pagination on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('employeeDeductionsTableBody')) {
        filterEmployeeDeductions();
    }
});

function editEmployeeDeduction(id) {
    // Fetch deduction data
    fetch(`/admin/deductions/employee/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set form action
            document.getElementById('editEmployeeDeductionForm').action = `/admin/deductions/employee/${id}`;
            
            // Populate basic fields
            document.getElementById('editDeductionId').value = data.id;
            document.getElementById('editStartDate').value = data.start_date;
            document.getElementById('editEndDate').value = data.end_date || '';
            document.getElementById('editStatus').value = data.status;
            document.getElementById('editRemarks').value = data.remarks || '';
            
            // Show employee and deduction type info
            document.getElementById('editEmployeeName').textContent = `${data.employee.first_name} ${data.employee.last_name}`;
            document.getElementById('editDeductionType').textContent = data.deduction_type.name;
            
            // Hide all conditional fields first
            document.getElementById('editLoanFields').style.display = 'none';
            document.getElementById('editInstallmentField').style.display = 'none';
            document.getElementById('editFixedAmountField').style.display = 'none';
            
            // Show relevant fields based on deduction type
            if (data.deduction_type.category === 'LOAN') {
                // Show loan fields
                document.getElementById('editLoanFields').style.display = 'flex';
                document.getElementById('editInstallmentField').style.display = 'block';
                document.getElementById('editTotalAmount').value = data.total_amount || '';
                document.getElementById('editRemainingBalance').value = data.remaining_balance || '';
                document.getElementById('editInstallmentAmount').value = data.installment_amount || '';
            } else if (data.deduction_type.computation_type === 'FIXED' && data.amount) {
                // Show fixed amount field for non-loan fixed deductions
                document.getElementById('editFixedAmountField').style.display = 'block';
                document.getElementById('editAmount').value = data.amount || '';
            }
            
            // Open modal
            openEditEmployeeDeductionModal();
        })
        .catch(error => {
            console.error('Error fetching deduction:', error);
            alert('Failed to load deduction data.');
        });
}

function deleteEmployeeDeduction(id, employeeName, deductionType) {
    if (confirm(`Are you sure you want to delete ${deductionType} for ${employeeName}?\n\nThis action cannot be undone.`)) {
        // Create a form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/deductions/employee/${id}/delete`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        
        form.appendChild(csrfInput);
        form.appendChild(methodInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function exportEmployeeDeductions() {
    window.location.href = '/admin/deductions/employee/export';
}
</script>

@include('admin.deductions.modals.assignDeductionModal')
@include('admin.deductions.modals.editEmployeeDeductionModal')
</div>
