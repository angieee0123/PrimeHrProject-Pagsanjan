// Edit DTR Modal
let currentEditId = null;

window.openEditModal = function(record) {
    currentEditId = record.employee_id;

    document.getElementById('editName').textContent = record.name;
    document.getElementById('editEmpId').textContent = record.id;
    document.getElementById('editPresent').value = record.present;
    document.getElementById('editAbsent').value = record.absent;
    document.getElementById('editLate').value = record.late;
    document.getElementById('editHalfday').value = record.halfday;
    document.getElementById('editOT').value = record.overtime;
    document.getElementById('editStatus').value = record.status;

    updateRatePreview();

    ['editPresent', 'editAbsent', 'editHalfday'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateRatePreview);
    });

    renderEditPassSlipList(record.pass_slips || []);

    document.getElementById('editModal').style.display = 'flex';
}

function renderEditPassSlipList(passSlips) {
    const container = document.getElementById('editPassSlipList');
    if (!passSlips.length) {
        container.innerHTML = '<div class="edit-passslip-empty">No approved pass slips in this period.</div>';
        return;
    }

    const fmt12 = (t) => {
        if (!t) return null;
        const [h, m] = t.split(':').map(Number);
        const suffix = h >= 12 ? 'PM' : 'AM';
        const h12 = h % 12 || 12;
        return `${h12}:${String(m).padStart(2, '0')} ${suffix}`;
    };

    container.innerHTML = passSlips.map(slip => {
        const isOfficial = slip.type === 'official_activity';
        const badgeClass = isOfficial ? 'official' : 'personal';
        const badgeLabel = isOfficial ? 'Official Activity' : 'Personal Reason';
        const gapNote = slip.gap_minutes > 0
            ? (slip.excused ? `Excused ${slip.gap_minutes} min (from approved Pass Slip)` : `Charged ${slip.gap_minutes} min undertime (from approved Pass Slip)`)
            : 'Times from approved Pass Slip';
        const timeRange = fmt12(slip.time_out) && fmt12(slip.time_in)
            ? `${fmt12(slip.time_out)} – ${fmt12(slip.time_in)}`
            : (fmt12(slip.time_out) || '');
        const slipNum = slip.slip_number ? `<span class="eps-slip-num">#${slip.slip_number}</span>` : '';

        return `
            <div class="edit-passslip-item">
                <div>
                    <div class="eps-date">${slip.date}${timeRange ? ' · ' + timeRange : ''} ${slipNum}</div>
                    <div class="eps-meta">${gapNote}</div>
                </div>
                <span class="eps-badge ${badgeClass}">${badgeLabel}</span>
            </div>
        `;
    }).join('');
}

window.closeEditModal = function() {
    document.getElementById('editModal').style.display = 'none';
}

function updateRatePreview() {
    const present = parseInt(document.getElementById('editPresent').value) || 0;
    const absent = parseInt(document.getElementById('editAbsent').value) || 0;
    const halfday = parseInt(document.getElementById('editHalfday').value) || 0;
    const workingDays = present + absent + halfday;
    const rate = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;
    document.getElementById('editRatePreview').textContent = rate + '%';
}

window.saveEdit = function() {
    alert('Save functionality to be implemented');
    closeEditModal();
}
