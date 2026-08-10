{{--
    Shared page-header "welcome banner": icon tile + title + subtitle, with an
    optional right-side slot for a search box, status badges, or an action button.

    Replaces the markup that used to be hand-copied into every admin/employee/mayor
    topbar partial (~26 files). Visual theme (navy panel, frosted icon tile, search
    box states) lives in resources/css/topbarTheme.css, loaded from the role layouts
    — this component only owns the DOM shape, not the styling.

    Usage:
        <x-topbar title="Attendance Management">
            <x-slot:icon><rect x="3" y="4" width="18" height="18" rx="2"/></x-slot:icon>
            <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; PRIME HRIS Admin Panel</x-slot:subtitle>
            <x-slot:actions>
                <div class="topbar-search-wrap">...</div>
            </x-slot:actions>
        </x-topbar>

    $icon receives only the inner SVG shapes (path/rect/circle/...) — the surrounding
    <svg> tag's size/stroke/viewBox attributes are identical on every topbar, so they
    live here instead of being retyped per page. Pass extra classes (e.g. mayor's
    "mayor-page-header") straight on the component tag; they merge onto the root div.
--}}
@props(['title'])

<div {{ $attributes->class(['welcome-banner']) }}>
    <div class="banner-left">
        <div class="banner-icon">
            <svg width="22" height="22" fill="none" stroke="var(--theme-topbar-icon)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{{ $icon }}</svg>
        </div>
        <div>
            <h2>{{ $title }}</h2>
            @isset($subtitle)
                <p>{{ $subtitle }}</p>
            @endisset
        </div>
    </div>
    @isset($actions)
        <div class="banner-right">
            {{ $actions }}
        </div>
    @endisset
</div>
