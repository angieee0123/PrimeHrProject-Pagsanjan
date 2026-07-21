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
        fetch(`${window.payrollRoutes.payslipApprove}/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
        fetch(`${window.payrollRoutes.payslipApprove}/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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
    window.location.href = `${window.payrollRoutes.payslipsExport}?status=${status}`;
}

function printPayslipDirect(id) {
    viewPayslipDetail(id);
    setTimeout(() => {
        window.print();
    }, 500);
}

window.filterPayslips = filterPayslips;
window.approvePayslip = approvePayslip;
window.rejectPayslip = rejectPayslip;
window.exportPayslips = exportPayslips;
window.printPayslipDirect = printPayslipDirect;
