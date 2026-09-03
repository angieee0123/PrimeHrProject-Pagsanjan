{{-- One notification, as it appears in the bell and on the history page.

     The card is an <a>, not a div with a click handler, and it points at
     `notifications.open` rather than at the record. That route marks the row
     read and then decides where the click lands, re-reading the link from the
     row and refusing any area this account may not enter — so the destination
     is never something the page handed over, and a copied link is useless to
     anybody else. Being a real link is also what makes the card keyboard-
     reachable and middle-clickable, which an onclick div never was.

     Expects $notif. Optional: $showActions (history page's read/delete row). --}}
@php
    $category = $notif->category();
@endphp
<a class="notif-card {{ $notif->is_read ? '' : 'is-unread' }}"
   href="{{ route('notifications.open', $notif->id) }}"
   data-notif-id="{{ $notif->id }}"
   aria-label="{{ $notif->is_read ? '' : 'Unread. ' }}{{ $notif->title }}">
    <span class="notif-avatar" style="background:linear-gradient(135deg,{{ $category['hue'][0] }},{{ $category['hue'][1] }})">
        <svg width="16" height="16" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">{!! $category['icon'] !!}</svg>
    </span>
    <span class="notif-content">
        <span class="notif-meta">
            <span class="notif-chip">{{ $category['label'] }}</span>
            <span class="notif-time">{{ $notif->time_ago }}</span>
        </span>
        <span class="notif-title">{{ $notif->title }}</span>
        <span class="notif-msg">{{ $notif->message }}</span>
    </span>
    {{-- The unread marker is a shape as well as a background tint: a colour
         difference alone is not a distinction every reader can see. --}}
    <span class="notif-dot" aria-hidden="true"></span>
</a>
@if(!empty($showActions))
<span class="notif-row-actions">
    <button type="button" class="notif-row-btn" data-notif-toggle="{{ $notif->id }}" data-notif-read="{{ $notif->is_read ? '1' : '0' }}">
        {{ $notif->is_read ? 'Mark unread' : 'Mark read' }}
    </button>
    <button type="button" class="notif-row-btn danger" data-notif-delete="{{ $notif->id }}">Delete</button>
</span>
@endif
