{{--
    Shared body for the full-page AI Assistant screen (employee/admin/mayor).
    Talks to the existing ChatbotController endpoints via resources/js/shared/aiAssistantPage.js.

    Props:
        $greeting     — string shown as the assistant's first message
        $quickActions — array of ['label' => ..., 'prompt' => ..., 'icon' => raw svg markup]
--}}
@php
    $greeting = $greeting ?? "Hello! I'm your PRIME HRIS AI Assistant. How can I help you today?";
    $quickActions = $quickActions ?? [];
@endphp

<div class="ai-assistant-page">
    @if(count($quickActions))
    <div class="chatbot-quick-actions">
        @foreach($quickActions as $qa)
        <button type="button" class="chatbot-quick-btn" onclick="aiPageQuickAsk({{ Illuminate\Support\Js::from($qa['prompt']) }})">
            {!! $qa['icon'] !!}
            {{ $qa['label'] }}
        </button>
        @endforeach
    </div>
    @endif

    <div class="chatbot-messages" id="ai-assistant-messages">
        <div class="chat-msg bot">
            <div class="chat-msg-avatar">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <div class="chat-msg-bubble">{{ $greeting }}</div>
        </div>
    </div>

    <div class="chatbot-input-row">
        <input type="text" id="ai-assistant-input" placeholder="Ask me anything about HR, leave, payroll, attendance..." onkeydown="if(event.key==='Enter') aiPageSend()">
        <button type="button" class="chatbot-send" onclick="aiPageSend()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
        </button>
    </div>
</div>

<script>window.aiAssistantGreeting = @json($greeting);</script>

@push('scripts')
    @vite('resources/js/shared/aiAssistantPage.js')
@endpush
