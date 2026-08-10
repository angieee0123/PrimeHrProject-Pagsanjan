@extends('layouts.app')

@section('content')

{{--
    Recruitment is switched off.

    This page used to render four hard-coded job postings with invented
    applicant counts, plus Post Job / Edit forms that validated their inputs
    and then closed without saving anything. There is no job-posting or
    applicant table in this schema. Posting a vacancy that is never stored is
    worse than no page at all, so the sample data has been removed rather
    than hidden — it is not sent to the browser at all now.
--}}

@include('admin.topbar.recruitmentTopbar')
@include('admin.notification.adminNotification')

<div class="glass-shell shell-notice">
    <x-module-unavailable
        module="Recruitment"
        reason="Job postings and applicants are not stored anywhere in the system yet." />
</div>

@endsection
