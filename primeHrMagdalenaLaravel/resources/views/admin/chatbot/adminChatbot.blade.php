<div class="admin-chatbot-ui">
@include('admin.chatbot.partials.fab-button')

<!-- Chatbot Window -->
<div class="chatbot-window" id="chatbotWindow" style="display: none;">
    @include('admin.chatbot.partials.chatbot-header')

    @include('admin.chatbot.partials.quick-actions')

    @include('admin.chatbot.partials.messages')

    @include('admin.chatbot.partials.input-row')
</div>
</div>

@push('scripts')
    @vite('resources/js/admin/chatbot/adminChatbot.js')
@endpush
