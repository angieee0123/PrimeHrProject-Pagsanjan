{{--
    Deduction Types.

    Was ten columns in a 1142px wrapper, 18 of whose cells rendered as a bare
    em-dash: `max_amount` is null on every row, so "Rate/Amount" and "Max
    Amount" both showed nothing — and for a FIXED type they would have shown
    the *same* value anyway, since both read max_amount.

    Computation type, rate and base are one fact ("12.00% of monthly salary"),
    so they are now one column, and the raw enums (PERCENTAGE, FIXED, MONTHLY)
    no longer surface. Six columns, none of them empty.
--}}
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
    <table class="payroll-table ded-types-table">
        <thead>
            <tr>
                <th>Deduction</th>
                <th>Category</th>
                <th>Borne by</th>
                <th>How it is computed</th>
                <th>Status</th>
                <th class="row-menu-head">Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
                $deductionTypes = \App\Models\DeductionType::with('schedules')->orderBy('category')->orderBy('name')->get();
            @endphp
            @forelse($deductionTypes as $type)
                @php
                    $categoryClass = match ($type->category) {
                        'MANDATORY' => 'is-mandatory',
                        'LOAN'      => 'is-loan',
                        default     => 'is-other',
                    };

                    // Computation type, rate and base salary read as one
                    // sentence rather than three columns of enum values.
                    $base = match ($type->base_salary_type) {
                        'MONTHLY' => 'monthly salary',
                        'DAILY'   => 'daily rate',
                        'ANNUAL'  => 'annual salary',
                        default   => 'salary',
                    };

                    if ($type->computation_type === 'PERCENTAGE' && $type->percentage_rate) {
                        $rate = number_format($type->percentage_rate, 2) . '%';
                        $note = 'of ' . $base;
                    } elseif ($type->max_amount) {
                        $rate = '₱' . number_format($type->max_amount, 2);
                        $note = 'fixed amount';
                    } else {
                        // FIXED with no amount on the type: the figure lives on
                        // each employee's assignment, which is true of every
                        // loan here. Saying so beats printing an em-dash.
                        $rate = 'Set per employee';
                        $note = $type->category === 'LOAN' ? 'amount agreed per loan' : 'entered on assignment';
                    }
                @endphp
                <tr data-category="{{ $type->category }}" data-status="{{ $type->is_active ? '1' : '0' }}">
                    <td>
                        <p class="ded-cell-title" title="{{ $type->name }}">{{ $type->name }}</p>
                        <p class="ded-cell-sub"><span class="ded-code ded-code-lead">{{ $type->code }}</span></p>
                    </td>

                    <td>
                        <span class="ded-chip {{ $categoryClass }}">{{ ucfirst(strtolower($type->category)) }}</span>
                    </td>

                    <td>
                        {{-- Who pays: a classification, not a state, so it takes a
                             neutral chip rather than the green/amber status pills
                             it used to borrow. --}}
                        <span class="ded-chip is-other">
                            {{ $type->deducted_from_employee ? 'Employee' : 'Employer' }}
                        </span>
                    </td>

                    <td>
                        <p class="ded-amount ded-amount-left">{{ $rate }}</p>
                        <p class="ded-cell-sub">{{ $note }}</p>
                    </td>

                    <td>
                        <span class="badge-status {{ $type->is_active ? 'processed' : 'is-neutral' }}">
                            {{ $type->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td class="row-menu-cell">
                        <button type="button" class="btn-view ded-edit-btn" onclick="editDeductionType('{{ $type->code }}')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            Edit
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="ded-empty-cell">
                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" class="ded-empty-icon"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        <p class="ded-empty-title">No deduction types yet</p>
                        <p class="ded-empty-sub">Use <strong>Add Deduction Type</strong> to create the first one.</p>
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
