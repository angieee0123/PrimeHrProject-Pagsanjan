<div id="deduction-types-tab">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Types</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Manage mandatory contributions, loans, and other deduction types</p>
        </div>
        <div class="table-actions">
            {{-- Same navy pill as "File Travel Order". The three sibling tabs put
                 their own primary action in this exact slot, so they move
                 together — otherwise switching tabs would reshape the button. --}}
            <button class="btn-export adm-btn-primary-solid" onclick="openAddDeductionTypeModal()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Add Deduction Type
            </button>
        </div>
    </div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Category</th>
                <th>Deduction Type</th>
                <th>Computation Type</th>
                <th>Rate/Amount</th>
                <th>Base</th>
                <th>Max Amount</th>
                <th>Status</th>
                <th class="th-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deductionTypes = \App\Models\DeductionType::with('schedules')->orderBy('category')->orderBy('name')->get();
            @endphp
            @forelse($deductionTypes as $type)
            <tr>
                <td><strong class="ded-cell-title">{{ $type->code }}</strong></td>
                <td>{{ $type->name }}</td>
                <td>
                    @if($type->category === 'MANDATORY')
                        <span class="badge-status processed">MANDATORY</span>
                    @elseif($type->category === 'LOAN')
                        <span class="badge-emptype">LOAN</span>
                    @else
                        <span class="badge-status pending">OTHER</span>
                    @endif
                </td>
                <td>
                    @if($type->deducted_from_employee)
                        <span class="badge-status pending ded-badge-employee-share">Employee Share</span>
                    @else
                        <span class="badge-status processed ded-badge-employer-share">Employer Share</span>
                    @endif
                </td>
                <td>{{ $type->computation_type }}</td>
                <td class="pay-cell">
                    @if($type->computation_type === 'PERCENTAGE' && $type->percentage_rate)
                        {{ number_format($type->percentage_rate, 2) }}%
                    @elseif($type->computation_type === 'FIXED' && $type->max_amount)
                        ₱{{ number_format($type->max_amount, 2) }}
                    @else
                        —
                    @endif
                </td>
                <td>{{ $type->base_salary_type ?? '—' }}</td>
                <td class="net-pay">
                    @if($type->max_amount)
                        ₱{{ number_format($type->max_amount, 2) }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($type->is_active)
                        <span class="badge-status processed">Active</span>
                    @else
                        <span class="badge-status pending">Inactive</span>
                    @endif
                </td>
                <td class="td-center">
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('{{ $type->code }}')">Edit</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="ded-empty-cell">
                    No deduction types found. Click "Add Deduction Type" to create one.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

    <div class="table-footer">
        <p>Showing <strong>{{ $deductionTypes->count() }}</strong> of <strong>{{ $deductionTypes->count() }}</strong> deduction types</p>
    </div>
</section>
</div>
