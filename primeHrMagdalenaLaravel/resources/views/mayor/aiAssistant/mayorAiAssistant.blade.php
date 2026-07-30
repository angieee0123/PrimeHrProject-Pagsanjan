@extends('layouts.mayor')

@section('title', "AI Assistant · PRIME HRIS")

@push('styles')
    @vite('resources/css/shared/aiAssistant.css')
@endpush

@section('content')
@include('shared.aiAssistant.page', ['area' => 'mayor', 'conversations' => $conversations])
@endsection

@push('scripts')
    @vite('resources/js/shared/aiAssistant.js')
@endpush
