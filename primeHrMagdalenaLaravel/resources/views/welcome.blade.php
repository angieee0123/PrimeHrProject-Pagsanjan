{{--
    Public welcome page.

    Every word here used to be a literal in this file — the announcements, the
    service catalogue, the vision and mission, the phone numbers — so changing
    one advisory meant editing Blade and redeploying. The copy now comes from
    SiteContentService, editable at Admin → Website Content, and this file is
    only the layout.

    Two things stay out of the editor on purpose:
      · the SVGs, resolved by name by the x-public-icon component, so a
        saved value can never inject markup into a page anonymous visitors read;
      · the two hero counts, which are read from the departments and employees
        tables rather than typed, so the public page cannot disagree with the
        HR system it sits on top of.
--}}
@php
    use App\Services\SiteContentService;

    $c        = SiteContentService::all();
    $stats    = SiteContentService::liveStats();
    $brand    = $c['brand'];
    $hero     = $c['hero'];
    $services = $c['services'];
    $news     = $c['announcements'];
    $about    = $c['about'];
    $contact  = $c['contact'];
    $cta      = $c['cta'];
    $footer   = $c['footer'];
    $bot      = $c['chatbot'];
    $logo     = SiteContentService::logoUrl();
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $footer['name'] ?: 'Municipal Government of Pagsanjan, Laguna' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- The active palette. Organisation-wide: this page has no signed-in
         viewer to carry a personal theme, so it is the global one. --}}
    <style>{!! \App\Services\SystemTheme::activeCss() !!}</style>
</head>
<body>

