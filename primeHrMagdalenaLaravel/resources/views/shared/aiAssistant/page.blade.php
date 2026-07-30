{{-- Full-page AI Assistant UI, shared by admin/employee/mayor. All content is
     rendered client-side from `data-initial-conversations` by aiAssistant.js so
     the list-rendering logic lives in exactly one place. --}}
@php
    $area = $area ?? 'admin';
@endphp

<div class="ai-page-outer">
@include($area . '.topbar.aiAssistantTopbar')

<div class="ai-page" id="ai-page"
     data-send-url="{{ route($area . '.ai-assistant.send') }}"
     data-search-url="{{ route($area . '.ai-assistant.search') }}"
     data-conversations-url="{{ url($area . '/ai-assistant/conversations') }}"
     data-initial-conversations='@json($conversations->map(fn ($c) => ["id" => $c->id, "title" => $c->title, "updated_at" => optional($c->updated_at)->toIso8601String()])->values())'>

    <button type="button" class="ai-page-mobile-list-toggle" id="ai-mobile-list-toggle" aria-label="Toggle conversation list">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <aside class="ai-page-sidebar" id="ai-page-sidebar">
        <div class="ai-page-sidebar-header">
            <h2>AI Assistant</h2>
            <button type="button" class="ai-page-new-btn" id="ai-new-chat-btn" title="New chat">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                New Chat
            </button>
        </div>
        <div class="ai-page-search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" id="ai-search-input" placeholder="Search your conversations...">
        </div>
        <div class="ai-page-conversations" id="ai-conversations-list"></div>
    </aside>

    <section class="ai-page-main">
        <div class="ai-page-messages" id="ai-messages">
            <div class="ai-page-welcome" id="ai-welcome">
                <div class="ai-page-welcome-icon">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3l1.6 4.6L18 9l-4.4 1.4L12 15l-1.6-4.6L6 9l4.4-1.4z"/><path d="M19 15l.8 2.2L22 18l-2.2.8L19 21l-.8-2.2L16 18l2.2-.8z"/></svg>
                </div>
                <h3>PRIME HRIS Assistant</h3>
                <p>Ask about employees, attendance, leave balances, payroll, or HR policies. Every conversation here is saved to your account and searchable later.</p>
            </div>
        </div>
        <form class="ai-page-input-row" id="ai-input-form" autocomplete="off">
            <input type="text" id="ai-input" placeholder="Ask something..." autocomplete="off">
            <button type="submit" id="ai-send-btn" aria-label="Send">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
            </button>
        </form>
    </section>
</div>
</div>
