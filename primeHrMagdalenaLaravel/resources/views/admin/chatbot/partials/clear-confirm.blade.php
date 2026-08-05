{{-- In-panel confirmation, in place of the browser's confirm() dialog --}}
<div class="chat-confirm" id="chatClearConfirm">
    <div class="chat-confirm-card" role="alertdialog" aria-modal="true"
         aria-labelledby="chatClearConfirmTitle" aria-describedby="chatClearConfirmText">
        <div class="chat-confirm-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
            </svg>
        </div>
        <p class="chat-confirm-title" id="chatClearConfirmTitle">Clear this conversation?</p>
        <p class="chat-confirm-text" id="chatClearConfirmText"></p>
        <div class="chat-confirm-actions">
            <button type="button" class="chat-confirm-btn is-cancel" id="chatClearConfirmCancel" onclick="closeClearConfirm()">Keep it</button>
            <button type="button" class="chat-confirm-btn is-danger" onclick="confirmClearChatbotConversation()">Clear chat</button>
        </div>
    </div>
</div>
