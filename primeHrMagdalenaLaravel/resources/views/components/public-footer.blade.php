{{--
    The footer shared by the welcome page and the three sign-in screens.

    `links` is the only difference between them: the welcome page carries the
    Privacy / Terms / Sitemap row, the auth screens do not. Everything else —
    the seal, the organisation name, the sub-line, the copyright — was four
    hard-coded copies, three of which ignored Website Content entirely and
    still read "© 2025".

    The year is rendered, never stored: a stored year is a year that goes
    stale on 1 January and nobody notices until somebody points at the
    homepage.
--}}
@props(['links' => false, 'class' => ''])

@php $footer = \App\Services\SiteContentService::section('footer'); @endphp

<footer class="pub-footer {{ $class }}">
    <div class="pub-footer-inner">
        <div class="pub-footer-brand">
            <div class="pub-logo-seal sm">
                <img src="{{ \App\Services\SiteContentService::logoUrl() }}" alt="{{ $footer['name'] }}"
                     onerror="this.style.display='none'"
                     style="width:28px;height:28px;border-radius:50%;object-fit:cover">
            </div>
            <div>
                <span class="pub-footer-name">{{ $footer['name'] }}</span>
                <span class="pub-footer-sub">{{ $footer['sub'] }}</span>
            </div>
        </div>

        @if($links)
        <div class="pub-footer-links">
            @foreach($footer['links'] as $link)
                <a href="{{ $link['anchor'] }}">{{ $link['label'] }}</a>
            @endforeach
        </div>
        @endif

        <p class="pub-footer-copy">&copy; {{ date('Y') }} {{ $footer['copyright'] }}</p>
    </div>
</footer>
