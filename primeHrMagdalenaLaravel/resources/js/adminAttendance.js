const avatarColors = ['#0b044d', '#8e1e18', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
const getInitials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

let currentEditId = null;
let currentDTRRecord = null;
let currentDetailedEmployeeId = null;
let currentDetailedEmployeeName = null;
let currentDetailedEmployeeEmpId = null;

window.openDTRModal = function(record, index) {
    currentDTRRecord = record;
    const workingDays = record.present + record.absent + record.halfday;
    const rate = workingDays > 0 ? Math.round((record.present / workingDays) * 100) : 0;

    document.getElementById('dtrPeriod').textContent = window.periodDisplay.toUpperCase();
    document.getElementById('dtrName').textContent = record.name;
    document.getElementById('dtrPosition').textContent = record.position;
    document.getElementById('dtrDept').textContent = record.dept;
    document.getElementById('dtrEmpId').textContent = record.id;

    const avatar = document.getElementById('dtrAvatar');
    avatar.textContent = getInitials(record.name);
    avatar.style.background = avatarColors[index % avatarColors.length];

    const statusBadge = document.getElementById('dtrStatus');
    statusBadge.textContent = record.status;
    statusBadge.className = 'badge-status ' + (record.status === 'Complete' ? 'processed' : 'pending');

    document.getElementById('dtrWorkingDays').textContent = workingDays + ' days';
    document.getElementById('dtrPresent').textContent = record.present + ' days';
    document.getElementById('dtrAbsent').textContent = record.absent + ' days';
    document.getElementById('dtrLate').textContent = record.late + ' times';
    document.getElementById('dtrHalfday').textContent = record.halfday + ' days';
    document.getElementById('dtrOT').textContent = record.overtime + ' hrs';
    document.getElementById('dtrRate').textContent = rate + '%';

    document.getElementById('dtrModal').style.display = 'flex';
}

window.closeDTRModal = function() {
    document.getElementById('dtrModal').style.display = 'none';
    currentDTRRecord = null;
}

window.downloadDTR = function() {
    if (!currentDTRRecord) return;

    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/></svg> Generating...';
    btn.disabled = true;

    setTimeout(() => {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        const workingDays = currentDTRRecord.present + currentDTRRecord.absent + currentDTRRecord.halfday;
        const rate = workingDays > 0 ? Math.round((currentDTRRecord.present / workingDays) * 100) : 0;

        doc.setFillColor(11, 4, 77);
        doc.rect(0, 0, 210, 40, 'F');

        doc.setTextColor(255, 255, 255);
        doc.setFontSize(20);
        doc.setFont(undefined, 'bold');
        doc.text('DAILY TIME RECORD', 105, 15, { align: 'center' });

        doc.setFontSize(11);
        doc.setFont(undefined, 'normal');
        doc.text('Municipal Government of Pagsanjan', 105, 23, { align: 'center' });
        doc.text(window.periodDisplay, 105, 30, { align: 'center' });

        doc.setTextColor(0, 0, 0);
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('EMPLOYEE INFORMATION', 20, 55);

        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.text('Name:', 20, 65);
        doc.setFont(undefined, 'bold');
        doc.text(currentDTRRecord.name, 50, 65);

        doc.setFont(undefined, 'normal');
        doc.text('Employee ID:', 20, 72);
        doc.setFont(undefined, 'bold');
        doc.text(currentDTRRecord.id, 50, 72);

        doc.setFont(undefined, 'normal');
        doc.text('Position:', 20, 79);
        doc.setFont(undefined, 'bold');
        doc.text(currentDTRRecord.position, 50, 79);

        doc.setFont(undefined, 'normal');
        doc.text('Department:', 20, 86);
        doc.setFont(undefined, 'bold');
        doc.text(currentDTRRecord.dept, 50, 86);

        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.text('ATTENDANCE SUMMARY', 20, 105);

        doc.setFillColor(247, 246, 255);
        doc.rect(20, 110, 170, 10, 'F');

        doc.setFontSize(9);
        doc.setFont(undefined, 'bold');
        doc.text('METRIC', 25, 116);
        doc.text('VALUE', 160, 116);

        const rows = [
            { label: 'Working Days', value: workingDays + ' days', color: [0, 0, 0] },
            { label: 'Days Present', value: currentDTRRecord.present + ' days', color: [21, 128, 61] },
            { label: 'Days Absent', value: currentDTRRecord.absent + ' days', color: [142, 30, 24] },
            { label: 'Late Arrivals', value: currentDTRRecord.late + ' times', color: [161, 98, 7] },
            { label: 'Half Days', value: currentDTRRecord.halfday + ' days', color: [161, 98, 7] },
        ];

        let yPos = 126;
        doc.setFont(undefined, 'normal');
        rows.forEach((row, i) => {
            if (i % 2 === 0) {
                doc.setFillColor(250, 250, 254);
                doc.rect(20, yPos - 5, 170, 8, 'F');
            }
            doc.setTextColor(107, 106, 138);
            doc.text(row.label, 25, yPos);
            doc.setTextColor(...row.color);
            doc.setFont(undefined, 'bold');
            doc.text(row.value, 160, yPos);
            doc.setFont(undefined, 'normal');
            yPos += 8;
        });

        yPos += 10;
        doc.setFontSize(12);
        doc.setFont(undefined, 'bold');
        doc.setTextColor(0, 0, 0);
        doc.text('OVERTIME', 20, yPos);

        yPos += 10;
        doc.setFillColor(247, 246, 255);
        doc.rect(20, yPos - 5, 170, 8, 'F');

        doc.setFontSize(9);
        doc.setFont(undefined, 'normal');
        doc.setTextColor(107, 106, 138);
        doc.text('Total OT Hours', 25, yPos);
        doc.setTextColor(11, 4, 77);
        doc.setFont(undefined, 'bold');
        doc.text(currentDTRRecord.overtime + ' hrs', 160, yPos);

        yPos += 20;
        doc.setFillColor(11, 4, 77);
        doc.rect(20, yPos - 8, 170, 15, 'F');

        doc.setTextColor(255, 255, 255);
        doc.setFontSize(10);
        doc.setFont(undefined, 'normal');
        doc.text('ATTENDANCE RATE', 25, yPos);

        doc.setFontSize(16);
        doc.setFont(undefined, 'bold');
        doc.text(rate + '%', 160, yPos);

        yPos += 20;
        doc.setFontSize(10);
        doc.setTextColor(0, 0, 0);
        doc.setFont(undefined, 'normal');
        doc.text('Status:', 20, yPos);

        const statusColor = currentDTRRecord.status === 'Complete' ? [21, 128, 61] : [161, 98, 7];
        doc.setTextColor(...statusColor);
        doc.setFont(undefined, 'bold');
        doc.text(currentDTRRecord.status, 50, yPos);

        doc.setFontSize(8);
        doc.setTextColor(153, 153, 187);
        doc.setFont(undefined, 'normal');
        doc.text('Generated on ' + new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }), 105, 280, { align: 'center' });
        doc.text('Municipal Government of Pagsanjan - Human Resource Management Office', 105, 285, { align: 'center' });

        const fileName = `DTR_${currentDTRRecord.id}_${currentDTRRecord.name.replace(/\s+/g, '_')}_${window.periodDisplayFile}.pdf`;
        doc.save(fileName);

        btn.innerHTML = originalHTML;
        btn.disabled = false;
    }, 500);
}

