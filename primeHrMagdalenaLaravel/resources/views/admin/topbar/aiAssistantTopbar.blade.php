<x-topbar title="AI Assistant">
    <x-slot:icon><path d="M12 3l1.6 4.6L18 9l-4.4 1.4L12 15l-1.6-4.6L6 9l4.4-1.4z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Admin Panel</x-slot:subtitle>
    <x-slot:actions>
        <span class="banner-badge">
            <span class="banner-badge-dot"></span>
            Assistant Online
        </span>
    </x-slot:actions>
</x-topbar>
