@extends('layouts.app')

@push('styles')
    @vite('resources/css/admin/adminWebsite.css')
@endpush

@section('content')

{{--
    Website Content — the editor for the public welcome page.

    Two states, deliberately not shown at once:

      · **Overview** is where you land. A card per part of the page, each
        saying in plain words which bit of the site it is and whether anyone
        has changed it. Nothing to fill in, nothing to read past.
      · **Editor** is one section's form, with a rail for hopping between
        sections once you are already in one.

    Opening straight into a twenty-field form was the thing that made this
    overwhelming: you had to recognise the section you wanted from a list of
    ten abstract names *while* looking at somebody else's form.

    Every panel is a plain <form>. The repeatable lists (announcements,
    services, facts, links) are rendered server-side from the current content
    and grown client-side from a <template>, so a row added in the browser has
    exactly the fields the validator expects.
--}}

@include('admin.topbar.websiteTopbar')
@include('admin.notification.adminNotification')

@php
    $icon = fn (string $d) => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $d . '</svg>';
    $sectionIcons = [
        'govbar'        => '<rect x="2" y="4" width="20" height="4" rx="1"/><line x1="2" y1="14" x2="22" y2="14"/><line x1="2" y1="19" x2="14" y2="19"/>',
        'brand'         => '<circle cx="12" cy="12" r="9"/><path d="M12 3v18"/><path d="M3 12h18"/>',
        'hero'          => '<rect x="3" y="4" width="18" height="12" rx="2"/><line x1="7" y1="20" x2="17" y2="20"/>',
        'services'      => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'announcements' => '<path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        'about'         => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/>',
        'contact'       => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
        'cta'           => '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
        'footer'        => '<line x1="2" y1="5" x2="22" y2="5"/><line x1="2" y1="10" x2="14" y2="10"/><rect x="2" y="16" width="20" height="4" rx="1"/>',
        'chatbot'       => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
    ];
    $editedCount = count(array_intersect(array_keys($editLog), array_keys($sections)));
@endphp

<div class="glass-shell">
<div class="wc-root" data-website-editor
     data-update-url="{{ url('/admin/website') }}"
     data-csrf="{{ csrf_token() }}">

    {{-- ══ Overview — the landing state ══════════════════════════════ --}}
    <section class="wc-overview" data-overview>
        {{-- Instruction on the left, status on the right. They were one
             paragraph, which read as a run-on and wrapped mid-sentence in a
             narrow ragged column against a much wider grid of cards. --}}
        <header class="wc-overview-head">
            <div class="wc-overview-intro">
                <h2 class="wc-overview-title">What would you like to change?</h2>
                <p class="wc-overview-sub">Pick a part of the public welcome page. Anything you save goes live straight away.</p>
            </div>

            <div class="wc-overview-stat{{ $editedCount ? ' is-edited' : '' }}">
                <span class="wc-overview-stat-value">{{ $editedCount }}<span class="wc-overview-stat-of">/{{ count($sections) }}</span></span>
                <span class="wc-overview-stat-label">{{ $editedCount === 1 ? 'section edited' : 'sections edited' }}</span>
            </div>
        </header>

        @foreach($groups as $groupLabel => $groupSections)
        <div class="wc-ov-group">
            <div class="wc-ov-group-head">
                <h3 class="wc-ov-group-title">{{ $groupLabel }}</h3>
                <p class="wc-ov-group-sub">
                    {{ $groupLabel === 'Page setup'
                        ? 'Changes when the site is being redesigned. These carry rules — icon choices, link formats, image sizes.'
                        : 'Changes when the world does: a new advisory, a new phone number, a reworded headline.' }}
                </p>
            </div>

            <div class="wc-ov-grid">
                @foreach($groupSections as $key => $label)
                <button type="button" class="wc-ov-card" data-open="{{ $key }}">
                    <span class="wc-ov-card-icon">{!! $icon($sectionIcons[$key] ?? '') !!}</span>
                    <span class="wc-ov-card-body">
                        <span class="wc-ov-card-title">{{ $label }}</span>
                        <span class="wc-ov-card-blurb">{{ $blurbs[$key] ?? '' }}</span>
                    </span>
                    <span class="wc-ov-card-state">
                        @if(isset($editLog[$key]))
                            <span class="wc-ov-badge is-edited">Edited</span>
                            <span class="wc-ov-when">{{ $editLog[$key]['at']?->diffForHumans() }}</span>
                        @else
                            <span class="wc-ov-badge">Original</span>
                        @endif
                    </span>
                </button>
                @endforeach
            </div>
        </div>
        @endforeach
    </section>

    {{-- ══ Editor — one section at a time ════════════════════════════ --}}
    <div class="wc-editor" data-editor hidden>

        {{-- The rail only exists once you are editing; on the overview it
             would be a second copy of the same ten choices. --}}
        <aside class="wc-rail">
            <button type="button" class="wc-back" data-back>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                All sections
            </button>

            @foreach($groups as $groupLabel => $groupSections)
            <div class="wc-nav-group">
                <p class="wc-nav-group-title">
                    {{ $groupLabel }}
                    @if($groupLabel === 'Page setup')
                        <span class="wc-nav-group-hint" title="These have rules to follow — icon choices, link formats, image sizes">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
                        </span>
                    @endif
                </p>
                <nav class="wc-nav">
                    @foreach($groupSections as $key => $label)
                    <button type="button" class="wc-nav-item" data-target="{{ $key }}">
                        <span class="wc-nav-icon">{!! $icon($sectionIcons[$key] ?? '') !!}</span>
                        <span class="wc-nav-label">{{ $label }}</span>
                        @if(isset($editLog[$key]))
                            <span class="wc-nav-dot" title="Edited{{ $editLog[$key]['by'] ? ' by ' . $editLog[$key]['by'] : '' }}"></span>
                        @endif
                    </button>
                    @endforeach
                </nav>
            </div>
            @endforeach
        </aside>

        <div class="wc-panels">
            @foreach($sections as $key => $label)
            <form class="wc-panel" data-section="{{ $key }}" data-panel="{{ $key }}">
                <div class="wc-panel-head">
                    <button type="button" class="wc-back is-inline" data-back>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        All sections
                    </button>
                    <h3 class="wc-panel-title">{{ $label }}</h3>
                    <p class="wc-panel-blurb">{{ $blurbs[$key] ?? '' }}</p>
                    <p class="wc-panel-sub" data-role="meta">
                        @if(isset($editLog[$key]))
                            Last edited {{ $editLog[$key]['at']?->diffForHumans() }}{{ $editLog[$key]['by'] ? ' by ' . $editLog[$key]['by'] : '' }}
                        @else
                            Showing the original wording — not edited yet
                        @endif
                    </p>
                </div>

                <div class="wc-fields">
                    @includeIf('admin.website.sections.' . $key)
                </div>

                <p class="wc-msg" data-role="msg" hidden></p>

                <div class="wc-actions">
                    <button type="button" class="btn-export" data-role="reset">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 2v6h6"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L3 8"/></svg>
                        Reset to original
                    </button>
                    <button type="submit" class="btn-export adm-btn-primary-solid" data-role="save">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        Save changes
                    </button>
                </div>
            </form>
            @endforeach
        </div>
    </div>

</div>
</div>

@push('scripts')
    @vite('resources/js/admin/website/adminWebsite.js')
@endpush
@endsection
