@extends('layouts.employee')

@section('title', 'Payslip · PRIME HRIS')

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Payslip'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-payslip glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.payslipTopbar')

        @include('employee.payslip.partials.stats-grid')

        @include('employee.payslip.partials.payslip-history-table')

    </main>

</div>

@include('employee.payslip.modals.payslipModal')

@include('employee.chatbot.employeeChatbot')

@include('employee.payslip.modals.payslipDetailModal')

@push('scripts')
    @vite('resources/js/employeePayslip.js')
@endpush

@endsection