window.openEditModal = function(record) {
    currentEditId = record.employee_id;

    document.getElementById('editName').textContent = record.name;
    document.getElementById('editEmpId').textContent = record.id;
    document.getElementById('editPresent').value = record.present;
    document.getElementById('editAbsent').value = record.absent;
    document.getElementById('editLate').value = record.late;
    document.getElementById('editHalfday').value = record.halfday;
    document.getElementById('editOT').value = record.overtime;
    document.getElementById('editStatus').value = record.status;

    updateRatePreview();

    ['editPresent', 'editAbsent', 'editHalfday'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateRatePreview);
    });

    document.getElementById('editModal').style.display = 'flex';
}

window.closeEditModal = function() {
    document.getElementById('editModal').style.display = 'none';
}

function updateRatePreview() {
    const present = parseInt(document.getElementById('editPresent').value) || 0;
    const absent = parseInt(document.getElementById('editAbsent').value) || 0;
    const halfday = parseInt(document.getElementById('editHalfday').value) || 0;
    const workingDays = present + absent + halfday;
    const rate = workingDays > 0 ? Math.round((present / workingDays) * 100) : 0;
    document.getElementById('editRatePreview').textContent = rate + '%';
}

window.saveEdit = function() {
    alert('Save functionality to be implemented');
    closeEditModal();
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDTRModal();
        closeEditModal();
        closeDetailedDTRModal();
        closeCorrectModal();
    }
});

