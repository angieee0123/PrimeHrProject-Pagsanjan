import { exportWithFilters } from './exportWithFilters.js';
function filterSchedules() {
    const searchTerm = document.getElementById('searchSchedule').value.toLowerCase();
    const departmentFilter = document.getElementById('filterDepartment').value;
    const rows = document.querySelectorAll('#schedulesTableBody tr:not(#noSchedulesRow)');

    let visibleCount = 0;

    rows.forEach(row => {
        const employeeName = row.dataset.employee || '';
        const department = row.dataset.department || '';

        const matchesSearch = employeeName.includes(searchTerm);
        const matchesDepartment = !departmentFilter || department === departmentFilter;

        if (matchesSearch && matchesDepartment) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update showing count
    document.getElementById('showingSchedulesCount').textContent = visibleCount;

    // Show/hide no data row
    const noSchedulesRow = document.getElementById('noSchedulesRow');
    if (noSchedulesRow) {
        noSchedulesRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function exportSchedules(btn) {
    exportWithFilters(btn, {
        search:     'searchSchedule',
        department: 'filterDepartment',
    });
}

window.filterSchedules = filterSchedules;
window.exportSchedules = exportSchedules;
