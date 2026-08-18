<x-topbar title="Audit Trail">
    <x-slot:icon><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; User Actions &amp; Changes</x-slot:subtitle>
    <x-slot:actions>
        {{--
            The search box sits in the topbar but drives the filter form below,
            which is where every other filter lives. It writes into that form's
            hidden `q` field and submits it (debounced), so search and the
            dropdowns produce one URL instead of two competing filters — one
            server-side, one in the browser over a single page of rows.
        --}}
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" id="auditSearchInput" class="topbar-search-input"
                   value="{{ $filters['q'] ?? '' }}"
                   placeholder="Search user, record, URL or value..."
                   autocomplete="off">
        </div>
    </x-slot:actions>
</x-topbar>