<div class="pub-root">

    <x-public-govbar />

    {{-- Navbar --}}
    <nav class="pub-nav">
        <x-public-brand />
        <div class="pub-nav-links">
            @foreach($brand['nav_links'] as $link)
                <a href="{{ $link['anchor'] }}">{{ $link['label'] }}</a>
            @endforeach
        </div>
        <a href="{{ route('login') }}" class="pub-hr-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            {{ $brand['portal_label'] }}
        </a>
    </nav>

    {{-- Hero --}}
    <section class="pub-hero">
        <div class="pub-hero-inner">
            <div class="pub-hero-text">
                <div class="pub-hero-badge">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:6px">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    {{ $hero['badge'] }}
                </div>
                <h1 class="pub-hero-title">
                    {{ $hero['title'] }}<br>
                    <span class="pub-hero-highlight">{{ $hero['title_highlight'] }}</span>
                </h1>
                <p class="pub-hero-sub">{{ $hero['subtitle'] }}</p>
                <div class="pub-hero-actions">
                    <a href="#services" class="pub-btn-primary">{{ $hero['primary_label'] }}</a>
                    <button class="pub-btn-ghost" onclick="document.getElementById('chatbot-window').style.display='flex'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        {{ $hero['secondary_label'] }}
                    </button>
                </div>
            </div>
            <div class="pub-hero-card">
                <div class="pub-hero-card-header">
                    <div class="pub-hero-card-dot active"></div>
                    <span>{{ $hero['card_title'] }}</span>
                </div>
                {{-- The first two figures are counted, not typed. A figure whose
                     table could not be read is omitted rather than shown as 0 —
                     "0 Government Personnel" on the municipality's homepage is
                     worse than one fewer statistic. --}}
                @php
                    $heroStats = [];
                    if ($stats['departments'] !== null) {
                        $heroStats[] = [number_format($stats['departments']), $hero['stat_departments_label']];
                    }
                    if ($stats['personnel'] !== null) {
                        $heroStats[] = [number_format($stats['personnel']), $hero['stat_personnel_label']];
                    }
                    if (filled($hero['stat_extra_value'])) {
                        $heroStats[] = [$hero['stat_extra_value'], $hero['stat_extra_label']];
                    }
                @endphp
                <div class="pub-hero-stats">
                    @foreach($heroStats as $i => [$value, $label])
                        @if($i > 0)<div class="pub-hstat-divider"></div>@endif
                        <div class="pub-hstat">
                            <span class="pub-hstat-val">{{ $value }}</span>
                            <span class="pub-hstat-label">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="pub-hero-card-tags">
                    @php
                    $check = '<svg class="pub-check" width="14" height="14" viewBox="0 0 24 24" fill="var(--theme-success)" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.1 14.3-3.6-3.6 1.4-1.4 2.2 2.2 4.9-4.9 1.4 1.4Z"/></svg>';
                    @endphp
                    @foreach($hero['tags'] as $tag)
                        <span class="pub-tag">{!! $check !!} {{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="pub-section" id="services">
        <div class="pub-section-inner">
            <div class="pub-section-head">
                <span class="pub-eyebrow">{{ $services['eyebrow'] }}</span>
                <h2>{{ $services['heading'] }}</h2>
                <p>{{ $services['sub'] }}</p>
            </div>
            <div class="pub-svc-panel">
                <div class="pub-svc-nav" role="tablist">
                    @foreach($services['categories'] as $i => $cat)
                    <button type="button" class="pub-svc-nav-item{{ $i === 0 ? ' active' : '' }}" data-category="cat{{ $i }}" role="tab" aria-selected="{{ $i === 0 ? 'true' : 'false' }}">
                        <span class="pub-svc-nav-icon"><x-public-icon :name="$cat['icon']" /></span>
                        <span class="pub-svc-nav-label">{{ $cat['label'] }}</span>
                        <span class="pub-svc-nav-count">{{ count($cat['items'] ?? []) }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="pub-svc-body">
                    @foreach($services['categories'] as $i => $cat)
                    <div class="pub-svc-panel-content{{ $i === 0 ? ' active' : '' }}" data-panel="cat{{ $i }}">
                        <div class="pub-svc-panel-head">
                            <h4>{{ $cat['label'] }}</h4>
                            <span>{{ count($cat['items'] ?? []) }} services</span>
                        </div>
                        @foreach($cat['items'] ?? [] as $s)
                        <div class="pub-svc-row">
                            <span class="pub-svc-row-icon"><x-public-icon :name="$s['icon']" /></span>
                            <div class="pub-svc-row-body">
                                <span class="pub-svc-row-title">{{ $s['title'] }}</span>
                                <span class="pub-svc-row-desc">{{ $s['desc'] }}</span>
                                @if(filled($s['office'] ?? null))
                                <span class="pub-svc-row-office">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
                                    {{ $s['office'] }}
                                </span>
                                @endif
                            </div>
                            <a href="#contact" class="pub-link-arrow">
                                Request
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Announcements --}}
    <section class="pub-section alt" id="announcements">
        <div class="pub-section-inner">
            <div class="pub-section-head">
                <span class="pub-eyebrow">{{ $news['eyebrow'] }}</span>
                <h2>{{ $news['heading'] }}</h2>
                <p>{{ $news['sub'] }}</p>
            </div>
            @php
                // Newest first, so an administrator does not have to keep the
                // list in order by hand for the feature slot to be the latest.
                $items = collect($news['items'] ?? [])
                    ->sortByDesc(fn ($a) => $a['date'] ?? '')
                    ->values();
                $featured    = $items->first();
                $moreUpdates = $items->slice(1);
            @endphp
            @if($featured)
            <div class="pub-news-grid">
                <div class="pub-news-feature">
                    <span class="pub-announce-tag pub-news-feature-tag {{ strtolower($featured['tag']) }}">{{ $featured['tag'] }}</span>
                    <h3 class="pub-news-feature-title">{{ $featured['title'] }}</h3>
                    <p class="pub-news-feature-meta">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ date('F j, Y', strtotime($featured['date'])) }}
                    </p>
                    <p class="pub-news-feature-desc">{{ $featured['excerpt'] }}</p>
                </div>

                <div class="pub-news-side">
                    <span class="pub-news-side-head">{{ $news['side_heading'] }}</span>
                    @forelse($moreUpdates as $a)
                    <span class="pub-news-side-item">
                        <span class="pub-news-side-dot pub-tag-{{ strtolower($a['tag']) }}"></span>
                        <span class="pub-news-side-body">
                            <span class="pub-news-side-title">{{ $a['title'] }}</span>
                            <span class="pub-news-side-date">{{ date('M j, Y', strtotime($a['date'])) }}</span>
                        </span>
                    </span>
                    @empty
                    <p class="pub-news-side-empty">No other updates at the moment.</p>
                    @endforelse
                </div>
            </div>
            @else
            <p class="pub-news-none">There are no announcements at the moment. Please check back soon.</p>
            @endif
        </div>
    </section>

    {{-- About --}}
    <section class="pub-section" id="about">
        <div class="pub-section-inner">
            <div class="pub-section-head">
                <span class="pub-eyebrow">{{ $about['eyebrow'] }}</span>
                <h2>{{ $about['heading'] }}</h2>
                <p>{{ $about['sub'] }}</p>
            </div>

            <div class="pub-profile-frame">
                <div class="pub-profile-frame-header">
                    <span class="pub-profile-frame-tag">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 6v6c0 5 3.8 9 9 10 5.2-1 9-5 9-10V6Z"/></svg>
                        {{ $about['frame_tag'] }}
                    </span>
                    <span class="pub-profile-frame-meta">{{ $about['frame_meta'] }}</span>
                </div>

                <div class="pub-about-profile">
                    <aside class="pub-profile-side">
                        <div class="pub-profile-seal">
                            <img src="{{ $logo }}" alt="Pagsanjan Logo" onerror="this.style.display='none'">
                        </div>
                        <p class="pub-profile-name">{{ $about['profile_name'] }}</p>
                        <span class="pub-profile-badge">{{ $about['profile_badge'] }}</span>
                        <dl class="pub-profile-facts">
                            @foreach($about['facts'] as $fact)
                            <div class="pub-profile-fact">
                                <dt>{{ $fact['label'] }}</dt>
                                <dd>{{ $fact['value'] }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </aside>

                    <div class="pub-profile-main">
                        <h3>{{ $about['body_heading'] }} <span>{{ $about['body_highlight'] }}</span></h3>
                        @foreach($about['paragraphs'] as $p)
                            <p>{{ $p }}</p>
                        @endforeach

                        <div class="pub-profile-statements">
                            <div class="pub-profile-statement">
                                <span class="pub-profile-statement-label">{{ $about['vision_label'] }}</span>
                                <p>{{ $about['vision'] }}</p>
                            </div>
                            <div class="pub-profile-statement">
                                <span class="pub-profile-statement-label">{{ $about['mission_label'] }}</span>
                                <p>{{ $about['mission'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section class="pub-section alt" id="contact">
        <div class="pub-section-inner">
            <div class="pub-section-head">
                <span class="pub-eyebrow">{{ $contact['eyebrow'] }}</span>
                <h2>{{ $contact['heading'] }}</h2>
                <p>{{ $contact['sub'] }}</p>
            </div>
            <div class="pub-contact-card">

                <div class="pub-contact-office">
                    <div class="pub-contact-office-head">
                        <div class="pub-contact-office-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                        <div>
                            <p class="pub-contact-office-title">{{ $contact['office_title'] }}</p>
                            <p class="pub-contact-office-sub">{{ $contact['office_sub'] }}</p>
                        </div>
                    </div>
                    @php
                        $pin   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>';
                        $tel   = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>';
                        $mail  = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>';
                        $clock = '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>';
                    @endphp
                    <ul class="pub-contact-lines">
                        @if(filled($contact['address'])) <li>{!! $pin !!} {{ $contact['address'] }}</li> @endif
                        @if(filled($contact['phone']))   <li>{!! $tel !!} {{ $contact['phone'] }}</li> @endif
                        @if(filled($contact['email']))   <li>{!! $mail !!} {{ $contact['email'] }}</li> @endif
                        @if(filled($contact['hours']))   <li>{!! $clock !!} {{ $contact['hours'] }}</li> @endif
                    </ul>
                    @if(filled($contact['closed_note']))
                    <div class="pub-contact-office-note">{{ $contact['closed_note'] }}</div>
                    @endif
                </div>

                <form class="pub-contact-form" id="contact-form">
                    <div class="pub-contact-form-head">
                        <div>
                            <p class="pub-contact-form-title">{{ $contact['form_title'] }}</p>
                            <p class="pub-contact-form-sub">{{ $contact['form_sub'] }}</p>
                        </div>
                        <span class="pub-contact-form-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ $contact['form_badge'] }}
                        </span>
                    </div>
                    <div class="pub-contact-row">
                        <div class="pub-contact-field">
                            <label>Full Name</label>
                            <input type="text" placeholder="Your full name" required>
                        </div>
                        <div class="pub-contact-field">
                            <label>Email Address</label>
                            <input type="email" placeholder="your@email.com" required>
                        </div>
                    </div>
                    <div class="pub-contact-field">
                        <label>Subject</label>
                        <input type="text" placeholder="e.g. Business Permit Inquiry" required>
                    </div>
                    <div class="pub-contact-field">
                        <label>Message</label>
                        <textarea rows="5" placeholder="Type your message here..." required></textarea>
                    </div>
                    <button type="submit" class="pub-btn-primary" style="width:100%;justify-content:center">
                        Send Message
                    </button>
                    <p class="pub-contact-form-privacy">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        {{ $contact['form_privacy'] }}
                    </p>
                </form>

            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="pub-cta-section">
        <div class="pub-cta-inner">
            <div class="pub-cta-text">
                <span class="pub-eyebrow light">{{ $cta['eyebrow'] }}</span>
                <h2>{{ $cta['heading'] }}</h2>
                <p>{{ $cta['text'] }}</p>
                <a href="{{ route('login') }}" class="pub-cta-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    {{ $cta['button_label'] }}
                </a>
                <p class="pub-cta-note">{{ $cta['note'] }}</p>
            </div>
            <div class="pub-cta-card">
                <div class="pub-cta-card-label">{{ $cta['card_label'] }}</div>
                <p class="pub-cta-card-sub">{{ $cta['card_sub'] }}</p>
                <div class="pub-cta-features">
                    @php
                    $ctaCheck = '<svg class="pub-check" width="15" height="15" viewBox="0 0 24 24" fill="#5fd694" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.1 14.3-3.6-3.6 1.4-1.4 2.2 2.2 4.9-4.9 1.4 1.4Z"/></svg>';
                    @endphp
                    @foreach($cta['features'] as $feature)
                        <div class="pub-cta-feat">{!! $ctaCheck !!} {{ $feature }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <x-public-footer :links="true" />

    {{-- AI Chatbot FAB --}}
    <button class="chat-fab" id="chat-fab" onclick="toggleChat()" title="AI Assistant">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <span class="chat-fab-badge" id="chat-fab-badge">AI</span>
    </button>

    {{-- Chatbot Window --}}
    <div class="chatbot-window" id="chatbot-window" style="display:none">
        <div class="chatbot-header">
            <div class="chatbot-header-left">
                <div class="chatbot-avatar">
                    <img src="{{ $logo }}" alt="Pagsanjan Logo"
                         onerror="this.style.display='none'"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                </div>
                <div>
                    <p class="chatbot-name">{{ $bot['name'] }}</p>
                    <p class="chatbot-status">● Online</p>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
                <button class="chatbot-clear" onclick="clearChat()" title="Clear conversation">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                    </svg>
                </button>
                <button class="chatbot-close" onclick="toggleChat()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
        </div>

        <div class="chatbot-quick-actions">
            @foreach($bot['quick_actions'] as $qa)
            <button class="chatbot-quick-btn" onclick="quickAsk(this.dataset.q)" data-q="{{ $qa['question'] }}">
                <x-public-icon :name="$qa['icon']" size="12" />
                {{ $qa['label'] }}
            </button>
            @endforeach
        </div>

        <div class="chatbot-messages" id="chatbot-messages">
            <div class="chat-msg bot">
                <div class="chat-msg-avatar">
                    <img src="{{ $logo }}" alt="Pagsanjan Logo"
                         onerror="this.style.display='none'"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                </div>
                <div class="chat-msg-bubble">{{ $bot['greeting'] }}</div>
            </div>
        </div>

        <div class="chatbot-input-row">
            <input type="text" id="chat-input" placeholder="{{ $bot['placeholder'] }}" onkeydown="if(event.key==='Enter') sendMessage()">
            <button class="chatbot-send" onclick="sendMessage()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </div>
    </div>

</div>

<script>
const CHAT_API = 'http://127.0.0.1:5000/chat';
// The greeting is editable copy, so the "clear chat" reset reads it from the
// page rather than keeping a second copy in this script that would drift.
const CHAT_GREETING = @json($bot['greeting']);
// The seal is admin-uploadable, so the avatars this script builds read it from
// here rather than repeating a path that would not follow an upload.
const LOGO_URL = @json($logo);

function getTimestamp() {
    return new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function toggleChat() {
    const win = document.getElementById('chatbot-window');
    const badge = document.getElementById('chat-fab-badge');
    const isOpen = win.style.display === 'flex';
    win.style.display = isOpen ? 'none' : 'flex';
    badge.style.display = isOpen ? 'block' : 'none';
}

function addMessage(text, isUser, followUps = [], fullResponse = null) {
    const container = document.getElementById('chatbot-messages');

    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg ' + (isUser ? 'user' : 'bot');

    if (!isUser) {
        const avatar = document.createElement('div');
        avatar.className = 'chat-msg-avatar';
        avatar.innerHTML = '<img src="' + LOGO_URL + '" alt="Pagsanjan Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';
        wrapper.appendChild(avatar);
    }

    const bubble = document.createElement('div');
    bubble.className = 'chat-msg-bubble';

    // render **bold** markdown and newlines
    let html = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
    const ts = document.createElement('span');
    ts.className = 'chat-ts';
    ts.textContent = getTimestamp();
    bubble.innerHTML = html;
    bubble.appendChild(ts);
    wrapper.appendChild(bubble);
    container.appendChild(wrapper);

    // See More / See Less toggle
    if (!isUser && fullResponse && fullResponse !== text) {
        const toggleWrap = document.createElement('div');
        toggleWrap.className = 'chat-toggle-wrap';
        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'chat-toggle-btn';
        toggleBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> See More';
        toggleBtn.onclick = () => {
            if (toggleBtn.dataset.open !== 'true') {
                bubble.innerHTML = fullResponse.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>').replace(/\n/g, '<br>');
                bubble.appendChild(ts);
                toggleBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg> See Less';
                toggleBtn.dataset.open = 'true';
            } else {
                bubble.innerHTML = html;
                bubble.appendChild(ts);
                toggleBtn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg> See More';
                toggleBtn.dataset.open = 'false';
            }
        };
        toggleWrap.appendChild(toggleBtn);
        container.appendChild(toggleWrap);
    }

    // Follow-up question buttons
    if (!isUser && followUps.length > 0) {
        const fuWrap = document.createElement('div');
        fuWrap.className = 'chat-followups';
        const label = document.createElement('p');
        label.className = 'chat-followup-label';
        label.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right:5px;vertical-align:middle"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>You might also want to ask:';
        fuWrap.appendChild(label);
        followUps.forEach(q => {
            const btn = document.createElement('button');
            btn.className = 'chat-followup-btn';
            btn.textContent = q;
            btn.onclick = () => { document.getElementById('chat-input').value = q; sendMessage(); };
            fuWrap.appendChild(btn);
        });
        container.appendChild(fuWrap);
    }

    container.scrollTop = container.scrollHeight;
}

function showTyping() {
    const container = document.getElementById('chatbot-messages');
    const wrapper = document.createElement('div');
    wrapper.className = 'chat-msg bot';
    wrapper.id = 'chat-typing';
    wrapper.innerHTML = '<div class="chat-msg-avatar"><img src="' + LOGO_URL + '" alt="Pagsanjan Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%"></div><div class="chat-typing-indicator"><span></span><span></span><span></span></div>';
    container.appendChild(wrapper);
    container.scrollTop = container.scrollHeight;
}

function removeTyping() {
    const el = document.getElementById('chat-typing');
    if (el) el.remove();
}

function clearChat() {
    if (!confirm('Clear the conversation?')) return;
    const container = document.getElementById('chatbot-messages');
    container.innerHTML = '';
    addMessage(CHAT_GREETING, false);
}

function quickAsk(question) {
    document.getElementById('chat-input').value = question;
    sendMessage();
}

async function sendMessage() {
    const input = document.getElementById('chat-input');
    const text = input.value.trim();
    if (!text) return;

    addMessage(text, true);
    input.value = '';
    showTyping();

    try {
        const res = await fetch(CHAT_API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ message: text })
        });
        const data = await res.json();
        removeTyping();
        if (data.error) {
            addMessage('Sorry, something went wrong. Please try again.', false);
        } else {
            addMessage(data.response, false, data.follow_up_questions || [], data.full_response || null);
        }
    } catch (e) {
        removeTyping();
        addMessage('Sorry, I could not connect to the assistant server. Please make sure the chatbot service is running.', false);
    }
}

document.getElementById('contact-form').addEventListener('submit', function(e) {
    e.preventDefault();
    alert('Message sent! We will get back to you within 1–2 business days.');
    this.reset();
});

window.addEventListener('scroll', function() {
    const fab = document.getElementById('chat-fab');
    const scrollY = window.scrollY + window.innerHeight;
    const docH = document.documentElement.scrollHeight;
    fab.classList.toggle('chat-fab-light', scrollY > docH - 400);
}, { passive: true });

document.querySelectorAll('.pub-svc-nav-item').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const category = btn.dataset.category;
        document.querySelectorAll('.pub-svc-nav-item').forEach(function(b) {
            b.classList.remove('active');
            b.setAttribute('aria-selected', 'false');
        });
        document.querySelectorAll('.pub-svc-panel-content').forEach(function(p) {
            p.classList.remove('active');
        });
        btn.classList.add('active');
        btn.setAttribute('aria-selected', 'true');
        document.querySelector('.pub-svc-panel-content[data-panel="' + category + '"]').classList.add('active');
    });
});
</script>

</body>
</html>
