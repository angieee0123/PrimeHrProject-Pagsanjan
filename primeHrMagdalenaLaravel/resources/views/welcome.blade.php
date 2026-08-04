<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Municipal Government of Pagsanjan, Laguna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $__s = \App\Models\SystemAiSetting::current();
        $activeTheme = $__s->theme ?? 'default';
    @endphp
    <style>{!! \App\Services\SystemTheme::toCss($activeTheme, $__s->custom_theme_primary, $__s->theme_secondary, $__s->theme_accent, $__s->theme_muted) !!}</style>
</head>
<body>

<div class="pub-root">

    {{-- Gov Bar --}}
    <div class="pub-govbar">
        <span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:4px">
                <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
            </svg>
            Republic of the Philippines &nbsp;·&nbsp; Province of Laguna
        </span>
        <span>Official Website of the Municipal Government of Pagsanjan</span>
    </div>

    {{-- Navbar --}}
    <nav class="pub-nav">
        <div class="pub-logo">
            <div class="pub-logo-seal">
                <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                     onerror="this.style.display='none'"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover">
            </div>
            <div>
                <span class="pub-logo-name">Pagsanjan, Laguna</span>
                <span class="pub-logo-sub">Municipal Government</span>
            </div>
        </div>
        <div class="pub-nav-links">
            <a href="#services">Services</a>
            <a href="#announcements">Announcements</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
        </div>
        <a href="{{ route('login') }}" class="pub-hr-btn">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            Employee Portal
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
                    Official Municipal Government Portal
                </div>
                <h1 class="pub-hero-title">
                    Serving the People of<br>
                    <span class="pub-hero-highlight">Pagsanjan, Laguna</span>
                </h1>
                <p class="pub-hero-sub">
                    Access municipal services, announcements, and government information
                    from the Municipal Government of Pagsanjan, Province of Laguna.
                </p>
                <div class="pub-hero-actions">
                    <a href="#services" class="pub-btn-primary">Explore Services</a>
                    <button class="pub-btn-ghost" onclick="document.getElementById('chatbot-window').style.display='flex'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                        </svg>
                        Ask Our AI Assistant
                    </button>
                </div>
            </div>
            <div class="pub-hero-card">
                <div class="pub-hero-card-header">
                    <div class="pub-hero-card-dot active"></div>
                    <span>Municipal Services Portal</span>
                </div>
                <div class="pub-hero-stats">
                    <div class="pub-hstat">
                        <span class="pub-hstat-val">17</span>
                        <span class="pub-hstat-label">Offices &amp; Departments</span>
                    </div>
                    <div class="pub-hstat-divider"></div>
                    <div class="pub-hstat">
                        <span class="pub-hstat-val">348</span>
                        <span class="pub-hstat-label">Government Personnel</span>
                    </div>
                    <div class="pub-hstat-divider"></div>
                    <div class="pub-hstat">
                        <span class="pub-hstat-val">24/7</span>
                        <span class="pub-hstat-label">AI Chatbot Support</span>
                    </div>
                </div>
                <div class="pub-hero-card-tags">
                    @php
                    $check = '<svg class="pub-check" width="14" height="14" viewBox="0 0 24 24" fill="#15803d" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.1 14.3-3.6-3.6 1.4-1.4 2.2 2.2 4.9-4.9 1.4 1.4Z"/></svg>';
                    @endphp
                    <span class="pub-tag">{!! $check !!} BIR Compliant</span>
                    <span class="pub-tag">{!! $check !!} GSIS Ready</span>
                    <span class="pub-tag">{!! $check !!} CSC Accredited</span>
                    <span class="pub-tag">{!! $check !!} ARTA Compliant</span>
                </div>
            </div>
        </div>
    </section>

    {{-- Services --}}
    <section class="pub-section" id="services">
        <div class="pub-section-inner">
            <div class="pub-section-head">
                <span class="pub-eyebrow">MUNICIPAL SERVICES</span>
                <h2>What can we help you with?</h2>
                <p>Access the services offered by the Municipal Government of Pagsanjan, Laguna.</p>
            </div>
            @php
            $svgs = [
                'building'  => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="m9 15 2 2 4-4"/></svg>',
                'clipboard' => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 12h6"/><path d="M9 16h4"/></svg>',
                'heart'     => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
                'users'     => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
                'leaf'      => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>',
                'tool'      => '<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>',
            ];
            $serviceCategories = [
                'permits' => [
                    'label' => 'Permits & Registration',
                    'icon'  => 'building',
                    'items' => [
                        ['svg'=>'building','title'=>'Business Permits','desc'=>'Apply and renew business permits online through the Municipal Business Office.','office'=>'Municipal Business Office'],
                        ['svg'=>'clipboard','title'=>'Civil Registration','desc'=>'Request birth, marriage, and death certificates from the Civil Registrar.','office'=>'Office of the Civil Registrar'],
                    ],
                ],
                'health' => [
                    'label' => 'Health & Social Services',
                    'icon'  => 'heart',
                    'items' => [
                        ['svg'=>'heart','title'=>'Health Services','desc'=>'Access municipal health programs, consultations, and medical assistance.','office'=>'Municipal Health Office'],
                        ['svg'=>'users','title'=>'Social Welfare','desc'=>'MSWD programs for senior citizens, PWDs, and indigent families.','office'=>'Social Welfare & Development Office'],
                    ],
                ],
                'community' => [
                    'label' => 'Community Development',
                    'icon'  => 'leaf',
                    'items' => [
                        ['svg'=>'leaf','title'=>'Agricultural Support','desc'=>'Livelihood programs and agricultural assistance for local farmers.','office'=>'Municipal Agriculture Office'],
                        ['svg'=>'tool','title'=>'Infrastructure Projects','desc'=>'Updates on public works, road projects, and community infrastructure.','office'=>'Municipal Engineering Office'],
                    ],
                ],
            ];
            @endphp
            <div class="pub-svc-panel">
                <div class="pub-svc-nav" role="tablist">
                    @foreach($serviceCategories as $key => $cat)
                    <button type="button" class="pub-svc-nav-item{{ $loop->first ? ' active' : '' }}" data-category="{{ $key }}" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                        <span class="pub-svc-nav-icon">{!! $svgs[$cat['icon']] !!}</span>
                        <span class="pub-svc-nav-label">{{ $cat['label'] }}</span>
                        <span class="pub-svc-nav-count">{{ count($cat['items']) }}</span>
                    </button>
                    @endforeach
                </div>
                <div class="pub-svc-body">
                    @foreach($serviceCategories as $key => $cat)
                    <div class="pub-svc-panel-content{{ $loop->first ? ' active' : '' }}" data-panel="{{ $key }}">
                        <div class="pub-svc-panel-head">
                            <h4>{{ $cat['label'] }}</h4>
                            <span>{{ count($cat['items']) }} services</span>
                        </div>
                        @foreach($cat['items'] as $s)
                        <div class="pub-svc-row">
                            <span class="pub-svc-row-icon">{!! $svgs[$s['svg']] !!}</span>
                            <div class="pub-svc-row-body">
                                <span class="pub-svc-row-title">{{ $s['title'] }}</span>
                                <span class="pub-svc-row-desc">{{ $s['desc'] }}</span>
                                <span class="pub-svc-row-office">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-6h6v6"/></svg>
                                    {{ $s['office'] }}
                                </span>
                            </div>
                            <a href="#" class="pub-link-arrow">
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
                <span class="pub-eyebrow">LATEST UPDATES</span>
                <h2>Announcements &amp; Advisories</h2>
                <p>Stay informed with the latest news from the Municipal Government.</p>
            </div>
            @php
            $announcements = [
                ['date'=>'Jun 20, 2025','tag'=>'Advisory','title'=>'Schedule of Payment for Real Property Tax — 2nd Quarter 2025','excerpt'=>'Property owners are reminded to settle 2nd quarter real property tax payments on or before the deadline to avoid penalties.'],
                ['date'=>'Jun 18, 2025','tag'=>'Event','title'=>'Pagsanjan Founding Anniversary Celebration — June 25, 2025','excerpt'=>'Join the community celebration marking the founding anniversary of Pagsanjan with a parade, cultural shows, and local exhibits.'],
                ['date'=>'Jun 15, 2025','tag'=>'Program','title'=>'MSWD Livelihood Training Program — Open for Registration','excerpt'=>'Residents may now register for the MSWD livelihood training program offering skills development and starter kits.'],
                ['date'=>'Jun 10, 2025','tag'=>'Notice','title'=>'Water Interruption Advisory — Barangay Pinagsanjan Area','excerpt'=>'A scheduled water service interruption will affect Barangay Pinagsanjan Area residents; please store water in advance.'],
            ];
            $tagDotColors = ['advisory'=>'#c9a227','event'=>'#15803d','program'=>'#0b044d','notice'=>'#8e1e18'];
            $featured = $announcements[0];
            $moreUpdates = array_slice($announcements, 1);
            @endphp
            <div class="pub-news-grid">
                <div class="pub-news-feature">
                    <span class="pub-announce-tag pub-news-feature-tag {{ strtolower($featured['tag']) }}">{{ $featured['tag'] }}</span>
                    <h3 class="pub-news-feature-title">{{ $featured['title'] }}</h3>
                    <p class="pub-news-feature-meta">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ date('F j, Y', strtotime($featured['date'])) }}
                    </p>
                    <p class="pub-news-feature-desc">{{ $featured['excerpt'] }}</p>
                    <a href="#" class="pub-news-feature-btn">
                        View Full Advisory
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>

                <div class="pub-news-side">
                    <span class="pub-news-side-head">More Updates</span>
                    @foreach($moreUpdates as $a)
                    <a href="#" class="pub-news-side-item">
                        <span class="pub-news-side-dot" style="background:{{ $tagDotColors[strtolower($a['tag'])] }}"></span>
                        <span class="pub-news-side-body">
                            <span class="pub-news-side-title">{{ $a['title'] }}</span>
                            <span class="pub-news-side-date">{{ date('M j, Y', strtotime($a['date'])) }}</span>
                        </span>
                    </a>
                    @endforeach
                    <a href="#" class="pub-link-arrow pub-news-side-all">
                        View all announcements
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- About --}}
    <section class="pub-section" id="about">
        <div class="pub-section-inner">
            <div class="pub-section-head">
                <span class="pub-eyebrow">ABOUT THE MUNICIPALITY</span>
                <h2>Municipal Government of Pagsanjan</h2>
                <p>A brief overview of the municipality, its leadership, and its commitment to public service.</p>
            </div>

            <div class="pub-profile-frame">
                <div class="pub-profile-frame-header">
                    <span class="pub-profile-frame-tag">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 3 6v6c0 5 3.8 9 9 10 5.2-1 9-5 9-10V6Z"/></svg>
                        Official Municipal Profile
                    </span>
                    <span class="pub-profile-frame-meta">Local Government Unit &middot; Province of Laguna</span>
                </div>

                <div class="pub-about-profile">
                    <aside class="pub-profile-side">
                        <div class="pub-profile-seal">
                            <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" onerror="this.style.display='none'">
                        </div>
                        <p class="pub-profile-name">Pagsanjan, Laguna</p>
                        <span class="pub-profile-badge">1st Class Municipality</span>
                        <dl class="pub-profile-facts">
                            @foreach([['Province','Laguna'],['Region','IV-A (CALABARZON)'],['Barangays','16'],['Departments','17 Offices'],['Personnel','348 Employees'],['Population','40,000+']] as $fact)
                            <div class="pub-profile-fact">
                                <dt>{{ $fact[0] }}</dt>
                                <dd>{{ $fact[1] }}</dd>
                            </div>
                            @endforeach
                        </dl>
                    </aside>

                    <div class="pub-profile-main">
                        <h3>Home of the Famous <span>Pagsanjan Falls</span></h3>
                        <p>
                            Pagsanjan is a <strong>first-class municipality</strong> in the Province of Laguna, Philippines —
                            known as the <strong>"Shooting the Rapids" capital</strong>. Composed of 16 barangays, it serves
                            a population of over 40,000 residents across Region IV-A (CALABARZON).
                        </p>
                        <p>
                            The Municipal Government is committed to transparent, efficient, and responsive governance
                            through its 17 offices and departments, serving every Pagsanjeño.
                        </p>

                        <div class="pub-profile-statements">
                            <div class="pub-profile-statement">
                                <span class="pub-profile-statement-label">Our Vision</span>
                                <p>A progressive, peaceful, and self-reliant municipality with empowered citizens enjoying a high quality of life under a transparent and accountable local government.</p>
                            </div>
                            <div class="pub-profile-statement">
                                <span class="pub-profile-statement-label">Our Mission</span>
                                <p>To deliver efficient, effective, and equitable public services through good governance, community participation, and sustainable development programs for all Pagsanjeños.</p>
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
                <span class="pub-eyebrow">GET IN TOUCH</span>
                <h2>Contact Us</h2>
                <p>Reach out to the Municipal Government of Pagsanjan for inquiries, concerns, or assistance.</p>
            </div>
            <div class="pub-contact-card">

                <div class="pub-contact-office">
                    <div class="pub-contact-office-head">
                        <div class="pub-contact-office-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></div>
                        <div>
                            <p class="pub-contact-office-title">Municipal Hall</p>
                            <p class="pub-contact-office-sub">Pagsanjan, Laguna</p>
                        </div>
                    </div>
                    <ul class="pub-contact-lines">
                        @php
                        $contactItems = [
                            ['icon' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>', 'val' => 'Poblacion, Pagsanjan, Laguna 4008'],
                            ['icon' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>', 'val' => '(049) 501-0000 · (049) 501-0001'],
                            ['icon' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>', 'val' => 'info@pagsanjan.gov.ph'],
                            ['icon' => '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>', 'val' => 'Mon – Fri, 8:00 AM – 5:00 PM'],
                        ];
                        @endphp
                        @foreach($contactItems as $item)
                        <li>{!! $item['icon'] !!} {{ $item['val'] }}</li>
                        @endforeach
                    </ul>
                    <div class="pub-contact-office-note">Closed on weekends &amp; public holidays</div>
                </div>

                <form class="pub-contact-form" id="contact-form">
                    <div class="pub-contact-form-head">
                        <div>
                            <p class="pub-contact-form-title">Send us a Message</p>
                            <p class="pub-contact-form-sub">Fill out the form and our office will get back to you.</p>
                        </div>
                        <span class="pub-contact-form-badge">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            1–2 business days
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
                        Your information is kept confidential and used only to respond to your inquiry.
                    </p>
                </form>

            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="pub-cta-section">
        <div class="pub-cta-inner">
            <div class="pub-cta-text">
                <span class="pub-eyebrow light">PRIME HRIS</span>
                <h2>Are you a Municipal Government Employee?</h2>
                <p>The PRIME HRIS portal is exclusively for authorized employees of the Municipal Government of Pagsanjan, Laguna. Access your payroll, leave, and personnel records here.</p>
                <a href="{{ route('login') }}" class="pub-cta-btn">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                    Sign In to PRIME HRIS
                </a>
                <p class="pub-cta-note">Municipal Government employees only · Contact your administrator for access</p>
            </div>
            <div class="pub-cta-card">
                <div class="pub-cta-card-label">PRIME HRIS</div>
                <p class="pub-cta-card-sub">Personnel Records &amp; Information Management for Employees</p>
                <div class="pub-cta-features">
                    @php
                    $ctaCheck = '<svg class="pub-check" width="15" height="15" viewBox="0 0 24 24" fill="#5fd694" aria-hidden="true"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.1 14.3-3.6-3.6 1.4-1.4 2.2 2.2 4.9-4.9 1.4 1.4Z"/></svg>';
                    @endphp
                    <div class="pub-cta-feat">{!! $ctaCheck !!} Payroll Processing</div>
                    <div class="pub-cta-feat">{!! $ctaCheck !!} 201 File Management</div>
                    <div class="pub-cta-feat">{!! $ctaCheck !!} Leave &amp; Benefits</div>
                    <div class="pub-cta-feat">{!! $ctaCheck !!} DTR Monitoring</div>
                    <div class="pub-cta-feat">{!! $ctaCheck !!} BIR / GSIS / PhilHealth</div>
                    <div class="pub-cta-feat">{!! $ctaCheck !!} Payroll Reports</div>
                </div>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="pub-footer">
        <div class="pub-footer-inner">
            <div class="pub-footer-brand">
                <div class="pub-logo-seal sm">
                    <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                         onerror="this.style.display='none'"
                         style="width:28px;height:28px;border-radius:50%;object-fit:cover">
                </div>
                <div>
                    <span class="pub-footer-name">Municipal Government of Pagsanjan</span>
                    <span class="pub-footer-sub">Province of Laguna · Republic of the Philippines</span>
                </div>
            </div>
            <div class="pub-footer-links">
                <a href="#privacy">Privacy Policy</a>
                <a href="#terms">Terms of Use</a>
                <a href="#contact">Contact Us</a>
                <a href="#sitemap">Sitemap</a>
            </div>
            <p class="pub-footer-copy">© 2025 Municipal Government of Pagsanjan, Laguna. All rights reserved.</p>
        </div>
    </footer>

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
                    <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                         onerror="this.style.display='none'"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                </div>
                <div>
                    <p class="chatbot-name">Pagsanjan LGU Assistant</p>
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
            <button class="chatbot-quick-btn" onclick="quickAsk('What services do you offer?')">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/></svg>
                Services
            </button>
            <button class="chatbot-quick-btn" onclick="quickAsk('What are your office hours?')">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Hours
            </button>
            <button class="chatbot-quick-btn" onclick="quickAsk('How do I get a permit?')">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Permits
            </button>
            <button class="chatbot-quick-btn" onclick="quickAsk('Contact information?')">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                Contact
            </button>
        </div>

        <div class="chatbot-messages" id="chatbot-messages">
            <div class="chat-msg bot">
                <div class="chat-msg-avatar">
                    <img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo"
                         onerror="this.style.display='none'"
                         style="width:100%;height:100%;object-fit:cover;border-radius:50%">
                </div>
                <div class="chat-msg-bubble">Hello! I'm the Pagsanjan LGU Assistant. I can help you with information about municipal services, requirements, fees, and procedures. How can I assist you today?</div>
            </div>
        </div>

        <div class="chatbot-input-row">
            <input type="text" id="chat-input" placeholder="Ask about our services..." onkeydown="if(event.key==='Enter') sendMessage()">
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
        avatar.innerHTML = '<img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';
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
    wrapper.innerHTML = '<div class="chat-msg-avatar"><img src="/municipal-of-pagsanjan-logo.jpg" alt="Pagsanjan Logo" style="width:100%;height:100%;object-fit:cover;border-radius:50%"></div><div class="chat-typing-indicator"><span></span><span></span><span></span></div>';
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
    addMessage("Hello! I'm the Pagsanjan LGU Assistant. I can help you with information about municipal services, requirements, fees, and procedures. How can I assist you today?", false);
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
