<x-topbar title="Leave &amp; Benefits Management">
    <x-slot:icon><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Municipal Government of Pagsanjan</x-slot:subtitle>
    <x-slot:actions>
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="leaveSearchInput" class="topbar-search-input" placeholder="Search by employee, leave type, or status..." oninput="searchLeaveRecords(this.value)">
        </div>
    </x-slot:actions>
</x-topbar>

<style>
.welcome-banner {
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", 'Poppins', sans-serif !important;
}
</style>

<script>
function searchLeaveRecords(query) {
    const searchTerm = query.toLowerCase().trim();
    const activeTab = document.querySelector('.tab-btn.active')?.textContent.trim();

    if (activeTab === 'Leave Requests') {
        const rows = document.querySelectorAll('#leaveRequestsTableBody tr');
        let visible = 0;

        rows.forEach(row => {
            if (row.querySelector('.emp-cell')) {
                const empName = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
                const empId = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
                const leaveType = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                const status = row.querySelector('.badge-status')?.textContent.toLowerCase() || '';
                const dept = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';

                const matches = searchTerm === '' ||
                    empName.includes(searchTerm) ||
                    empId.includes(searchTerm) ||
                    leaveType.includes(searchTerm) ||
                    status.includes(searchTerm) ||
                    dept.includes(searchTerm);

                row.style.display = matches ? '' : 'none';
                if (matches) visible++;
            }
        });

        const total = rows.length - (rows[0]?.querySelector('.emp-cell') ? 0 : 1);
        if (document.getElementById('leaveRequestCount')) {
            document.getElementById('leaveRequestCount').textContent = visible;
        }
        if (document.getElementById('leaveRequestFooter')) {
            document.getElementById('leaveRequestFooter').innerHTML = `Showing <strong>${visible}</strong> of <strong>${total}</strong> records`;
        }
    } else if (activeTab === 'Transaction History') {
        const rows = document.querySelectorAll('.transaction-row');
        let visible = 0;

        rows.forEach(row => {
            const empName = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
            const empId = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
            const leaveCode = row.querySelector('.dept-tag')?.textContent.toLowerCase() || '';
            const type = row.querySelector('.badge-status')?.textContent.toLowerCase() || '';

            const matches = searchTerm === '' ||
                empName.includes(searchTerm) ||
                empId.includes(searchTerm) ||
                leaveCode.includes(searchTerm) ||
                type.includes(searchTerm);

            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });

        const total = rows.length;
        if (document.getElementById('transactionFooter')) {
            document.getElementById('transactionFooter').innerHTML = `Showing <strong>${visible}</strong> of <strong>${total}</strong> transactions`;
        }
    } else if (activeTab === 'Leave Types') {
        const rows = document.querySelectorAll('.leave-type-row');
        let visible = 0;

        rows.forEach(row => {
            const code = row.querySelector('.emp-avatar')?.textContent.toLowerCase() || '';
            const name = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const status = row.querySelector('.badge-status')?.textContent.toLowerCase() || '';

            const matches = searchTerm === '' ||
                code.includes(searchTerm) ||
                name.includes(searchTerm) ||
                status.includes(searchTerm);

            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });
    } else if (activeTab === 'CSC Daily Accrual') {
        const rows = document.querySelectorAll('.accrual-rate-row');
        let visible = 0;

        rows.forEach(row => {
            const leaveType = row.querySelector('td:nth-child(1)')?.textContent.toLowerCase() || '';
            const frequency = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            const status = row.querySelector('td:nth-child(7) .badge-status')?.textContent.toLowerCase() || '';

            const matches = searchTerm === '' ||
                leaveType.includes(searchTerm) ||
                frequency.includes(searchTerm) ||
                status.includes(searchTerm);

            row.style.display = matches ? '' : 'none';
            if (matches) visible++;
        });
    }
}
</script>
