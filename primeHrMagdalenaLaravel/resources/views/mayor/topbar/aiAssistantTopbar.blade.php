<x-topbar title="AI Assistant" class="mayor-page-header">
    <x-slot:icon><path d="M12 3l1.6 4.6L18 9l-4.4 1.4L12 15l-1.6-4.6L6 9l4.4-1.4z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Oversight Assistant</x-slot:subtitle>
    <x-slot:actions>
        <span class="banner-badge outline">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:-2px"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="7" r="4"/></svg>
            View Only Access
        </span>
    </x-slot:actions>
</x-topbar>

<style>
.mayor-page-header { margin-bottom: 18px; }
.mayor-page-header h2 { font-size: 19px; }
.mayor-page-header p { font-size: 12.5px; }
</style>
