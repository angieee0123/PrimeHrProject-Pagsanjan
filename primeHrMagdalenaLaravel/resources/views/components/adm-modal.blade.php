{{--
    Shared shell for the ".adm-*" modal family (admin/departments) — a third,
    incompatible system alongside <x-modal> (.modal-box) and <x-modal-container>
    (.modal-container): its own class names (adm-overlay/adm-box/adm-header/
    adm-eyebrow/adm-title/adm-close), a header icon tile every file carries,
    and close handlers that take no `event` argument at all (unlike
    <x-modal-container>'s family).

    Body is deliberately NOT auto-wrapped — viewDepartment.blade.php uses its
    own .vdm-stats/.vdm-body sections instead of the usual .adm-body, so the
    whole thing after the header is left as free slot content, same as
    <x-modal>.

    Usage:
        <x-adm-modal id="add-dept-modal" close="closeAddModal"
                      eyebrow="DEPARTMENTS · NEW" title="Register Department">
            <x-slot:icon>
                <div class="adm-header-icon">
                    <svg ...>...</svg>
                </div>
            </x-slot:icon>
            <form method="POST" action="...">
                <div class="adm-body">...</div>
                <div class="adm-footer">...</div>
            </form>
        </x-adm-modal>

    `icon` is a full-markup slot (not just inner SVG shapes like <x-topbar>)
    because it varies more here — a colored icon tile in most files,
    a plain avatar div in viewDepartment.
--}}
@props([
    'id',
    'maxWidth' => null,
    'close' => null,
    'eyebrow' => null,
    'eyebrowId' => null,
    'title' => null,
    'titleId' => null,
])

<div id="{{ $id }}" {{ $attributes->class(['adm-overlay']) }} @if($close) onclick="{{ $close }}()" @endif>
    <div class="adm-box" onclick="event.stopPropagation()" @if($maxWidth) style="max-width: {{ $maxWidth }};" @endif>
        <div class="adm-header">
            <div class="adm-header-left">
                {{ $icon }}
                <div>
                    @if($eyebrow || $eyebrowId)
                        <span class="adm-eyebrow" @if($eyebrowId) id="{{ $eyebrowId }}" @endif>{{ $eyebrow }}</span>
                    @endif
                    <h3 class="adm-title" @if($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h3>
                </div>
            </div>
            @if($close)
                <button class="adm-close" onclick="{{ $close }}()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            @endif
        </div>
        {{ $slot }}
    </div>
</div>
