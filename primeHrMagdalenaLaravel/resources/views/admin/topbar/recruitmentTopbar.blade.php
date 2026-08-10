{{-- No search slot: the module is switched off, so there are no job postings
     to search. See admin/*/admin*.blade.php. --}}
<x-topbar title="Recruitment Management">
    <x-slot:icon><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Job Postings &amp; Applications</x-slot:subtitle>
</x-topbar>
