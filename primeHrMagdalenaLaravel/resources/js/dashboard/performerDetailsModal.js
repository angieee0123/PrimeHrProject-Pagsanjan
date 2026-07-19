const perfPeriods = window.perfPeriods;

window.showPerformerDetails = function (emp, period, rank) {
    const modal = document.getElementById('performerDetailsModal');
    const rankEmojis = ['🥇', '🥈', '🥉', '4', '5'];
    const periodLabels = {
        'month': 'Previous Month · ' + perfPeriods.month,
        'week': 'Previous Week · ' + perfPeriods.week
    };

    document.getElementById('modalPerformerName').textContent = emp.name;
    document.getElementById('modalPerformerRank').textContent = rankEmojis[rank - 1] || rank;
    document.getElementById('modalPerformerPosition').textContent = emp.position;
    document.getElementById('modalPerformerDept').textContent = emp.department;
    document.getElementById('modalPeriodLabel').textContent = periodLabels[period];
    document.getElementById('modalAttendanceRate').textContent = emp.rate + '%';
    document.getElementById('modalPresentDays').textContent = emp.present_days;
    document.getElementById('modalAbsentDays').textContent = emp.absent_days;
    document.getElementById('modalLateDays').textContent = emp.late_days;
    document.getElementById('modalWorkingDays').textContent = emp.working_days;
    document.getElementById('modalPresentDays2').textContent = emp.present_days;
    document.getElementById('modalAbsentDays2').textContent = emp.absent_days;
    document.getElementById('modalLateDays2').textContent = emp.late_days;
    document.getElementById('modalRate2').textContent = emp.rate + '%';

    const tierEl = document.getElementById('modalTier');
    const tierLabels = {
        'excellent': 'Excellent',
        'good': 'Good',
        'needs_improvement': 'Needs Improvement',
        'poor': 'Poor'
    };
    const tierColors = {
        'excellent': 'background:#e8f9ef;color:#15803d',
        'good': 'background:#e8f9ef;color:#15803d',
        'needs_improvement': 'background:#fbf6e3;color:#c9a227',
        'poor': 'background:#fde8e8;color:#8e1e18'
    };
    tierEl.textContent = tierLabels[emp.tier] || emp.tier;
    tierEl.style.cssText = 'font-size:12px;padding:4px 10px;border-radius:999px;font-weight:700;' + tierColors[emp.tier];

    const avatar = document.getElementById('modalPerformerAvatar');
    if (emp.photo) {
        avatar.innerHTML = '<img src="' + emp.photo + '" style="width:100%;height:100%;border-radius:50%;object-fit:cover">';
    } else {
        avatar.innerHTML = '<span style="color:#fff;font-weight:700;font-size:24px">' + emp.initials + '</span>';
        avatar.style.backgroundColor = emp.color;
    }

    let reason = '<ul style="margin:0;padding-left:20px">';

    if (emp.rate >= 95) {
        reason += '<li style="margin-bottom:8px"><strong>Outstanding attendance rate of ' + emp.rate + '%</strong> - Near perfect attendance record!</li>';
    } else if (emp.rate >= 80) {
        reason += '<li style="margin-bottom:8px"><strong>Excellent attendance rate of ' + emp.rate + '%</strong> - Consistently present at work.</li>';
    }

    if (emp.absent_days === 0) {
        reason += '<li style="margin-bottom:8px"><strong>Zero absences</strong> during the evaluation period.</li>';
    } else if (emp.absent_days <= 2) {
        reason += '<li style="margin-bottom:8px">Only <strong>' + emp.absent_days + ' day(s) absent</strong> - Minimal absenteeism.</li>';
    }

    if (emp.late_days === 0) {
        reason += '<li style="margin-bottom:8px"><strong>Always on time</strong> - Zero late arrivals recorded.</li>';
    } else if (emp.late_days <= 2) {
        reason += '<li style="margin-bottom:8px">Punctual with only <strong>' + emp.late_days + ' late instance(s)</strong>.</li>';
    } else {
        reason += '<li style="margin-bottom:8px">Recorded <strong>' + emp.late_days + ' late arrivals</strong> but maintained excellent overall attendance.</li>';
    }

    reason += '<li style="margin-bottom:8px">Present for <strong>' + emp.present_days + ' out of ' + emp.working_days + ' working days</strong> in this period.</li>';

    if (rank === 1) {
        reason += '<li><strong>🏆 #1 Top Performer</strong> - Leading by example with exceptional dedication!</li>';
    } else if (rank === 2) {
        reason += '<li><strong>🥈 Silver Medal</strong> - Outstanding performance and reliability!</li>';
    } else if (rank === 3) {
        reason += '<li><strong>🥉 Bronze Medal</strong> - Excellent work ethic and consistency!</li>';
    } else {
        reason += '<li>Among the <strong>Top 5 Performers</strong> - Recognized for excellent attendance!</li>';
    }

    reason += '</ul>';
    document.getElementById('modalReason').innerHTML = reason;

    modal.style.display = 'flex';
};

window.closePerformerModal = function () {
    document.getElementById('performerDetailsModal').style.display = 'none';
};

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closePerformerModal();
    }
});
