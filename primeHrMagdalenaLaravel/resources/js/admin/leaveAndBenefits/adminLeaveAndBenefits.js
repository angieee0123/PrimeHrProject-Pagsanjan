// Admin Leave and Benefits Dashboard — tab switching, generic success/error modals

window.switchTab = function(tab) {
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));

    buttons.forEach(btn => {
        if ((tab === 'leave' && btn.textContent.includes('Leave Requests')) ||
            (tab === 'monetization' && btn.textContent.includes('Monetization Requests')) ||
            (tab === 'transactions' && btn.textContent.includes('Transaction History')) ||
            (tab === 'leave-credits' && btn.textContent.includes('Leave Credits')) ||
            (tab === 'benefits' && btn.textContent.includes('Benefits Summary')) ||
            (tab === 'types' && btn.textContent.includes('Leave Types')) ||
            (tab === 'accrual' && btn.textContent.includes('CSC Daily Accrual')) ||
            (tab === 'import' && btn.textContent.includes('Import Records'))) {
            btn.classList.add('active');
        }
    });

    const tabs = ['leave-tab', 'monetization-tab', 'transactions-tab', 'leave-credits-tab', 'benefits-tab', 'types-tab', 'accrual-tab', 'migrate-tab'];
    tabs.forEach(tabId => {
        const element = document.getElementById(tabId);
        if (element) element.style.display = 'none';
    });

    document.querySelectorAll('.filter-group').forEach(g => g.style.display = 'none');
    const filterGroup = document.getElementById(tab + '-filter-group');
    const filterCard = document.getElementById('leaveFilterCard');
    if (filterGroup) {
        filterGroup.style.display = 'contents';
        if (filterCard) filterCard.style.display = 'flex';
    } else if (filterCard) {
        filterCard.style.display = 'none';
    }

    if (tab === 'leave') {
        const el = document.getElementById('leave-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'monetization') {
        const el = document.getElementById('monetization-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'transactions') {
        const el = document.getElementById('transactions-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'leave-credits') {
        const el = document.getElementById('leave-credits-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'benefits') {
        const el = document.getElementById('benefits-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'types') {
        const el = document.getElementById('types-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'accrual') {
        const el = document.getElementById('accrual-tab');
        if (el) el.style.display = 'block';
    } else if (tab === 'import') {
        const el = document.getElementById('migrate-tab');
        if (el) el.style.display = 'block';
    }
}

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

            const redirectUrl = window.successModalRedirectUrl || modal.dataset.defaultRedirect;
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

// Check URL parameter and switch to correct tab on page load
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get('tab');

    if (activeTab === 'types') {
        switchTab('types');
    } else if (activeTab === 'benefits') {
        switchTab('benefits');
    } else if (activeTab === 'leave') {
        switchTab('leave');
    } else if (activeTab === 'monetization') {
        switchTab('monetization');
    } else if (activeTab === 'accrual') {
        switchTab('accrual');
    } else if (activeTab === 'transactions') {
        switchTab('transactions');
    } else if (activeTab === 'import') {
        switchTab('import');
    } else if (activeTab === 'leave-credits') {
        switchTab('leave-credits');
    }
});

// ── CSV export ────────────────────────────────────────────────────────────────
//
// One handler for all six tabs. The file itself is built by
// LeaveBenefitsExportController from the records, not from the rendered table:
// four of these tabs paginate, so scraping the DOM would export page 1 of 12.
// This function's only job is to hand the endpoint the filters the toolbar is
// currently showing — including the ones this page applies in the browser,
// which the server would otherwise never hear about.
//
// Only filters that are actually set are sent, so the CSV's parameter block
// reads "All Departments" rather than an empty cell.

const LEAVE_EXPORT_FILTERS = {
    'leave': {
        date_from:  'filterLeaveDateFrom',
        date_to:    'filterLeaveDateTo',
        department: 'filterDepartment',
        leave_type: 'filterLeaveType',
        status:     'filterLeaveStatus',
    },
    'transactions': {
        filter_transaction_date_from: 'filterTransactionDateFrom',
        filter_transaction_date_to:   'filterTransactionDateTo',
        filter_transaction_year:      'filterTransactionYear',
        filter_employee:              'filterTransactionEmployee',
        filter_type:                  'filterTransactionType',
        filter_leave_code:            'filterTransactionLeaveType',
    },
    'leave-credits': {
        filter_credits_date_from:  'filterCreditsDateFrom',
        filter_credits_date_to:    'filterCreditsDateTo',
        filter_credits_year:       'filterCreditsYear',
        filter_credits_employee:   'filterCreditsEmployee',
        filter_credits_leave_code: 'filterCreditsLeaveType',
        filter_credits_type:       'filterCreditsType',
    },
    // The Benefits Summary toolbar has no filters; the export covers everyone.
    'benefits': {},
    'types': {
        status:  'filterLeaveTypeStatus',
        accrual: 'filterLeaveTypeAccrual',
    },
    'accrual': {
        status:    'filterAccrualStatus',
        frequency: 'filterAccrualFrequency',
    },
};

window.exportLeaveTab = function(tab) {
    const button = document.querySelector(`[data-export-tab="${tab}"][data-export-url]`);
    const exportUrl = button?.dataset.exportUrl;

    if (!exportUrl) {
        window.openErrorModal('The export endpoint for this tab is unavailable. Please reload the page and try again.');
        return;
    }

    const params = new URLSearchParams();

    Object.entries(LEAVE_EXPORT_FILTERS[tab] || {}).forEach(([param, elementId]) => {
        const value = document.getElementById(elementId)?.value || '';
        // "all" is the Leave Types / Accrual selects' own word for "no filter",
        // and sending it would print "All" where the report should say
        // "All Status".
        if (value && value !== 'all') {
            params.set(param, value);
        }
    });

    const query = params.toString();

    // The endpoint answers with a Content-Disposition attachment, so this
    // downloads the file without the page navigating away — which matters,
    // because leaving the page would reset the tab and every filter on it.
    window.location.href = query ? `${exportUrl}?${query}` : exportUrl;
};
