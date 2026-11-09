@extends('layouts.app')

{{--
    The recruitment audit trail.

    Read-only, and administrators only — it is oversight of who did what rather
    than screening work.

    Every action is recorded against the officer who took it, with a snapshot of
    their name, so this page is the complete trail rather than one half of it.
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
            'title' => 'The activity log could not be loaded',
        ])
    @else

        <form method="GET" action="{{ route('admin.recruitment.audit') }}" class="filter-card">
            <div class="filter-card-fields">
                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    <select name="action" class="fc-select" aria-label="Filter by action">
                        <option value="">All actions</option>
                        @foreach($actions as $code)
                            <option value="{{ $code }}" @selected($action === $code)>
                                {{ str_replace('_', ' ', $code) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="fld">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <select name="entity" class="fc-select" aria-label="Filter by record type">
                        <option value="">All records</option>
                        @foreach($entities as $code)
                            <option value="{{ $code }}" @selected($entity === $code)>
                                {{ \Illuminate\Support\Str::headline($code) }}
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
                @if($action !== '' || $entity !== '')
                    <a href="{{ route('admin.recruitment.audit') }}" class="btn-ghost">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <div class="table-section">
            <div class="table-header">
                <div>
                    <h3 class="table-title">Activity log</h3>
                    <p class="table-sub">
                        <strong>{{ count($entries) }}</strong>
                        @if($action !== '' || $entity !== '')
                            of <strong>{{ $total }}</strong> recorded actions matching this view
                        @else
                            {{ \Illuminate\Support\Str::plural('action', count($entries)) }} recorded
                        @endif
                        &nbsp;·&nbsp; the newest {{ $total > 500 ? '500' : $total }} are shown here
                    </p>
                </div>
            </div>

            <div class="table-wrapper hire-scroll">
                <table class="payroll-table">
                    <thead>
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Who</th>
                            <th scope="col">Action</th>
                            <th scope="col">Record</th>
                            <th scope="col">Record id</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                            <tr>
                                <td class="hire-nowrap">{{ \App\Services\HiringPresenter::dateTime($entry['at'] ?? null) ?? '—' }}</td>
                                <td>{{ $entry['user_name'] ?? 'System' }}</td>
                                <td>
                                    <span class="hire-chip">{{ str_replace('_', ' ', $entry['action'] ?? '—') }}</span>
                                </td>
                                <td>{{ $entry['entity'] ? \Illuminate\Support\Str::headline($entry['entity']) : '—' }}</td>
                                <td class="hire-muted">{{ $entry['entity_id'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <p class="empty-title">Nothing recorded</p>
                                        <p class="empty-sub">Actions taken on vacancies and applications appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="table-footer">
                <p>Showing <strong>{{ count($entries) }}</strong> of <strong>{{ $total }}</strong> recorded actions</p>
            </div>
        </div>

    @endif
</div>
@endsection
