<div id="deduction-types-tab" style="display: block;">
<section class="table-section">
    <div class="table-header">
        <div>
            <h3 class="table-title">Deduction Types</h3>
            <p class="table-sub">Municipal Government of Pagsanjan · Manage mandatory contributions, loans, and other deduction types</p>
        </div>
        <div class="table-actions">
            <select class="filter-select">
                <option value="">All Categories</option>
                <option value="MANDATORY">Mandatory</option>
                <option value="LOAN">Loan</option>
                <option value="OTHER">Other</option>
            </select>
            <select class="filter-select">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <button class="btn-export">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </button>
            <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAddDeductionTypeModal()">
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
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deductionTypes = \App\Models\DeductionType::with('schedules')->orderBy('category')->orderBy('name')->get();
            @endphp
            @forelse($deductionTypes as $type)
            <tr>
                <td><strong style="color: #0b044d; font-size: 13px;">{{ $type->code }}</strong></td>
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
                        <span class="badge-status pending" style="background: #fff3e0; color: #e65100;">Employee Share</span>
                    @else
                        <span class="badge-status processed" style="background: #e8f5e9; color: #2e7d32;">Employer Share</span>
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
                <td>
                    <div class="row-actions">
                        <button class="btn-view" onclick="editDeductionType('{{ $type->code }}')">Edit</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" style="text-align: center; padding: 40px; color: #9999bb;">
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

@include('admin.deductions.modals.addDeductionTypeModal')
@include('admin.deductions.modals.editDeductionTypeModal')
</div>
