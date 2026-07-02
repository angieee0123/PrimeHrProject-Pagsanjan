<div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding: 24px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #fff;">Leave & Benefits Management</h3>
        <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.7);">{{ now()->format('l, F j, Y') }} · Municipal Government of Pagsanjan</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- Search Bar -->
        <div style="position: relative;">
            <input type="text" id="leaveSearchInput" placeholder="Search by employee, leave type, or status..." style="width: 320px; padding: 10px 40px 10px 16px; border: none; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff;" oninput="searchLeaveRecords(this.value)" />
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </div>
    </div>
</div>

<style>
no-notification-styles
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
