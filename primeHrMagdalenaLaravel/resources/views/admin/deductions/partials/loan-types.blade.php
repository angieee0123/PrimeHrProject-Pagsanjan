<div id="loan-types-tab" class="ded-hidden">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Loan Type Registry</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Register and manage reusable loan types that can be assigned to multiple employees</p>
        </div>
        <div class="table-actions">
            <button class="modal-btn-primary ded-btn-sm" onclick="openAddLoanTypeModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Register Loan Type
            </button>
        </div>
    </div>

<div class="ded-notice-banner">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"/>
        <line x1="12" y1="16" x2="12" y2="12"/>
        <line x1="12" y1="8" x2="12.01" y2="8"/>
    </svg>
    <div class="ded-notice-text">
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
                        <span class="ded-mono-code">
                            {{ $loanType->code }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p class="ded-cell-title">{{ $loanType->name }}</p>
                            <p class="ded-cell-sub">{{ $loanType->category }}</p>
                        </div>
                    </td>
                    <td>
                        @php
                            $providerColors = [
                                'GSIS' => ['bg' => '#0b044d18', 'text' => '#0b044d'],
                                'PAG-IBIG' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'OTHER' => ['bg' => '#56547a18', 'text' => '#56547a'],
                            ];
                            $providerColor = $providerColors[$provider] ?? $providerColors['OTHER'];
                        @endphp
                        <span class="badge" style="background: {{ $providerColor['bg'] }}; color: {{ $providerColor['text'] }};">
                            {{ $providerDisplay }}
                        </span>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ $loanType->max_amount ? '₱' . number_format($loanType->max_amount, 2) : 'No limit' }}
                        </span>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">
                            {{ $loanType->percentage_rate ? $loanType->percentage_rate . '%' : 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <span class="ded-text-muted-sm">N/A</span>
                    </td>
                    <td>
                        <div class="ded-row-flex">
                            <span style="font-size:14px;" class="ded-cell-title">{{ $employeesCount }}</span>
                            <span class="ded-cell-sub" style="font-size:11px;">{{ $employeesCount == 1 ? 'employee' : 'employees' }}</span>
                        </div>
                    </td>
                    <td>
                        @if($loanType->is_active)
                            <span class="badge ded-badge-active">Active</span>
                        @else
                            <span class="badge ded-badge-inactive">Inactive</span>
                        @endif
                    </td>
                    <td>
                        <div class="ded-actions">
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
                                <button class="action-btn ded-danger-btn" onclick="deleteLoanType({{ $loanType->id }}, '{{ $loanType->name }}')" title="Delete">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            @else
                                <button class="action-btn ded-disabled-btn" disabled title="Cannot delete - in use by {{ $employeesCount }} employee(s)">
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
                    <td colspan="9" class="ded-empty-cell">
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
</section>

@push('scripts')
    @vite('resources/js/admin/deductions/loan-types.js')
@endpush

</div>
