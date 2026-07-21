@php
    $mayorEmployee = Auth::check() ? Auth::user()->employee : null;
    $mayorName = $mayorEmployee ? trim($mayorEmployee->first_name . ' ' . $mayorEmployee->last_name) : 'Mayor';
@endphp
<x-topbar title="Welcome, {{ $mayorName }}">
    <x-slot:icon><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Oversight Dashboard</x-slot:subtitle>
    <x-slot:actions>
        <span class="banner-badge">
            <span class="banner-badge-dot mayor-live-dot"></span>
            Live · Updated {{ now()->format('h:i A') }}
        </span>
        <span class="banner-badge outline">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" style="margin-right:4px;vertical-align:-2px"><rect x="3" y="11" width="18" height="10" rx="2"/><circle cx="12" cy="7" r="4"/></svg>
            View Only Access
        </span>
    </x-slot:actions>
</x-topbar>

<style>
.mayor-live-dot { animation: pulse 2s ease-in-out infinite; }
</style>
