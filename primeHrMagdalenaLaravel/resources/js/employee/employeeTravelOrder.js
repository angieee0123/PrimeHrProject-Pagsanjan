// ── Sidebar / mobile menu toggle ──
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggle-btn');
const logoText = document.getElementById('logo-text');
const navLabel = document.getElementById('nav-label');
const userInfo = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn = document.getElementById('mobile-menu-btn');
const overlay = document.getElementById('mobile-overlay');

if (toggleBtn) {
    toggleBtn.addEventListener('click', () => {
        const collapsed = sidebar.classList.toggle('collapsed');
        toggleBtn.textContent = collapsed ? '›' : '‹';
        if (logoText) logoText.style.display = collapsed ? 'none' : '';
        if (navLabel) navLabel.style.display = collapsed ? 'none' : '';
        if (userInfo) userInfo.style.display = collapsed ? 'none' : '';
        if (sidebarFooter) sidebarFooter.classList.toggle('collapsed-footer', collapsed);
        document.querySelectorAll('.nav-label, .nav-active-bar').forEach(el => {
            el.style.display = collapsed ? 'none' : '';
        });
    });
}

if (mobileBtn) {
    mobileBtn.addEventListener('click', () => {
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
    });
}

if (overlay) {
    overlay.addEventListener('click', () => {
        sidebar.classList.remove('mobile-open');
        overlay.classList.remove('active');
    });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeTravelOrderModal();
    }
});

// ── Companion invitations tab: respond to a companion request ──
function respondToCompanionRequest(travelOrderId, response) {
    const verb = response === 'accepted' ? 'accept' : 'reject';
    if (!confirm(`Are you sure you want to ${verb} this companion request?`)) return;

    let note = null;
    if (response === 'rejected') {
        note = prompt('Optionally, tell the filer why you are rejecting (or leave blank):', '');
        if (note === null) return; // prompt cancelled
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/employee/travelorder/${travelOrderId}/companion-response`;

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = document.querySelector('meta[name="csrf-token"]')?.content;
    form.appendChild(csrf);

    const responseInput = document.createElement('input');
    responseInput.type = 'hidden';
    responseInput.name = 'response';
    responseInput.value = response;
    form.appendChild(responseInput);

    if (note) {
        const noteInput = document.createElement('input');
        noteInput.type = 'hidden';
        noteInput.name = 'response_note';
        noteInput.value = note.substring(0, 300);
        form.appendChild(noteInput);
    }

    document.body.appendChild(form);
    form.submit();
}

// ── Travel history tab: rows-per-page / status filter / (stub) sort ──
function changeTravelRowsPerPage() {
    const perPage = document.getElementById('travelRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Applies the status dropdown and the topbar search together, so neither one
// re-shows rows the other has filtered out.
function filterTravelOrders() {
    const statusFilter = document.getElementById('filterTravelStatus').value;
    const searchInput = document.getElementById('travelOrderSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.travel-order-row');

    rows.forEach(row => {
        const matchesStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
        const matchesQuery = query === '' || row.textContent.toLowerCase().includes(query);
        row.style.display = (matchesStatus && matchesQuery) ? '' : 'none';
    });
}

// NOTE: sortTravelOrders() and editTravelOrder() were already stubs (console.log
// only) before this refactor — the sortable column headers don't actually sort,
// and nothing calls editTravelOrder(). Kept as-is rather than removed.
function sortTravelOrders(column) {
    console.log('Sort by:', column);
}

function editTravelOrder(id) {
    // Open edit modal
    console.log('Edit travel order:', id);
}

// ── File Travel Order modal ──
function closeTravelOrderModal() {
    document.getElementById('fileTravelOrderModal').style.display = 'none';
    document.getElementById('travelOrderForm').reset();
    document.getElementById('travelFileNameDisplay').style.display = 'none';
    document.getElementById('travelErrorMessage').style.display = 'none';
    document.body.style.overflow = '';
    clearCompanionSelection();
    closeCompanionDropdown();
}

// ===== Travel companions multi-select =====
let selectedCompanions = {}; // id -> { name, empid, photo }

function toggleCompanionDropdown(event) {
    // Ignore clicks on chip remove buttons
    if (event.target.closest('.companion-chip-remove')) return;
    const dropdown = document.getElementById('companionDropdown');
    const isOpen = dropdown.style.display === 'block';
    dropdown.style.display = isOpen ? 'none' : 'block';
    if (!isOpen) {
        const search = document.getElementById('companionSearch');
        search.value = '';
        filterCompanionOptions();
        search.focus();
    }
}

function closeCompanionDropdown() {
    document.getElementById('companionDropdown').style.display = 'none';
}

function filterCompanionOptions() {
    const query = document.getElementById('companionSearch').value.toLowerCase().trim();
    document.querySelectorAll('.companion-option').forEach(option => {
        const haystack = (option.dataset.name + ' ' + option.dataset.empid).toLowerCase();
        option.style.display = haystack.includes(query) ? 'flex' : 'none';
    });
}

function toggleCompanionSelection(option) {
    const id = option.dataset.id;
    if (selectedCompanions[id]) {
        delete selectedCompanions[id];
        option.classList.remove('selected');
        option.querySelector('.companion-check').style.display = 'none';
    } else {
        selectedCompanions[id] = {
            name: option.dataset.name,
            empid: option.dataset.empid,
            photo: option.dataset.photo
        };
        option.classList.add('selected');
        option.querySelector('.companion-check').style.display = 'inline-flex';
    }
    renderCompanionChips();
}

function removeCompanion(id) {
    delete selectedCompanions[id];
    const option = document.querySelector(`.companion-option[data-id="${id}"]`);
    if (option) {
        option.classList.remove('selected');
        option.querySelector('.companion-check').style.display = 'none';
    }
    renderCompanionChips();
}

function clearCompanionSelection() {
    selectedCompanions = {};
    document.querySelectorAll('.companion-option').forEach(option => {
        option.classList.remove('selected');
        option.querySelector('.companion-check').style.display = 'none';
    });
    renderCompanionChips();
}

function companionAvatarHtml(companion) {
    if (companion.photo) {
        return `<img src="${companion.photo}" alt="" class="to-chip-avatar-img">`;
    }
    const initials = companion.name.split(' ').filter(Boolean).map(part => part[0]).slice(0, 2).join('').toUpperCase();
    return `<span class="to-chip-avatar-initials">${initials}</span>`;
}

function renderCompanionChips() {
    const box = document.getElementById('companionSelectBox');
    const placeholder = document.getElementById('companionPlaceholder');
    const hiddenInputs = document.getElementById('companionHiddenInputs');

    box.querySelectorAll('.companion-chip').forEach(chip => chip.remove());
    hiddenInputs.innerHTML = '';

    const ids = Object.keys(selectedCompanions);
    placeholder.style.display = ids.length ? 'none' : 'inline';

    ids.forEach(id => {
        const companion = selectedCompanions[id];

        const chip = document.createElement('span');
        chip.className = 'companion-chip to-companion-chip';
        chip.innerHTML = companionAvatarHtml(companion) +
            `<span>${companion.name}</span>` +
            `<button type="button" class="companion-chip-remove to-chip-remove" onclick="removeCompanion('${id}')" title="Remove">` +
            `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;
        box.insertBefore(chip, box.lastElementChild);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'companions[]';
        input.value = id;
        hiddenInputs.appendChild(input);
    });
}

