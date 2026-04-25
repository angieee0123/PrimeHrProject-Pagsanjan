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

    const tbody = document.getElementById('personnelTableBody');
    if (tbody) {
        tbody.innerHTML = '';

        if (filteredRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 40px; color: #6b6a8a;">No employees found matching your search.</td></tr>';
        } else {
            filteredRows.forEach(row => tbody.appendChild(row.cloneNode(true)));
        }
    }

    const showingStart = document.getElementById('showingStart');
    const showingEnd = document.getElementById('showingEnd');
    const totalRecords = document.getElementById('totalRecords');

    if (showingStart && showingEnd && totalRecords) {
        showingStart.textContent = filteredRows.length > 0 ? '1' : '0';
        showingEnd.textContent = filteredRows.length;
        totalRecords.textContent = filteredRows.length;
    }

    if (typeof window.currentPage !== 'undefined') {
        window.currentPage = 1;
    }

    const paginationControls = document.getElementById('paginationControls');
    if (paginationControls) {
        paginationControls.style.display = searchTerm === '' ? '' : 'none';
    }
}
