// Admin Leave and Benefits Dashboard — tab switching, generic success/error modals

window.switchTab = function(tab) {
    const buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(btn => btn.classList.remove('active'));

    buttons.forEach(btn => {
        if ((tab === 'leave' && btn.textContent.includes('Leave Requests')) ||
            (tab === 'transactions' && btn.textContent.includes('Transaction History')) ||
            (tab === 'leave-credits' && btn.textContent.includes('Leave Credits')) ||
            (tab === 'benefits' && btn.textContent.includes('Benefits Summary')) ||
            (tab === 'types' && btn.textContent.includes('Leave Types')) ||
            (tab === 'accrual' && btn.textContent.includes('CSC Daily Accrual')) ||
            (tab === 'import' && btn.textContent.includes('Import Records'))) {
            btn.classList.add('active');
        }
    });

    const tabs = ['leave-tab', 'transactions-tab', 'leave-credits-tab', 'benefits-tab', 'types-tab', 'accrual-tab', 'migrate-tab'];
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
