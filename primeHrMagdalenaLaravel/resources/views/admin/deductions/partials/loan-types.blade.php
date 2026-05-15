<div class="table-header" style="margin-bottom: 16px;">
    <div>
        <h3 class="table-title" style="font-size: 16px; margin-bottom: 4px;">Loan Type Registry</h3>
        <p class="table-sub">Register and manage reusable loan types that can be assigned to multiple employees</p>
    </div>
    <div class="table-actions">
        <input type="text" id="searchLoanType" class="filter-select" placeholder="Search loan type..." style="width: 200px;" onkeyup="filterLoanTypes()">
        <select id="filterLoanTypeProvider" class="filter-select" onchange="filterLoanTypes()">
            <option value="">All Providers</option>
            <option value="GSIS">GSIS</option>
            <option value="PAG-IBIG">Pag-IBIG</option>
            <option value="OTHER">Other</option>
        </select>
        <select id="filterLoanTypeStatus" class="filter-select" onchange="filterLoanTypes()">
            <option value="">All Status</option>
            <option value="1">Active</option>
            <option value="0">Inactive</option>
        </select>
        <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAddLoanTypeModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Register Loan Type
        </button>
    </div>
</div>

<div style="background: #e3f2fd; border: 1px solid #90caf9; border-radius: 8px; padding: 14px 18px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; color: #1565c0;">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="16" x2="12" y2="12"/>
        <line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <div style="font-size: 13px;">
        <strong>Loan Type Registry:</strong> Register loan types once, then assign them to multiple employees with different amounts and payment terms. Registered loan types automatically appear in the "Add Employee Loan" dropdown.
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Loan Type Name</th>
                <th>Provider</th>
                <th>Max Loanable</th>
                <th>Interest Rate</th>
                <th>Max Terms</th>
                <th>Employees Using</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="loanTypesTableBody">
            @php
                $loanTypes = \App\Models\DeductionType::where('category', 'LOAN')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            @endphp
            
            @forelse($loanTypes as $loanType)
                @php
                    // Determine provider from code
                    $provider = 'OTHER';
                    $providerDisplay = 'Other';
                    if (str_contains($loanType->code, 'GSIS')) {
                        $provider = 'GSIS';
                        $providerDisplay = 'GSIS';
                    } elseif (str_contains($loanType->code, 'PAGIBIG')) {
                        $provider = 'PAG-IBIG';
                        $providerDisplay = 'Pag-IBIG';
                    }
                    
                    // Count employees using this loan type
                    $employeesCount = \App\Models\EmployeeDeduction::where('deduction_type_id', $loanType->id)
                        ->where('status', 'ACTIVE')
                        ->distinct('employee_id')
                        ->count();
                @endphp
                <tr data-loan-type="{{ strtolower($loanType->name) }}" 
                    data-provider="{{ $provider }}" 
                    data-status="{{ $loanType->is_active ? '1' : '0' }}">
                    <td>
                        <span style="font-family: 'Courier New', monospace; font-size: 12px; color: #6b6a8a; background: #f7f6ff; padding: 4px 8px; border-radius: 4px;">
                            {{ $loanType->code }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">{{ $loanType->name }}</p>
                            <p style="color: #9999bb; margin: 0; font-size: 11px;">{{ $loanType->category }}</p>
                        </div>
                    </td>
                    <td>
                        @php
                            $providerColors = [
                                'GSIS' => ['bg' => '#0b044d18', 'text' => '#0b044d'],
                                'PAG-IBIG' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'OTHER' => ['bg' => '#6b6a8a18', 'text' => '#6b6a8a'],
                            ];
                            $providerColor = $providerColors[$provider] ?? $providerColors['OTHER'];
                        @endphp
                        <span class="badge" style="background: {{ $providerColor['bg'] }}; color: {{ $providerColor['text'] }};">
                            {{ $providerDisplay }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ $loanType->max_amount ? '₱' . number_format($loanType->max_amount, 2) : 'No limit' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ $loanType->percentage_rate ? $loanType->percentage_rate . '%' : 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">N/A</span>
                    </td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-weight: 600; color: #0b044d; font-size: 14px;">{{ $employeesCount }}</span>
                            <span style="font-size: 11px; color: #9999bb;">{{ $employeesCount == 1 ? 'employee' : 'employees' }}</span>
                        </div>
                    </td>
                    <td>
                        @if($loanType->is_active)
                            <span class="badge" style="background: #15803d18; color: #15803d;">Active</span>
                        @else
                            <span class="badge" style="background: #6b6a8a18; color: #6b6a8a;">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button class="action-btn" onclick="viewLoanTypeDetails({{ $loanType->id }})" title="View Details">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button class="action-btn" onclick="editLoanType({{ $loanType->id }})" title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            @if($employeesCount == 0)
                                <button class="action-btn" onclick="deleteLoanType({{ $loanType->id }}, '{{ $loanType->name }}')" title="Delete" style="color: #8e1e18;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            @else
                                <button class="action-btn" disabled title="Cannot delete - in use by {{ $employeesCount }} employee(s)" style="opacity: 0.3; cursor: not-allowed;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noLoanTypesRow">
                    <td colspan="9" style="text-align: center; padding: 40px; color: #9999bb;">
                        No loan types registered. Click "Register Loan Type" to add a new loan type.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-footer">
    <p>Showing <strong id="showingLoanTypesCount">{{ $loanTypes->count() }}</strong> of <strong id="totalLoanTypesCount">{{ $loanTypes->count() }}</strong> loan types</p>
</div>

<script>
function filterLoanTypes() {
    const searchTerm = document.getElementById('searchLoanType').value.toLowerCase();
    const providerFilter = document.getElementById('filterLoanTypeProvider').value;
    const statusFilter = document.getElementById('filterLoanTypeStatus').value;
    const rows = document.querySelectorAll('#loanTypesTableBody tr:not(#noLoanTypesRow)');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const loanTypeName = row.dataset.loanType || '';
        const provider = row.dataset.provider || '';
        const status = row.dataset.status || '';
        
        const matchesSearch = loanTypeName.includes(searchTerm);
        const matchesProvider = !providerFilter || provider === providerFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesProvider && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    document.getElementById('showingLoanTypesCount').textContent = visibleCount;
    
    const noLoanTypesRow = document.getElementById('noLoanTypesRow');
    if (noLoanTypesRow) {
        noLoanTypesRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function viewLoanTypeDetails(id) {
    fetch(`/admin/deductions/types/${id}`)
        .then(response => response.json())
        .then(data => {
            const provider = data.code.includes('GSIS') ? 'GSIS' : (data.code.includes('PAGIBIG') ? 'Pag-IBIG' : 'Other');
            const maxLoanable = data.max_amount ? '₱' + parseFloat(data.max_amount).toFixed(2) : 'No limit';
            const interestRate = data.percentage_rate ? data.percentage_rate + '%' : 'N/A';
            
            alert(`
╔════════════════════════════════════════════╗
║          LOAN TYPE DETAILS                 ║
╠════════════════════════════════════════════╣
║ Code: ${data.code.padEnd(37)} ║
║ Name: ${data.name.padEnd(37)} ║
║ Provider: ${provider.padEnd(33)} ║
╠════════════════════════════════════════════╣
║ Max Loanable: ${maxLoanable.padEnd(29)} ║
║ Interest Rate: ${interestRate.padEnd(28)} ║
║ Category: ${data.category.padEnd(33)} ║
║ Computation: ${data.computation_type.padEnd(30)} ║
╠════════════════════════════════════════════╣
║ Status: ${(data.is_active ? 'Active' : 'Inactive').padEnd(35)} ║
╚════════════════════════════════════════════╝
            `.trim());
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load loan type details.');
        });
}

function editLoanType(id) {
    // TODO: Implement edit functionality
    alert('Edit loan type functionality - Coming soon!');
}

function deleteLoanType(id, name) {
    if (!confirm(`Are you sure you want to delete the loan type "${name}"?\n\nThis action cannot be undone.`)) {
        return;
    }
    
    // Create a form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/admin/deductions/loan-types/${id}`;
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);
    
    // Add DELETE method
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    document.body.appendChild(form);
    form.submit();
}
</script>

@include('admin.deductions.modals.addLoanTypeModal')

<script>
// Ensure modal functions are in global scope
window.openAddLoanTypeModal = function() {
    document.getElementById('addLoanTypeModal').classList.add('active');
};

window.closeAddLoanTypeModal = function(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addLoanTypeModal').classList.remove('active');
    document.getElementById('addLoanTypeForm').reset();
};

window.updateLoanCode = function() {
    const provider = document.getElementById('loanProvider').value;
    const name = document.getElementById('loanTypeName').value;
    const codeInput = document.getElementById('loanTypeCode');
    
    if (provider && name) {
        const namePart = name.toUpperCase()
            .replace(/[^A-Z0-9\s]/g, '')
            .split(' ')
            .map(word => word.substring(0, 4))
            .join('_')
            .substring(0, 20);
        
        const code = `${provider}_${namePart}`;
        codeInput.value = code;
    }
};
</script>
