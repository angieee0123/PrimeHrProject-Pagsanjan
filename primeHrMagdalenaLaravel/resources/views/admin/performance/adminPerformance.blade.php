@extends('layouts.app')

@section('content')

{{--
    Performance Management is switched off.

    This page used to render six hard-coded employees with invented ratings
    (Maria B. Santos, 4.8 / 5.0 …), a working Evaluate modal, and an Export
    button — none of it backed by anything. There is no performance table in
    this schema, so a submitted evaluation went into a JavaScript array and
    vanished on refresh, and the ratings on screen were fiction that read as
    record. The sample data has been removed rather than hidden: it is not
    sent to the browser at all now.
--}}

@include('admin.topbar.performanceTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell shell-notice">
    <x-module-unavailable
        module="Performance Management"
        reason="Employee evaluations are not stored anywhere in the system yet — there is no performance record to read from or write to." />
</div>

@endsection
