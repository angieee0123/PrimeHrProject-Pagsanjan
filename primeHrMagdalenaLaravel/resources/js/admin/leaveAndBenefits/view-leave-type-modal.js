// View Leave Type Modal

window.viewLeaveType = function(code) {
    fetch(`/admin/leave/types/${code}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('viewLeaveCode').textContent = data.leave_code;
            document.getElementById('viewLeaveName').textContent = data.leave_name;
            document.getElementById('viewAnnualLimit').textContent = data.annual_limit > 0 ? `${data.annual_limit} days` : 'As needed';
            document.getElementById('viewLeaveTypeAccrual').textContent = data.is_accrued ? 'Accrued' : 'Fixed';

            const statusBadge = document.getElementById('viewLeaveStatus');
            statusBadge.textContent = data.is_active ? 'Active' : 'Inactive';
            statusBadge.className = data.is_active ? 'badge-status processed' : 'badge-status on-hold';

            const configContainer = document.getElementById('viewLeaveConfig');
            configContainer.innerHTML = '';
            const configs = [];
            if (data.is_accrued) configs.push('Accrued');
            if (data.is_cumulative) configs.push('Cumulative');
            if (data.requires_6_months) configs.push('Requires 6 Months');
            if (data.is_monetizable) configs.push('Monetizable');
            if (data.requires_attachment) configs.push('Requires Attachment');

            if (configs.length > 0) {
                configs.forEach(config => {
                    const badge = document.createElement('span');
                    badge.className = 'config-badge';
                    badge.textContent = config;
                    configContainer.appendChild(badge);
                });
            } else {
                configContainer.innerHTML = '<span style="color: #9ca3af; font-size: 13px;">No special configuration</span>';
            }

            const attachmentGroup = document.getElementById('viewAttachmentInfoGroup');
            if (data.attachment_info) {
                document.getElementById('viewAttachmentInfo').textContent = data.attachment_info;
                attachmentGroup.style.display = 'block';
            } else {
                attachmentGroup.style.display = 'none';
            }

            const documentGroup = document.getElementById('viewDocumentGroup');
            if (data.document_path) {
                document.getElementById('viewDocumentLink').href = `/storage/${data.document_path}`;
                documentGroup.style.display = 'block';
            } else {
                documentGroup.style.display = 'none';
            }

            document.getElementById('viewLeaveTypeModal').setAttribute('data-leave-code', code);
            document.getElementById('viewLeaveTypeModal').classList.add('active');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load leave type details');
        });
}

window.closeViewLeaveTypeModal = function(event) {
    if (!event || event.target.id === 'viewLeaveTypeModal') {
        document.getElementById('viewLeaveTypeModal').classList.remove('active');
    }
}

window.editLeaveTypeFromView = function() {
    const code = document.getElementById('viewLeaveTypeModal').getAttribute('data-leave-code');
    closeViewLeaveTypeModal();
    editLeaveType(code);
}