let currentCorrectAttendanceId = null;

window.calculateTotalHours = function() {
    const amIn = document.getElementById('correctAmIn').value;
    const amOut = document.getElementById('correctAmOut').value;
    const pmIn = document.getElementById('correctPmIn').value;
    const pmOut = document.getElementById('correctPmOut').value;
    const otIn = document.getElementById('correctOtIn').value;
    const otOut = document.getElementById('correctOtOut').value;

    let workMinutes = 0;
    let lateMinutes = 0;
    let undertimeMinutes = 0;
    let otMinutes = 0;

    // Calculate work hours
    if (amIn && pmOut) {
        const amInTime = new Date('1970-01-01 ' + amIn);
        const pmOutTime = new Date('1970-01-01 ' + pmOut);
        
        // WorkHours = (PM Out - AM In) - 1 hour
        let totalMinutes = (pmOutTime - amInTime) / 1000 / 60;
        if (totalMinutes < 0) totalMinutes += 24 * 60;
        workMinutes = Math.max(0, totalMinutes - 60);
    }
    // If only AM session
    else if (amIn && amOut && !pmIn && !pmOut) {
        const amInTime = new Date('1970-01-01 ' + amIn);
        const amOutTime = new Date('1970-01-01 ' + amOut);
        let amMinutes = (amOutTime - amInTime) / 1000 / 60;
        if (amMinutes < 0) amMinutes += 24 * 60;
        workMinutes = Math.max(0, amMinutes);
    }
    // If only PM session
    else if (pmIn && pmOut && !amIn && !amOut) {
        const pmInTime = new Date('1970-01-01 ' + pmIn);
        const pmOutTime = new Date('1970-01-01 ' + pmOut);
        let pmMinutes = (pmOutTime - pmInTime) / 1000 / 60;
        if (pmMinutes < 0) pmMinutes += 24 * 60;
        workMinutes = Math.max(0, pmMinutes);
    }

    // Calculate overtime
    if (otIn && otOut) {
        let otInTime = new Date('1970-01-01 ' + otIn);
        const otOutTime = new Date('1970-01-01 ' + otOut);
        const expectedOtStart = new Date('1970-01-01 17:00:00');
        
        // IF OT In < 5:00 PM, OT In = 5:00 PM
        if (otInTime < expectedOtStart) {
            otInTime = expectedOtStart;
        }
        
        let otDiff = (otOutTime - otInTime) / 1000 / 60;
        if (otDiff < 0) otDiff += 24 * 60;
        otMinutes = Math.max(0, otDiff);
    }

    // Calculate late with 15-min grace period
    if (amIn) {
        const amInTime = new Date('1970-01-01 ' + amIn);
        const graceThreshold = new Date('1970-01-01 08:15:00');
        const expectedIn = new Date('1970-01-01 08:00:00');
        if (amInTime > graceThreshold) {
            lateMinutes = Math.max(0, (amInTime - expectedIn) / 1000 / 60);
        }
    }

    // Calculate undertime
    if (pmOut) {
        const pmOutTime = new Date('1970-01-01 ' + pmOut);
        const expectedOut = new Date('1970-01-01 17:00:00');
        
        // UT_time = max(0, 5:00 PM - PM Out)
        let utTime = 0;
        if (pmOutTime < expectedOut) {
            utTime = Math.max(0, (expectedOut - pmOutTime) / 1000 / 60);
        }
        
        // UT_hours = max(0, 8 hours - WorkHours)
        let utHours = Math.max(0, (8 * 60) - workMinutes);
        
        // Undertime = max(UT_time, UT_hours)
        undertimeMinutes = Math.max(utTime, utHours);
    }

    // Total = WorkHours + OT - Late - Undertime
    const totalMinutes = workMinutes + otMinutes - lateMinutes - undertimeMinutes;
    const totalHours = Math.max(0, totalMinutes / 60);
    document.getElementById('calculatedTotalHours').textContent = totalHours.toFixed(1) + ' hrs';

    const display = document.getElementById('calculatedTotalHours');
    if (totalHours >= 8) {
        display.style.color = '#15803d';
    } else if (totalHours >= 4) {
        display.style.color = '#d9bb00';
    } else if (totalHours > 0) {
        display.style.color = '#a16207';
    } else {
        display.style.color = '#8e1e18';
    }
}

