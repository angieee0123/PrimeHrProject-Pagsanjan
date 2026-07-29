@extends('layouts.app')

@push('styles')
    @vite('resources/css/shared/aiAssistantPage.css')
    {{-- The floating chat widget is included globally in layouts.app; hide it
         here so it doesn't duplicate this full-page screen. --}}
    <style>.chat-fab, .chatbot-window { display: none !important; }</style>
@endpush

@section('content')

<main class="glass-shell">

    <x-topbar title="AI Assistant">
        <x-slot:icon><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></x-slot:icon>
        <x-slot:subtitle>Ask about employees, attendance, leave, payroll, and HR policies</x-slot:subtitle>
        <x-slot:actions>
            <button type="button" class="banner-badge outline" style="cursor:pointer" onclick="aiPageClear()">Clear chat</button>
        </x-slot:actions>
    </x-topbar>

    @include('partials.aiAssistantBody', [
        'greeting' => "Hello! I'm the PRIME HRIS Assistant. I can help you with employee information, departments, attendance, leave, and payroll data. Try asking something like \"How many people work here?\" or \"Who's on leave today?\"",
        'quickActions' => [
            ['label' => 'Attendance Today', 'prompt' => "Who's absent today?", 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>'],
            ['label' => 'Pending Leaves', 'prompt' => 'Show pending leave applications', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'],
            ['label' => 'Employee Count', 'prompt' => 'How many employees do we have?', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>'],
            ['label' => 'Payroll', 'prompt' => 'Give me a payroll summary for this month', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" stroke="none"><text x="3" y="19" font-size="17" font-weight="bold" font-family="Arial, sans-serif">₱</text></svg>'],
        ],
    ])

</main>

@endsection
