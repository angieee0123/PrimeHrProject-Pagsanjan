@extends('layouts.app')

@push('styles')
    @vite('resources/css/admin/adminReports.css')
@endpush

@section('content')
@include('admin.topbar.reportsTopbar')
@include('admin.notification.adminNotification')

@php
    $reportTypes = [
        'payroll'     => ['label' => 'Payroll Summary',       'icon' => 'creditCard'],
        'department'  => ['label' => 'Department Breakdown',  'icon' => 'building'],
        'deductions'  => ['label' => 'Deductions Report',     'icon' => 'trendingUp'],
        'headcount'   => ['label' => 'Headcount Report',      'icon' => 'users'],
        'recruitment' => ['label' => 'Recruitment Report',    'icon' => 'clipboard'],
        'training'    => ['label' => 'Training Report',       'icon' => 'bookOpen'],
        'performance' => ['label' => 'Performance Report',    'icon' => 'star'],
    ];
    $months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
@endphp

<div class="glass-shell">
    {{-- Report type tabs --}}
    <div class="report-tabs">
        @foreach($reportTypes as $id => $rt)
        <button class="report-tab-btn {{ $activeTab === $id ? 'active' : '' }}" data-report="{{ $id }}" onclick="switchReport('{{ $id }}')">
            <x-report-icon :name="$rt['icon']" />
            {{ $rt['label'] }}
        </button>
        @endforeach
    </div>

    {{-- Filters. Submitted as a GET form so the selected report and period both
         live in the URL — that keeps Export/Print and browser refresh honest. --}}
    <section class="table-section report-filter-bar">
        <form method="GET" action="{{ route('admin.reports') }}" class="table-header" id="reportFilterForm">
            <input type="hidden" name="tab" id="reportTabInput" value="{{ $activeTab }}">
            <div>
                <h3 class="table-title" id="report-title">{{ $reports[$activeTab]['title'] }} — {{ $periodLabel }}</h3>
                <p class="table-sub">Municipal Government of Pagsanjan · Fiscal Year {{ $year }}</p>
            </div>
            <div class="table-actions">
                <select class="filter-select" name="semi" onchange="this.form.submit()" title="Pay period half">
                    <option value="1" @selected($semi === 1)>1st (1–15)</option>
                    <option value="2" @selected($semi === 2)>2nd (16–end)</option>
                </select>
                <select class="filter-select" name="month" onchange="this.form.submit()">
                    @foreach($months as $i => $m)
                        <option value="{{ $i + 1 }}" @selected($month === $i + 1)>{{ $m }}</option>
                    @endforeach
                </select>
                <select class="filter-select" name="year" onchange="this.form.submit()">
                    @foreach($years as $y)
                        <option value="{{ $y }}" @selected($year === $y)>{{ $y }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn-export" onclick="window.print()">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Export / Print
                </button>
            </div>
        </form>
        <p class="report-filter-note">
            The period filter drives Payroll, Department, and Deductions. Headcount is a live snapshot;
            Training is filtered by year.
        </p>
    </section>

    @foreach($reports as $id => $report)
    <div class="report-content" id="{{ $id }}-report" style="{{ $activeTab === $id ? '' : 'display:none' }}">

        {{-- Per-report summary stats, so the figures always match the open tab --}}
        @if(count($report['stats']))
        <section class="stats-grid report-stats">
            @foreach($report['stats'] as $s)
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">{{ $s['label'] }}</p>
                    <div class="stat-icon-wrap" style="background: {{ $s['accent'] }}18;">
                        <x-report-icon :name="$s['icon']" :stroke="$s['accent']" />
                    </div>
                </div>
                <h2 class="stat-value report-stat-value">{{ $s['value'] }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot" style="background: {{ $s['accent'] }};"></span>
                    <p class="stat-sub">{{ $s['sub'] }}</p>
                </div>
            </div>
            @endforeach
        </section>
        @endif

        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">{{ $report['title'] }}</h3>
                    <p class="table-sub">{{ $report['subtitle'] }}</p>
                </div>
            </div>

            @if(!empty($report['unavailable']))
                <div class="report-empty">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <p class="report-empty-title">Not available</p>
                    <p class="report-empty-text">{{ $report['unavailable'] }}</p>
                </div>
            @elseif($report['rows']->isEmpty())
                <div class="report-empty">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <p class="report-empty-title">Nothing to show</p>
                    <p class="report-empty-text">{{ $report['empty'] }}</p>
                </div>
            @else
                @if(!empty($report['note']))
                <p class="report-note">{{ $report['note'] }}</p>
                @endif

                <div class="table-wrapper">
                    <table class="payroll-table">
                        @include('admin.reports.partials.' . $id . '-table', ['report' => $report])
                    </table>
                </div>
            @endif
        </section>
    </div>
    @endforeach
</div>

<script>
    function switchReport(reportId) {
        document.querySelectorAll('.report-tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-report="${reportId}"]`).classList.add('active');

        document.querySelectorAll('.report-content').forEach(section => { section.style.display = 'none'; });
        document.getElementById(`${reportId}-report`).style.display = 'block';

        // Keep the header and the filter form pointed at the open report, so a
        // period change reloads onto the same tab.
        const active = window.reportTitles[reportId];
        if (active) document.getElementById('report-title').textContent = active;
        document.getElementById('reportTabInput').value = reportId;

        const url = new URL(window.location);
        url.searchParams.set('tab', reportId);
        window.history.replaceState({}, '', url);
    }

    window.reportTitles = @json(collect($reports)->map(fn ($r) => $r['title'] . ' — ' . $periodLabel));
</script>
@endsection
