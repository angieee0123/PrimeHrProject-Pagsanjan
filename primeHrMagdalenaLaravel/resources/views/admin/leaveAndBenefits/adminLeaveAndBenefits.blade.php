@extends('layouts.app')

@section('content')
@php
$avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
function getInitials($name) {
    $parts = explode(' ', $name);
    $initials = '';
    foreach ($parts as $part) {
        if (preg_match('/^[A-Z]/', $part)) {
            $initials .= $part[0];
        }
    }
    return strtoupper(substr($initials, 0, 2));
}

$totalApproved = $leaveApplications->where('status', 'approved')->count();
$totalPending = $leaveApplications->where('status', 'pending')->count();
$totalDays = $leaveApplications->where('status', 'approved')->sum('number_of_days');
@endphp

@include('admin.topbar.leaveandbenefitsTopbar')
@include('admin.notification.adminNotification')

<div class="stats-grid stats-grid-4" style="margin-bottom: 20px;">
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Requests</p>
            <div class="stat-icon-wrap" style="background: #f0effe;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $leaveApplications->count() }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #0b044d;"></span>
            <p class="stat-sub">All time</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Approved</p>
            <div class="stat-icon-wrap" style="background: #e8f9ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalApproved }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #15803d;"></span>
            <p class="stat-sub">This period</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Pending Approval</p>
            <div class="stat-icon-wrap" style="background: #fefce8;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ $totalPending }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #d9bb00;"></span>
            <p class="stat-sub">Needs action</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-top">
            <p class="stat-label">Total Leave Days</p>
            <div class="stat-icon-wrap" style="background: #fdf0ef;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
        </div>
        <h2 class="stat-value">{{ number_format($totalDays, 0) }}</h2>
        <div class="stat-footer">
            <span class="stat-dot" style="background: #8e1e18;"></span>
            <p class="stat-sub">Across all employees</p>
        </div>
    </div>
</div>

<!-- Tabs -->
<div style="display: flex; gap: 4px; margin-bottom: 20px; border-bottom: 1.5px solid #eceaf8; padding-bottom: 0;">
    <button class="tab-btn active" onclick="switchTab('leave')">Leave Requests</button>
    <button class="tab-btn" onclick="switchTab('transactions')">Transaction History</button>
    <button class="tab-btn" onclick="switchTab('benefits')">Benefits Summary</button>
    <button class="tab-btn" onclick="switchTab('types')">Leave Types</button>
    <button class="tab-btn" onclick="switchTab('accrual')">CSC Daily Accrual</button>
    <button class="tab-btn" onclick="switchTab('import')">Import Records</button>
</div>

@include('admin.leaveAndBenefits.partials.leave-requests-tab')

@include('admin.leaveAndBenefits.partials.transaction-history-tab')

@include('admin.leaveAndBenefits.partials.benefits-summary-tab')

@include('admin.leaveAndBenefits.partials.leave-types-tab')

@include('admin.leaveAndBenefits.partials.csc-daily-accrual-tab')

<!-- Import Tab -->
<div id="import-tab" style="display: none;">
    <div style="padding: 20px; background: white; border-radius: 8px; border: 1px solid #eceaf8;">
        <div style="margin-bottom: 20px;">
            <h4 style="margin: 0 0 10px 0; font-size: 16px; font-weight: 600; color: #1a1a1a;">Migrate Leave Records</h4>
            <p style="margin: 0; color: #6b6a8a; font-size: 14px;">Import historical leave records from Excel files for your employees. This feature allows you to migrate old leave balance data into the system.</p>
        </div>
        
        <button class="btn btn-primary" onclick="openImportLeaveRecordsModal()" style="margin-bottom: 20px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display: inline; margin-right: 8px; vertical-align: -2px;">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Import Leave Records
        </button>
        
        <div style="background: #f5f5f5; border-left: 3px solid #2196F3; padding: 15px; border-radius: 3px;">
            <p style="font-weight: 500; margin: 0 0 10px 0; color: #333;">How to use:</p>
            <ol style="margin: 0; padding-left: 20px; color: #666; font-size: 14px;">
                <li>Click "Import Leave Records" button</li>
                <li>Select the employee to import records for</li>
                <li>Upload their Excel file (format: .xlsx or .xls)</li>
                <li>Review the import summary and confirm</li>
                <li>Records will be added to Transaction History</li>
            </ol>
        </div>
    </div>
