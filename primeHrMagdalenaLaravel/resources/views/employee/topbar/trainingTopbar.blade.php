<x-topbar title="Training &amp; Development">
    <x-slot:icon><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></x-slot:icon>
    <x-slot:subtitle><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; CSC PDS Section IV · Fiscal Year {{ date('Y') }}</x-slot:subtitle>
    <x-slot:actions>
        <div class="topbar-search-wrap">
            <svg class="topbar-search-icon" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="employeeTrainingSearch" class="topbar-search-input" placeholder="Search title or status..." oninput="filterPermanentTraining()">
        </div>
    </x-slot:actions>
</x-topbar>
