@extends('layouts.app')

@push('styles')
    @vite('resources/css/shared/aiAssistant.css')
@endpush

@section('content')
@include('shared.aiAssistant.page', ['area' => 'admin', 'conversations' => $conversations])
@endsection

@push('scripts')
    @vite('resources/js/shared/aiAssistant.js')
@endpush
