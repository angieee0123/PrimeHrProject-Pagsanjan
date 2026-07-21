@extends('layouts.employee')

@section('title', 'Training · PRIME HRIS')

@push('styles')
    @vite('resources/css/employee/employeeTraining.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'Training'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content permanent-dashboard permanent-training glass-shell" data-fiscal-year="{{ date('Y') }}" data-flash-success="{{ session('success') ? '1' : '0' }}">

        @include('employee.notification.employeeNotification')

        @include('employee.topbar.trainingTopbar')

        @if(session('error'))
        <div style="background:#fdf0ef;border:1px solid #f5c6c3;color:#8e1e18;padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;font-weight:600;">
            {{ session('error') }}
        </div>
        @endif

        {{-- Stats row --}}
        <div class="stats-grid stats-grid-4 training-stats-grid">
            @include('employee.training.partials.stat-cards')
        </div>

        {{-- Category breakdown --}}
        @include('employee.training.partials.breakdown-panel')

        {{-- Training History Table --}}
        @include('employee.training.partials.training-history-table')

    </main>
            </div>

<div class="training-toast" id="trainingToast" role="alert" aria-live="polite"></div>

@include('employee.training.modals.addTrainingModal')
@include('employee.training.modals.flashSuccessModal')
@include('employee.training.modals.submitSuccessModal')
@include('employee.training.modals.viewCertModal')
@include('employee.training.modals.pdsExportModal')

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tesseract.js@5.1.0/dist/tesseract.min.js"></script>
<script src="{{ asset('js/training-certificate-parser.js') }}?v=2"></script>
    @vite('resources/js/employee/employeeTraining.js')
@endpush

@include('employee.chatbot.employeeChatbot')

@endsection
