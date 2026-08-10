@extends('layouts.employee')

@section('title', 'Performance · PRIME HRIS')

@section('content')

{{--
    Performance is switched off, the same as the admin-side Performance
    Management and Recruitment pages.

    Every figure on this page was a literal in the markup: a 4.8 "Latest
    Rating", a 4.7 average, four EVAL-20xx-xx rows with named evaluators and
    written feedback, four goals with progress bars, and a trend chart whose
    bar heights were CSS classes (h-90, h-92, h-94, h-96). There was not one
    `$employee->` reference in the file — nothing was read from the database,
    because there is no performance table in this schema to read.

    That made it the most dangerous kind of mock: an employee reads their own
    rating off it. The sample data is removed rather than hidden, so it is not
    sent to the browser at all.
--}}

<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Performance'
    ])

    {{-- Mobile Overlay --}}
    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content permanent-dashboard glass-shell shell-notice">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.performanceTopbar')

        <x-module-unavailable
            module="Performance"
            reason="Evaluations and goals are not stored anywhere in the system yet, so there is no rating of yours to show."
            :back="route('employee.dashboard')" />
    </main>

</div>

@include('employee.chatbot.employeeChatbot')

@endsection
