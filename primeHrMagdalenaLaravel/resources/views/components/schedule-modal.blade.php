{{--
    Shared shell for a fourth modal family found in admin/personnel and
    admin/deductions: fully inline-styled (no CSS classes at all), a
    gradient purple header bar with an icon tile, uppercase eyebrow label,
    title, and a round "×" close button. No click-outside-to-close in any
    of the three files this was extracted from.

    Unlike the other three modal components, there's no existing CSS class
    system to preserve here — the component owns the actual visual styling
    directly, since duplicating ~15 lines of identical inline styles across
    files was exactly the problem.

    Usage:
        <x-schedule-modal id="assignScheduleModal" close="closeAssignScheduleModal"
                           eyebrow="WORK SCHEDULE" title="Employee Name" title-id="scheduleEmployeeName">
            <x-slot:icon>
                <svg ...>...</svg>
            </x-slot:icon>
            <form ...>
                <div style="padding:24px; ...">...</div>
                <div style="padding:16px 24px; border-top:1px solid var(--gp-bg-tint-2); ...">...</div>
            </form>
        </x-schedule-modal>

    `max-width` defaults to 550px (assignSchedule's size); pass a wider one
    for larger content (viewSchedules uses 900px). `box-style`/`overlay-style`
    are raw passthrough for the handful of per-file layout tweaks (viewSchedules'
    scrollable flex-column box, scrollable overlay).
--}}
@props([
    'id',
    'maxWidth' => '550px',
    'boxStyle' => null,
    'overlayStyle' => null,
    'close' => null,
    'eyebrow' => null,
    'title' => null,
    'titleId' => null,
])

<div id="{{ $id }}" {{ $attributes }} style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:2000; align-items:center; justify-content:center; {{ $overlayStyle }}">
    <div style="background:#fff; border-radius:12px; width:100%; max-width:{{ $maxWidth }}; box-shadow:0 8px 32px rgba(11,4,77,0.2); overflow:hidden; {{ $boxStyle }}">
        <div style="background:linear-gradient(135deg, var(--gp-pri) 0%, var(--gp-pri-2) 100%); padding:20px 24px; display:flex; justify-content:space-between; align-items:center;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:40px; height:40px; background:rgba(255,255,255,0.12); border-radius:10px; display:flex; align-items:center; justify-content:center;">
                    {{ $icon }}
                </div>
                <div>
                    <p style="margin:0; font-size:10px; font-weight:700; letter-spacing:1.5px; color:rgba(255,255,255,0.5);">{{ $eyebrow }}</p>
                    <h3 style="margin:0; font-size:16px; font-weight:700; color:#fff;" @if($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h3>
                </div>
            </div>
            @if($close)
                <button type="button" onclick="{{ $close }}()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; width:32px; height:32px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:20px;">&times;</button>
            @endif
        </div>
        {{ $slot }}
    </div>
</div>
