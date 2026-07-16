<x-topbar title="Performance Management">
    <x-slot:icon><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Employee Evaluations</x-slot:subtitle>
    <x-slot:actions>
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="search-input" class="topbar-search-input" placeholder="Search evaluations...">
        </div>
    </x-slot:actions>
</x-topbar>
