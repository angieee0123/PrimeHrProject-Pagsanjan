{{-- Starter prompts live in the conversation, not in a bar above it: they are
     only useful before the first question, and a pinned strip cost a permanent
     row of the panel. Removed by adminChatbot.js on first send. --}}
<div class="chat-suggest-wrap" id="chatSuggestions">
    <p class="chat-suggest-label">Try asking</p>
    <div class="chat-suggest" role="group" aria-label="Suggested questions">
        <button class="chatbot-quick-btn" onclick="sendPredefinedMessage('How many people work here?')">Total employees</button>
        <button class="chatbot-quick-btn" onclick="sendPredefinedMessage('Show me active employees')">Active staff</button>
        <button class="chatbot-quick-btn" onclick="sendPredefinedMessage('Who works in Mayor office?')">Mayor's Office</button>
        <button class="chatbot-quick-btn" onclick="sendPredefinedMessage('Find administrator')">Find employee</button>
        <button class="chatbot-quick-btn" onclick="sendPredefinedMessage('Pending leave approvals')">Pending approvals</button>
        <button class="chatbot-quick-btn" onclick="sendPredefinedMessage('How many are absent today?')">Absent today</button>
    </div>
</div>
