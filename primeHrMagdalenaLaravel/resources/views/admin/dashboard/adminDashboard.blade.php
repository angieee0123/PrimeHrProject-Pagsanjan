@extends('layouts.app')

@section('content')

@php
    $adminName = optional(Auth::user())->name ?? 'Admin';
    $adminInitials = collect(explode(' ', trim($adminName)))->filter()->map(fn($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') ?: 'AD';
@endphp

<main class="enterprise-hr-dashboard glass-shell">

@include('admin.topbar.adminTopbar')
@include('admin.notification.adminNotification')

@include('admin.dashboard.partials.stats-grid')

@include('admin.dashboard.partials.quick-actions-bar')

{{-- Main reference layout: wide chart + right requests --}}
<div class="enterprise-overview-grid">
    @include('admin.dashboard.partials.main-chart-card')
    @include('admin.dashboard.partials.pending-requests-card')
</div>

<div class="enterprise-secondary-grid">
    @include('admin.dashboard.partials.top-performers-card')
    @include('admin.dashboard.partials.attendance-trend-chart-card')
    @include('admin.dashboard.partials.top-earners-card')
</div>

<div class="enterprise-compact-grid">
    @include('admin.dashboard.partials.early-birds-card')
    @include('admin.dashboard.partials.recent-leave-filers-card')
    @include('admin.dashboard.partials.department-distribution-card')
</div>

@include('admin.dashboard.partials.employee-directory')

</main>

@include('admin.dashboard.modals.performerDetailsModal')
@include('admin.dashboard.modals.viewEmployeeDashboardModal')
@include('admin.dashboard.modals.addEmployeeModal')

@include('admin.dashboard.partials.bird-schedule-tooltip')

@push('scripts')
<script>
    window.dashboardChartData = @json($chartData);
    window.perfPeriods = { month: @json($perfPeriodMonth), week: @json($perfPeriodWeek) };
</script>
    @vite('resources/js/admin/dashboard/adminDashboard.js')
@endpush


@endsection
