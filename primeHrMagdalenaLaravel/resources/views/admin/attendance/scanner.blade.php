@extends('layouts.app')

@php
    use App\Models\AttendancePunch;
@endphp

@section('content')
<x-topbar title="Attendance Scanner">
    <x-slot:icon><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="18" y1="14" x2="21" y2="14"/><line x1="18" y1="18" x2="21" y2="21"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; QR terminal &mdash; biometric simulator</x-slot:subtitle>
    <x-slot:actions>
        <a href="{{ route('admin.attendance') }}" class="scan-topbar-link">Back to Attendance</a>
    </x-slot:actions>
</x-topbar>

<main class="scan-page glass-shell"
      data-punch-url="{{ route('admin.attendance.scanner.punch') }}"
      data-suggest-url="{{ route('admin.attendance.scanner.suggest') }}"
      data-recent-url="{{ route('admin.attendance.scanner.recent') }}">

    {{-- Standing notice: this terminal is not a biometric device, and the
         record it writes should never be mistaken for one. --}}
    <div class="scan-notice">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>
            <strong>Simulator.</strong> A QR badge proves the card was present, not the person holding it.
            Every scan is logged against the staff member operating this terminal. When the biometric reader
            arrives it writes to the same records through the same rules.
        </span>
    </div>

    <div class="scan-grid">

        {{-- ============ CAMERA + SLOT PICKER ============ --}}
        <section class="scan-card scan-card--camera">
            <div class="scan-card-header">
                <h2>Scan badge</h2>
                <span class="scan-status" id="scanStatus" data-state="idle">Camera off</span>
            </div>

            <div class="scan-slot-picker" role="group" aria-label="Which punch is this?">
                @foreach (AttendancePunch::SLOTS as $slot)
                    <button type="button"
                            class="scan-slot{{ $slot === 'am_in' ? ' is-active' : '' }}"
                            data-slot="{{ $slot }}"
                            aria-pressed="{{ $slot === 'am_in' ? 'true' : 'false' }}">
                        {{ AttendancePunch::slotLabel($slot) }}
                    </button>
                @endforeach
            </div>
            <p class="scan-slot-hint" id="scanSlotHint">Pick the punch, then scan. The badge says who — you say which.</p>

            <div class="scan-viewport">
                <div id="scanReader" class="scan-reader"></div>
                <div class="scan-viewport-idle" id="scanViewportIdle">
                    <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <p>Camera is off</p>
                </div>
            </div>

            <div class="scan-controls">
                <button type="button" class="scan-btn scan-btn--primary" id="scanStartBtn">Start camera</button>
                <button type="button" class="scan-btn" id="scanStopBtn" disabled>Stop</button>
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
            </form>
        </section>

        {{-- ============ RESULT ============ --}}
        <section class="scan-card scan-card--result">
            <div class="scan-card-header">
                <h2>Last scan</h2>
            </div>

            <div class="scan-result" id="scanResult" data-state="empty">
                <div class="scan-result-empty" id="scanResultEmpty">
                    <p>No scans yet.</p>
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

                    <p class="scan-message" id="scanMessage"></p>

                    <div class="scan-day">
                        <p class="scan-day-title">Today's record</p>
                        <div class="scan-day-strip" id="scanDayStrip"></div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ LIVE FEED ============ --}}
        <section class="scan-card scan-card--feed">
            <div class="scan-card-header">
                <h2>Today's punches</h2>
                <span class="scan-count" id="scanFeedCount">{{ count($recent) }}</span>
            </div>

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
