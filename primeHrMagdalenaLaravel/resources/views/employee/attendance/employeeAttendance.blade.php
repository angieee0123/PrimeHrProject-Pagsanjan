@extends('layouts.employee')

@section('title', 'Attendance · PRIME HRIS')

@push('styles')
    @vite('resources/css/employee/employeeAttendance.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Attendance'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-attendance glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.attendanceTopbar')

        {{-- ══════════ DETAILED TIME RECORD ══════════ --}}
        <div class="ddtr-page">

            @include('employee.attendance.partials.detailed-header')

            {{-- ── BODY ── --}}
            <div class="ddtr-body">

                @include('employee.attendance.partials.kpi-cards')

                @include('employee.attendance.partials.filter-toolbar')

                @include('employee.attendance.partials.dtr-table')

            </div>
        </div>

    </main>

</div>
@push('scripts')
<script>
    window.attendancePageData = {
        defaultStart: @json(now()->startOfMonth()->format('Y-m-d')),
        defaultEnd: @json(now()->endOfMonth()->format('Y-m-d')),
        detailedRoute: @json(route('employee.attendance.detailed')),
        employeeId: @json($employee->employee_id),
    };
</script>
    @vite('resources/js/employee/employeeAttendance.js')
@endpush

@include('employee.chatbot.employeeChatbot')

@endsection
