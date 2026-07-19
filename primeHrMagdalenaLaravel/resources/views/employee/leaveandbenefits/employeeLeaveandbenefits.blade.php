@extends('layouts.employee')

@section('title', 'Leave & Benefits · PRIME HRIS')

@push('styles')
    @vite('resources/css/employee/employeeLeaveAndBenefits.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Leave & Benefits'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    {{-- Main Content --}}
    <main class="main-content permanent-dashboard permanent-leavebenefits glass-shell">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.leaveandbenefitsTopbar')

        @include('employee.leaveandbenefits.partials.stats-grid')

        @include('employee.leaveandbenefits.partials.tab-nav')

        {{-- Tab Content --}}
        <div id="tab-leave" class="tab-content">
            @include('employee.leaveandbenefits.tabs.leave-requests.leaveRequestsTab')
        </div>

        <div id="tab-credits" class="tab-content hidden">
            @include('employee.leaveandbenefits.tabs.leave-credits.leaveCreditsTab')
        </div>

        <div id="tab-transactions" class="tab-content hidden">
            @include('employee.leaveandbenefits.tabs.transaction-history.transactionHistoryTab')
        </div>

        <div id="tab-benefits" class="tab-content hidden">
            @include('employee.leaveandbenefits.tabs.benefits.benefitsTab')
        </div>

    </main>

</div>

@include('employee.leaveandbenefits.modals.leaveDetailModal')
@include('employee.leaveandbenefits.modals.fileLeaveModal')

@push('scripts')
    @vite('resources/js/employee/employeeLeaveAndBenefits.js')
@endpush

@include('employee.chatbot.employeeChatbot')

@endsection
