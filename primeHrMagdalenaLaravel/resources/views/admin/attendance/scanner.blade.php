@extends('layouts.app')

@php
    use App\Models\AttendancePunch;

    // Grouped off AttendancePunch::SLOTS rather than listed by hand, so a new
    // slot appears here the moment the model gains one. Flat, the six buttons
    // fell into a 3-column grid as "AM In · AM Out · PM In / PM Out · OT In ·
    // OT Out" — the PM pair split across two rows, which is the sort of thing
    // an operator mis-clicks while someone is standing at the desk.
    $slotGroups = collect(AttendancePunch::SLOTS)->groupBy(fn ($slot) => strtok($slot, '_'));

    $groupLabels = ['am' => 'Morning', 'pm' => 'Afternoon', 'ot' => 'Overtime'];
@endphp

@section('content')
<x-topbar title="Attendance Scanner">
    <x-slot:icon><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="18" y1="14" x2="21" y2="14"/><line x1="18" y1="18" x2="21" y2="21"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; QR terminal &mdash; biometric simulator</x-slot:subtitle>
    <x-slot:actions>
        <a href="{{ route('admin.attendance') }}" class="scan-topbar-link">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Attendance
        </a>
    </x-slot:actions>
</x-topbar>

<main class="scan-page glass-shell"
      data-punch-url="{{ route('admin.attendance.scanner.punch') }}"
      data-suggest-url="{{ route('admin.attendance.scanner.suggest') }}"
      data-recent-url="{{ route('admin.attendance.scanner.recent') }}">

    {{-- Standing notice: this terminal is not a biometric device, and the
         record it writes should never be mistaken for one. --}}
    <div class="scan-notice" role="note">
        <span class="scan-notice-icon" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </span>
        <p class="scan-notice-text">
            <strong>Simulator.</strong> A QR badge proves the card was present, not the person holding it.
            Every scan is logged against the staff member operating this terminal. When the biometric reader
            arrives it writes to the same records through the same rules.
        </p>
    </div>

    <div class="scan-grid">

        {{-- ============ CAMERA + SLOT PICKER ============ --}}
        <section class="scan-card scan-card--camera" aria-labelledby="scanCameraHeading">
            <div class="scan-card-header">
                <div class="scan-card-heading">
                    <span class="scan-card-eyebrow">Capture</span>
                    <h2 id="scanCameraHeading">Scan badge</h2>
                </div>
                {{-- textContent is rewritten by attendanceScanner.js, so the live
                     dot is a ::before pseudo-element rather than a child node. --}}
                <span class="scan-status" id="scanStatus" data-state="idle" role="status">Camera off</span>
            </div>

            <div class="scan-step">
                <p class="scan-step-label"><span class="scan-step-num">1</span> Choose the punch</p>

                <div class="scan-slot-picker" role="group" aria-label="Which punch is this?">
                    @foreach ($slotGroups as $prefix => $slots)
                        <div class="scan-slot-group">
                            <span class="scan-slot-group-label">{{ $groupLabels[$prefix] ?? strtoupper($prefix) }}</span>
                            <div class="scan-slot-pair">
                                @foreach ($slots as $slot)
                                    {{-- The visible text is just In / Out under the group
                                         caption — bigger target, read faster. The full
                                         label stays on aria-label for screen readers. --}}
                                    <button type="button"
                                            class="scan-slot{{ $slot === 'am_in' ? ' is-active' : '' }}"
                                            data-slot="{{ $slot }}"
                                            aria-label="{{ AttendancePunch::slotLabel($slot) }}"
                                            aria-pressed="{{ $slot === 'am_in' ? 'true' : 'false' }}">
                                        {{ Str::endsWith($slot, '_in') ? 'In' : 'Out' }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="scan-slot-hint" id="scanSlotHint">Pick the punch, then scan. The badge says who — you say which.</p>
            </div>

            <div class="scan-step">
                <p class="scan-step-label"><span class="scan-step-num">2</span> Present the badge</p>

                <div class="scan-viewport">
                    <div id="scanReader" class="scan-reader"></div>

                    <div class="scan-viewport-idle" id="scanViewportIdle">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        <p>Camera is off</p>
                        <span>Start it below, or use the handheld scanner.</span>
                    </div>

                    {{-- Aiming frame. A sibling of the idle panel (not a child of
                         #scanReader, which html5-qrcode owns and rewrites), and
                         shown only once the idle panel is hidden — i.e. only while
                         the camera is actually running. --}}
                    <div class="scan-frame" aria-hidden="true">
                        <span class="scan-frame-corner scan-frame-corner--tl"></span>
                        <span class="scan-frame-corner scan-frame-corner--tr"></span>
                        <span class="scan-frame-corner scan-frame-corner--bl"></span>
                        <span class="scan-frame-corner scan-frame-corner--br"></span>
                        <span class="scan-frame-sweep"></span>
                    </div>
                </div>

                <div class="scan-controls">
                    <button type="button" class="scan-btn scan-btn--primary" id="scanStartBtn">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                        Start camera
                    </button>
                    <button type="button" class="scan-btn" id="scanStopBtn" disabled>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="6" width="12" height="12" rx="2"/></svg>
                        Stop
                    </button>
                </div>
            </div>

            {{-- A USB QR gun types the payload and presses Enter, exactly like a
                 keyboard. Supporting it costs one input and means the kiosk
                 works on a desktop with no webcam. --}}
            <form class="scan-manual" id="scanManualForm" autocomplete="off">
                <label for="scanManualInput">Handheld scanner / manual entry</label>
                <div class="scan-manual-row">
                    <input type="text" id="scanManualInput" placeholder="Scan or type badge code…" spellcheck="false">
                    <button type="submit" class="scan-btn scan-btn--primary">Record</button>
                </div>
                <p class="scan-manual-hint">Typing anywhere on this page jumps to this box, so a scanner gun works without clicking it first.</p>
            </form>
        </section>

        {{-- ============ RESULT ============ --}}
        <section class="scan-card scan-card--result" aria-labelledby="scanResultHeading">
            <div class="scan-card-header">
                <div class="scan-card-heading">
                    <span class="scan-card-eyebrow">Outcome</span>
                    <h2 id="scanResultHeading">Last scan</h2>
                </div>
            </div>

            {{-- aria-live: the operator is looking at the person, not the panel.
                 The beep says something happened; this says what. --}}
            <div class="scan-result" id="scanResult" data-state="empty" aria-live="polite">
                <div class="scan-result-empty" id="scanResultEmpty">
                    <span class="scan-result-empty-icon" aria-hidden="true">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="18" y1="14" x2="21" y2="14"/><line x1="18" y1="18" x2="21" y2="21"/></svg>
                    </span>
                    <p>No scans yet</p>
                    <span>Results appear here as badges are read.</span>
                </div>

                <div class="scan-result-body" id="scanResultBody" hidden>
                    <div class="scan-identity">
                        <div class="scan-avatar" id="scanAvatar"></div>
                        <div class="scan-identity-text">
                            <p class="scan-name" id="scanName"></p>
                            <p class="scan-meta" id="scanMeta"></p>
                        </div>
                    </div>

                    {{-- The glyph is chosen by CSS from the panel's [data-state],
                         so the outcome reads from across the desk before a single
                         word of the message does. --}}
                    <div class="scan-outcome">
                        <span class="scan-outcome-icon" aria-hidden="true">
                            <svg class="scan-outcome-ico" data-for="ok" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <svg class="scan-outcome-ico" data-for="warn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <svg class="scan-outcome-ico" data-for="bad" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        </span>
                        <p class="scan-message" id="scanMessage"></p>
                    </div>

                    <div class="scan-day">
                        <p class="scan-day-title">Today's record</p>
                        <div class="scan-day-strip" id="scanDayStrip"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ LIVE FEED ============ --}}
        <section class="scan-card scan-card--feed" aria-labelledby="scanFeedHeading">
            <div class="scan-card-header">
                <div class="scan-card-heading">
                    <span class="scan-card-eyebrow">Live</span>
                    <h2 id="scanFeedHeading">Today's punches</h2>
                </div>
                <span class="scan-count" id="scanFeedCount">{{ count($recent) }}</span>
            </div>

            {{-- Item markup is duplicated by renderFeed() in attendanceScanner.js;
                 the two must stay identical or the list changes shape on the first
                 scan. --}}
            <ul class="scan-feed" id="scanFeed">
                @forelse ($recent as $punch)
                    <li class="scan-feed-item">
                        <div class="scan-feed-avatar">
                            @if ($punch['photo'])
                                <img src="{{ $punch['photo'] }}" alt="">
                            @else
                                <span>{{ strtoupper(substr($punch['name'], 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="scan-feed-text">
                            <p class="scan-feed-name">{{ $punch['name'] }}</p>
                            <p class="scan-feed-sub">{{ $punch['slot_label'] }} &middot; {{ $punch['time'] }}</p>
                        </div>
                    </li>
                @empty
                    <li class="scan-feed-empty">No punches recorded today.</li>
                @endforelse
            </ul>
        </section>
    </div>
</main>

{{-- Decoder for the camera feed. Matches how the QR *generator* is already
     loaded on the Personnel page. --}}
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@push('styles')
    @vite('resources/css/admin/attendanceScanner.css')
@endpush

@push('scripts')
    @vite('resources/js/admin/attendance/attendanceScanner.js')
@endpush
