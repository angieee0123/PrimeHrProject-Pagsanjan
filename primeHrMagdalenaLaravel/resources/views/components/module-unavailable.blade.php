{{--
    "This part isn't available yet" — the in-app notice for a module that has
    not been built.

    Used instead of the page it replaces, not on top of it. Performance
    Management and Recruitment were both rendering hard-coded sample rows —
    invented employees, invented ratings, invented job postings — with working
    filters, modals and Export buttons. A convincing screen backed by nothing
    is worse than no screen: somebody reads a 4.8 rating off it, or files an
    evaluation that is never stored. Deleting the mock data and saying so
    plainly is the honest state.

    Usage:
        <x-module-unavailable
            module="Performance Management"
            reason="Employee evaluations are not recorded anywhere yet." />
--}}
@props([
    'module',
    'reason' => null,
    'back' => null,
    'backLabel' => 'Back to Dashboard',
])

<div {{ $attributes->class(['module-unavailable']) }} role="status" aria-live="polite">
    <div class="module-unavailable-card">
        <span class="module-unavailable-icon" aria-hidden="true">
            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="13"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </span>

        <p class="module-unavailable-eyebrow">Not available yet</p>
        <h2 class="module-unavailable-title">{{ $module }} isn&rsquo;t ready</h2>

        <p class="module-unavailable-text">
            This part of PRIME HRIS is still being built, so it has been switched off rather than
            shown with placeholder information.
            @if($reason)
                {{ $reason }}
            @endif
        </p>

        <p class="module-unavailable-note">
            Nothing you do here would be saved, and nothing shown here would be real —
            so the page stays closed until the module works.
        </p>

        <div class="module-unavailable-actions">
            <a href="{{ $back ?? route('admin.dashboard') }}" class="module-unavailable-btn">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
                </svg>
                {{ $backLabel }}
            </a>
        </div>
    </div>
</div>
