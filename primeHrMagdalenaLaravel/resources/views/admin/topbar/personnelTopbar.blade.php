<div style="background: linear-gradient(135deg, #0b044d 0%, #1a0f6e 100%); padding: 24px; border-radius: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h3 style="margin: 0 0 4px; font-size: 20px; font-weight: 700; color: #fff;">Personnel Management</h3>
        <p style="margin: 0; font-size: 13px; color: rgba(255,255,255,0.7);">{{ now()->format('l, F j, Y') }} · Municipal Government of Pagsanjan</p>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <!-- Search Bar -->
        <div style="position: relative;">
            <input type="text" id="personnelSearchInput" placeholder="Search by ID, name, or position..." style="width: 320px; padding: 10px 40px 10px 16px; border: none; border-radius: 8px; font-size: 13px; font-family: 'Poppins', sans-serif; color: #0b044d; background: #fff;" oninput="searchPersonnel(this.value)" />
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#9999bb" stroke-width="2" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); pointer-events: none;">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.35-4.35"/>
            </svg>
        </div>

        <!-- Notification Bell -->
        <button style="background: rgba(255,255,255,0.1); border: none; color: #fff; width: 40px; height: 40px; border-radius: 10px; cursor: pointer; display: flex; align-items: center; justify-content: center; position: relative;" title="Notifications" onclick="toggleNotifications()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <span style="position: absolute; top: 6px; right: 6px; width: 8px; height: 8px; background: #ef4444; border-radius: 50%; border: 2px solid #0b044d;"></span>
        </button>
    </div>
</div>

<script>
function toggleNotifications() {
    alert('Notifications feature coming soon!');
}

function searchPersonnel(query) {
    const searchTerm = query.toLowerCase().trim();
    
    if (!window.allRows || window.allRows.length === 0) {
        const tbody = document.getElementById('personnelTableBody');
        if (tbody) {
            window.allRows = Array.from(tbody.querySelectorAll('tr'));
        }
    }
    
    if (!window.allRows) return;
    
    const filteredRows = window.allRows.filter(row => {
        const empName = row.querySelector('.emp-name')?.textContent.toLowerCase() || '';
        const empId = row.querySelector('.emp-id')?.textContent.toLowerCase() || '';
        const position = row.querySelector('.position-cell')?.textContent.toLowerCase() || '';
        
        return searchTerm === '' || 
               empName.includes(searchTerm) || 
               empId.includes(searchTerm) || 
               position.includes(searchTerm);
    });
    
    // Update the table display
    const tbody = document.getElementById('personnelTableBody');
    if (tbody) {
        tbody.innerHTML = '';
        
        if (filteredRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No employees found matching your search.</td></tr>';
        } else {
            filteredRows.forEach(row => tbody.appendChild(row.cloneNode(true)));
        }
    }
    
    // Update the showing text
    const showingStart = document.getElementById('showingStart');
    const showingEnd = document.getElementById('showingEnd');
    const totalRecords = document.getElementById('totalRecords');
    
    if (showingStart && showingEnd && totalRecords) {
        showingStart.textContent = filteredRows.length > 0 ? '1' : '0';
        showingEnd.textContent = filteredRows.length;
        totalRecords.textContent = filteredRows.length;
    }
    
    // Reset pagination
    if (typeof window.currentPage !== 'undefined') {
        window.currentPage = 1;
    }
    
    // Hide pagination when searching
    const paginationControls = document.getElementById('paginationControls');
    if (paginationControls) {
        paginationControls.style.display = searchTerm === '' ? '' : 'none';
    }
}
</script>
