<!-- Detailed DTR Modal -->
<div id="detailedDTRModal" class="modal-overlay" style="display: none;" onclick="closeDetailedDTRModal()">
    <div class="modal-box modal-box-wide" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div>
                <span class="modal-eyebrow">DETAILED DTR · <span id="detailedPeriod">{{ strtoupper($periodDisplay) }}</span></span>
                <h3 class="modal-title" id="detailedName">{{ $employee->first_name }} {{ $employee->last_name }}</h3>
                <p class="modal-sub" id="detailedEmpId">{{ $employee->employee_id }}</p>
            </div>
            <button class="modal-close" onclick="closeDetailedDTRModal()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="detailed-dtr-filters" style="padding: 16px 24px; border-bottom: 1px solid #f0effe; display: flex; gap: 12px; align-items: center;">
                <span style="font-size: 12px; font-weight: 600; color: #6b6a8a;">Date Range:</span>
                <input type="date" id="detailedStartDate" class="filter-select-sm" style="width: auto;" value="{{ now()->startOfMonth()->format('Y-m-d') }}">
                <span style="font-size: 12px; color: #9999bb;">to</span>
                <input type="date" id="detailedEndDate" class="filter-select-sm" style="width: auto;" value="{{ now()->endOfMonth()->format('Y-m-d') }}">
                <button class="btn-filter" onclick="loadDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filter
                </button>
                <div style="flex: 1;"></div>
                <button class="btn-export-sm" onclick="exportDetailedDTR()">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export
                </button>
            </div>
            <div style="max-height: 500px; overflow-y: auto; padding: 24px;">
                <div id="detailedDTRLoading" style="text-align: center; padding: 40px; color: #9999bb;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite; margin: 0 auto;">
                        <circle cx="12" cy="12" r="10" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/>
                    </svg>
                    <p style="margin-top: 12px;">Loading attendance records...</p>
                </div>
                <table class="detailed-dtr-table" id="detailedDTRTable" style="display: none;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>AM In</th>
                            <th>AM Out</th>
                            <th>PM In</th>
                            <th>PM Out</th>
                            <th>OT In</th>
                            <th>OT Out</th>
                            <th>Undertime</th>
                            <th>Late</th>
                            <th>Total Hours</th>
                            <th>Accredited Hours</th>
                            <th>Leave Deduction</th>
                        </tr>
                    </thead>
                    <tbody id="detailedDTRBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer" style="background: #fafafe; border-top: 1px solid #f0effe;">
            <div style="flex: 1; display: flex; gap: 16px; font-size: 12px;">
                <div><span style="color: #9999bb;">Total Days:</span> <strong id="detailedTotalDays" style="color: #0b044d;">0</strong></div>
                <div><span style="color: #9999bb;">Present:</span> <strong id="detailedTotalPresent" style="color: #15803d;">0</strong></div>
                <div><span style="color: #9999bb;">Absent:</span> <strong id="detailedTotalAbsent" style="color: #8e1e18;">0</strong></div>
                <div><span style="color: #9999bb;">Late:</span> <strong id="detailedTotalLate" style="color: #a16207;">0 times</strong></div>
                <div><span style="color: #9999bb;">Total Late:</span> <strong id="detailedTotalLateMinutes" style="color: #a16207;">0 min</strong></div>
                <div><span style="color: #9999bb;">Total Undertime:</span> <strong id="detailedTotalUndertime" style="color: #8e1e18;">0 min</strong></div>
            </div>
            <button class="modal-btn-ghost" onclick="closeDetailedDTRModal()">Close</button>
        </div>
    </div>
</div>

<style>
.modal-box-wide {
    max-width: 1200px;
    width: 95%;
}

.filter-select-sm {
    padding: 6px 12px;
    border: 1.5px solid #e8e7f5;
    border-radius: 8px;
    font-size: 12px;
    font-family: 'Poppins', sans-serif;
    color: #0b044d;
    background: #fff;
    cursor: pointer;
}

.filter-select-sm:focus {
    outline: none;
    border-color: #0b044d;
}

.btn-filter {
    height: 32px;
    padding: 0 14px;
    background: #0b044d;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Poppins', sans-serif;
    transition: background 0.2s;
}

.btn-filter:hover {
    background: #1a0f6e;
}

