{{--
    The search box is gone with the page's data. It called
    filterPermanentPerformance(), which was defined in the inline script that
    rendered the mock evaluations — so it would now throw on every keystroke,
    and there is nothing left on the page to filter.
--}}
<x-topbar title="Performance Overview">
    <x-slot:icon><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></x-slot:icon>
    <x-slot:subtitle><span data-live-datetime data-variant="datetime">{{ now()->timezone('Asia/Manila')->format('l, F j, Y g:i:s A') }}</span> &nbsp;·&nbsp; {{ $employee->employmentDetail->designationRelation->title ?? 'N/A' }} · {{ $employee->employmentDetail->departmentRelation->name ?? 'N/A' }} · {{ $employee->employee_id ?? 'N/A' }}</x-slot:subtitle>
</x-topbar>
