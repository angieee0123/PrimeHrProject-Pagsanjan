@extends('layouts.employee')

@section('title', 'Dashboard · PRIME HRIS')

@push('styles')
    @vite('resources/css/employeeDashboard.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Dashboard'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content permanent-dashboard glass-shell">

        @include('employee.notification.employeeNotification')
        @include('employee.topbar.employeeTopbar')

        <div class="perm-dash">

            {{-- Greeting · the one thing most people open this page for --}}
            @php
                $hour = now()->timezone('Asia/Manila')->hour;
                $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

                // Most-actionable fact first: something waiting on you, then something
                // to correct, then the neutral standing balance.
                $headlines = [];

                if (($isPermanent ?? false) && $pendingLeaveCount > 0) {
                    $headlines[] = [
                        'tone' => 'warn',
                        'text' => 'You have <strong>' . $pendingLeaveCount . ' leave request' . ($pendingLeaveCount != 1 ? 's' : '') . '</strong> awaiting approval.',
                    ];
                }

                if ($lateDays > 0) {
                    $headlines[] = [
                        'tone' => 'warn',
                        'text' => 'You were late <strong>' . $lateDays . ' day' . ($lateDays != 1 ? 's' : '') . '</strong> this month.',
                    ];
                }

                if (($isPermanent ?? false)) {
                    $credits = $leaveBalances->sum('available_credits');
                    $headlines[] = [
                        'tone' => 'calm',
                        'text' => 'You have <strong>' . number_format($credits, 1) . ' leave credit' . ($credits != 1 ? 's' : '') . '</strong> available.',
                    ];
                }

                if (empty($headlines)) {
                    $headlines[] = [
                        'tone' => 'calm',
                        'text' => 'Nothing needs your attention right now.',
                    ];
                }

                $headlines = array_slice($headlines, 0, 2);
            @endphp

            <header class="perm-greeting">
                <h1 class="perm-greeting-title">{{ $greeting }}, {{ $employee->first_name }}.</h1>
                <p class="perm-greeting-sub">
                    @foreach($headlines as $h)
                        <span class="perm-greeting-fact perm-greeting-{{ $h['tone'] }}">{!! $h['text'] !!}</span>
                    @endforeach
                </p>
            </header>

            {{-- Greeting, stats and quick actions sit above the tabs: they're the
                 at-a-glance layer and shouldn't cost a click to see. --}}
            @include('employee.dashboard.partials.stats')

            @include('employee.dashboard.partials.tabNav')

            <div class="perm-tab-panel active"
                 id="tab-panel-overview"
                 role="tabpanel"
                 aria-labelledby="tab-btn-overview"
                 data-panel="overview">
                @include('employee.dashboard.partials.overviewTab')
            </div>

            <div class="perm-tab-panel"
                 id="tab-panel-payroll"
                 role="tabpanel"
                 aria-labelledby="tab-btn-payroll"
                 data-panel="payroll">
                @include('employee.dashboard.partials.payrollTab')
            </div>

        </div>
    </main>

</div>

@include('employee.dashboard.partials.deductionModal')

@include('employee.chatbot.employeeChatbot')
@endsection

@push('scripts')
{{-- Server data for employeeDashboard.js. Plain script, so it runs before the
     deferred module that reads it. --}}
<script>
    window.employeeDashboardData = {
        deductions: @json($deductions),
        attendance: @json($chartData['attendance']),
        salary:     @json($chartData['salary']),
    };
</script>
@vite('resources/js/employeeDashboard.js')
@endpush