.btn-export-sm {
    height: 32px;
    padding: 0 14px;
    background: #fff;
    border: 1.5px solid #e4e3f0;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #0b044d;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-family: 'Poppins', sans-serif;
    transition: all 0.2s;
}

.btn-export-sm:hover {
    border-color: #0b044d;
    background: #f4f3ff;
}

.detailed-dtr-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.detailed-dtr-table thead tr {
    background: #f7f6ff;
}

.detailed-dtr-table th {
    text-align: left;
    padding: 10px 12px;
    font-size: 10px;
    font-weight: 700;
    color: #9999bb;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    border-bottom: 1.5px solid #f0effe;
    white-space: nowrap;
}

.detailed-dtr-table td {
    padding: 12px;
    font-size: 12px;
    color: #0b044d;
    border-bottom: 1px solid #f7f6ff;
    vertical-align: middle;
    white-space: nowrap;
}

.detailed-dtr-table tbody tr:hover {
    background: #fafafe;
}

.detailed-dtr-table tbody tr:last-child td {
    border-bottom: none;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .modal-box-wide {
        width: 100%;
        max-width: 100%;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .detailed-dtr-filters {
        flex-wrap: wrap;
    }
    
    .detailed-dtr-table {
        font-size: 11px;
    }
    
    .detailed-dtr-table th,
    .detailed-dtr-table td {
        padding: 8px 6px;
        font-size: 10px;
    }
}
</style>

<script>
function closeDetailedDTRModal() {
    document.getElementById('detailedDTRModal').style.display = 'none';
}

function loadDetailedDTR() {
    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    document.getElementById('detailedDTRLoading').style.display = 'block';
    document.getElementById('detailedDTRTable').style.display = 'none';
    
    fetch(`{{ route('permanent.attendance.detailed') }}?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            displayDetailedDTR(data.records);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load attendance records');
            document.getElementById('detailedDTRLoading').style.display = 'none';
        });
}

function displayDetailedDTR(records) {
    const tbody = document.getElementById('detailedDTRBody');
    tbody.innerHTML = '';
    
    let totalDays = 0;
    let totalPresent = 0;
    let totalAbsent = 0;
    let totalLate = 0;
    let totalLateMinutes = 0;
    let totalUndertimeMinutes = 0;
    
    records.forEach(record => {
        const row = document.createElement('tr');
        
        const isOnLeave = record.is_on_leave;
        const isAbsent = !record.am_in && !record.pm_in && !isOnLeave;
        
        if (!isOnLeave) totalDays++;
        if (record.accredited_minutes > 0 || isOnLeave) totalPresent++;
        if (isAbsent) totalAbsent++;
        if (record.late_minutes > 0) {
            totalLate++;
            totalLateMinutes += record.late_minutes;
        }
        totalUndertimeMinutes += record.undertime;
        
        row.innerHTML = `
            <td>${record.date}</td>
            <td>${record.day}</td>
            <td>${record.am_in || '-'}</td>
            <td>${record.am_out || '-'}</td>
            <td>${record.pm_in || '-'}</td>
            <td>${record.pm_out || '-'}</td>
            <td>${record.ot_in || '-'}</td>
            <td>${record.ot_out || '-'}</td>
            <td>${record.undertime_display}</td>
            <td>${record.late_display}</td>
            <td>${record.total_hours}</td>
            <td>${(record.accredited_minutes / 60).toFixed(1)} hrs</td>
            <td>${record.leave_deduction}</td>
        `;
        tbody.appendChild(row);
    });
    
    document.getElementById('detailedTotalDays').textContent = totalDays;
    document.getElementById('detailedTotalPresent').textContent = totalPresent;
    document.getElementById('detailedTotalAbsent').textContent = totalAbsent;
    document.getElementById('detailedTotalLate').textContent = totalLate + ' times';
    document.getElementById('detailedTotalLateMinutes').textContent = totalLateMinutes + ' min';
    document.getElementById('detailedTotalUndertime').textContent = totalUndertimeMinutes + ' min';
    
    document.getElementById('detailedDTRLoading').style.display = 'none';
    document.getElementById('detailedDTRTable').style.display = 'table';
}

function exportDetailedDTR() {
    alert('Export functionality - to be implemented');
}

// Load initial data when modal opens
function showDetailedDTRModal() {
    document.getElementById('detailedDTRModal').style.display = 'flex';
    loadDetailedDTR();
}
</script>
