<div class="table-header">
    <div>
        <h3 class="table-title">Payslip Management</h3>
        <p class="table-sub">View and manage all generated payslips</p>
    </div>
    <div class="table-actions">
        <select id="statusFilter" class="filter-select" onchange="filterPayslips()">
            <option value="">All Status</option>
            <option value="pending">Pending/Draft</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
        <button class="btn-export" onclick="exportPayslips()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export
        </button>
    </div>
</div>

<div class="table-wrapper">
    <table class="payroll-table">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Department</th>
                <th>Period</th>
                <th>Basic Pay</th>
                <th>Deductions</th>
                <th>Net Pay</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="payslipsTableBody">
            @forelse($salaryComputations ?? [] as $computation)
            <tr data-status="{{ $computation->status }}">
                <td>{{ $computation->employee->employee_id ?? 'N/A' }}</td>
                <td>{{ $computation->employee->first_name ?? '' }} {{ $computation->employee->last_name ?? '' }}</td>
                <td>{{ $computation->employee->employmentDetail->departmentRelation->name ?? 'N/A' }}</td>
                <td class="table-cell-period">{{ $computation->period_start->format('M d') }}-{{ $computation->period_end->format('d, Y') }}</td>
                <td class="pay-cell">₱{{ number_format($computation->basic_pay ?? 0, 2) }}</td>
                <td class="deduction">₱{{ number_format(($computation->late_deduction ?? 0) + ($computation->undertime_deduction ?? 0) + ($computation->other_deductions ?? 0), 2) }}</td>
                <td class="net-pay">₱{{ number_format($computation->net_pay ?? 0, 2) }}</td>
                <td>
                    @if($computation->status === 'pending' || $computation->status === 'draft')
                        <span class="badge-status pending">Pending</span>
                    @elseif($computation->status === 'approved')
                        <span class="badge-status approved">Approved</span>
                    @elseif($computation->status === 'rejected')
                        <span class="badge-status rejected">Rejected</span>
                    @else
                        <span class="badge-status processed">{{ ucfirst($computation->status) }}</span>
                    @endif
                </td>
                <td>
                    <div class="row-actions">
                        <button class="btn-action btn-view" onclick="viewPayslipDetail({{ $computation->id }})" title="View Details">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        @if($computation->status === 'pending' || $computation->status === 'draft')
                        <button class="btn-action btn-approve" onclick="approvePayslip({{ $computation->id }})" title="Approve">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        </button>
                        <button class="btn-action btn-reject" onclick="rejectPayslip({{ $computation->id }})" title="Reject">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 40px; color: #9999bb;">
                    No payslips generated yet. Go to "Generate Payroll" tab to create payslips.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(isset($salaryComputations) && $salaryComputations->count() > 0)
<div class="table-footer">
    <span>Showing <strong>{{ $salaryComputations->firstItem() ?? 0 }}–{{ $salaryComputations->lastItem() ?? 0 }}</strong> of <strong>{{ $salaryComputations->total() }}</strong> payslips</span>
    <div class="pagination">
        @if($salaryComputations->onFirstPage())
            <button class="page-btn" disabled>‹</button>
        @else
            <a href="{{ $salaryComputations->appends(request()->except('page'))->previousPageUrl() }}" class="page-btn">‹</a>
        @endif
        
        @foreach($salaryComputations->getUrlRange(1, $salaryComputations->lastPage()) as $page => $url)
            <a href="{{ $salaryComputations->appends(request()->except('page'))->url($page) }}" class="page-btn {{ $page == $salaryComputations->currentPage() ? 'active' : '' }}">{{ $page }}</a>
        @endforeach
        
        @if($salaryComputations->hasMorePages())
            <a href="{{ $salaryComputations->appends(request()->except('page'))->nextPageUrl() }}" class="page-btn">›</a>
        @else
            <button class="page-btn" disabled>›</button>
        @endif
    </div>
</div>
@endif

@include('admin.payroll.modals.payslip-detail-modal')

<style>
.filter-select {
    padding: 8px 12px;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    font-size: 13px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: #fff;
    cursor: pointer;
}

.filter-select:focus {
    outline: none;
    border-color: #0b044d;
}

.btn-action {
    padding: 6px 8px;
    border: 1px solid #e8e7f5;
    border-radius: 6px;
    background: #fff;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-view {
    color: #0b044d;
}

.btn-view:hover {
    background: #f7f6ff;
    border-color: #0b044d;
}

.btn-approve {
    color: #15803d;
}

.btn-approve:hover {
    background: #f0fdf4;
    border-color: #15803d;
}

.btn-reject {
    color: #8e1e18;
}

.btn-reject:hover {
    background: #fef2f2;
    border-color: #8e1e18;
}

.badge-status.approved {
    background: #f0fdf4;
    color: #15803d;
    border: 1px solid #bbf7d0;
}

.badge-status.rejected {
    background: #fef2f2;
    color: #8e1e18;
    border: 1px solid #fecaca;
}
</style>

<script>
function filterPayslips() {
    const status = document.getElementById('statusFilter').value.toLowerCase();
    const rows = document.querySelectorAll('#payslipsTableBody tr[data-status]');
    
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        // Treat 'draft' as 'pending' for filtering
        const normalizedStatus = rowStatus === 'draft' ? 'pending' : rowStatus;
        
        if (status === '' || normalizedStatus === status) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function approvePayslip(id) {
    if (confirm('Are you sure you want to approve this payslip?')) {
        fetch(`/admin/payroll/payslip/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to approve payslip'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to approve payslip');
        });
    }
}

function rejectPayslip(id) {
    const reason = prompt('Please enter rejection reason:');
    if (reason) {
        fetch(`/admin/payroll/payslip/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to reject payslip'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to reject payslip');
        });
    }
}

function exportPayslips() {
    const status = document.getElementById('statusFilter').value;
    window.location.href = `/admin/payroll/payslips/export?status=${status}`;
}
</script>
