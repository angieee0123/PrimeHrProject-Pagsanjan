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

function closeViewLoanTypeModal(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('viewLoanTypeModal').classList.remove('active');
}

function viewLoanTypeDetails(id) {
    // Was a window.alert() drawing a box out of ╔═╗ characters, padded with
    // padEnd(37) — unthemeable, and it truncated any name longer than the pad.
    fetch(`/admin/deductions/types/${encodeURIComponent(id)}`)
        .then(response => {
            if (!response.ok) throw new Error(`Request failed (${response.status})`);
            return response.json();
        })
        .then(data => {
            const set = (el, value) => { document.getElementById(el).textContent = value; };

            const provider = data.code.includes('GSIS') ? 'GSIS'
                : (data.code.includes('PAGIBIG') || data.code.includes('PAG-IBIG')) ? 'Pag-IBIG'
                : 'Other';

            set('viewLoanTypeName', data.name);
            set('viewLoanTypeCode', data.code);
            set('viewLoanTypeProvider', provider);
            set('viewLoanTypeCategory', data.category.charAt(0) + data.category.slice(1).toLowerCase());

            // Say where the figure lives rather than printing "N/A": for every
            // loan type here the amount is agreed per employee.
            set('viewLoanTypeMax', data.max_amount
                ? '₱' + Number(data.max_amount).toLocaleString('en-PH', { minimumFractionDigits: 2 })
                : 'Set per employee');
            set('viewLoanTypeRate', data.percentage_rate ? data.percentage_rate + '%' : 'Not set');
            set('viewLoanTypeComputation', data.computation_type === 'PERCENTAGE'
                ? 'Percentage of salary'
                : 'Fixed amount');

            const count = data.employees_count ?? 0;
            set('viewLoanTypeUsage', count === 0
                ? 'Not assigned to anyone'
                : `${count} ${count === 1 ? 'employee' : 'employees'}`);

            const status = document.getElementById('viewLoanTypeStatus');
            status.className = 'badge-status ' + (data.is_active ? 'processed' : 'is-neutral');
            status.textContent = data.is_active ? 'Active' : 'Inactive';

            // Reading the details is usually the step before changing them.
            document.getElementById('viewLoanTypeEditBtn').onclick = () => {
                closeViewLoanTypeModal();
                editLoanType(data.id);
            };

            document.getElementById('viewLoanTypeModal').classList.add('active');
        })
        .catch(error => {
            console.error('Loan type details failed to load:', error);
            alert('Could not load that loan type. Please refresh the page and try again.');
        });
}

function editLoanType(id) {
    // Fetch loan type data
    fetch(`/admin/deductions/types/${encodeURIComponent(id)}`)
        .then(response => response.json())
        .then(data => {
            // Determine provider from code
            let provider = 'OTHER';
            if (data.code.includes('GSIS')) provider = 'GSIS';
            else if (data.code.includes('PAGIBIG')) provider = 'PAGIBIG';
            else if (data.code.includes('SSS')) provider = 'SSS';
            else if (data.code.includes('BANK')) provider = 'BANK';
            else if (data.code.includes('COOP')) provider = 'COOP';

            // Populate form
            document.getElementById('editLoanTypeId').value = data.id;
            document.getElementById('editLoanProvider').value = provider;
            document.getElementById('editLoanTypeCode').value = data.code;
            document.getElementById('editLoanTypeName').value = data.name;
            document.getElementById('editMaxLoanable').value = data.max_amount || '';
            document.getElementById('editInterestRate').value = data.percentage_rate || '';
            document.getElementById('editMaxTerms').value = '';
            document.getElementById('editIsActive').value = data.is_active ? '1' : '0';
            document.getElementById('editDescription').value = '';

            // Show warning if employees are using this loan type
            if (data.employees_count > 0) {
                document.getElementById('editEmployeesUsingWarning').style.display = 'block';
                document.getElementById('editEmployeesCount').textContent = data.employees_count;
            } else {
                document.getElementById('editEmployeesUsingWarning').style.display = 'none';
            }

            // Set form action
            document.getElementById('editLoanTypeForm').action = `/admin/deductions/loan-types/${data.id}`;

            // Open modal
            document.getElementById('editLoanTypeModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load loan type details.');
        });
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

// Ensure modal functions are in global scope
window.openAddLoanTypeModal = function() {
    document.getElementById('addLoanTypeModal').classList.add('active');
};

window.closeAddLoanTypeModal = function(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('addLoanTypeModal').classList.remove('active');
    document.getElementById('addLoanTypeForm').reset();
};

window.closeEditLoanTypeModal = function(event) {
    if (event && event.target !== event.currentTarget) return;
    document.getElementById('editLoanTypeModal').classList.remove('active');
    document.getElementById('editLoanTypeForm').reset();
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

window.filterLoanTypes = filterLoanTypes;
window.viewLoanTypeDetails = viewLoanTypeDetails;
window.closeViewLoanTypeModal = closeViewLoanTypeModal;
window.editLoanType = editLoanType;
window.deleteLoanType = deleteLoanType;