// Close the companion dropdown when clicking anywhere else in the modal
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('companionDropdown');
    if (!dropdown || dropdown.style.display !== 'block') return;
    if (!e.target.closest('#companionDropdown') && !e.target.closest('#companionSelectBox')) {
        closeCompanionDropdown();
    }
});

function openTravelOrderModal() {
    document.getElementById('fileTravelOrderModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('travelDateFrom').min = today;
    document.getElementById('travelDateTo').min = today;
}

function calculateTravelDuration() {
    const fromDate = document.getElementById('travelDateFrom').value;
    const toDate = document.getElementById('travelDateTo').value;

    if (fromDate && toDate) {
        const from = new Date(fromDate);
        const to = new Date(toDate);

        if (to >= from) {
            const diffTime = Math.abs(to - from);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
            document.getElementById('travelDuration').value = diffDays;
        } else {
            document.getElementById('travelDuration').value = 1;
            showTravelError('Return date must be after departure date');
        }
    }
}

function handleTravelFileSelect(input) {
    const file = input.files[0];
    const display = document.getElementById('travelFileNameDisplay');

    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showTravelError('File size must not exceed 5MB');
            input.value = '';
            display.style.display = 'none';
            return;
        }

        display.textContent = `📎 ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        display.style.display = 'block';
    } else {
        display.style.display = 'none';
    }
}

// Dropzone label reads "drag and drop" — wire up the actual drop behavior.
const travelDropZone = document.getElementById('travelAttachmentDropZone');
if (travelDropZone) {
    const travelFileInput = document.getElementById('travelAttachment');

    ['dragover', 'dragenter'].forEach(evt => {
        travelDropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            travelDropZone.classList.add('to-dropzone-active');
        });
    });

    ['dragleave', 'dragend'].forEach(evt => {
        travelDropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            travelDropZone.classList.remove('to-dropzone-active');
        });
    });

    travelDropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        travelDropZone.classList.remove('to-dropzone-active');
        const file = e.dataTransfer.files[0];
        if (!file) return;
        travelFileInput.files = e.dataTransfer.files;
        handleTravelFileSelect(travelFileInput);
    });
}

function showTravelError(message) {
    const errorDiv = document.getElementById('travelErrorMessage');
    const errorText = document.getElementById('travelErrorMessageText');
    errorText.textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Character counter for purpose
document.addEventListener('DOMContentLoaded', function() {
    const purposeField = document.getElementById('purpose');
    const purposeCounter = document.getElementById('purposeCounter');

    if (purposeField && purposeCounter) {
        purposeField.addEventListener('input', function() {
            const length = this.value.length;
            purposeCounter.textContent = `${length} / 300`;
            if (length > 300) {
                purposeCounter.style.color = '#dc2626';
            } else {
                purposeCounter.style.color = '#9ca3af';
            }
        });
    }
});

// ── View Travel Order Detail modal ──
let currentTravelOrderId = null;

function closeTravelDetailModal() {
    document.getElementById('viewTravelDetailModal').style.display = 'none';
    document.body.style.overflow = '';
    currentTravelOrderId = null;
}

function viewTravelOrder(id) {
    currentTravelOrderId = id;

    fetch(`/employee/travelorder/${id}`)
        .then(response => response.json())
        .then(data => {
            // Set header info
            document.getElementById('detailOrderId').textContent = data.id;
            document.getElementById('detailDestination').textContent = data.destination;
            document.getElementById('detailTravelDates').textContent =
                new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) +
                ' — ' +
                new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

            // Set status badge
            const statusBadge = document.getElementById('detailTravelStatus');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Rejected';
            } else if (data.status === 'awaiting_companions') {
                statusBadge.className = 'badge-status pending to-badge-awaiting';
                statusBadge.textContent = 'Awaiting Companions';
            } else {
                statusBadge.className = 'badge-status to-badge-cancelled';
                statusBadge.textContent = 'Cancelled';
            }

            // Set travel details
            document.getElementById('detailDestinationFull').textContent = data.destination;
            document.getElementById('detailDepartureDate').textContent = new Date(data.travel_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailReturnDate').textContent = new Date(data.return_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
            document.getElementById('detailDuration').textContent = data.duration + ' days';
            document.getElementById('detailTransportation').textContent = data.transportation_mode || 'Not specified';
            document.getElementById('detailBudget').textContent = data.estimated_budget ? '₱' + parseFloat(data.estimated_budget).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : 'Not specified';
            document.getElementById('detailPurpose').textContent = data.purpose;

            // Show/hide approval section
            const approvalSection = document.getElementById('detailApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                document.getElementById('detailApprovedBy').textContent = data.approver ?
                    `${data.approver.employee?.first_name || 'Admin'} ${data.approver.employee?.last_name || 'User'}` :
                    'Admin User';
                document.getElementById('detailApprovedAt').textContent = data.approved_at ?
                    new Date(data.approved_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) :
                    'N/A';
            } else {
                approvalSection.style.display = 'none';
            }

            // Show/hide remarks
            const remarksSection = document.getElementById('detailRemarksSection');
            if (data.status === 'rejected' && data.remarks) {
                remarksSection.style.display = 'block';
                document.getElementById('detailRemarks').textContent = data.remarks;
            } else {
                remarksSection.style.display = 'none';
            }

            // Show/hide attachment
            const attachmentSection = document.getElementById('detailAttachmentSection');
            if (data.attachment) {
                attachmentSection.style.display = 'block';
                document.getElementById('detailAttachmentLink').href = `/storage/${data.attachment}`;
            } else {
                attachmentSection.style.display = 'none';
            }

            // Show who filed the order (a companion may be viewing someone else's order)
            if (data.employee) {
                document.getElementById('detailFilerAvatar').textContent =
                    ((data.employee.first_name || 'E')[0] + (data.employee.last_name || 'E')[0]).toUpperCase();
                document.getElementById('detailFilerInfo').textContent =
                    `${data.employee.first_name || ''} ${data.employee.last_name || ''} · ${data.employee.employee_id || 'N/A'}`;
            }

            // Render companions and document history
            renderTravelCompanions(data.companions || []);
            renderTravelHistory(data.histories || []);

            // Show/hide cancel button (only while not yet processed by HR, and only for the filer)
            const cancelBtn = document.getElementById('detailCancelBtn');
            const isFiler = data.employee && data.employee.employee_id === window.travelOrderPageData?.employeeId;
            if (isFiler && (data.status === 'pending' || data.status === 'awaiting_companions')) {
                cancelBtn.style.display = 'inline-flex';
            } else {
                cancelBtn.style.display = 'none';
            }

            // Show modal
            document.getElementById('viewTravelDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load travel order details');
        });
}

function travelCompanionAvatar(employee) {
    if (employee && employee.photo) {
        const src = /^(\/|https?:)/.test(employee.photo) ? employee.photo : `/storage/${employee.photo}`;
        return `<img src="${src}" alt="" class="to-companion-avatar">`;
    }
    const initials = (((employee?.first_name || 'E')[0]) + ((employee?.last_name || 'E')[0])).toUpperCase();
    return `<span class="to-companion-avatar-initials">${initials}</span>`;
}

function renderTravelCompanions(companions) {
    const section = document.getElementById('detailCompanionsSection');
    const list = document.getElementById('detailCompanionsList');

    if (!companions.length) {
        section.style.display = 'none';
        list.innerHTML = '';
        return;
    }

    const badges = {
        pending:  '<span class="badge-status pending">Pending</span>',
        accepted: '<span class="badge-status processed">Accepted</span>',
        rejected: '<span class="badge-status on-hold">Rejected</span>'
    };

    list.innerHTML = companions.map(companion => {
        const emp = companion.employee || {};
        const name = `${emp.first_name || ''} ${emp.last_name || ''}`.trim() || 'Unknown employee';
        const note = companion.response_note ? `<p class="to-companion-note">“${companion.response_note}”</p>` : '';
        return `<div class="to-companion-card">
            ${travelCompanionAvatar(emp)}
            <div class="to-flex-1-minw0">
                <p class="to-option-name">${name}</p>
                <p class="to-option-id">${emp.employee_id || ''}</p>
                ${note}
            </div>
            ${badges[companion.status] || ''}
        </div>`;
    }).join('');

    section.style.display = 'block';
}

const travelHistoryLabels = {
    filed: 'Filed',
    companion_invited: 'Companion Invited',
    companion_accepted: 'Companion Accepted',
    companion_rejected: 'Companion Rejected',
    forwarded_to_hr: 'Forwarded to HR',
    approved: 'Approved',
    disapproved: 'Disapproved'
};

function renderTravelHistory(histories) {
    const section = document.getElementById('detailHistorySection');
    const list = document.getElementById('detailHistoryList');

    if (!histories.length) {
        section.style.display = 'none';
        list.innerHTML = '';
        return;
    }

    const dotColors = {
        filed: '#0369a1',
        companion_invited: '#6d28d9',
        companion_accepted: '#15803d',
        companion_rejected: '#dc2626',
        forwarded_to_hr: '#a16207',
        approved: '#15803d',
        disapproved: '#dc2626'
    };

    list.innerHTML = histories.map(entry => {
        const when = new Date(entry.created_at).toLocaleString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
        const label = travelHistoryLabels[entry.action] || entry.action;
        const remarks = entry.remarks ? `<p class="to-history-remarks">${entry.remarks}</p>` : '';
        return `<div class="to-history-item">
            <div class="to-history-rail">
                <span class="to-history-dot" style="background: ${dotColors[entry.action] || '#9ca3af'};"></span>
                <span class="to-history-line"></span>
            </div>
            <div class="to-history-content">
                <p class="to-history-label">${label} <span class="to-history-time">· ${when}</span></p>
                ${remarks}
            </div>
        </div>`;
    }).join('');

    section.style.display = 'block';
}

function cancelTravelOrder() {
    if (!currentTravelOrderId) return;

    if (confirm('Are you sure you want to cancel this travel order?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/employee/travelorder/${currentTravelOrderId}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]')?.content;
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        document.body.appendChild(form);
        form.submit();
    }
}

// Expose functions invoked from inline HTML attributes (onclick/onchange)
window.respondToCompanionRequest = respondToCompanionRequest;
window.changeTravelRowsPerPage = changeTravelRowsPerPage;
window.filterTravelOrders = filterTravelOrders;
window.sortTravelOrders = sortTravelOrders;
window.editTravelOrder = editTravelOrder;
window.closeTravelOrderModal = closeTravelOrderModal;
window.toggleCompanionDropdown = toggleCompanionDropdown;
window.filterCompanionOptions = filterCompanionOptions;
window.toggleCompanionSelection = toggleCompanionSelection;
window.removeCompanion = removeCompanion;
window.openTravelOrderModal = openTravelOrderModal;
window.calculateTravelDuration = calculateTravelDuration;
window.handleTravelFileSelect = handleTravelFileSelect;
window.closeTravelDetailModal = closeTravelDetailModal;
window.viewTravelOrder = viewTravelOrder;
window.cancelTravelOrder = cancelTravelOrder;
