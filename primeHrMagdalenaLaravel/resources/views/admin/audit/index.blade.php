@extends('layouts.app')

@push('styles')
    @vite('resources/css/admin/adminAudit.css')
@endpush

{{--
    Audit Trail.

    The design rule for this page is that **a row is one line tall, always**.
    An audit row can carry twenty changed fields, a 150-character user agent
    and a full URL; rendering all of that inline is what made the table
    unreadable the moment there was real history in it — row heights varied
    wildly, the eye had nothing to track down the page, and the technical
    columns (URL / IP / agent) took up the width that the human-readable ones
    needed.

    So the table shows the five things that answer "who changed what, when",
    each in a fixed-width column, and every row opens a drawer holding the
    complete record: the full before/after diff, the URL, the IP, the agent
    string and the exact timestamp. Nothing was dropped — it moved one click
    away, which is the only way the list stays scannable at a thousand rows.
--}}

@section('content')
@include('admin.topbar.auditTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell audit-page">

    {{-- Filters. A GET form, so the filtered view is a shareable URL and the
         browser's back button works through it. --}}
    <form method="GET" action="{{ route('admin.audit') }}" id="auditFilterForm" class="filter-card">
        <input type="hidden" name="q" id="auditSearchField" value="{{ $filters['q'] }}">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir" value="{{ $dir }}">
        <input type="hidden" name="per_page" value="{{ $perPage }}" id="auditPerPageField">

        <div class="filter-card-fields">
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                <select class="fc-select" name="event" aria-label="Filter by action">
                    <option value="">All actions</option>
                    @foreach(\App\Services\AuditTrailPresenter::EVENTS as $key => $meta)
                        <option value="{{ $key }}" @selected($filters['event'] === $key)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <select class="fc-select" name="type" aria-label="Filter by record type">
                    <option value="">All records</option>
                    @foreach($recordTypes as $class => $label)
                        <option value="{{ $class }}" @selected($filters['type'] === $class)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <select class="fc-select" name="user" aria-label="Filter by user">
                    <option value="">All users</option>
                    @foreach($auditUsers as $auditUser)
                        <option value="{{ $auditUser->id }}" @selected($filters['user'] === (string) $auditUser->id)>{{ $auditUser->username }}</option>
                    @endforeach
                </select>
            </div>

            <div class="fc-divider"></div>

            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" name="from" value="{{ $filters['from'] }}" aria-label="From date">
            </div>
            <span class="fc-sep">to</span>
            <div class="fld">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <input type="date" class="fc-input" name="to" value="{{ $filters['to'] }}" aria-label="To date">
            </div>
        </div>

        <div class="filter-card-actions">
            <button type="submit" class="btn-solid">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Apply
            </button>
            @if($activeChips)
                <a href="{{ route('admin.audit') }}" class="btn-ghost">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>
                    Reset
                </a>
            @endif
        </div>
    </form>

    {{-- What is currently narrowing the list. Only rendered when something is
         — an always-present strip becomes furniture and stops being read. --}}
    @if($activeChips)
        <div class="au-chips" role="status">
            <span class="au-chips-label">Filtered by</span>
            @foreach($activeChips as $chip)
                <a href="{{ $chip['url'] }}" class="au-chip" title="Remove this filter">
                    {{ $chip['label'] }}
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </a>
            @endforeach
        </div>
    @endif

    <div class="table-section">
        <div class="table-header au-header">
            <div class="au-header-lead">
                <span class="au-header-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2"/><path d="M3.05 11a9 9 0 1 1 .5 4"/><polyline points="3 21 3 15 9 15"/></svg>
                </span>
                <div>
                    <p class="table-title">Activity log</p>
                    <p class="table-sub">
                        <strong>{{ number_format($audits->total()) }}</strong>
                        {{ \Illuminate\Support\Str::plural('record', $audits->total()) }}
                        @if($activeChips) matching your filters @else recorded @endif
                    </p>
                </div>
            </div>

            {{-- The same numbers the table is showing, split by action. Counted
                 over the filtered set, so these and the total above always agree. --}}
            <div class="au-tallies">
                @foreach(\App\Services\AuditTrailPresenter::EVENTS as $key => $meta)
                    @continue(($counts[$key] ?? 0) === 0)
                    <span class="au-tally au-tally-{{ $key }}">
                        <span class="au-tally-dot"></span>
                        <strong>{{ number_format($counts[$key]) }}</strong> {{ $meta['label'] }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="table-wrapper au-scroll">
            <table class="payroll-table au-table" id="auditsTable">
                {{-- Column widths ride on the <th>, not on a <colgroup>. A
                     `<col>` keeps reserving its width even when the cells in
                     that column are hidden at narrow widths, which left the
                     table ending a third of the way short of its own card. --}}
                <thead>
                    <tr>
                        <th class="au-c-when">@include('admin.audit.partials.sort-link', ['column' => 'created_at', 'label' => 'When'])</th>
                        <th class="au-c-action">@include('admin.audit.partials.sort-link', ['column' => 'event', 'label' => 'Action'])</th>
                        <th class="au-c-record">@include('admin.audit.partials.sort-link', ['column' => 'auditable_type', 'label' => 'Record'])</th>
                        <th class="au-c-user">@include('admin.audit.partials.sort-link', ['column' => 'user_id', 'label' => 'Performed by'])</th>
                        <th class="au-c-changes">Changes</th>
                        <th class="au-c-open au-th-end"><span class="au-sr">Details</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="au-row" tabindex="0" role="button"
                            data-audit="{{ $row['id'] }}"
                            aria-label="View audit entry {{ $row['id'] }}">
                            {{-- The exact timestamp is on the whole cell, so
                                 "Today" never costs anyone precision. --}}
                            <td class="au-when" title="{{ $row['full'] }}">
                                <span class="au-when-date">{{ $row['date'] }}</span>
                                <span class="au-when-time">{{ $row['time'] }}</span>
                            </td>
                            <td>
                                <span class="badge-status {{ $row['badge'] }}">{{ $row['event_label'] }}</span>
                            </td>
                            <td class="au-record">
                                <span class="au-record-type">{{ $row['record_type'] }}</span>
                                <span class="au-record-id">#{{ $row['record_id'] }}</span>
                            </td>
                            <td>
                                <div class="au-user">
                                    {{-- The photo is the employee record's own, so a
                                         face in the log matches the face on the
                                         personnel page. The initials tile stays in the
                                         markup underneath rather than being replaced:
                                         `employees.photo` is a URL that can outlive the
                                         file it points at, and `onerror` then drops back
                                         to initials instead of leaving a broken-image
                                         icon in every row that user touched. --}}
                                    <span class="au-avatar @if($row['user_photo']) has-photo @endif">
                                        @if($row['user_photo'])
                                            <img src="{{ $row['user_photo'] }}" alt="" class="au-avatar-img" loading="lazy"
                                                 onerror="this.parentElement.classList.remove('has-photo'); this.remove();">
                                        @endif
                                        <span class="au-avatar-initials">{{ $row['user_initials'] }}</span>
                                    </span>
                                    <span class="au-user-name">{{ $row['user_name'] }}</span>
                                </div>
                            </td>
                            <td>
                                {{-- The flex lives on an inner div, not on the <td>: a
                                     table cell set to `display: flex` stops being a table
                                     cell, and the fixed column widths go with it. --}}
                                <div class="au-changes">
                                    @if($row['change_count'] > 0)
                                        <span class="au-count">{{ $row['change_count'] }}</span>
                                    @endif
                                    <span class="au-summary" title="{{ $row['summary'] }}">{{ $row['summary'] }}</span>
                                </div>
                            </td>
                            <td class="au-th-end">
                                <span class="au-open" aria-hidden="true">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="au-empty-cell">
                                <div class="au-empty">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="15" x2="15" y2="15"/>
                                    </svg>
                                    @if($activeChips)
                                        <p class="au-empty-title">No entries match these filters</p>
                                        <p class="au-empty-sub">Try widening the date range or clearing a filter.</p>
                                        <a href="{{ route('admin.audit') }}" class="btn-ghost">Clear all filters</a>
                                    @else
                                        <p class="au-empty-title">Nothing has been recorded yet</p>
                                        <p class="au-empty-sub">Changes made to employee, leave, payroll and department records will appear here.</p>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="table-footer au-footer">
            <div class="au-footer-left">
                <p>
                    Showing <strong>{{ number_format($audits->firstItem() ?? 0) }}</strong>–<strong>{{ number_format($audits->lastItem() ?? 0) }}</strong>
                    of <strong>{{ number_format($audits->total()) }}</strong>
                </p>
                <select id="auditPerPage" class="au-perpage" aria-label="Rows per page">
                    @foreach($perPageSizes as $size)
                        <option value="{{ $size }}" @selected($perPage === $size)>{{ $size }} per page</option>
                    @endforeach
                </select>
            </div>

            @if($audits->hasPages())
                @include('admin.audit.partials.pagination', ['paginator' => $audits])
            @endif
        </div>
    </div>

</div>

@include('admin.audit.partials.detail-modal')

{{-- The rows the drawer reads, keyed by audit id. `@json` encodes with
     JSON_HEX_TAG, so a `</script>` sitting in a logged URL or user agent
     cannot close this tag. --}}
<script type="application/json" id="auditData">@json($rows->keyBy('id'))</script>

@endsection

@push('scripts')
    @vite('resources/js/admin/audit/adminAudit.js')
@endpush
