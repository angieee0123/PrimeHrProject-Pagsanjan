@extends('layouts.app')

@push('styles')
    @vite('resources/css/adminPersonnel.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/personnel/adminPersonnel.js')
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

@if(session('active_tab') === 'schedules')
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

    const schedulesTabBtn = document.querySelector('.tab-btn[data-tab="schedules"]');
    const schedulesTabContent = document.getElementById('schedules');

    if (schedulesTabBtn && schedulesTabContent) {
        schedulesTabBtn.classList.add('active');
        schedulesTabContent.classList.add('active');
    }
});
</script>
@endif
@endpush
@endsection
