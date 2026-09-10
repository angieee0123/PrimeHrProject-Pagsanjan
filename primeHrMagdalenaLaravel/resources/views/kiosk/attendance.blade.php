@extends('layouts.kiosk')

@php
    use App\Models\AttendancePunch;

    // Grouped off AttendancePunch::SLOTS rather than listed by hand, so a slot
    // added to the model appears in the override list with no change here.
    $slotGroups = collect(AttendancePunch::SLOTS)->groupBy(fn ($slot) => strtok($slot, '_'));
    $groupLabels = ['am' => 'Morning', 'pm' => 'Afternoon', 'ot' => 'Overtime'];

    // Handed to the script as data rather than retyped there. The old kiosk kept
    // its own `SLOT_LABELS` map beside the model's `slotLabel()`, which is two
    // names for one thing and drifts the first time a slot is renamed.
    $slotLabels = collect(AttendancePunch::SLOTS)
        ->mapWithKeys(fn ($slot) => [$slot => AttendancePunch::slotLabel($slot)]);
@endphp

@section('content')
<main class="kiosk-shell"
      data-peek-url="{{ $peekUrl }}"
      data-punch-url="{{ $punchUrl }}"
      data-slot-labels="{{ $slotLabels->toJson() }}">

    <header class="kiosk-head">
        <div class="kiosk-brand">
            <img src="{{ \App\Services\SiteContentService::logoUrl() }}" alt="">
            <div>
                <p class="kiosk-brand-name">Attendance Kiosk</p>
                <p class="kiosk-brand-sub">Municipal Government of Pagsanjan</p>
            </div>
        </div>
        <div class="kiosk-clock">
            <p class="kiosk-clock-time" id="kioskClock">--:--</p>
            <p class="kiosk-clock-date" id="kioskClockDate">&nbsp;</p>
        </div>
    </header>

    {{-- Standing notice. This terminal reads a card, not a person, and saying so
         on the screen is the difference between a convenience and a claim the
         system cannot support. --}}
    <p class="kiosk-notice">
        <strong>Scan your own badge.</strong> This terminal records the time your card was scanned, not who
        was holding it. Ask HR if your card is lost or not scanning.
    </p>

    <div class="kiosk-grid">

        {{-- ============ CAPTURE ============ --}}
        <section class="kiosk-card kiosk-card--camera">
            <div class="kiosk-card-head">
                <h1>Scan your badge</h1>
                {{-- textContent is rewritten by the script, so the live dot has
                     to be a ::before pseudo-element rather than a child node. --}}
                <span class="kiosk-status" id="kioskStatus" data-state="idle" role="status">Camera off</span>
            </div>

            <div class="kiosk-viewport">
                <div id="kioskReader" class="kiosk-reader"></div>

                <div class="kiosk-viewport-idle" id="kioskViewportIdle">
                    <svg width="52" height="52" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/><line x1="7" y1="12" x2="17" y2="12"/></svg>
                    <p>Camera is off</p>
                    <span>Tap below to start, or use the scanner gun.</span>
                </div>

                {{-- Aiming frame. A sibling of the idle panel, never a child of
                     #kioskReader — html5-qrcode owns and rewrites that element. --}}
                <div class="kiosk-frame" aria-hidden="true">
                    <span class="kiosk-frame-corner kiosk-frame-corner--tl"></span>
                    <span class="kiosk-frame-corner kiosk-frame-corner--tr"></span>
                    <span class="kiosk-frame-corner kiosk-frame-corner--bl"></span>
                    <span class="kiosk-frame-corner kiosk-frame-corner--br"></span>
                    <span class="kiosk-frame-sweep"></span>
                </div>
            </div>

            <button type="button" class="kiosk-btn kiosk-btn--primary kiosk-btn--wide" id="kioskStartBtn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                {{-- The label is its own node because the script rewrites it to
                     "Restart camera" once the camera is live; writing textContent
                     on the button would take the icon with it. --}}
                <span id="kioskStartLabel">Start camera</span>
            </button>

            {{-- A USB QR gun types the payload and presses Enter, exactly like a
                 keyboard. Supporting it costs one input and means the kiosk
                 works on a tablet with a broken camera. --}}
            <form class="kiosk-manual" id="kioskManualForm" autocomplete="off">
                <label for="kioskManualInput">Scanner gun / manual entry</label>
                <div class="kiosk-manual-row">
                    <input type="text" id="kioskManualInput" placeholder="Scan or type badge code…" spellcheck="false">
                    <button type="submit" class="kiosk-btn kiosk-btn--primary">Read</button>
                </div>
            </form>
        </section>

        {{-- ============ PANEL: idle → confirm → result ============ --}}
        <section class="kiosk-card kiosk-card--panel">
            <div class="kiosk-panel" id="kioskPanel" data-state="idle" aria-live="polite">

                {{-- IDLE --}}
                <div class="kiosk-idle" id="kioskIdle">
                    <svg width="46" height="46" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="18" y1="14" x2="21" y2="14"/><line x1="18" y1="18" x2="21" y2="21"/></svg>
                    <p>Ready when you are</p>
                    <span>Hold your badge up to the camera.</span>
                </div>

                {{-- CONFIRM — the system names the person and the punch, and the
                     employee confirms. Nothing is written until they do. --}}
                <div class="kiosk-confirm" id="kioskConfirm" hidden>
                    <div class="kiosk-identity">
                        <div class="kiosk-avatar" id="kioskAvatar"></div>
                        <div class="kiosk-identity-text">
                            <p class="kiosk-name" id="kioskName"></p>
                            <p class="kiosk-meta" id="kioskMeta"></p>
                        </div>
                    </div>

                    <p class="kiosk-confirm-lead">This looks like</p>
                    <p class="kiosk-confirm-slot" id="kioskConfirmSlot"></p>

                    <button type="button" class="kiosk-btn kiosk-btn--primary kiosk-btn--wide kiosk-btn--xl" id="kioskConfirmBtn">
                        Confirm
                    </button>

                    {{-- The escape hatch. Inference from a schedule is right most
                         of the time, not always — a half day, a field assignment,
                         a forgotten earlier punch. Presented as a disclosure so
                         the default path stays one tap. --}}
                    <details class="kiosk-override">
                        <summary>Not this punch?</summary>
                        <div class="kiosk-slot-picker" role="group" aria-label="Choose a different punch">
                            @foreach ($slotGroups as $prefix => $slots)
                                <div class="kiosk-slot-group">
                                    <span class="kiosk-slot-group-label">{{ $groupLabels[$prefix] ?? strtoupper($prefix) }}</span>
                                    <div class="kiosk-slot-pair">
                                        @foreach ($slots as $slot)
                                            <button type="button" class="kiosk-slot" data-slot="{{ $slot }}"
                                                    aria-label="{{ AttendancePunch::slotLabel($slot) }}">
                                                {{ Str::endsWith($slot, '_in') ? 'In' : 'Out' }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>

                    <button type="button" class="kiosk-btn kiosk-btn--ghost" id="kioskCancelBtn">Cancel</button>
                </div>

                {{-- RESULT --}}
                <div class="kiosk-result" id="kioskResult" hidden>
                    <div class="kiosk-outcome">
                        <span class="kiosk-outcome-icon" aria-hidden="true">
                            <svg class="kiosk-outcome-ico" data-for="ok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <svg class="kiosk-outcome-ico" data-for="warn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <svg class="kiosk-outcome-ico" data-for="bad" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </span>
                        <p class="kiosk-message" id="kioskMessage"></p>
                    </div>

                    <div class="kiosk-identity" id="kioskResultIdentity" hidden>
                        <div class="kiosk-avatar" id="kioskResultAvatar"></div>
                        <div class="kiosk-identity-text">
                            <p class="kiosk-name" id="kioskResultName"></p>
                            <p class="kiosk-meta" id="kioskResultMeta"></p>
                        </div>
                    </div>

                    <div class="kiosk-day" id="kioskDay" hidden>
                        <p class="kiosk-day-title">Your record today</p>
                        <div class="kiosk-day-strip" id="kioskDayStrip"></div>
                    </div>

                    <button type="button" class="kiosk-btn kiosk-btn--ghost" id="kioskDoneBtn">Done</button>
                </div>
            </div>
        </section>
    </div>
</main>

{{-- Decoder for the camera feed, from the same CDN as the QR generator. --}}
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@push('styles')
    @vite('resources/css/kiosk/attendanceKiosk.css')
@endpush

@push('scripts')
    @vite('resources/js/kiosk/attendanceKiosk.js')
@endpush
