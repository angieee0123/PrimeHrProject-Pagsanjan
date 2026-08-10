import { initBusySingleDate } from '../shared/busyDatesCalendar.js';

// ── Sidebar / mobile menu toggle ──
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggle-btn');
const logoText = document.getElementById('logo-text');
const navLabel = document.getElementById('nav-label');
const userInfo = document.getElementById('user-info');
const sidebarFooter = document.getElementById('sidebar-footer');
const mobileBtn = document.getElementById('mobile-menu-btn');
const overlay = document.getElementById('mobile-overlay');

// Busy-aware calendar for the File Pass Slip modal. Marks the employee's leave
// and travel dates as a heads-up only — nothing is blocked, since the system
// allows several pass slips a day and has no pass-slip/leave overlap rule.
document.addEventListener('DOMContentLoaded', function () {
    initBusySingleDate({ inputId: 'passSlipDate' });
});

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

// Escape closes whichever modal is actually open. It used to always call
// closePassSlipModal(), which reset the *File* form — so pressing Escape on
// the detail modal left it on screen and silently wiped a half-filled form
// behind it.
document.addEventListener('keydown', function(e) {
    if (e.key !== 'Escape') return;

    const detail = document.getElementById('viewPassSlipDetailModal');
    if (detail && detail.style.display === 'flex') {
        closePassSlipDetailModal();
        return;
    }

    const file = document.getElementById('filePassSlipModal');
    if (file && file.style.display === 'flex') {
        closePassSlipModal();
    }
});

// ── File Pass Slip modal ──
function closePassSlipModal() {
    document.getElementById('filePassSlipModal').style.display = 'none';
    document.getElementById('passSlipForm').reset();
    // form.reset() clears the input but not flatpickr's internal selection
    document.getElementById('passSlipDate')?._flatpickr?.clear();
    const fileDisplay = document.getElementById('passSlipFileNameDisplay');
    fileDisplay.style.display = 'none';
    fileDisplay.innerHTML = '';
    document.getElementById('passSlipErrorMessage').style.display = 'none';
    document.body.style.overflow = '';

    const submitBtn = document.getElementById('passSlipSubmitBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            Submit Pass Slip
        `;
    }
}

function openPassSlipModal() {
    document.getElementById('filePassSlipModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    togglePassSlipPurposeOptions();
    // Set today's date as default
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('passSlipDate').value = today;
}

function escapePassSlipHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
}

function handlePassSlipFileSelect(input) {
    const file = input.files[0];
    const display = document.getElementById('passSlipFileNameDisplay');

    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            showPassSlipError('File size must not exceed 5MB');
            input.value = '';
            display.style.display = 'none';
            display.innerHTML = '';
            return;
        }

        display.innerHTML = `
            <div class="ps-file-info">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>${escapePassSlipHtml(file.name)} <span class="ps-file-size">(${(file.size / 1024).toFixed(1)} KB)</span></span>
            </div>
            <button type="button" class="ps-file-remove-btn" onclick="removePassSlipFile()" aria-label="Remove attachment">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        display.style.display = 'flex';
    } else {
        display.style.display = 'none';
        display.innerHTML = '';
    }
}

function removePassSlipFile() {
    const input = document.getElementById('passSlipAttachment');
    const display = document.getElementById('passSlipFileNameDisplay');
    input.value = '';
    display.style.display = 'none';
    display.innerHTML = '';
}

// ── Drag-and-drop for the attachment dropzone ──
const passSlipDropZone = document.getElementById('passSlipAttachmentDropZone');
if (passSlipDropZone) {
    ['dragenter', 'dragover'].forEach(evt => {
        passSlipDropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            passSlipDropZone.classList.add('ps-dropzone-active');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        passSlipDropZone.addEventListener(evt, function(e) {
            e.preventDefault();
            e.stopPropagation();
            passSlipDropZone.classList.remove('ps-dropzone-active');
        });
    });
    passSlipDropZone.addEventListener('drop', function(e) {
        const file = e.dataTransfer.files[0];
        if (!file) return;
        const input = document.getElementById('passSlipAttachment');
        input.files = e.dataTransfer.files;
        handlePassSlipFileSelect(input);
    });
}

