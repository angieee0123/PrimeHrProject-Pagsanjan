{{-- Shared logout confirmation modal + the real POST logout form.

     Rendered as a SIBLING of <aside class="sidebar"> (i.e. placed after the
     closing </aside> in each *Sidebar.blade.php), never inside it — the
     sidebar sets overflow:hidden, which would clip this position:fixed overlay.

     $firstName personalizes the heading; callers pass it from their own auth
     context. Falls back to a neutral greeting when unavailable. --}}
@php
    $logoutFirstName = trim($firstName ?? '') !== '' ? $firstName : 'there';
@endphp

<div class="logout-modal" id="logoutModal" aria-hidden="true">
    <div class="logout-modal-backdrop" onclick="closeLogoutModal()"></div>
    <div class="logout-modal-card" role="dialog" aria-modal="true" aria-labelledby="logoutModalTitle">
        <div class="logout-modal-icon">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
        </div>
        <h3 class="logout-modal-title" id="logoutModalTitle">Sign out, {{ $logoutFirstName }}?</h3>
        <p class="logout-modal-text">You're about to end your PRIME HRIS session. You'll need to sign in again to pick up where you left off.</p>
        <div class="logout-modal-actions">
            <button type="button" class="logout-modal-btn logout-modal-cancel" onclick="closeLogoutModal()">Stay signed in</button>
            <button type="button" class="logout-modal-btn logout-modal-confirm" onclick="confirmLogout()">Yes, log me out</button>
        </div>
    </div>
</div>

<form action="{{ route('logout') }}" method="POST" id="logout-form" class="logout-hidden-form">
    @csrf
</form>
