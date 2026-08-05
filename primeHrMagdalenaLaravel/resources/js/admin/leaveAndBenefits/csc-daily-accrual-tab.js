// CSC Daily Accrual Tab

let _currentViewId = null;

window.changeAccrualRowsPerPage = function () {
    const perPage = document.getElementById('accrualRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('accrual_per_page', perPage);
    url.searchParams.set('tab', 'accrual');
    url.searchParams.delete('page');
    window.location.href = url.toString();
};

window.filterAccrualRates = function () {
    const statusFilter = document.getElementById('filterAccrualStatus')?.value || 'all';
    const frequencyFilter = document.getElementById('filterAccrualFrequency')?.value || 'all';
    document.querySelectorAll('.accrual-rate-row').forEach(row => {
        const matchStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
        const matchFreq = frequencyFilter === 'all' || row.dataset.frequency === frequencyFilter;
        row.style.display = matchStatus && matchFreq ? '' : 'none';
    });
};

window.navigateToAccrualPage = function (url) {
    const urlObj = new URL(url, window.location.origin);
    urlObj.searchParams.set('tab', 'accrual');
    window.location.href = urlObj.toString();
};

// ── View ──────────────────────────────────────────────────────────────────────

window.viewAccrualRate = function (id) {
    _currentViewId = id;
    fetch(`/admin/leave/accrual-rates/${id}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('viewAccrualLeaveCode').textContent = data.leave_code ?? (data.leave_type?.leave_code ?? '');
        document.getElementById('viewAccrualLeaveName').textContent = data.leave_type?.leave_name ?? '';

        const freqBadge = document.getElementById('viewAccrualFrequency');
        freqBadge.textContent = data.accrual_frequency.charAt(0).toUpperCase() + data.accrual_frequency.slice(1);
        freqBadge.className = 'badge-status ' + (data.accrual_frequency === 'daily' ? 'processed' : data.accrual_frequency === 'monthly' ? 'pending' : 'on-hold');

        const statusBadge = document.getElementById('viewAccrualStatus');
        statusBadge.textContent = data.is_active ? 'Active' : 'Inactive';
        statusBadge.className = 'badge-status ' + (data.is_active ? 'processed' : 'on-hold');

        document.getElementById('viewAccrualDaysService').textContent = parseFloat(data.days_of_service_required).toFixed(2) + ' days';
        document.getElementById('viewAccrualCredits').textContent = parseFloat(data.credits_earned_per_period).toFixed(4) + ' credits';
        document.getElementById('viewAccrualEffectiveDate').textContent = data.effective_date ?? '—';
        document.getElementById('viewAccrualEndDate').textContent = data.end_date ?? '—';

        const notesGroup = document.getElementById('viewAccrualNotesGroup');
        if (data.notes) {
            document.getElementById('viewAccrualNotes').textContent = data.notes;
            notesGroup.style.display = '';
        } else {
            notesGroup.style.display = 'none';
        }

        document.getElementById('viewAccrualRateModal').classList.add('active');
    })
    .catch(() => alert('Failed to load accrual rate details.'));
};

window.closeViewAccrualRateModal = function (e) {
    if (e && e.target !== document.getElementById('viewAccrualRateModal')) return;
    document.getElementById('viewAccrualRateModal').classList.remove('active');
};

window.switchToEditFromView = function () {
    document.getElementById('viewAccrualRateModal').classList.remove('active');
    editAccrualRate(_currentViewId);
};

// ── Edit ──────────────────────────────────────────────────────────────────────

window.editAccrualRate = function (id) {
    fetch(`/admin/leave/accrual-rates/${id}`, {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('editAccrualRateId').value = id;
        document.getElementById('editAccrualLeaveTypeId').value = data.leave_type_id;
        document.getElementById('editAccrualFrequency').value = data.accrual_frequency;
        document.getElementById('editAccrualDaysService').value = parseFloat(data.days_of_service_required).toFixed(2);
        document.getElementById('editAccrualCredits').value = parseFloat(data.credits_earned_per_period).toFixed(4);
        document.getElementById('editAccrualEffectiveDate').value = data.effective_date ?? '';
        document.getElementById('editAccrualEndDate').value = data.end_date ?? '';
        document.getElementById('editAccrualIsActive').value = data.is_active ? '1' : '0';
        document.getElementById('editAccrualNotes').value = data.notes ?? '';
        document.getElementById('editAccrualError').style.display = 'none';

        document.getElementById('editAccrualRateModal').classList.add('active');
    })
    .catch(() => alert('Failed to load accrual rate for editing.'));
};

window.closeEditAccrualRateModal = function (e) {
    if (e && e.target !== document.getElementById('editAccrualRateModal')) return;
    document.getElementById('editAccrualRateModal').classList.remove('active');
};

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('editAccrualRateForm')?.addEventListener('submit', function (e) {
        e.preventDefault();

        const id = document.getElementById('editAccrualRateId').value;
        const submitBtn = document.getElementById('editAccrualSubmitBtn');
        const errorBox = document.getElementById('editAccrualError');
        const original = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Saving...';
        errorBox.style.display = 'none';

        const formData = new FormData(this);
        // FormData doesn't send PUT natively — use fetch with JSON body
        const payload = {
            leave_type_id: formData.get('leave_type_id'),
            accrual_frequency: formData.get('accrual_frequency'),
            days_of_service_required: formData.get('days_of_service_required'),
            credits_earned_per_period: formData.get('credits_earned_per_period'),
            effective_date: formData.get('effective_date'),
            end_date: formData.get('end_date') || null,
            is_active: formData.get('is_active'),
            notes: formData.get('notes') || null,
        };

        fetch(`/admin/leave/accrual-rates/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success !== false) {
                submitBtn.innerHTML = '✓ Saved!';
                submitBtn.style.background = '#15803d';
                setTimeout(() => {
                    document.getElementById('editAccrualRateModal').classList.remove('active');
                    window.location.reload();
                }, 800);
            } else {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Failed to save changes.');
                errorBox.textContent = msg;
                errorBox.style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = original;
            }
        })
        .catch(() => {
            errorBox.textContent = 'An error occurred. Please try again.';
            errorBox.style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = original;
        });
    });
});
