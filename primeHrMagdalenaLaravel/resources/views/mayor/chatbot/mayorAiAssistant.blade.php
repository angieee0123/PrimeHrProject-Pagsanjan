@extends('layouts.mayor')

@section('title', "AI Assistant · PRIME HRIS")

@push('styles')
    @vite('resources/css/shared/aiAssistantPage.css')
@endpush

@section('content')

<main class="enterprise-hr-dashboard">

    <x-topbar title="AI Assistant" class="mayor-page-header">
        <x-slot:icon><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></x-slot:icon>
        <x-slot:subtitle>Ask about personnel, leave applications, and travel orders</x-slot:subtitle>
        <x-slot:actions>
            <button type="button" class="banner-badge outline" style="cursor:pointer" onclick="aiPageClear()">Clear chat</button>
        </x-slot:actions>
    </x-topbar>

    @include('partials.aiAssistantBody', [
        'greeting' => "Hello! I'm the PRIME HRIS Assistant. I can help you with personnel information, leave applications, and travel orders across the municipality. How can I assist you today?",
        'quickActions' => [
            ['label' => 'Personnel', 'prompt' => 'Give me a personnel overview', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>'],
            ['label' => 'Leave Applications', 'prompt' => 'Show pending leave applications', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>'],
            ['label' => 'Travel Orders', 'prompt' => 'Show pending travel orders', 'icon' => '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>'],
        ],
    ])

</main>

<style>
.mayor-page-header { margin-bottom: 18px; }
.mayor-page-header h2 { font-size: 19px; }
.mayor-page-header p { font-size: 12.5px; }
</style>

@endsection
