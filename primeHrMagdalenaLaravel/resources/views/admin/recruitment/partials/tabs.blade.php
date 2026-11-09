{{--
    The Recruitment section tabs.

    Real links, not buttons: every section is its own page with its own URL, so
    this is navigation and the browser's back button should work through it.

    "Activity" is administrators only — it is oversight of who did what, not
    screening work. HiringAuditController enforces it too; the hidden tab is
    tidiness, not the permission.
--}}
@php
    $isAdministrator = auth()->user()?->hasRole('admin');

    $sections = [
        [
            'label' => 'Overview',
            'route' => 'admin.recruitment',
            'active' => request()->routeIs('admin.recruitment'),
        ],
        [
            'label' => 'Vacancies',
            'route' => 'admin.recruitment.vacancies',
            'active' => request()->routeIs(
                'admin.recruitment.vacancies',
                'admin.recruitment.show',
                'admin.recruitment.create'
            ),
        ],
        [
            'label' => 'Archive',
            'route' => 'admin.recruitment.archive',
            'active' => request()->routeIs('admin.recruitment.archive'),
        ],
        [
            'label' => 'Rankings',
            'route' => 'admin.recruitment.rankings',
            'active' => request()->routeIs('admin.recruitment.rankings'),
        ],
        [
            'label' => 'Approvals',
            'route' => 'admin.recruitment.approvals',
            'active' => request()->routeIs('admin.recruitment.approvals'),
        ],
    ];

    if ($isAdministrator) {
        $sections[] = [
            'label' => 'Activity',
            'route' => 'admin.recruitment.audit',
            'active' => request()->routeIs('admin.recruitment.audit'),
        ];
    }
@endphp

<nav class="tab-nav hire-tabs" aria-label="Recruitment sections">
    @foreach($sections as $section)
        <a href="{{ route($section['route']) }}"
           class="tab-link {{ $section['active'] ? 'active' : '' }}"
           @if($section['active']) aria-current="page" @endif>
            {{ $section['label'] }}
        </a>
    @endforeach
</nav>
