// DTR Detail Modal (quick-view Daily Time Record + PDF export)
const avatarColors = ['var(--gp-pri)', 'var(--theme-danger)', '#1a0f6e', '#5a0f0b', '#2d1a8e', '#6b3fa0'];
const getInitials = name => name.split(' ').filter(n => /^[A-Z]/.test(n)).map(n => n[0]).join('').slice(0, 2).toUpperCase();

let currentDTRRecord = null;
let currentDTREmployeeId = null;
let currentDTRAppointmentDate = null;

window.openDTRModal = function(record, index) {
    currentDTRRecord = record;
    currentDTREmployeeId = record.employee_id;

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

    fetch(`/admin/attendance/employee-appointment/${currentDTREmployeeId}`)
        .then(response => response.json())
        .then(data => {
            currentDTRAppointmentDate = data.appointment_date;
            const today = new Date();

            document.getElementById('dtrStartDate').min = data.appointment_date;
            document.getElementById('dtrStartDate').value = data.appointment_date;
            document.getElementById('dtrEndDate').min = data.appointment_date;
            document.getElementById('dtrEndDate').value = today.toISOString().split('T')[0];
        })
        .catch(error => console.error('Error fetching appointment date:', error));

    document.getElementById('dtrModal').style.display = 'flex';
    loadDTRSummary();
}

window.closeDTRModal = function() {
    document.getElementById('dtrModal').style.display = 'none';
    currentDTRRecord = null;
    currentDTREmployeeId = null;
    currentDTRAppointmentDate = null;
}

window.loadDTRSummary = function() {
    if (!currentDTRRecord || !currentDTREmployeeId) return;

    const startDate = document.getElementById('dtrStartDate').value;
    const endDate = document.getElementById('dtrEndDate').value;

    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }

    if (new Date(startDate) > new Date(endDate)) {
        alert('Start date must be before end date');
        return;
    }

    if (new Date(startDate) < new Date(currentDTRAppointmentDate)) {
        alert('Start date cannot be before appointment date: ' + currentDTRAppointmentDate);
        return;
    }

    fetch(`/admin/attendance/dtr-summary/${currentDTREmployeeId}?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('dtrWorkingDays').textContent = data.working_days + ' days';
            document.getElementById('dtrPresent').textContent = data.present + ' days';
            document.getElementById('dtrAbsent').textContent = data.absent + ' days';
            document.getElementById('dtrLate').textContent = data.late + ' times';
            document.getElementById('dtrHalfday').textContent = data.halfday + ' days';
            document.getElementById('dtrOT').textContent = data.overtime + ' hrs';
            document.getElementById('dtrRate').textContent = data.rate + '%';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading DTR summary');
        });
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
