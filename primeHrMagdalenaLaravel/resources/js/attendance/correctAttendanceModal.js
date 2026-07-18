// Correct Attendance Modal
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

    // Calculate late with 5-min grace period
    if (amIn) {
        const amInTime = new Date('1970-01-01 ' + amIn);
        const graceThreshold = new Date('1970-01-01 08:05:00');
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

    // Handle both numeric IDs and string-based new record IDs
    const endpoint = typeof attendanceId === 'string' && attendanceId.startsWith('new_')
        ? `/admin/attendance/record/${attendanceId}`
        : `/admin/attendance/record/${attendanceId}`;

    fetch(endpoint)
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

            // Add validation warning
            let validationMessage = '';
            if (!data.am_out) {
                validationMessage += '⚠️ Missing AM Out time\n';
            }
            if (!data.pm_in) {
                validationMessage += '⚠️ Missing PM In time\n';
            }

            if (validationMessage) {
                const warningDiv = document.getElementById('correctReason');
                warningDiv.placeholder = 'REQUIRED: ' + validationMessage + '\n\nExplain why this correction is needed...';
                warningDiv.style.borderLeft = '3px solid #8e1e18';
            }

            calculateTotalHours();

            // Pass slip banner
            const banner = document.getElementById('correctPassSlipBanner');
            if (banner) {
                const slips = data.pass_slips || [];
                if (slips.length) {
                    const fmt12 = (t) => {
                        if (!t) return null;
                        const [h, m] = t.split(':').map(Number);
                        return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${h >= 12 ? 'PM' : 'AM'}`;
                    };
                    banner.style.display = 'block';
                    banner.innerHTML = `<div class="correct-ps-banner">
                        <div class="correct-ps-banner-title">&#10003; Approved Pass Slip(s) on this date &mdash; times noted below</div>
                        ${slips.map(s => {
                            const out = fmt12(s.time_out), inn = fmt12(s.time_in);
                            const range = out ? (inn ? `${out} &ndash; ${inn}` : `Out: ${out}`) : '';
                            const badge = s.type === 'official_activity' ? 'official' : 'personal';
                            const label = s.type === 'official_activity' ? 'Official Activity' : 'Personal Reason';
                            return `<div class="correct-ps-row">
                                <span class="cps-times">${range}</span>
                                ${s.slip_number ? `<span class="cps-num">#${s.slip_number}</span>` : ''}
                                <span class="cps-badge ${badge}">${label}</span>
                            </div>`;
                        }).join('')}
                    </div>`;
                } else {
                    banner.style.display = 'none';
                    banner.innerHTML = '';
                }
            }

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

document.addEventListener('DOMContentLoaded', function() {

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
            if (window.currentDetailedEmployeeId) {
                // Correction was made from the per-employee Detailed DTR modal —
                // refresh its own AJAX table in place.
                loadDetailedDTR();
            } else {
                // Correction was made from the Detailed Time Record tab's timeline,
                // which is server-rendered — reload once the user dismisses the
                // success modal so the updated status/avatar/times actually show.
                window.reloadAfterSuccessModal = true;
            }
            openSuccessModal();
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

}); // end DOMContentLoaded
