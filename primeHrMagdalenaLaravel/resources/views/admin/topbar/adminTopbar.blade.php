<x-topbar title="Welcome back, Admin!">
    <x-slot:icon><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Admin Panel</x-slot:subtitle>
    <x-slot:actions>
        <span class="banner-badge">
            <span class="banner-badge-dot"></span>
            System Online
        </span>
        <span class="banner-badge outline">FY {{ now()->year }}</span>
    </x-slot:actions>
</x-topbar>
