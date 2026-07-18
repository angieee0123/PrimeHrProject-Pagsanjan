@extends('layouts.app')

@push('styles')
    @vite('resources/css/adminPersonnel.css')
@endpush

@push('scripts')
    @vite('resources/js/employeeWizard.js')
    @vite('resources/js/adminPersonnel.js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
@endpush

@section('content')
@include('admin.topbar.personnelTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

@php
    // Shared by employee-records-tab and schedule-tab — declared here (not in either
    // partial) because Blade's @include only passes variables down from parent to
    // child; it does not share state between sibling @includes.
    $avatarColors = ['#0b044d', '#8e1e18', '#150c63', '#a52820', '#c9a227', '#56547a'];
    function getInitials($name) {
        $parts = explode(' ', $name);
        $initials = '';
        foreach ($parts as $part) {
            if (preg_match('/^[A-Z]/', $part)) {
                $initials .= $part[0];
            }
        }
        return strtoupper(substr($initials, 0, 2));
    }
@endphp

@include('admin.personnel.partials.stats-grid')

{{-- Filter Toolbar --}}
@include('admin.personnel.partials.filter-toolbar')

{{-- Tabs --}}
@include('admin.personnel.partials.tab-nav')

@include('admin.personnel.partials.employee-records-tab')

@include('admin.personnel.partials.schedule-tab')

</div>

@include('admin.personnel.modals.employeeWizardComplete')
@include('admin.personnel.modals.assignSchedule')
@include('admin.personnel.modals.bulkAssignSchedule')
@include('admin.personnel.modals.viewSchedules')
@include('admin.personnel.modals.successModal')
@include('admin.personnel.modals.errorModal')
@include('admin.personnel.modals.confirmModal')
@include('admin.personnel.modals.viewEmployeeModal')
@include('admin.personnel.modals.exportSuccessModal')
@include('admin.personnel.modals.exportErrorModal')
@include('admin.personnel.modals.qrCodeModal')
@include('admin.personnel.modals.bulkImportModal')

@push('scripts')
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('successMessage').textContent = "{{ session('success') }}";
    document.getElementById('successModal').style.display = 'flex';
    if (document.getElementById('employeeWizardModal')) {
        document.getElementById('employeeWizardModal').style.display = 'none';
    }
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('errorMessage').textContent = "{{ session('error') }}";
    document.getElementById('errorModal').style.display = 'flex';
});
</script>
@endif

<script>
// Tab switching functionality
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        this.classList.add('active');
        document.getElementById(this.dataset.tab).classList.add('active');
    });
});

// Check if we should activate schedules tab on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('active_tab') === 'schedules')
        // Activate schedules tab
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

        const schedulesTabBtn = document.querySelector('.tab-btn[data-tab="schedules"]');
        const schedulesTabContent = document.getElementById('schedules');

        if (schedulesTabBtn && schedulesTabContent) {
            schedulesTabBtn.classList.add('active');
            schedulesTabContent.classList.add('active');
        }
    @endif
});

// Schedule filters
function applyScheduleFilters() {
    const deptFilter = document.getElementById('schedDepartmentFilter').value;
    const rows = document.querySelectorAll('#scheduleTableBody tr');

    rows.forEach(row => {
        const deptCell = row.querySelector('.dept-tag');
        if (!deptCell) return;

        const deptMatch = !deptFilter || deptCell.textContent.trim() === deptFilter;
        row.style.display = deptMatch ? '' : 'none';
    });
}

function changeScheduleRowsPerPage(value) {
    // Implement pagination logic similar to main table
    console.log('Change schedule rows per page:', value);
}

function exportSchedules() {
    window.location.href = '{{ route("admin.schedules.export") }}';
}

function openBulkScheduleModal() {
    document.getElementById('bulkScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function openAssignScheduleModal(employeeId, employeeName, schedule) {
    document.getElementById('scheduleEmployeeId').value = employeeId;
    document.getElementById('scheduleEmployeeName').textContent = employeeName;

    if (schedule) {
        document.getElementById('scheduleId').value = schedule.id || '';
        document.getElementById('scheduleStartDate').value = schedule.start_date || '';
        document.getElementById('scheduleEndDate').value = schedule.end_date || '';
        document.getElementById('scheduleAmIn').value = schedule.am_in || '';
        document.getElementById('scheduleAmOut').value = schedule.am_out || '';
        document.getElementById('schedulePmIn').value = schedule.pm_in || '';
        document.getElementById('schedulePmOut').value = schedule.pm_out || '';
    } else {
        document.getElementById('scheduleId').value = '';
        document.getElementById('scheduleStartDate').value = '';
        document.getElementById('scheduleEndDate').value = '';
        document.getElementById('scheduleAmIn').value = '';
        document.getElementById('scheduleAmOut').value = '';
        document.getElementById('schedulePmIn').value = '';
        document.getElementById('schedulePmOut').value = '';
    }

    document.getElementById('assignScheduleModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function confirmRemoveSchedule(employeeId, employeeName) {
    if (confirm(`Are you sure you want to remove the schedule for ${employeeName}?`)) {
        // Submit form to remove schedule
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/schedules/${employeeId}/remove`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
