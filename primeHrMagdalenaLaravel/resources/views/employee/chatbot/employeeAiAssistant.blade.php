@extends('layouts.employee')

@section('title', 'AI Assistant · PRIME HRIS')

@push('styles')
    @vite('resources/css/shared/aiAssistantPage.css')
@endpush

@section('content')
<div class="app-layout">

    @include('employee.topbar.mobileTopbar', [
        'mobileTopbarEyebrow' => 'Permanent Employee',
        'mobileTopbarTitle' => 'AI Assistant'
    ])

    <div class="mobile-overlay" id="mobile-overlay"></div>

    @include('employee.sidebar.employeeSidebar')

    <main class="main-content glass-shell">

        <x-topbar title="AI Assistant">
            <x-slot:icon><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></x-slot:icon>
            <x-slot:subtitle>Ask about leave, payslip, attendance, training, and HR policies</x-slot:subtitle>
            <x-slot:actions>
                <button type="button" class="banner-badge outline" style="cursor:pointer" onclick="aiPageClear()">Clear chat</button>
            </x-slot:actions>
        </x-topbar>

        @include('partials.aiAssistantBody', [
            'greeting' => "Hello! I'm your PRIME HRIS assistant. I can help you with leave requests, payslip inquiries, attendance records, training programs, and performance evaluations. How can I assist you today?",
            'quickActions' => [
                ['label' => 'Leave', 'prompt' => 'How do I file a leave request?', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'],
                ['label' => 'Payslip', 'prompt' => 'Check my payslip', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>'],
                ['label' => 'Attendance', 'prompt' => 'View my attendance', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'],
                ['label' => 'Training', 'prompt' => 'My training programs', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>'],
            ],
        ])

    </main>
</div>
@endsection
