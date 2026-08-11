{{--
    The thin government strip above every public-facing page.

    It was the same two hard-coded sentences copy-pasted into welcome, login,
    forgot-password and select-role. Editing "Website Content → Top government
    bar" changed one of the four, so the sign-in screens kept whatever the
    municipality used to be called.

    One component, one source. Anything reading site copy on a page a visitor
    can reach should come through here rather than repeat the strings.
--}}
@php $govbar = \App\Services\SiteContentService::section('govbar'); @endphp

<div class="pub-govbar">
    <span>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px">
            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
        </svg>
        {{ $govbar['left'] }}
    </span>
    <span>{{ $govbar['right'] }}</span>
</div>
