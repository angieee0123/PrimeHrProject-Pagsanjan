<div class="chatbot-header">
    <div class="chatbot-header-left">
        <div class="chatbot-avatar">
            <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div>
            <p class="chatbot-name" id="chatbotTitle">PRIME HRIS Assistant</p>
            <p class="chatbot-status"><span class="chatbot-status-dot" aria-hidden="true"></span>Online</p>
        </div>
    </div>
    <div class="chatbot-header-right">
        <button class="chatbot-clear" onclick="clearChatbotConversation()" title="Clear conversation" aria-label="Clear conversation">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                <path d="M10 11v6"/>
                <path d="M14 11v6"/>
                <path d="M9 6V4h6v2"/>
            </svg>
        </button>
        {{-- Expand opens the full AI Assistant page, not a taller floating panel.
             It used to toggle `.fullscreen-mode`, which only stretched the widget
             to 560px — still the cut-down surface, with no conversation list, no
             search and no history, which is exactly what someone reaching for
             "expand" is after. The thread carries over: the page and the chathead
             are one conversation (AiConversationStore::continueLatestOrStart). --}}
        <a class="chatbot-fullscreen" id="fullscreenButton" href="{{ route('admin.ai-assistant') }}"
           title="Open full AI Assistant" aria-label="Open the full AI Assistant page">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
            </svg>
        </a>
        <button class="chatbot-close" onclick="toggleChatbot()" title="Close" aria-label="Close assistant">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>
</div>
