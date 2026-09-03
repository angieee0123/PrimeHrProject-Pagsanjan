{{-- Notification history — "View all notifications" from any bell.

     One view for all three areas. The layout, the sidebar and the audience come
     from the controller, which reads the area off the route's own defaults, so
     this page cannot be asked to render another area's notifications. Writing
     it three times is how the bell ended up with two copies and the mayor with
     none.

     Expects: $area, $audience, $layout, $notifications (paginator), $state,
     $activeType, $totalCount, $unreadCount, $typeCounts. --}}
@extends($layout)

@section('title', 'Notifications · PRIME HRIS')

@section('content')

@php
    $areaLabel = ['admin' => 'Admin Panel', 'mayor' => "Mayor's View", 'employee' => 'Employee Portal'][$area] ?? 'PRIME HRIS';

    // Filter links keep whatever else is set, so switching category does not
    // silently drop the unread filter the reader had chosen.
    $filterUrl = fn (array $overrides) => request()->fullUrlWithQuery(array_merge(
        ['state' => $state === 'all' ? null : $state, 'type' => $activeType, 'page' => null],
        $overrides
    ));
@endphp

@if($area === 'employee')
<div class="app-layout">
    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Notifications',
        'mobileTopbarTitle' => 'Notifications'
    ])
    <div class="mobile-overlay" id="mobile-overlay"></div>
    @include('employee.sidebar.employeeSidebar')
    <main class="main-content permanent-dashboard glass-shell">
