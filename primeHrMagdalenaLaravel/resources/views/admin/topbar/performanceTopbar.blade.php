{{-- No search slot: the module is switched off, so there are no evaluations
     to search. See admin/*/admin*.blade.php. --}}
<x-topbar title="Performance Management">
    <x-slot:icon><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></x-slot:icon>
    <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; Employee Evaluations</x-slot:subtitle>
</x-topbar>
