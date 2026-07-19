@extends('layouts.app')



@section('content')
@php
$avatarColors   = ['#0b044d','#8e1e18','#150c63','#a52820','#150c63','#0b044d','#3b1a6e','#6b0f0b'];
$totalPersonnel = $departments->sum('personnel_count');
$activeDepts    = $departments->where('status','Active')->count();
$largestDept    = $departments->sortByDesc('personnel_count')->first();
@endphp

@include('admin.topbar.departmentsTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

@include('admin.departments.partials.stats-grid')

@include('admin.departments.partials.tab-nav')

@include('admin.departments.partials.filter-toolbar')

@include('admin.departments.partials.departments-tab')

@include('admin.departments.partials.designations-tab')

</div>

{{-- Modals --}}
@include('admin.departments.modals.addDepartment')
@include('admin.departments.modals.addDesignation')
@include('admin.departments.modals.bulkImportDepartment')
@include('admin.departments.modals.bulkImportDesignation')
@include('admin.departments.modals.viewDepartment')
@include('admin.departments.modals.feedbackModals')

@push('scripts')
<script>
    window.departmentsData = @json($departments->values());
    window.designationsData = @json($designations->values());
    window.avatarColors = @json($avatarColors);
    window.exportRoutes = {
        departments: '{{ route('admin.departments.export') }}',
        designations: '{{ route('admin.designations.export') }}'
    };
    window.deptFlash = {
        success: @json((bool) session('success')),
        errorFirst: @json($errors->any() ? $errors->first() : null),
        exportError: @json(session('export_error')),
        importImported: @json(session('import_imported')),
        importSkipped: @json(session('import_skipped', [])),
        importType: @json(session('import_type', 'record'))
    };
</script>
    @vite('resources/js/admin/departments/adminDepartments.js')
@endpush
@endsection
