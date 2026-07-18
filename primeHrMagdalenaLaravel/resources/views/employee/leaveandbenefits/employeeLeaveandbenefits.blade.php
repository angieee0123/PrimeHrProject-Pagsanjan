@extends('layouts.employee')

@section('title', 'Leave & Benefits · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Leave & Benefits'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-leavebenefits glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.leaveandbenefitsTopbar')

        @include('employee.leaveandbenefits.partials.stats-grid')

        @include('employee.leaveandbenefits.partials.tab-nav')

        {{-- Tab Content --}}
        <div id="tab-leave" class="tab-content">
            @include('employee.leaveandbenefits.tabs.leave-requests.leaveRequestsTab')
        </div>

        <div id="tab-credits" class="tab-content hidden">
            @include('employee.leaveandbenefits.tabs.leave-credits.leaveCreditsTab')
        </div>

        <div id="tab-transactions" class="tab-content hidden" style="display: none;">
            @include('employee.leaveandbenefits.tabs.transaction-history.transactionHistoryTab')
        </div>

        <div id="tab-benefits" class="tab-content hidden">
            @include('employee.leaveandbenefits.tabs.benefits.benefitsTab')
        </div>

    </main>

</div>

@include('employee.leaveandbenefits.modals.leaveDetailModal')
@include('employee.leaveandbenefits.modals.fileLeaveModal')

@push('scripts')
<script>
    const sidebar   = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggle-btn');
    const logoText  = document.getElementById('logo-text');
    const navLabel  = document.getElementById('nav-label');
    const userInfo  = document.getElementById('user-info');
    const sidebarFooter = document.getElementById('sidebar-footer');
    const mobileBtn = document.getElementById('mobile-menu-btn');
    const overlay   = document.getElementById('mobile-overlay');

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

    function applyLeaveFilters() {
        const type   = document.getElementById('filterType').value;
        const status = document.getElementById('filterStatus').value;
        const rows   = document.querySelectorAll('#tab-leave tbody tr');
        let visible  = 0;
        rows.forEach(row => {
            const matchType   = !type   || row.dataset.type   === type;
            const matchStatus = !status || row.dataset.status === status;
            const show = matchType && matchStatus;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        const total = rows.length;
        document.getElementById('leaveCount').innerHTML =
            visible === total
                ? 'Showing <strong>' + total + '</strong> of <strong>' + total + '</strong> records'
                : 'Showing <strong>' + visible + '</strong> of <strong>' + total + '</strong> records';
    }

    function filterLeaveCredits() {
        const category = document.getElementById('filterLeaveCategory').value;
        const rows = document.querySelectorAll('.leave-credit-row');
        let visible = 0;

        rows.forEach(row => {
            let show = true;

            if (category === 'accrued') {
                show = row.dataset.type === 'accrued';
            } else if (category === 'fixed') {
                show = row.dataset.type === 'fixed';
            } else if (category === 'available') {
                show = parseFloat(row.dataset.available) > 0;
            }

            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Reset pagination after filter
        initLeaveCreditsTable();
    }

    function changeItemsPerPage() {
        const select = document.getElementById('itemsPerPage');
        const value = select.value;

        if (value === 'all') {
            leaveCreditsRowsPerPage = 999999; // Show all
        } else {
            leaveCreditsRowsPerPage = parseInt(value);
        }

        leaveCreditsCurrentPage = 1;
        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    // Leave Credits Table Pagination
    let leaveCreditsCurrentPage = 1;
    let leaveCreditsRowsPerPage = 20; // Increased from 10 to 20 to show all leave types
    let leaveCreditsVisibleRows = [];

    function initLeaveCreditsTable() {
        const allRows = document.querySelectorAll('.leave-credit-row');
        leaveCreditsVisibleRows = Array.from(allRows).filter(row => row.style.display !== 'none');
        leaveCreditsCurrentPage = 1;
        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    function displayLeaveCreditsPage() {
        const startIndex = (leaveCreditsCurrentPage - 1) * leaveCreditsRowsPerPage;
        const endIndex = startIndex + leaveCreditsRowsPerPage;

        leaveCreditsVisibleRows.forEach((row, index) => {
            if (index >= startIndex && index < endIndex) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updateLeaveCreditsCounter();
    }

    function updateLeaveCreditsCounter() {
        const total = leaveCreditsVisibleRows.length;
        const startIndex = (leaveCreditsCurrentPage - 1) * leaveCreditsRowsPerPage + 1;
        const endIndex = Math.min(leaveCreditsCurrentPage * leaveCreditsRowsPerPage, total);

        const counter = document.getElementById('leaveCreditsCount');
        if (counter) {
            if (total === 0) {
                counter.innerHTML = 'No leave types found';
            } else {
                counter.innerHTML = `Showing <strong>${startIndex}</strong> to <strong>${endIndex}</strong> of <strong>${total}</strong> leave types`;
            }
        }
    }

    function updateLeaveCreditsPageButtons() {
        const totalPages = Math.ceil(leaveCreditsVisibleRows.length / leaveCreditsRowsPerPage);
        const pagination = document.getElementById('leaveCreditspagination');
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');

        if (!pagination) return;

        // Update prev/next buttons
        prevBtn.disabled = leaveCreditsCurrentPage === 1;
        nextBtn.disabled = leaveCreditsCurrentPage === totalPages || totalPages === 0;

        // Clear existing page buttons (except prev/next)
        const pageButtons = pagination.querySelectorAll('.page-btn:not(#prevPageBtn):not(#nextPageBtn)');
        pageButtons.forEach(btn => btn.remove());

        // Add page buttons
        if (totalPages > 0) {
            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = 'page-btn' + (i === leaveCreditsCurrentPage ? ' active' : '');
                pageBtn.textContent = i;
                pageBtn.onclick = () => goToLeaveCreditsPage(i);
                pagination.insertBefore(pageBtn, nextBtn);
            }
        }
    }

    function changeLeaveCreditsPage(direction) {
        const totalPages = Math.ceil(leaveCreditsVisibleRows.length / leaveCreditsRowsPerPage);

        if (direction === 'prev' && leaveCreditsCurrentPage > 1) {
            leaveCreditsCurrentPage--;
        } else if (direction === 'next' && leaveCreditsCurrentPage < totalPages) {
            leaveCreditsCurrentPage++;
        }

        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    function goToLeaveCreditsPage(page) {
        leaveCreditsCurrentPage = page;
        displayLeaveCreditsPage();
        updateLeaveCreditsPageButtons();
    }

    // Initialize pagination when credits tab is shown
    document.addEventListener('DOMContentLoaded', function() {
        initLeaveCreditsTable();

        // Check URL for tab parameter and switch to that tab
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab');

        if (activeTab && ['leave', 'credits', 'transactions', 'benefits'].includes(activeTab)) {
            // Hide all tabs
            document.querySelectorAll('.tab-content').forEach(c => {
                c.classList.add('hidden');
                c.style.display = 'none';
            });

            // Show the active tab
            const tabContent = document.getElementById('tab-' + activeTab);
            if (tabContent) {
                tabContent.classList.remove('hidden');
                tabContent.style.display = 'block';
            }

            // Update tab button states
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Find and activate the correct button
            const buttons = document.querySelectorAll('.tab-btn');
            const tabIndex = ['leave', 'credits', 'transactions', 'benefits'].indexOf(activeTab);
            if (buttons[tabIndex]) {
                buttons[tabIndex].classList.add('active');
            }

            // Initialize pagination for credits tab
            if (activeTab === 'credits') {
                setTimeout(() => initLeaveCreditsTable(), 100);
            }
        }
    });

    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(c => {
            c.classList.add('hidden');
            c.style.display = 'none';
        });
        const active = document.getElementById('tab-' + tabId);
        active.classList.remove('hidden');
        active.style.display = 'block';
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // Update URL with tab parameter
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url.toString());

        // Initialize pagination when switching to credits tab
        if (tabId === 'credits') {
            setTimeout(() => initLeaveCreditsTable(), 100);
        }
    }

    function openDetailModal(type, from, to, days, reason, status, appNumber, attachmentUrl, remarks, applicationId) {
        document.getElementById('detailType').textContent = type;
        document.getElementById('detailType2').textContent = type;
        document.getElementById('detailFrom').textContent = from;
        document.getElementById('detailTo').textContent = to;
        document.getElementById('detailDays').textContent = days + ' day' + (days > 1 ? 's' : '');
        document.getElementById('detailReason').textContent = reason;
        document.getElementById('detailDates').textContent = from + ' — ' + to;

        const statusBadge = document.getElementById('detailStatus');
        statusBadge.textContent = status;
        statusBadge.className = 'badge-status ' +
            (status === 'Approved' ? 'processed' :
             status === 'Pending' ? 'pending' :
             status === 'Rejected' ? 'rejected' : 'cancelled');

        const modalEyebrow = document.querySelector('#detailModal .modal-eyebrow');
        modalEyebrow.textContent = 'LEAVE REQUEST · ' + appNumber;

        // Handle remarks section
        const remarksSection = document.getElementById('remarksSection');
        const remarksText = document.getElementById('remarksText');
        if (remarks && remarks.trim() !== '') {
            remarksText.textContent = remarks;
            remarksSection.style.display = 'block';
        } else {
            remarksSection.style.display = 'none';
        }

        // Handle download button
        const downloadBtn = document.getElementById('downloadBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        if (attachmentUrl && attachmentUrl.trim() !== '') {
            downloadBtn.style.display = 'flex';
            downloadBtn.onclick = () => window.open(attachmentUrl, '_blank');
        } else {
            downloadBtn.style.display = 'none';
        }

        // Show cancel button only for pending requests
        if (status === 'Pending') {
            cancelBtn.style.display = 'flex';
            cancelBtn.onclick = () => cancelLeaveRequest(applicationId, appNumber);
        } else {
            cancelBtn.style.display = 'none';
        }

        document.getElementById('detailModal').style.display = 'flex';
    }

    function closeModal() {
        document.getElementById('detailModal').style.display = 'none';
    }

    function openFileModal() {
        document.getElementById('fileModal').style.display = 'flex';
    }

    function closeFileModal() {
        document.getElementById('fileModal').style.display = 'none';
        document.getElementById('leaveApplicationForm').reset();
        document.getElementById('errorMessage').style.display = 'none';
        document.getElementById('attachmentField').style.display = 'none';
        document.getElementById('leaveDetailsSection').style.display = 'none';
        document.getElementById('leaveLocationSpecify').style.display = 'none';
    }

    function toggleAbroadSpecify() {
        const abroad = document.querySelector('input[name="leave_location"][value="abroad"]')?.checked;
        const field = document.getElementById('leaveLocationSpecify');
        if (field) {
            field.style.display = abroad ? 'block' : 'none';
            field.required = !!abroad;
        }
    }

    function updateLeaveDetailsFields(code) {
        const section = document.getElementById('leaveDetailsSection');
        const vlSpl = document.getElementById('vlSplDetails');
        const sick = document.getElementById('sickDetails');
        const slbw = document.getElementById('slbwDetails');
        const study = document.getElementById('studyDetails');

        const showVlSpl = ['VL', 'SPL'].includes(code);
        const showSick = code === 'SL';
        const showSlbw = ['SLBW', 'MCL'].includes(code);
        const showStudy = code === 'STL';
        const any = showVlSpl || showSick || showSlbw || showStudy;

        section.style.display = any ? 'block' : 'none';
        vlSpl.style.display = showVlSpl ? 'block' : 'none';
        sick.style.display = showSick ? 'block' : 'none';
        slbw.style.display = showSlbw ? 'block' : 'none';
        study.style.display = showStudy ? 'block' : 'none';

        document.getElementById('sickIllnessSpecify').disabled = !showSick;
        document.getElementById('slbwIllnessSpecify').disabled = !showSlbw;
        if (!showSick) document.getElementById('sickIllnessSpecify').value = '';
        if (!showSlbw) document.getElementById('slbwIllnessSpecify').value = '';
        if (!showVlSpl) {
            document.querySelectorAll('input[name="leave_location"]').forEach(r => r.checked = false);
            document.getElementById('leaveLocationSpecify').value = '';
            document.getElementById('leaveLocationSpecify').style.display = 'none';
        }
    }

    function calculateDays() {
        const from = document.getElementById('leaveFrom').value;
        const to = document.getElementById('leaveTo').value;
        const select = document.getElementById('leaveType');
        const option = select.options[select.selectedIndex];
        const available = parseFloat(option.dataset.available) || 0;

        if (from && to) {
            const startDate = new Date(from);
            const endDate = new Date(to);

            if (endDate < startDate) {
                document.getElementById('errorMessageText').textContent = 'End date cannot be before start date';
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('leaveDays').value = 0;
                return;
            }

            // Calculate business days (excluding weekends)
            let days = 0;
            let currentDate = new Date(startDate);

            while (currentDate <= endDate) {
                const dayOfWeek = currentDate.getDay();
                if (dayOfWeek !== 0 && dayOfWeek !== 6) { // Not Sunday (0) or Saturday (6)
                    days++;
                }
                currentDate.setDate(currentDate.getDate() + 1);
            }

            document.getElementById('leaveDays').value = days;

            // Only check balance if a leave type is selected
            if (select.value && days > available) {
                document.getElementById('errorMessageText').textContent = `Insufficient leave balance. You have ${available.toFixed(1)} days available but requested ${days.toFixed(1)} days.`;
                document.getElementById('errorMessage').style.display = 'block';
                document.getElementById('leaveDays').style.color = '#dc2626';
                document.getElementById('leaveDays').style.borderColor = '#dc2626';
                return;
            }

            document.getElementById('leaveDays').style.color = '#0b044d';
            document.getElementById('leaveDays').style.borderColor = '#e5e7eb';
            document.getElementById('errorMessage').style.display = 'none';
        }
    }

    function updateLeaveInfo() {
        const select = document.getElementById('leaveType');
        const option = select.options[select.selectedIndex];
        const requiresAttachment = option.dataset.requiresAttachment === '1';
        const attachmentInfo = option.dataset.attachmentInfo;
        const available = parseFloat(option.dataset.available) || 0;
        const isAccrued = option.dataset.isAccrued === '1';

        const attachmentField = document.getElementById('attachmentField');
        const leaveTypeInfo = document.getElementById('leaveTypeInfo');
        const leaveTypeInfoText = document.getElementById('leaveTypeInfoText');
        const attachmentInput = document.getElementById('leaveAttachment');
        const attachmentInfoText = document.getElementById('attachmentInfoText');

        if (requiresAttachment) {
            attachmentField.style.display = 'block';
            attachmentInput.required = true;
            if (attachmentInfo) {
                attachmentInfoText.textContent = attachmentInfo;
            }
        } else {
            attachmentField.style.display = 'none';
            attachmentInput.required = false;
        }

        updateLeaveDetailsFields(select.value);

        if (select.value) {
            let infoText = '';
            if (available > 0) {
                infoText = `Available balance: ${available.toFixed(1)} days`;
                leaveTypeInfo.style.background = '#f0f9ff';
                leaveTypeInfo.style.borderColor = '#0ea5e9';
                leaveTypeInfoText.style.color = '#0369a1';
            } else {
                infoText = 'No available balance for this leave type';
                leaveTypeInfo.style.background = '#fee2e2';
                leaveTypeInfo.style.borderColor = '#ef4444';
                leaveTypeInfoText.style.color = '#991b1b';
            }
            if (isAccrued) {
                infoText += (infoText ? ' • ' : '') + 'This leave accrues monthly (1.25 days/month)';
            }
            if (infoText) {
                leaveTypeInfoText.textContent = infoText;
                leaveTypeInfo.style.display = 'block';
            } else {
                leaveTypeInfo.style.display = 'none';
            }

            // Recalculate days when leave type changes
            calculateDays();
        } else {
            leaveTypeInfo.style.display = 'none';
        }
    }

    function handleFileSelect(input) {
        const fileNameDisplay = document.getElementById('fileNameDisplay');
        const dropZone = document.getElementById('attachmentDropZone');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);

            if (file.size > 5 * 1024 * 1024) {
                document.getElementById('errorMessageText').textContent = 'File size exceeds 5MB limit';
                document.getElementById('errorMessage').style.display = 'block';
                input.value = '';
                fileNameDisplay.style.display = 'none';
                return;
            }

            fileNameDisplay.innerHTML = `
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0369a1" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <span style="font-weight: 500;">${file.name}</span>
                    </div>
                    <span style="color: #6b7280; font-size: 11px;">${fileSize} MB</span>
                </div>
            `;
            fileNameDisplay.style.display = 'block';
            dropZone.style.borderColor = '#0ea5e9';
            dropZone.style.background = '#f0f9ff';
            document.getElementById('errorMessage').style.display = 'none';
        }
    }

    // Character counter for reason
    document.getElementById('leaveReason')?.addEventListener('input', function() {
        const counter = document.getElementById('reasonCounter');
        const length = this.value.length;
        counter.textContent = `${length} / 500`;

        if (length > 500) {
            counter.style.color = '#dc2626';
            this.value = this.value.substring(0, 500);
        } else if (length > 450) {
            counter.style.color = '#f59e0b';
        } else {
            counter.style.color = '#9ca3af';
        }
    });

    // Form submission
    document.getElementById('leaveApplicationForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        // Validate available balance
        const select = document.getElementById('leaveType');
        const option = select.options[select.selectedIndex];
        const available = parseFloat(option.dataset.available) || 0;
        const requestedDays = parseFloat(document.getElementById('leaveDays').value) || 0;

        if (requestedDays > available) {
            document.getElementById('errorMessageText').textContent = `Insufficient leave balance. You have ${available.toFixed(1)} days available but requested ${requestedDays.toFixed(1)} days.`;
            document.getElementById('errorMessage').style.display = 'block';
            document.getElementById('errorMessage').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        if (requestedDays <= 0) {
            document.getElementById('errorMessageText').textContent = 'Please select valid leave dates.';
            document.getElementById('errorMessage').style.display = 'block';
            document.getElementById('errorMessage').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const originalBtnContent = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/></svg><style>@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }</style> Submitting...';

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                submitBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Success!';
                submitBtn.style.background = '#15803d';
                setTimeout(() => {
                    closeFileModal();
                    location.reload();
                }, 1000);
            } else {
                document.getElementById('errorMessageText').textContent = data.message || 'Failed to submit leave request';
                document.getElementById('errorMessage').style.display = 'block';
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnContent;
                document.getElementById('errorMessage').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('errorMessageText').textContent = 'An error occurred. Please try again.';
            document.getElementById('errorMessage').style.display = 'block';
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnContent;
        });
    });

    function filterLeaveTable(query) {
        const q = query.toLowerCase();
        document.querySelectorAll('#tab-leave tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
        document.querySelectorAll('#tab-benefits tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }
    function submitLeave() {
        document.getElementById('leaveApplicationForm').submit();
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
            closeFileModal();
        }
    });

    function cancelLeaveRequest(applicationId, appNumber) {
        if (!confirm(`Are you sure you want to cancel leave request ${appNumber}?\n\nThis action cannot be undone. Your leave balance will be restored.`)) {
            return;
        }

        const cancelBtn = document.getElementById('cancelBtn');
        const originalBtnContent = cancelBtn.innerHTML;
        cancelBtn.disabled = true;
        cancelBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10"/></svg> Cancelling...';

        fetch(`/leave/${applicationId}/cancel`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cancelBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg> Cancelled!';
                cancelBtn.style.background = '#15803d';
                setTimeout(() => {
                    closeModal();
                    location.reload();
                }, 1000);
            } else {
                alert(data.message || 'Failed to cancel leave request');
                cancelBtn.disabled = false;
                cancelBtn.innerHTML = originalBtnContent;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while cancelling the leave request');
            cancelBtn.disabled = false;
            cancelBtn.innerHTML = originalBtnContent;
        });
    }
</script>
@endpush

@include('employee.chatbot.employeeChatbot')

@endsection
