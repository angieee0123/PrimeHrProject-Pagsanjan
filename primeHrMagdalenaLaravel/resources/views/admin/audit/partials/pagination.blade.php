{{--
    Pagination for a log that can run to hundreds of pages.

    A bare ±2 window (what this page had) gives no way to reach page 300 short
    of clicking Next three hundred times, and no sense of how much history
    there is. First and last are always reachable, with an ellipsis marking
    the gap, so the control stays the same width whether there are 2 pages or
    2,000 — a pager that grows with the data is the other way a table stops
    being restful to look at.

    Expects: $paginator.
--}}
@php
    $current = $paginator->currentPage();
    $last    = $paginator->lastPage();
    $window  = range(max(1, $current - 2), min($last, $current + 2));
@endphp

<nav class="pagination au-pagination" aria-label="Audit trail pages">
    @if($paginator->onFirstPage())
        <span class="page-btn is-disabled" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" rel="prev" aria-label="Previous page">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
        </a>
    @endif

    @if(! in_array(1, $window, true))
        <a href="{{ $paginator->url(1) }}" class="page-btn">1</a>
        @if($window[0] > 2)<span class="au-gap">…</span>@endif
    @endif

    @foreach($window as $page)
        <a href="{{ $paginator->url($page) }}"
           class="page-btn {{ $page === $current ? 'active' : '' }}"
           @if($page === $current) aria-current="page" @endif>{{ $page }}</a>
    @endforeach

    @if(! in_array($last, $window, true))
        @if(end($window) < $last - 1)<span class="au-gap">…</span>@endif
        <a href="{{ $paginator->url($last) }}" class="page-btn">{{ number_format($last) }}</a>
    @endif

    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" rel="next" aria-label="Next page">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
    @else
        <span class="page-btn is-disabled" aria-hidden="true">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
        </span>
    @endif
</nav>