function togglePassSlipPurposeOptions() {
    const type = document.querySelector('input[name="type"]:checked').value;
    const officialGroup = document.getElementById('officialPurposeGroup');
    const personalGroup = document.getElementById('personalPurposeGroup');
    const select = document.getElementById('purposeCategory');

    if (type === 'personal_reason') {
        officialGroup.style.display = 'none';
        personalGroup.style.display = '';
        select.value = 'personal_matter';
    } else {
        officialGroup.style.display = '';
        personalGroup.style.display = 'none';
        select.value = 'coordinate_with';
    }

    document.querySelectorAll('.ps-radio-label').forEach(label => {
        const radio = label.querySelector('input[type="radio"]');
        label.classList.toggle('is-selected', !!radio && radio.checked);
    });
}

// Disable + spin the submit button once the form actually passes native
// validation and starts posting, so a slow connection can't be double-submitted.
const passSlipForm = document.getElementById('passSlipForm');
if (passSlipForm) {
    passSlipForm.addEventListener('submit', function() {
        const btn = document.getElementById('passSlipSubmitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="ps-spinner"></span> Submitting...';
        }
    });
}

function showPassSlipError(message) {
    const errorDiv = document.getElementById('passSlipErrorMessage');
    const errorText = document.getElementById('passSlipErrorMessageText');
    errorText.textContent = message;
    errorDiv.style.display = 'block';
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Character counter for reason
document.addEventListener('DOMContentLoaded', function() {
    const reasonField = document.getElementById('reason');
    const reasonCounter = document.getElementById('reasonCounter');

    if (reasonField && reasonCounter) {
        reasonField.addEventListener('input', function() {
            const length = this.value.length;
            reasonCounter.textContent = `${length} / 300`;
            if (length > 300) {
                reasonCounter.style.color = 'var(--theme-danger)';
            } else if (length > 270) {
                reasonCounter.style.color = 'var(--theme-warning)';
            } else {
                reasonCounter.style.color = 'var(--theme-neutral-600)';
            }
        });
    }
});

// ── View Pass Slip Detail modal ──
let currentPassSlipId = null;

/** Endpoint for one pass slip, built from the route the server published. */
function passSlipUrl(which, id) {
    const template = (window.passSlipData || {})[which];

    return template ? template.replace('__ID__', encodeURIComponent(id)) : null;
}

/**
 * "2026-08-05T00:00:00.000000Z" → "Aug 5, 2026".
 *
 * Reads the calendar date off the string rather than through `new Date()`:
 * a date-only value arrives as UTC midnight, which any negative-offset
 * viewer would render as the previous day.
 */
function formatPassSlipDate(value) {
    if (!value) return 'Not specified';
    const [y, m, d] = String(value).slice(0, 10).split('-').map(Number);
    if (!y || !m || !d) return value;

    return new Date(y, m - 1, d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

/** "13:00:00" → "1:00 PM", matching how the history table prints times. */
function formatPassSlipTime(value) {
    if (!value) return 'Not specified';
    const [h, min] = String(value).split(':').map(Number);
    if (Number.isNaN(h) || Number.isNaN(min)) return value;
    const suffix = h >= 12 ? 'PM' : 'AM';

    return `${((h + 11) % 12) + 1}:${String(min).padStart(2, '0')} ${suffix}`;
}

function closePassSlipDetailModal() {
    document.getElementById('viewPassSlipDetailModal').style.display = 'none';
    document.body.style.overflow = '';
    currentPassSlipId = null;
}

function viewPassSlip(id) {
    currentPassSlipId = id;

    const url = passSlipUrl('showUrl', id);
    if (!url) {
        // showPassSlipError() writes into a box inside the *File* Pass Slip
        // modal, which is closed while viewing details — routing a detail
        // failure there would report nothing at all.
        alert('Pass slip details are unavailable on this page.');
        return;
    }

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(response => {
            if (!response.ok) throw new Error(`Request failed (${response.status})`);
            return response.json();
        })
        .then(data => {
            // Set header info
            document.getElementById('detailSlipId').textContent = data.slip_number || data.id;
            document.getElementById('detailReason').textContent = data.reason;
            document.getElementById('detailSlipDate').textContent = formatPassSlipDate(data.date);

            // Set status badge
            const statusBadge = document.getElementById('detailPassSlipStatus');
            if (data.status === 'pending') {
                statusBadge.className = 'badge-status pending';
                statusBadge.textContent = 'Pending';
            } else if (data.status === 'approved') {
                statusBadge.className = 'badge-status processed';
                statusBadge.textContent = 'Approved';
            } else if (data.status === 'rejected') {
                statusBadge.className = 'badge-status on-hold';
                statusBadge.textContent = 'Rejected';
            } else {
                statusBadge.className = 'badge-status ps-badge-cancelled';
                statusBadge.textContent = 'Cancelled';
            }

            // Set pass slip details
            document.getElementById('detailReasonFull').textContent = data.reason;
            document.getElementById('detailDestination').textContent = data.destination || 'Not specified';
            document.getElementById('detailDate').textContent = formatPassSlipDate(data.date);
            document.getElementById('detailTimeOut').textContent = formatPassSlipTime(data.time_out);
            document.getElementById('detailTimeIn').textContent = formatPassSlipTime(data.time_in);

            // Show/hide approval section
            const approvalSection = document.getElementById('detailApprovalSection');
            if (data.status !== 'pending' && data.approved_by) {
                approvalSection.style.display = 'block';
                // Resolved server-side (`users` has no `name` column). No
                // invented fallback: "Admin User" used to be printed for
                // whoever actually signed the slip.
                document.getElementById('detailApprovedBy').textContent = data.approver_name || '—';
                document.getElementById('detailApprovedAt').textContent = formatPassSlipDate(data.approved_at);
            } else {
                approvalSection.style.display = 'none';
            }

            // Remarks show whenever the approver left any — they were gated on
            // `status === 'rejected'`, which hid the note attached to an
            // approval ("approved, be back by 3pm") entirely.
            const remarksSection = document.getElementById('detailRemarksSection');
            if (data.remarks) {
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

            // Show/hide cancel button (only for pending pass slips)
            const cancelBtn = document.getElementById('detailPassSlipCancelBtn');
            if (data.status === 'pending') {
                cancelBtn.style.display = 'inline-flex';
            } else {
                cancelBtn.style.display = 'none';
            }

            // Show modal
            document.getElementById('viewPassSlipDetailModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Pass slip details failed to load:', error);
            alert('Could not load that pass slip. Please refresh the page and try again.');
        });
}

function cancelPassSlip() {
    if (!currentPassSlipId) return;

    const url = passSlipUrl('cancelUrl', currentPassSlipId);
    if (!url) return;

    if (confirm('Are you sure you want to cancel this pass slip?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = (window.passSlipData || {}).csrfToken
            || document.querySelector('meta[name="csrf-token"]')?.content;
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

// ── Pass Slip history tab: rows-per-page / status filter / (stub) sort ──
function changePassSlipRowsPerPage() {
    const perPage = document.getElementById('passSlipRowsPerPage').value;
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

// Applies the status dropdown and the topbar search together, so neither one
// re-shows rows the other has filtered out.
function filterPassSlips() {
    const statusFilter = document.getElementById('filterPassSlipStatus').value;
    const searchInput = document.getElementById('passSlipSearchInput');
    const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
    const rows = document.querySelectorAll('.passslip-row');

    rows.forEach(row => {
        const matchesStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
        const matchesQuery = query === '' || row.textContent.toLowerCase().includes(query);
        row.style.display = (matchesStatus && matchesQuery) ? '' : 'none';
    });
}

// NOTE: sortPassSlips() was already a stub (console.log only) before this
// refactor — the sortable column headers don't actually sort. Kept as-is.
function sortPassSlips(column) {
    console.log('Sort by:', column);
}

// Expose functions invoked from inline HTML attributes (onclick/onchange)
window.closePassSlipModal = closePassSlipModal;
window.openPassSlipModal = openPassSlipModal;
window.handlePassSlipFileSelect = handlePassSlipFileSelect;
window.removePassSlipFile = removePassSlipFile;
window.togglePassSlipPurposeOptions = togglePassSlipPurposeOptions;
window.closePassSlipDetailModal = closePassSlipDetailModal;
window.viewPassSlip = viewPassSlip;
window.cancelPassSlip = cancelPassSlip;
window.changePassSlipRowsPerPage = changePassSlipRowsPerPage;
window.filterPassSlips = filterPassSlips;
window.sortPassSlips = sortPassSlips;