@else
<main class="enterprise-hr-dashboard glass-shell">
@endif

    @include($area . '.notification.' . $area . 'Notification')

    <x-topbar title="Notifications" @class(['mayor-page-header' => $area === 'mayor'])>
        <x-slot:icon><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></x-slot:icon>
        <x-slot:subtitle>{{ now()->format('l, F j, Y') }} &nbsp;·&nbsp; {{ $areaLabel }}</x-slot:subtitle>
        <x-slot:actions>
            <span class="banner-badge outline">
                {{ number_format($unreadCount) }} unread of {{ number_format($totalCount) }}
            </span>
        </x-slot:actions>
    </x-topbar>

    <div class="notif-page">
        <div class="notif-page-bar">
            <div class="notif-filters">
                <a class="notif-filter {{ $state === 'all' ? 'active' : '' }}" href="{{ $filterUrl(['state' => null]) }}">
                    All <span class="notif-filter-count">{{ number_format($totalCount) }}</span>
                </a>
                <a class="notif-filter {{ $state === 'unread' ? 'active' : '' }}" href="{{ $filterUrl(['state' => 'unread']) }}">
                    Unread <span class="notif-filter-count">{{ number_format($unreadCount) }}</span>
                </a>
                <a class="notif-filter {{ $state === 'read' ? 'active' : '' }}" href="{{ $filterUrl(['state' => 'read']) }}">
                    Read <span class="notif-filter-count">{{ number_format(max(0, $totalCount - $unreadCount)) }}</span>
                </a>
            </div>

            @if($unreadCount > 0)
            <button type="button" class="notif-row-btn" id="notifPageMarkAll" data-audience="{{ $audience }}">
                Mark all as read
            </button>
            @endif
        </div>

        {{-- Category filter. Only categories this reader actually has are
             offered: a chip that always returns nothing is a filter that reads
             as a bug. --}}
        @if(count($typeCounts) > 1)
        <div class="notif-filters" style="margin-bottom:14px">
            <a class="notif-filter {{ $activeType === null ? 'active' : '' }}" href="{{ $filterUrl(['type' => null]) }}">All categories</a>
            @foreach($typeCounts as $type => $count)
                @php $meta = \App\Models\Notification::CATEGORIES[$type] ?? null; @endphp
                @if($meta)
                <a class="notif-filter {{ $activeType === $type ? 'active' : '' }}" href="{{ $filterUrl(['type' => $type]) }}">
                    {{ $meta['label'] }} <span class="notif-filter-count">{{ number_format($count) }}</span>
                </a>
                @endif
            @endforeach
        </div>
        @endif

        <div class="notif-list" id="notifHistoryList">
            @forelse($notifications as $notif)
                <div class="notif-list-row" data-notif-row="{{ $notif->id }}">
                    @include('partials.notificationCard', ['notif' => $notif, 'showActions' => true])
                </div>
            @empty
                <div class="notif-empty">
                    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <p>
                        @if($state !== 'all' || $activeType)
                            Nothing matches this filter
                        @else
                            No notifications yet
                        @endif
                    </p>
                    <span>Approvals, decisions and account changes will appear here.</span>
                </div>
            @endforelse
        </div>

        {{-- Paginated rather than rendered whole: a year of approvals is a long
             page nobody reaches the end of, and every row is a database read.

             The markup is written here rather than through `->links()` because
             the framework's default paginator view is a block of Tailwind
             utility classes, and Tailwind does not scan the vendor Blade files
             they live in — so those classes are never generated and the control
             renders as bare text. These are the page's own classes. --}}
        @if($notifications->hasPages())
        <div class="notif-pagination notif-filters" role="navigation" aria-label="Notification pages">
            @if($notifications->onFirstPage())
                <span class="notif-filter" aria-disabled="true" style="opacity:.45">Previous</span>
            @else
                <a class="notif-filter" href="{{ $notifications->previousPageUrl() }}" rel="prev">Previous</a>
            @endif

            <span class="notif-filter active">
                Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}
            </span>

            @if($notifications->hasMorePages())
                <a class="notif-filter" href="{{ $notifications->nextPageUrl() }}" rel="next">Next</a>
            @else
                <span class="notif-filter" aria-disabled="true" style="opacity:.45">Next</span>
            @endif
        </div>
        @endif
    </div>

@if($area === 'employee')
    </main>
</div>
@else
</main>
@endif

{{-- No stylesheet include here: the bell above already pulled
     partials/notificationStyles.blade.php in, and that one file styles the
     cards on this page as well as the ones in the dropdown. --}}

<script>
/* Per-row read/unread and delete, and the page's own Mark all. Delegated from
   the list so a row that has just been re-rendered stays wired.

   Every one of these posts an id and nothing else: the server matches it
   against the signed-in account's own rows, so an id belonging to somebody
   else is a 404 rather than a permission the page could ask for. */
(function () {
    const list = document.getElementById('notifHistoryList');
    const csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    function post(url, method) {
        return fetch(url, {
            method: method || 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
            credentials: 'same-origin',
        }).then(res => res.ok ? res.json() : Promise.reject(res));
    }

    if (list) {
        list.addEventListener('click', (e) => {
            const toggle = e.target.closest('[data-notif-toggle]');
            const remove = e.target.closest('[data-notif-delete]');

            if (toggle) {
                const id = toggle.dataset.notifToggle;
                const isRead = toggle.dataset.notifRead === '1';
                post(`/api/notifications/${id}/${isRead ? 'mark-unread' : 'mark-read'}`)
                    .then(() => {
                        const row = list.querySelector(`[data-notif-row="${id}"]`);
                        const card = row && row.querySelector('.notif-card');
                        if (card) card.classList.toggle('is-unread', isRead);
                        toggle.dataset.notifRead = isRead ? '0' : '1';
                        toggle.textContent = isRead ? 'Mark read' : 'Mark unread';
                    })
                    .catch(() => {});
                return;
            }

            if (remove) {
                const id = remove.dataset.notifDelete;
                post(`/api/notifications/${id}`, 'DELETE')
                    .then(() => {
                        const row = list.querySelector(`[data-notif-row="${id}"]`);
                        if (row) row.remove();
                    })
                    .catch(() => {});
            }
        });
    }

    const markAll = document.getElementById('notifPageMarkAll');
    if (markAll) {
        markAll.addEventListener('click', () => {
            fetch('/api/notifications/mark-all-read', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ audience: markAll.dataset.audience }),
            })
            // Reloaded rather than patched in place: the counts in the filter
            // chips, the topbar and the bell all change together, and a page
            // that updates three of the four is worse than one that reloads.
            .then(() => window.location.reload())
            .catch(() => {});
        });
    }
})();
</script>

@endsection
