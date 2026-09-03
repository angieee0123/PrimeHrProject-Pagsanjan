<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Live refresh
    |--------------------------------------------------------------------------
    |
    | How often an open page asks the server whether anything new arrived.
    | There is no broadcast driver configured in this project, so the bell is
    | polled — deliberately slowly. One municipality's worth of staff on a
    | shared host is the budget this number is set against; dropping it to a
    | couple of seconds turns every open tab into a permanent request stream
    | for a page that changes a handful of times a day.
    |
    */

    'poll_seconds' => (int) env('NOTIFICATION_POLL_SECONDS', 20),

    /*
    |--------------------------------------------------------------------------
    | Panel size
    |--------------------------------------------------------------------------
    |
    | How many rows the dropdown holds. Everything older is reached through
    | "View all notifications", which paginates — the bell is a glance, not an
    | archive.
    |
    */

    'panel_limit' => 8,

    'history_per_page' => 20,

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | `notifications:prune` deletes *read* notifications older than this many
    | days. Unread ones are never pruned however old they are: an unread
    | notification is work nobody has looked at, and deleting it on a timer
    | would silently drop it. Set `read_days` to 0 to keep everything.
    |
    | This is a convenience copy of an event, not the record of it — the leave
    | application, travel order and audit log all outlive the notification that
    | announced them — which is what makes a retention window safe here.
    |
    */

    'retention' => [
        'read_days' => (int) env('NOTIFICATION_RETENTION_DAYS', 180),
    ],

];