</div>

@include('admin.leaveAndBenefits.modals.add-leave-type-modal')

@include('admin.leaveAndBenefits.modals.view-leave-type-modal')

@include('admin.leaveAndBenefits.modals.add-accrual-rate-modal')

@include('admin.leaveAndBenefits.modals.add-manual-credit-modal')

@include('admin.leaveAndBenefits.modals.success-modal')

@include('admin.leaveAndBenefits.modals.error-modal')

@include('admin.leaveAndBenefits.modals.import-leave-records-modal')

@vite(['resources/css/adminLeaveAndBenefits.css', 'resources/js/adminLeaveAndBenefits.js'])

<script>
// Success Modal Functions
window.successModalRedirectUrl = null;

window.openSuccessModal = function(message, redirectUrl) {
    const modal = document.getElementById('successModal');
    const messageEl = document.getElementById('successMessage');

    if (redirectUrl !== undefined) {
        window.successModalRedirectUrl = redirectUrl;
    }

    if (messageEl && message) {
        messageEl.textContent = message;
    }

    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.closeSuccessModal = function(event) {
    if (!event || event.target.id === 'successModal' || event.type === 'click') {
        const modal = document.getElementById('successModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';

            const redirectUrl = window.successModalRedirectUrl
                || '{{ route('admin.leave', ['tab' => 'types']) }}';
            window.successModalRedirectUrl = null;
            window.location.href = redirectUrl;
        }
    }
};

// Error Modal Functions
window.openErrorModal = function(message) {
    const modal = document.getElementById('errorModal');
    const messageEl = document.getElementById('errorMessage');
    
    if (messageEl && message) {
        messageEl.textContent = message;
    }
    
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
};

window.closeErrorModal = function(event) {
    if (!event || event.target.id === 'errorModal' || event.type === 'click') {
        const modal = document.getElementById('errorModal');
        if (modal) {
            modal.classList.remove('active');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }
    }
};

// Handle form submission with AJAX
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('addLeaveTypeForm');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = form.querySelector('.btn-submit');
            const originalText = submitBtn.textContent;
            
            // Disable submit button and show loading
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            // Create FormData object
            const formData = new FormData(form);
            
            // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        throw data;
                    });
                }
                return response.json();
            })
            .then(data => {
                // Close add modal
                closeAddLeaveTypeModal();
                
                // Show success modal
                openSuccessModal(data.message || 'Leave type registered successfully!', '{{ route('admin.leave', ['tab' => 'types']) }}');
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
                
                // Close add modal
                closeAddLeaveTypeModal();
                
                // Show error modal
                let errorMessage = 'Failed to register leave type. Please try again.';
                
                if (error.errors) {
                    // Laravel validation errors
                    const firstError = Object.values(error.errors)[0];
                    errorMessage = Array.isArray(firstError) ? firstError[0] : firstError;
                } else if (error.message) {
                    errorMessage = error.message;
                }
                
                openErrorModal(errorMessage);
            });
        });
    }
    
    // Check URL parameter and switch to correct tab on page load
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');
    
    if (activeTab === 'types') {
        switchTab('types');
    } else if (activeTab === 'benefits') {
        switchTab('benefits');
    } else if (activeTab === 'leave') {
        switchTab('leave');
    } else if (activeTab === 'accrual') {
        switchTab('accrual');
    } else if (activeTab === 'transactions') {
        switchTab('transactions');
    } else if (activeTab === 'import') {
        switchTab('import');
    }
});
</script>
@endsection
