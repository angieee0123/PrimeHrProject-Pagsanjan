{{--
    Shared modal shell: overlay + box, with an optional standard header
    (eyebrow + title + subtitle + close button). Everything below the header
    — form, body, footer — stays as freeform slot content, since that's what
    genuinely differs from modal to modal.

    Replaces the hand-copied `<div class="modal-overlay" style="display:none"
    onclick="closeX()"><div class="modal-box" onclick="event.stopPropagation()">`
    wrapper duplicated across the .modal-overlay/.modal-box family of modals
    (~30 of the app's 42 modal files; the .adm-*, .status-modal-*, and .fb-*
    families are a separate, still-duplicated visual system left for a later
    pass — see the roadmap).

    Usage:
        <x-modal id="correctModal" close="closeCorrectModal" max-width="600px"
                  eyebrow="CORRECT ATTENDANCE TIME" title-id="correctEmployeeName"
                  subtitle-id="correctDate">
            <form id="correctForm">...</form>
        </x-modal>

    Omit `title` (and `title-id`) entirely for a headerless confirmation modal
    like successModal — no header renders, and omitting `close` also skips the
    click-outside-to-close wiring (some confirmations should only dismiss via
    their own button).

    `title`/`subtitle` carry static text; `title-id`/`subtitle-id` are for
    modals whose header text is blank on render and filled in by JS afterwards
    (e.g. an employee's name once a row is clicked).
--}}
@props([
    'id',
    'maxWidth' => null,
    'boxStyle' => null,
    'close' => null,
    'eyebrow' => null,
    'title' => null,
    'titleId' => null,
    'subtitle' => null,
    'subtitleId' => null,
])

<div id="{{ $id }}" class="modal-overlay" style="display: none;" @if($close) onclick="{{ $close }}()" @endif>
    <div class="modal-box" @if($close) onclick="event.stopPropagation()" @endif @if($maxWidth || $boxStyle) style="@if($maxWidth)max-width: {{ $maxWidth }};@endif {{ $boxStyle }}" @endif>
        @if($title || $titleId)
            <div class="modal-header">
                <div>
                    @isset($eyebrow)<span class="modal-eyebrow">{{ $eyebrow }}</span>@endisset
                    <h3 class="modal-title" @if($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h3>
                    @if($subtitle || $subtitleId)
                        <p class="modal-sub" @if($subtitleId) id="{{ $subtitleId }}" @endif>{{ $subtitle }}</p>
                    @endif
                </div>
                @if($close)
                    <button type="button" class="modal-close" onclick="{{ $close }}()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                @endif
            </div>
        @endif
        {{ $slot }}
    </div>
</div>
