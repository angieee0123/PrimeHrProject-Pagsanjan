@extends('layouts.employee')

@section('title', 'AI Assistant · PRIME HRIS')

@push('styles')
    @vite('resources/css/shared/aiAssistant.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'AI Assistant'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content">
        @include('shared.aiAssistant.page', ['area' => 'employee', 'conversations' => $conversations])
    </main>
</div>
@endsection

@push('scripts')
    @vite('resources/js/shared/aiAssistant.js')
@endpush
