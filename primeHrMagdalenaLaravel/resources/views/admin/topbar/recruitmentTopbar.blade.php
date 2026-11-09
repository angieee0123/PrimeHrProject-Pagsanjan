{{-- Recruitment Management — served by this application's own recruitment
     tables. The New Vacancy action is administrators only; the controller's
     requireManager() enforces that as well, so hiding it here is tidiness
     rather than the permission. It is also hidden on the create page itself,
     where it would point at the form already on screen. --}}
@php
    $canCreateVacancy = auth()->user()?->hasRole('admin')
        && ! request()->routeIs('admin.recruitment.create');
@endphp

<x-topbar title="Recruitment Management">
    <x-slot:icon><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Job postings, screening &amp; approvals</x-slot:subtitle>
    @if($canCreateVacancy)
        <x-slot:actions>
            <a href="{{ route('admin.recruitment.create') }}" class="btn-export adm-btn-primary-solid">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Vacancy
            </a>
        </x-slot:actions>
    @endif
</x-topbar>
