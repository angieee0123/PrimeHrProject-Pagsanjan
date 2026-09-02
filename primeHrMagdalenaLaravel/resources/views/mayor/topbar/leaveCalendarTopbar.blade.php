<x-topbar title="Leave &amp; Travel Calendar" class="mayor-page-header">
    <x-slot:icon><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Who is out on leave or travel</x-slot:subtitle>
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
