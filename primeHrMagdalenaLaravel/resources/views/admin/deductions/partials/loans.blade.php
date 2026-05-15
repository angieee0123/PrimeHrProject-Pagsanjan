<div class="table-header" style="margin-bottom: 16px;">
    <div>
        <h3 class="table-title" style="font-size: 16px; margin-bottom: 4px;">Employee Loans</h3>
        <p class="table-sub">Manage GSIS and Pag-IBIG loans with automatic balance tracking</p>
    </div>
    <div class="table-actions">
        <input type="text" id="searchLoan" class="filter-select" placeholder="Search employee..." style="width: 200px;" onkeyup="filterLoans()">
        <select id="filterLoanType" class="filter-select" onchange="filterLoans()">
            <option value="">All Loan Types</option>
            @foreach(\App\Models\DeductionType::where('category', 'LOAN')->where('is_active', true)->orderBy('name')->get() as $loanType)
                <option value="{{ $loanType->id }}">{{ $loanType->name }}</option>
            @endforeach
        </select>
        <select id="filterLoanStatus" class="filter-select" onchange="filterLoans()">
            <option value="">All Status</option>
            <option value="ACTIVE">Active</option>
            <option value="COMPLETED">Completed</option>
            <option value="SUSPENDED">Suspended</option>
        </select>
        <button class="btn-export" onclick="exportLoans()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="7 10 12 15 17 10"/>
                <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export
        </button>
        <button class="modal-btn-primary" style="padding: 7px 16px; font-size: 12.5px; display: flex; align-items: center; gap: 6px;" onclick="openAddLoanModal()">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Loan
        </button>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee</th>
                <th>Department</th>
                <th>Loan Type</th>
                <th>Provider</th>
                <th>Total Amount</th>
                <th>Remaining Balance</th>
                <th>Monthly Installment</th>
                <th>Per Cutoff</th>
                <th>Progress</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="loansTableBody">
            @forelse($loans as $loan)
                @php
                    $progress = $loan->total_amount > 0 ? (($loan->total_amount - $loan->remaining_balance) / $loan->total_amount) * 100 : 0;
                    
                    // Get the deduction schedule
                    $schedule = $loan->deductionType->schedules->first();
                    $cutoffSchedule = $schedule ? $schedule->cutoff_schedule : 'BOTH_SPLIT';
                    
                    // Calculate per-cutoff based on schedule
                    $monthlyInstallment = $loan->installment_amount ?? 0;
                    if ($cutoffSchedule === '1ST_ONLY') {
                        $perCutoff1st = $monthlyInstallment;
                        $perCutoff2nd = 0;
                        $perCutoffDisplay = '₱' . number_format($perCutoff1st, 2) . ' (1st only)';
                    } elseif ($cutoffSchedule === '2ND_ONLY') {
                        $perCutoff1st = 0;
                        $perCutoff2nd = $monthlyInstallment;
                        $perCutoffDisplay = '₱' . number_format($perCutoff2nd, 2) . ' (2nd only)';
                    } elseif ($cutoffSchedule === 'BOTH_FULL') {
                        $perCutoff1st = $monthlyInstallment;
                        $perCutoff2nd = $monthlyInstallment;
                        $perCutoffDisplay = '₱' . number_format($monthlyInstallment, 2) . ' (each cutoff)';
                    } else { // BOTH_SPLIT (default)
                        $perCutoff1st = $monthlyInstallment / 2;
                        $perCutoff2nd = $monthlyInstallment / 2;
                        $perCutoffDisplay = '₱' . number_format($perCutoff1st, 2) . ' (split)';
                    }
                    
                    // Determine provider from loan type code
                    $provider = 'Other';
                    if (str_contains($loan->deductionType->code, 'GSIS')) {
                        $provider = 'GSIS';
                    } elseif (str_contains($loan->deductionType->code, 'PAGIBIG')) {
                        $provider = 'Pag-IBIG';
                    }
                @endphp
                <tr data-employee="{{ strtolower($loan->employee->first_name . ' ' . $loan->employee->last_name) }}" 
                    data-loan-type="{{ $loan->deduction_type_id }}" 
                    data-status="{{ $loan->status }}">
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar" style="background: {{ $avatarColors[($loan->employee_id ?? 0) % count($avatarColors)] }};">
                                {{ getInitials($loan->employee->first_name . ' ' . $loan->employee->last_name) }}
                            </div>
                            <div>
                                <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">
                                    {{ $loan->employee->first_name }} {{ $loan->employee->last_name }}
                                </p>
                                <p style="color: #9999bb; margin: 0; font-size: 11px;">ID: {{ $loan->employee->employee_id }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ $loan->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">{{ $loan->deductionType->name }}</p>
                            <p style="color: #9999bb; margin: 0; font-size: 11px;">{{ $loan->deductionType->code }}</p>
                        </div>
                    </td>
                    <td>
                        @php
                            $providerColors = [
                                'GSIS' => ['bg' => '#0b044d18', 'text' => '#0b044d'],
                                'Pag-IBIG' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'Other' => ['bg' => '#6b6a8a18', 'text' => '#6b6a8a'],
                            ];
                            $providerColor = $providerColors[$provider] ?? $providerColors['Other'];
                        @endphp
                        <span class="badge" style="background: {{ $providerColor['bg'] }}; color: {{ $providerColor['text'] }};">
                            {{ $provider }}
                        </span>
                    </td>
                    <td>
                        <span style="font-weight: 600; color: #0b044d; font-size: 13px;">
                            ₱{{ number_format($loan->total_amount ?? 0, 2) }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p style="font-weight: 600; color: {{ $loan->remaining_balance > 0 ? '#d9bb00' : '#15803d' }}; margin: 0; font-size: 13px;">
                                ₱{{ number_format($loan->remaining_balance ?? 0, 2) }}
                            </p>
                            @if($loan->remaining_balance > 0)
                                <p style="color: #9999bb; margin: 0; font-size: 11px;">
                                    {{ number_format($progress, 1) }}% paid
                                </p>
                            @else
                                <p style="color: #15803d; margin: 0; font-size: 11px;">Fully paid</p>
                            @endif
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 13px; color: #6b6a8a;">
                            ₱{{ number_format($loan->installment_amount ?? 0, 2) }}
                        </span>
                    </td>
                    <td>
                        <div>
                            <p style="font-weight: 600; color: #0b044d; margin: 0; font-size: 13px;">
                                {!! $perCutoffDisplay !!}
                            </p>
                            <p style="color: #9999bb; margin: 0; font-size: 11px;">Schedule: {{ $cutoffSchedule }}</p>
                        </div>
                    </td>
                    <td style="min-width: 120px;">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <div style="width: 100%; height: 6px; background: #f0effe; border-radius: 3px; overflow: hidden;">
                                <div style="width: {{ $progress }}%; height: 100%; background: {{ $progress >= 100 ? '#15803d' : '#0b044d' }}; transition: width 0.3s;"></div>
                            </div>
                            <span style="font-size: 11px; color: #6b6a8a;">{{ number_format($progress, 1) }}%</span>
                        </div>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ \Carbon\Carbon::parse($loan->start_date)->format('M d, Y') }}
                        </span>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: #6b6a8a;">
                            {{ $loan->end_date ? \Carbon\Carbon::parse($loan->end_date)->format('M d, Y') : 'Ongoing' }}
                        </span>
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'ACTIVE' => ['bg' => '#15803d18', 'text' => '#15803d'],
                                'SUSPENDED' => ['bg' => '#d9bb0018', 'text' => '#d9bb00'],
                                'COMPLETED' => ['bg' => '#6b6a8a18', 'text' => '#6b6a8a'],
                            ];
                            $statusColor = $statusColors[$loan->status] ?? $statusColors['ACTIVE'];
                        @endphp
                        <span class="badge" style="background: {{ $statusColor['bg'] }}; color: {{ $statusColor['text'] }};">
                            {{ $loan->status }}
                        </span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 6px;">
                            <button class="action-btn" onclick="viewLoanDetails({{ $loan->id }})" title="View Details">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                            <button class="action-btn" onclick="editEmployeeDeduction({{ $loan->id }})" title="Edit">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                </svg>
                            </button>
                            <button class="action-btn" onclick="deleteEmployeeDeduction({{ $loan->id }}, '{{ $loan->employee->first_name }} {{ $loan->employee->last_name }}', '{{ $loan->deductionType->name }}')" title="Delete" style="color: #8e1e18;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6"/>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr id="noLoansRow">
                    <td colspan="13" style="text-align: center; padding: 40px; color: #9999bb;">
                        No loans found. Click "Add Loan" to create a new loan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-footer">
    <p>Showing <strong id="showingLoansCount">{{ $loans->count() }}</strong> of <strong id="totalLoansCount">{{ $loans->count() }}</strong> loans</p>
