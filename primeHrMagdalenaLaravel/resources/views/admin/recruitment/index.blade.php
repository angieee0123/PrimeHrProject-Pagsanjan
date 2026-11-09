@extends('layouts.app')

{{--
    Recruitment overview.

    Every figure on this page is computed from the same recruitment tables the
    ranking and approval pages read, so the cards cannot disagree with them.
    Nothing is cached: HR acts on these numbers, and a stale count is a count
    somebody makes a decision about after it moved.
--}}

@section('content')
@include('admin.topbar.recruitmentTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell">

    @include('admin.recruitment.partials.flash')
    @include('admin.recruitment.partials.tabs')

    @if($failure)
        @include('admin.recruitment.partials.unavailable', [
            'failure' => $failure,
            'title' => 'The recruitment service is not available right now',
        ])
    @else

        @php
            $stats = $dashboard['stats'] ?? [];
            $openCount = (int) ($stats['openVacancies'] ?? 0);
            $applicantCount = (int) ($stats['totalApplicants'] ?? 0);
            $screenedCount = (int) ($stats['screened'] ?? 0);
            $averageFit = $stats['avgFit'] ?? null;
            $pendingApprovals = count($approvals);
        @endphp

        <div class="stats-grid stats-grid-4">
            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Open Vacancies</p>
                    <div class="stat-icon-wrap rc-icon-success">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $openCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot rc-dot-success"></span>
                    <p class="stat-sub">Publicly listed right now</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Applicants</p>
                    <div class="stat-icon-wrap rc-icon-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $applicantCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot rc-dot-primary"></span>
                    <p class="stat-sub">Across every position</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Screened</p>
                    <div class="stat-icon-wrap rc-icon-warning">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $screenedCount }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot rc-dot-warning"></span>
                    <p class="stat-sub">
                        @if(is_numeric($averageFit))
                            Average fit {{ number_format((float) $averageFit, 0) }}/100
                        @else
                            Fit scores available
                        @endif
                    </p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <p class="stat-label">Awaiting Decision</p>
                    <div class="stat-icon-wrap rc-icon-danger">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 8v4l3 2"/><circle cx="12" cy="12" r="10"/></svg>
                    </div>
                </div>
                <h2 class="stat-value">{{ $pendingApprovals }}</h2>
                <div class="stat-footer">
                    <span class="stat-dot rc-dot-danger"></span>
                    <p class="stat-sub">
                        <a href="{{ route('admin.recruitment.approvals', ['review_status' => 'PENDING']) }}">Portal applications to review</a>
                    </p>
                </div>
            </div>
        </div>

        <div class="hire-split">

            {{-- Open positions, as cards: this is the list HR scans first. --}}
            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Open positions</h3>
                        <p class="table-sub">Accepting applications today</p>
                    </div>
                    <div class="table-actions">
                        <a href="{{ route('admin.recruitment.vacancies', ['status' => 'OPEN']) }}" class="btn-export">View all</a>
                    </div>
                </div>

                @if($vacancyFailure)
                    <div class="hire-degraded">
                        <strong>Open positions could not be loaded.</strong>
                        {{ $vacancyFailure->message }}
                    </div>
                @endif

                <div class="hire-section-body">
                    @if(empty($vacancies))
                        <div class="empty-state">
                            <p class="empty-title">No open positions</p>
                            <p class="empty-sub">A published vacancy appears here until its closing date.</p>
                        </div>
                    @else
                        <div class="job-grid">
                            @foreach($vacancies as $vacancy)
                                @php
                                    $daysLeft = \App\Services\HiringPresenter::daysUntil($vacancy['closing_date'] ?? null);
                                    $closing = \App\Services\HiringPresenter::date($vacancy['closing_date'] ?? null);
                                @endphp
                                <a class="job-card" href="{{ route('admin.recruitment.show', $vacancy['id']) }}">
                                    <div class="job-card-header">
                                        <x-hiring-status :value="$vacancy['status'] ?? 'OPEN'" />
                                    </div>

                                    <div class="job-card-body">
                                        <div class="job-slot-badge">{{ (int) ($vacancy['slots'] ?? 1) }}</div>
                                        <div class="job-card-info">
                                            <p class="job-id">{{ ($vacancy['plantilla_item_no'] ?? null) ?: 'No plantilla item' }}</p>
                                            <p class="job-title">{{ $vacancy['position_title'] }}</p>
                                        </div>
                                    </div>

                                    <p class="job-dept">{{ $vacancy['place_of_assignment'] }}</p>

                                    <div class="job-badges">
                                        <span class="hire-chip"><b>{{ (int) ($vacancy['applicant_count'] ?? 0) }}</b> applicants</span>
                                        @if(!empty($vacancy['salary_grade']))
                                            <span class="hire-chip is-muted">SG {{ $vacancy['salary_grade'] }}</span>
                                        @endif
                                        @if(isset($vacancy['monthly_salary']))
                                            <span class="hire-chip is-muted">{{ \App\Services\HiringPresenter::peso($vacancy['monthly_salary']) }}</span>
                                        @endif
                                    </div>

                                    <div class="job-card-footer">
                                        <div>
                                            <p class="job-deadline-label">Closing date</p>
                                            <p class="job-deadline-date">{{ $closing ?? 'Not set' }}</p>
                                        </div>
                                        @if($daysLeft !== null)
                                            <span class="hire-chip {{ $daysLeft <= 3 ? '' : 'is-muted' }}">
                                                @if($daysLeft < 0)
                                                    Closing date passed
                                                @elseif($daysLeft === 0)
                                                    Closes today
                                                @elseif($daysLeft === 1)
                                                    Closes tomorrow
                                                @else
                                                    {{ $daysLeft }} days left
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- The actionable queue: a decision is the only thing here that
                 anybody is waiting on. --}}
            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Waiting for a decision</h3>
                        <p class="table-sub">Portal applications not yet accepted or disapproved</p>
                    </div>
                    <div class="table-actions">
                        <a href="{{ route('admin.recruitment.approvals') }}" class="btn-export">Open approvals</a>
                    </div>
                </div>

                @if($approvalFailure)
                    <div class="hire-degraded">
                        <strong>Approvals could not be loaded.</strong>
                        {{ $approvalFailure->message }}
                    </div>
                @endif

                @if(empty($approvals))
                    <div class="hire-section-body">
                        <div class="empty-state">
                            <p class="empty-title">Nothing waiting</p>
                            <p class="empty-sub">Applications from the applicant portal appear here until they are decided.</p>
                        </div>
                    </div>
                @else
                    <div class="hire-scroll">
                        <table class="payroll-table">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Position</th>
                                    <th class="hire-center">Fit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(array_slice($approvals, 0, 8) as $approval)
                                    <tr>
                                        <td>
                                            <div class="hire-title-cell">
                                                <strong>{{ $approval['name'] ?: $approval['anon_code'] }}</strong>
                                                <span>{{ $approval['anon_code'] }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="hire-title-cell">
                                                <strong>{{ $approval['position_title'] }}</strong>
                                                <span>{{ \App\Services\HiringPresenter::date($approval['created_at'] ?? null) ?? 'Filed' }}</span>
                                            </div>
                                        </td>
                                        <td class="hire-center">
                                            @include('admin.recruitment.partials.fit-score', ['score' => $approval['fit_score'] ?? null])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if(count($approvals) > 8)
                        <div class="table-footer">
                            <p>Showing <strong>8</strong> of <strong>{{ count($approvals) }}</strong> waiting</p>
                            <a href="{{ route('admin.recruitment.approvals', ['review_status' => 'PENDING']) }}" class="btn-export">View all {{ count($approvals) }}</a>
                        </div>
                    @endif
                @endif
            </section>
        </div>

        <div class="hire-split">

            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Applicants by position</h3>
                        <p class="table-sub">Every position in the system, filled or not</p>
                    </div>
                </div>

                @php $byPosition = $dashboard['byPosition'] ?? []; @endphp

                @if(empty($byPosition))
                    <div class="hire-section-body">
                        <div class="empty-state">
                            <p class="empty-title">No positions yet</p>
                            <p class="empty-sub">Create a vacancy to start collecting applications.</p>
                        </div>
                    </div>
                @else
                    <div class="hire-scroll">
                        <table class="payroll-table">
                            <thead>
                                <tr>
                                    <th>Position</th>
                                    <th class="hire-center">Applicants</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byPosition as $position)
                                    <tr>
                                        <td>{{ $position['name'] ?? '—' }}</td>
                                        <td class="hire-center hire-num">{{ (int) ($position['applicants'] ?? 0) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>

            <section class="table-section">
                <div class="table-header">
                    <div>
                        <h3 class="table-title">Fit score distribution</h3>
                        <p class="table-sub">How the screened pool is spread</p>
                    </div>
                </div>

                @php
                    $distribution = $dashboard['fitDistribution'] ?? [];
                    $distributionMax = max(1, collect($distribution)->max('count') ?: 1);
                @endphp

                <div class="hire-section-body">
                    @if(empty($distribution))
                        <p class="hire-hint">No screening results yet.</p>
                    @else
                        <div class="hire-bars">
                            @foreach($distribution as $band)
                                @php
                                    $label = $band['band'] ?? '—';
                                    $count = (int) ($band['count'] ?? 0);
                                    $fill = str_contains($label, 'Strong')
                                        ? ''
                                        : (str_contains($label, 'Moderate') ? 'is-warning' : 'is-danger');
                                @endphp
                                <div class="hire-bar-row">
                                    <span>{{ $label }}</span>
                                    <span class="hire-bar-track">
                                        <span class="hire-bar-fill {{ $fill }}" style="width: {{ round($count / $distributionMax * 100) }}%"></span>
                                    </span>
                                    <span class="hire-bar-value">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <section class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Recent activity</h3>
                    <p class="table-sub">
                        Every recruitment action, recorded against the officer who took it. The
                        full trail is on the activity page.
                    </p>
                </div>
                @if(auth()->user()?->hasRole('admin'))
                    <div class="table-actions">
                        <a href="{{ route('admin.recruitment.audit') }}" class="btn-export">Full activity log</a>
                    </div>
                @endif
            </div>

            @php $activity = $dashboard['recentActivity'] ?? []; @endphp

            @if(empty($activity))
                <div class="hire-section-body">
                    <p class="hire-hint">Nothing recorded yet.</p>
                </div>
            @else
                <ul class="hire-activity">
                    @foreach($activity as $entry)
                        <li>
                            <span class="hire-activity-dot" aria-hidden="true"></span>
                            <div class="hire-activity-body">
                                <strong>{{ $entry['user_name'] ?? 'System' }}</strong>
                                {{ str_replace('_', ' ', strtolower($entry['action'] ?? 'did something')) }}
                                @if(!empty($entry['entity']))
                                    on <strong>{{ $entry['entity'] }}</strong>
                                @endif
                                <span class="hire-activity-time">{{ \App\Services\HiringPresenter::dateTime($entry['at'] ?? null) }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif
</div>
@endsection
