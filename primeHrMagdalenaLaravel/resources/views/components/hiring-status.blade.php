{{--
    One status, rendered as the application's standard badge.

    The label and the colour come from App\Services\HiringPresenter, because
    the hiring backend sends a code (`ON_HOLD`, `NOT_QUALIFIED`) and never a
    label — and printing the code would put SCREAMING_SNAKE_CASE in front of HR
    staff. One map also means the archive tab and the detail page cannot call
    the same state two different things.

    Usage:
        <x-hiring-status :value="$vacancy['status']" />
        <x-hiring-status kind="parse" :value="$profile['parse_status']" />
--}}
@props(['kind' => 'vacancy', 'value' => null])

@php
    $status = \App\Services\HiringPresenter::status($kind, $value);
@endphp

<span {{ $attributes->class(['badge-status', $status['badge']]) }}>{{ $status['label'] }}</span>