</div>

<script>
function filterLoans() {
    const searchTerm = document.getElementById('searchLoan').value.toLowerCase();
    const loanTypeFilter = document.getElementById('filterLoanType').value;
    const statusFilter = document.getElementById('filterLoanStatus').value;
    const rows = document.querySelectorAll('#loansTableBody tr:not(#noLoansRow)');
    
    let visibleCount = 0;
    
    rows.forEach(row => {
        const employeeName = row.dataset.employee || '';
        const loanType = row.dataset.loanType || '';
        const status = row.dataset.status || '';
        
        const matchesSearch = employeeName.includes(searchTerm);
        const matchesType = !loanTypeFilter || loanType === loanTypeFilter;
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesType && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update showing count
    document.getElementById('showingLoansCount').textContent = visibleCount;
    
    // Show/hide no data row
    const noLoansRow = document.getElementById('noLoansRow');
    if (noLoansRow) {
        noLoansRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function viewLoanDetails(id) {
    // Fetch loan data
    fetch(`/admin/deductions/employee/${id}`)
        .then(response => response.json())
        .then(data => {
            const employeeName = `${data.employee.first_name} ${data.employee.last_name}`;
            const loanType = data.deduction_type.name;
            const totalAmount = parseFloat(data.total_amount || 0);
            const remainingBalance = parseFloat(data.remaining_balance || 0);
            const installment = parseFloat(data.installment_amount || 0);
            const amountPaid = totalAmount - remainingBalance;
            const progress = totalAmount > 0 ? (amountPaid / totalAmount) * 100 : 0;
            const monthsRemaining = installment > 0 ? Math.ceil(remainingBalance / installment) : 0;
            
            // Get schedule from deduction type
            const schedule = data.deduction_type.schedules && data.deduction_type.schedules.length > 0 
                ? data.deduction_type.schedules[0].cutoff_schedule 
                : 'BOTH_SPLIT';
            
            // Calculate per-cutoff based on schedule
            let perCutoff1st, perCutoff2nd, scheduleText;
            if (schedule === '1ST_ONLY') {
                perCutoff1st = installment;
                perCutoff2nd = 0;
                scheduleText = '1st Cutoff Only';
            } else if (schedule === '2ND_ONLY') {
                perCutoff1st = 0;
                perCutoff2nd = installment;
                scheduleText = '2nd Cutoff Only';
            } else if (schedule === 'BOTH_FULL') {
                perCutoff1st = installment;
                perCutoff2nd = installment;
                scheduleText = 'Both Cutoffs (Full Amount Each)';
            } else { // BOTH_SPLIT
                perCutoff1st = installment / 2;
                perCutoff2nd = installment / 2;
                scheduleText = 'Both Cutoffs (Split 50-50)';
            }
            
            const message = `
╔════════════════════════════════════════════╗
║          LOAN DETAILS                      ║
╠════════════════════════════════════════════╣
║ Employee: ${employeeName.padEnd(32)} ║
║ Loan Type: ${loanType.padEnd(31)} ║
╠════════════════════════════════════════════╣
║ Total Amount: ₱${totalAmount.toFixed(2).padStart(26)} ║
║ Amount Paid: ₱${amountPaid.toFixed(2).padStart(27)} ║
║ Remaining Balance: ₱${remainingBalance.toFixed(2).padStart(21)} ║
║ Progress: ${progress.toFixed(1)}%${' '.repeat(32 - progress.toFixed(1).length)} ║
╠════════════════════════════════════════════╣
║ Monthly Installment: ₱${installment.toFixed(2).padStart(19)} ║
║ Schedule: ${scheduleText.padEnd(31)} ║
║ 1st Cutoff: ₱${perCutoff1st.toFixed(2).padStart(26)} ║
║ 2nd Cutoff: ₱${perCutoff2nd.toFixed(2).padStart(26)} ║
║ Months Remaining: ${monthsRemaining} months${' '.repeat(22 - monthsRemaining.toString().length)} ║
╠════════════════════════════════════════════╣
║ Start Date: ${new Date(data.start_date).toLocaleDateString().padEnd(28)} ║
║ End Date: ${(data.end_date ? new Date(data.end_date).toLocaleDateString() : 'Ongoing').padEnd(30)} ║
║ Status: ${data.status.padEnd(32)} ║
╠════════════════════════════════════════════╣
║ Remarks: ${(data.remarks || 'None').padEnd(31)} ║
╚════════════════════════════════════════════╝
            `.trim();
            
            alert(message);
        })
        .catch(error => {
            console.error('Error fetching loan details:', error);
            alert('Failed to load loan details.');
        });
}

function exportLoans() {
    window.location.href = '/admin/deductions/loans/export';
}
</script>

@include('admin.deductions.modals.addLoanModal')

<script>
// Ensure modal functions are in global scope
window.openAddLoanModal = function() {
    document.getElementById('addLoanModal').classList.add('active');
};

window.closeAddLoanModal = function(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addLoanModal').classList.remove('active');
    document.getElementById('addLoanForm').reset();
    document.getElementById('providerName').value = '';
    document.getElementById('otherProviderFields').style.display = 'none';
    document.getElementById('otherProviderName').removeAttribute('required');
    document.getElementById('otherLoanType').removeAttribute('required');
};
</script>
