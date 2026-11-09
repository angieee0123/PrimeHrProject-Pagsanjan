@extends('layouts.app')

{{--
    Archived positions: lapsed, closed and cancelled.

    Deliberately not one undifferentiated list. A position nobody decided on
    (lapsed), one that was filled (closed) and one that was withdrawn (cancelled)
    call for different actions, so the badge is the backend's own
    `archive_reason` discriminator rather than something re-derived here from
    nullable timestamps.

    Drafts and holds are absent on purpose: a draft was never public and a hold
    is expected back, so neither is archival.
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
            'title' => 'The archive could not be loaded',
        ])
    @else

        <form method="GET" action="{{ route('admin.recruitment.archive') }}" class="filter-card">
            <div class="filter-card-fields">
                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    <select name="reason" class="fc-select" aria-label="Filter by reason">
                        <option value="">Everything archived ({{ $total }})</option>
                        @foreach(['LAPSED' => 'Lapsed', 'CLOSED' => 'Closed', 'CANCELLED' => 'Cancelled'] as $code => $label)
                            <option value="{{ $code }}" @selected($reason === $code)>
                                {{ $label }} ({{ $counts[$code] ?? 0 }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="filter-card-actions">
                <button type="submit" class="btn-solid">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Apply
                </button>
                @if($reason)
                    <a href="{{ route('admin.recruitment.archive') }}" class="btn-ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Archived positions</h3>
                    <p class="table-sub">
                        <strong>{{ count($vacancies) }}</strong>
                        @if($reason)
                            of <strong>{{ $total }}</strong> archived positions
                        @else
                            {{ \Illuminate\Support\Str::plural('position', count($vacancies)) }} no longer in service
                        @endif
                    </p>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col">Position</th>
                            <th scope="col">Why archived</th>
                            <th scope="col">Ended</th>
                            <th scope="col">Detail</th>
                            <th scope="col" class="hire-center">Applicants</th>
                            <th scope="col" class="hire-center">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vacancies as $vacancy)
                            @php
                                $endedAt = $vacancy['cancelled_at'] ?? ($vacancy['closed_at'] ?? ($vacancy['lapsed_at'] ?? null));
                                $detail = $vacancy['cancel_reason_label']
                                    ?? ($vacancy['cancel_reason'] ?? null)
                                    ?? ($vacancy['close_reason'] ?? null);
                            @endphp
                            <tr>
                                <td>
                                    <div class="hire-title-cell">
                                        <strong>{{ $vacancy['position_title'] }}</strong>
                                        <span>{{ $vacancy['place_of_assignment'] }}</span>
                                    </div>
                                </td>
                                <td><x-hiring-status :value="$vacancy['archive_reason'] ?? $vacancy['status'] ?? null" /></td>
                                <td class="hire-nowrap">{{ \App\Services\HiringPresenter::dateTime($endedAt) ?? '—' }}</td>
                                <td>
                                    @if($detail)
                                        {{ $detail }}
                                        @if(!empty($vacancy['cancel_reason_detail']))
                                            <div class="hire-hint">{{ $vacancy['cancel_reason_detail'] }}</div>
                                        @endif
                                    @else
                                        <span class="hire-muted">—</span>
                                    @endif
                                </td>
                                <td class="hire-center hire-num">{{ (int) ($vacancy['applicant_count'] ?? 0) }}</td>
                                <td class="hire-center">
                                    <a href="{{ route('admin.recruitment.show', $vacancy['id']) }}" class="btn-export">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <p class="empty-title">Nothing archived</p>
                                        <p class="empty-sub">Positions appear here when they lapse, are filled, or are withdrawn.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>Showing <strong>{{ count($vacancies) }}</strong> of <strong>{{ $total }}</strong> archived positions</p>
            </div>
        </div>

    @endif
</div>
@endsection
