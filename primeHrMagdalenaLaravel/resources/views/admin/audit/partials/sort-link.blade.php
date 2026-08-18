{{--
    One sortable column header.

    Sorting is a link, not a JS handler, because the rows on screen are one
    page of many — re-ordering only the fifteen rows the browser happens to
    hold would put "the newest record" somewhere in the middle of page four.
    The arrow only appears on the column actually in force; the previous
    version drew a faded arrow on every header, which read as "unsorted".

    Expects: $column, $label, and $sort / $dir / $filters / $perPage from the view.
--}}
@php
    $isActive = $sort === $column;
    // Clicking the active column flips it; clicking a new one starts at the
    // reading most people want first — newest, Z→A, highest.
    $nextDir = $isActive && $dir === 'desc' ? 'asc' : 'desc';
@endphp

<a href="{{ request()->fullUrlWithQuery(['sort' => $column, 'dir' => $nextDir, 'page' => null]) }}"
   class="au-sort {{ $isActive ? 'is-active' : '' }}"
   aria-sort="{{ $isActive ? ($dir === 'asc' ? 'ascending' : 'descending') : 'none' }}">
    {{ $label }}
    @if($isActive)
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
             style="transform: rotate({{ $dir === 'asc' ? '0' : '180' }}deg)"><polyline points="18 15 12 9 6 15"/></svg>
    @endif
</a>
