{{--
    A generic "this page could not be loaded" panel.

    The Recruitment module used this for a remote service that did not answer.
    It now reads this application's own database, so the only failure a page can
    render is an ordinary one (a record that vanished between the click and the
    read, a database error). The partial is kept because the pages still guard
    on `$failure`, and a page must never draw zeroes when it means "unknown".

    Included with: ['failure' => $failure, 'title' => '…']
--}}
@php
    $failure = $failure ?? null;
    $title = $title ?? 'This page could not be loaded';
@endphp

<div class="hire-unavailable" role="status">
    <span class="hire-unavailable-icon" aria-hidden="true">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
            <line x1="12" y1="9" x2="12" y2="13"/>
            <line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
    </span>

    <div>
        <p class="hire-unavailable-title">{{ $title }}</p>
        <p class="hire-unavailable-text">
            {{ $failure?->message ?? 'Nothing could be read for this page, so it is shown empty rather than as zeroes.' }}
        </p>

        <a href="{{ request()->fullUrl() }}" class="btn-export">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="1 4 1 10 7 10"/>
                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/>
            </svg>
            Try again
        </a>
    </div>
</div>
