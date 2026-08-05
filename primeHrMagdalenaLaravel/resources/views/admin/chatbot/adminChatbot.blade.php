<div class="admin-chatbot-ui">
@include('admin.chatbot.partials.fab-button')

<!-- Chatbot Window -->
<div class="chatbot-window" id="chatbotWindow" role="dialog" aria-labelledby="chatbotTitle" aria-hidden="true">
    @include('admin.chatbot.partials.chatbot-header')

    {{-- quick-actions is included inside messages: the starter prompts sit in
         the conversation rather than in a pinned strip above it. --}}
    @include('admin.chatbot.partials.messages')

    @include('admin.chatbot.partials.input-row')

    @include('admin.chatbot.partials.clear-confirm')
</div>
</div>

@push('scripts')
    @vite('resources/js/admin/chatbot/adminChatbot.js')
@endpush