window.openCorrectModal = function(attendanceId, date) {
    currentCorrectAttendanceId = attendanceId;

    fetch(`/admin/attendance/record/${attendanceId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('correctEmployeeName').textContent = data.employee_name;
            document.getElementById('correctDate').textContent = new Date(data.date).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            document.getElementById('correctAttendanceId').value = data.is_new ? '' : data.id;
            document.getElementById('correctEmployeeId').value = data.employee_id;
            document.getElementById('correctDateValue').value = data.date;

            const convertTime = (time) => {
                if (!time) return '';
                if (/^\d{2}:\d{2}$/.test(time)) return time;
                if (/^\d{2}:\d{2}:\d{2}$/.test(time)) return time.substring(0, 5);
                try {
                    const date = new Date('1970-01-01 ' + time);
                    return date.toTimeString().substring(0, 5);
                } catch (e) {
                    return '';
                }
            };

            document.getElementById('correctAmIn').value = convertTime(data.am_in);
            document.getElementById('correctAmOut').value = convertTime(data.am_out);
            document.getElementById('correctPmIn').value = convertTime(data.pm_in);
            document.getElementById('correctPmOut').value = convertTime(data.pm_out);
            document.getElementById('correctOtIn').value = convertTime(data.ot_in);
            document.getElementById('correctOtOut').value = convertTime(data.ot_out);

            document.getElementById('correctReason').value = '';
            document.getElementById('correctAttachments').value = '';
            document.getElementById('filePreview').innerHTML = '';

            calculateTotalHours();

            document.getElementById('correctModal').style.display = 'flex';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading attendance record');
        });
}

window.closeCorrectModal = function() {
    document.getElementById('correctModal').style.display = 'none';
    currentCorrectAttendanceId = null;
}

document.getElementById('correctAttachments').addEventListener('change', function(e) {
    const preview = document.getElementById('filePreview');
    preview.innerHTML = '';

    Array.from(e.target.files).forEach(file => {
        const item = document.createElement('div');
        item.className = 'file-preview-item';

        const icon = file.type === 'application/pdf'
            ? '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>'
            : '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';

        item.innerHTML = icon + '<span>' + file.name + '</span>';
        preview.appendChild(item);
    });
});

document.getElementById('correctForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const btn = document.getElementById('correctSubmitBtn');
    const originalHTML = btn.innerHTML;

    btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation: spin 1s linear infinite;"><circle cx="12" cy="12" r="10" opacity="0.25"/><path d="M12 2a10 10 0 0 1 10 10" opacity="0.75"/></svg> Saving...';
    btn.disabled = true;

    fetch('/admin/attendance/correct', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeCorrectModal();
            openSuccessModal();
            loadDetailedDTR();
        } else {
            alert('Error: ' + (data.message || 'Failed to correct attendance'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error correcting attendance');
    })
    .finally(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
});

window.openDetailedDTRModal = function(employeeId, name, empId) {
    currentDetailedEmployeeId = employeeId;
    currentDetailedEmployeeName = name;
    currentDetailedEmployeeEmpId = empId;

    document.getElementById('detailedName').textContent = name;
    document.getElementById('detailedEmpId').textContent = empId;

    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    document.getElementById('detailedStartDate').value = firstDay.toISOString().split('T')[0];
    document.getElementById('detailedEndDate').value = lastDay.toISOString().split('T')[0];

    document.getElementById('detailedDTRModal').style.display = 'flex';

    loadDetailedDTR();
}

window.closeDetailedDTRModal = function() {
    document.getElementById('detailedDTRModal').style.display = 'none';
    currentDetailedEmployeeId = null;
}

window.loadDetailedDTR = function() {
    if (!currentDetailedEmployeeId) return;

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }

    document.getElementById('detailedDTRLoading').style.display = 'block';
    document.getElementById('detailedDTRTable').style.display = 'none';

    fetch(`/admin/attendance/detailed/${currentDetailedEmployeeId}?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            renderDetailedDTR(data);
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('detailedDTRLoading').innerHTML = '<p style="color: #8e1e18;">Error loading attendance records</p>';
        });
}

function renderDetailedDTR(data) {
    const tbody = document.getElementById('detailedDTRBody');
    tbody.innerHTML = '';

    let totalPresent = 0;
    let totalAbsent = 0;
    let totalLate = 0;
    let totalLateMinutes = 0;
    let totalUndertimeMinutes = 0;

    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    const startFormatted = new Date(startDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    const endFormatted = new Date(endDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    document.getElementById('detailedPeriod').textContent = startFormatted + ' - ' + endFormatted;

    data.records.forEach(record => {
        const tr = document.createElement('tr');

        const isWeekend = record.day === 'Saturday' || record.day === 'Sunday';
        if (isWeekend) {
            tr.className = 'day-weekend';
        }

        const hasAnyLog = record.am_in || record.am_out || record.pm_in || record.pm_out;
        const isAbsent = !isWeekend && !hasAnyLog;
        if (isAbsent) {
            tr.className = 'day-absent';
            totalAbsent++;
        } else if (hasAnyLog) {
            totalPresent++;
        }

        const isLate = record.late_minutes > 0;
        if (isLate) {
            totalLate++;
            totalLateMinutes += record.late_minutes;
        }

        if (record.undertime > 0) {
            totalUndertimeMinutes += record.undertime;
        }

        tr.innerHTML = `
            <td><strong>${record.date}</strong></td>
            <td>${record.day}</td>
            <td>${record.am_in || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.am_out || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.pm_in || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.pm_out || '<span class="log-missing">Log Missing</span>'}</td>
            <td>${record.ot_in || '—'}</td>
            <td>${record.ot_out || '—'}</td>
            <td>${record.undertime > 0 ? '<span class="log-late">' + record.undertime + ' min</span>' : (record.pm_out ? '0 min' : '—')}</td>
            <td>${record.late_minutes > 0 ? '<span class="log-late">' + record.late_minutes + ' min</span>' : (record.am_in ? '0 min' : '—')}</td>
            <td><strong>${record.total_hours}</strong></td>
            <td><button class="btn-edit-time" onclick="openCorrectModal(${record.attendance_id ? record.attendance_id : "'new_" + currentDetailedEmployeeId + "_" + record.date_key + "'"}, '${record.date}')" title="${record.attendance_id ? 'Edit time records' : 'Add time records'}">Edit</button></td>
        `;

        tbody.appendChild(tr);
    });

    document.getElementById('detailedTotalDays').textContent = data.records.length;
    document.getElementById('detailedTotalPresent').textContent = totalPresent;
    document.getElementById('detailedTotalAbsent').textContent = totalAbsent;
    document.getElementById('detailedTotalLate').textContent = totalLate + ' times';
    document.getElementById('detailedTotalLateMinutes').textContent = totalLateMinutes + ' min';
    document.getElementById('detailedTotalUndertime').textContent = totalUndertimeMinutes + ' min';

    document.getElementById('detailedDTRLoading').style.display = 'none';
    document.getElementById('detailedDTRTable').style.display = 'table';
}

window.exportDetailedDTR = function() {
    const startDate = document.getElementById('detailedStartDate').value;
    const endDate = document.getElementById('detailedEndDate').value;
    window.location.href = `/admin/attendance/detailed/${currentDetailedEmployeeId}/export?start_date=${startDate}&end_date=${endDate}`;
}

window.openSuccessModal = function() {
    document.getElementById('successModal').style.display = 'flex';
}

window.closeSuccessModal = function() {
    document.getElementById('successModal').style.display = 'none';
}
