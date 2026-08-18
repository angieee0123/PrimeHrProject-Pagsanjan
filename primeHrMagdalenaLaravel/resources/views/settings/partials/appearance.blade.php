{{--
    Appearance picker — shared by the admin and employee settings pages, and
    by both scopes on the admin page.

    Pass $scope = 'personal' (what you see) or 'global' (the organisation's
    default, administrators only). Everything else comes from
    SystemTheme::pickerPayload(), so the two scopes cannot drift on how a
    palette is presented.

    Two of these can sit on one page, so every id is namespaced with the
    scope and the JS binds per [data-appearance] root rather than to globals.
--}}
@php
    $data     = \App\Services\SystemTheme::pickerPayload($scope);
    $isGlobal = $scope === 'global';
    $ns       = fn (string $name) => $scope . '-' . $name;
@endphp

<div class="theme-scope" data-appearance="{{ $scope }}"
     data-preview-url="{{ route('appearance.preview') }}"
     data-apply-url="{{ $isGlobal ? route('appearance.global.update') : route('appearance.update') }}"
     data-reset-url="{{ $isGlobal ? route('appearance.global.reset') : route('appearance.reset') }}"
     {{-- What "System theme" resolves to, so previewing that card asks the
          server for the organisation's palette rather than a made-up key. --}}
     data-global-theme="{{ $data['globalTheme'] }}"
     data-csrf="{{ csrf_token() }}">

    {{-- Lede: what this panel changes, whose look it changes, and what is
         live right now — one block rather than a loose sentence followed by
         a loose status line.

         Deliberately *not* classed .settings-row-desc. That class is defined
         in adminSettings.css and nowhere else, so on the employee settings
         page this sentence fell back to the browser's default size and ink
         and sat two steps above every label around it. All of its type now
         comes from themePicker.css, which app.css imports on every layout. --}}
    <div class="theme-lede">
        <span class="theme-lede-icon" aria-hidden="true">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10a1.7 1.7 0 0 0 1.7-1.7c0-.44-.18-.84-.44-1.13a1.63 1.63 0 0 1 1.23-2.72h2C19.54 16.45 22 13.99 22 10.9 22 6 17.5 2 12 2z"/>
                <circle cx="6.6" cy="12.4" r="1.1" fill="currentColor" stroke="none"/>
                <circle cx="9.3" cy="7.6" r="1.1" fill="currentColor" stroke="none"/>
                <circle cx="14.7" cy="7.6" r="1.1" fill="currentColor" stroke="none"/>
                <circle cx="17.4" cy="12.4" r="1.1" fill="currentColor" stroke="none"/>
            </svg>
        </span>
        <div class="theme-lede-copy">
            <p class="theme-lede-title">{{ $isGlobal ? "Everyone's default look" : 'Your personal look' }}</p>
            <p class="theme-intro">
                @if($isGlobal)
                    This is the look everyone gets by default. Anyone who has chosen their own
                    appearance keeps it &mdash; changing this will not overwrite their choice.
                @else
                    Pick a look for your own account. Only you see it, and you can go back to the
                    organisation&rsquo;s theme at any time.
                @endif
            </p>

            {{-- Current-theme status, in plain words rather than a stored flag
                 name. Inside the lede so "what you may change" and "what is on
                 right now" read as one thought. --}}
            @unless($isGlobal)
                <span class="theme-status" data-role="status">
                    <span class="theme-status-dot" aria-hidden="true"></span>
                    Currently using <strong data-role="statusText">{{ $data['usingPersonal'] ? 'your own appearance' : 'the system theme' }}</strong>
                </span>
            @endunless
        </div>
    </div>

    <div class="theme-grid" role="radiogroup" aria-label="{{ $isGlobal ? 'System colour palette' : 'Your colour palette' }}">

        {{-- "System theme" is a palette card rather than a separate control:
             choosing it and pressing Apply is the same thing as resetting, so
             the one grid answers "what do I look like?" completely. --}}
        @unless($isGlobal)
            <button type="button"
                class="theme-card theme-card-inherit{{ $data['selected'] === 'inherit' ? ' is-selected' : '' }}"
                data-theme-key="inherit" role="radio"
                aria-checked="{{ $data['selected'] === 'inherit' ? 'true' : 'false' }}"
                style="--card-pri:{{ $data['globalSwatches'][0] }};--card-acc:{{ $data['globalSwatches'][1] }};--card-tint:{{ $data['globalSwatches'][2] }}">
                <span class="theme-card-swatch" aria-hidden="true">
                    <span class="theme-card-band band-pri"></span>
                    <span class="theme-card-band band-acc"></span>
                    <span class="theme-card-band band-tint"></span>
                </span>
                <span class="theme-card-label">System theme</span>
                <span class="theme-card-blurb">Follows the organisation ({{ $data['globalLabel'] }})</span>
                <span class="theme-card-check" aria-hidden="true">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
            </button>
        @endunless

        @foreach($data['palettes'] as $palette)
            @php $isSelected = $data['selected'] === $palette['key']; @endphp
            <button type="button"
                class="theme-card{{ $isSelected ? ' is-selected' : '' }}{{ $palette['key'] === 'custom' ? ' theme-card-custom' : '' }}"
                data-theme-key="{{ $palette['key'] }}" role="radio"
                aria-checked="{{ $isSelected ? 'true' : 'false' }}"
                style="--card-pri:{{ $palette['swatches'][0] }};--card-acc:{{ $palette['swatches'][1] }};--card-tint:{{ $palette['swatches'][2] }}">
                <span class="theme-card-swatch" aria-hidden="true">
                    <span class="theme-card-band band-pri"></span>
                    <span class="theme-card-band band-acc"></span>
                    <span class="theme-card-band band-tint"></span>
                </span>
                <span class="theme-card-label">{{ $palette['label'] }}</span>
                <span class="theme-card-blurb">{{ $palette['blurb'] }}</span>
                @if($palette['key'] === 'custom')
                    <label class="theme-card-picker" onclick="event.stopPropagation()">
                        <input type="color" data-role="customColor" value="{{ $data['customColor'] }}"
                               aria-label="Pick any colour">
                        <span data-role="customHex">{{ strtoupper($data['customColor']) }}</span>
                    </label>
                @endif
                <span class="theme-card-check" aria-hidden="true">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
            </button>
        @endforeach
    </div>

    {{-- Sidebar and topbar, chosen by name. These sit in the basic section
         rather than under Advanced because they are the two changes people
         actually ask for, and neither requires knowing a colour. --}}
    <div class="theme-surfaces">
        @foreach([
            ['sidebar', 'Sidebar', $data['sidebarStyle']],
            ['topbar',  'Top bar', $data['topbarStyle']],
        ] as [$surface, $label, $current])
            <div class="theme-surface" data-surface="{{ $surface }}">
                <span class="theme-surface-label">{{ $label }}</span>
                <div class="theme-surface-options" role="radiogroup" aria-label="{{ $label }} style">
                    @foreach(\App\Services\SystemTheme::SURFACE_STYLES as $key => $styleLabel)
                        <button type="button"
                            class="theme-surface-btn{{ $current === $key ? ' is-selected' : '' }}"
                            data-surface-style="{{ $key }}" role="radio"
                            aria-checked="{{ $current === $key ? 'true' : 'false' }}">
                            <span class="theme-surface-chip chip-{{ $key }}" aria-hidden="true"></span>
                            {{ $styleLabel }}
                        </button>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- Live preview: real theme variables on a miniature of the real UI, set
         by the server's own resolver, so what is shown is what Apply produces. --}}
    <div class="theme-preview-wrap">
        <div class="theme-preview-head">
            <p class="theme-preview-title">Preview</p>
            <span class="theme-preview-hint" data-role="hint">Currently applied</span>
        </div>
        <div class="theme-preview" data-role="preview">
            <div class="tpv-sidebar">
                <div class="tpv-brand">
                    <span class="tpv-brand-mark">PH</span>
                    <span class="tpv-brand-text">PRIME HRIS</span>
                </div>
                <span class="tpv-nav is-active">Dashboard</span>
                <span class="tpv-nav">Personnel</span>
                <span class="tpv-nav">Payroll</span>
                <span class="tpv-nav">Leave</span>
            </div>
            <div class="tpv-main">
                <div class="tpv-topbar">
                    <span class="tpv-topbar-title">Dashboard</span>
                    <span class="tpv-btn">Run Payroll</span>
                </div>
                <div class="tpv-body">
                <div class="tpv-stats">
                    <div class="tpv-stat"><span class="tpv-stat-icon"></span><span class="tpv-stat-label">Employees</span><span class="tpv-stat-value">248</span></div>
                    <div class="tpv-stat"><span class="tpv-stat-icon"></span><span class="tpv-stat-label">On Leave</span><span class="tpv-stat-value">12</span></div>
                    <div class="tpv-stat"><span class="tpv-stat-icon"></span><span class="tpv-stat-label">Pending</span><span class="tpv-stat-value">7</span></div>
                </div>
                <div class="tpv-table">
                    <div class="tpv-tr tpv-th"><span>Employee</span><span>Department</span><span>Status</span></div>
                    <div class="tpv-tr"><span>Dela Cruz, J.</span><span class="tpv-soft">Mayor's Office</span><span class="tpv-badge">Approved</span></div>
                    <div class="tpv-tr"><span>Santos, M.</span><span class="tpv-soft">Treasury</span><span class="tpv-badge tpv-badge-pending">Pending</span></div>
                </div>
                    <p class="tpv-foot">Body text on the page background, with <a href="#" onclick="return false">a link</a> in the accent colour.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Contrast warning. Only ever a warning: a legitimate choice is never
         blocked, but nobody should discover unreadable nav labels by using
         the app for a day. --}}
    <p class="theme-warning" data-role="warning" hidden>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span data-role="warningText"></span>
    </p>

    {{-- 80/20: the three fine-tune pickers are folded away. Most people never
         open this, and the presets above already produce a complete palette. --}}
    <details class="theme-override-block">
        <summary class="theme-override-title">Advanced &mdash; fine-tune colours</summary>
        <p class="theme-override-note">
            Optional. Each picker starts on the value the selected palette generated. Overrides skip
            the contrast checks a generated palette passes, so keep them dark enough to carry white text.
        </p>
        <div class="theme-override-grid">
            @foreach([
                ['secondary', 'Secondary', 'Button gradients, active tabs & pagination'],
                ['accent',    'Accent',    'Links, focus rings, chart tabs, form labels'],
                ['muted',     'Muted',     'Stat labels, table subtitles, sidebar nav text'],
            ] as [$key, $label, $help])
                <div class="theme-override">
                    <label for="{{ $ns('override-' . $key) }}">{{ $label }}</label>
                    <p>{{ $help }}</p>
                    <span class="theme-override-input">
                        <input type="color" id="{{ $ns('override-' . $key) }}" data-override="{{ $key }}">
                        <span data-hex-for="{{ $ns('override-' . $key) }}">&mdash;</span>
                    </span>
                </div>
            @endforeach
        </div>
        <button type="button" class="theme-reset-btn" data-role="resetOverrides">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            Back to palette defaults
        </button>
    </details>

    <p class="settings-message error hidden" data-role="message"></p>

    <div class="settings-save-bar theme-actions">
        @if($isGlobal)
            <button type="button" class="settings-btn-reset" data-role="reset">Reset to PRIME HRIS default</button>
        @else
            <button type="button" class="settings-btn-reset" data-role="reset"
                    @disabled(! $data['usingPersonal'])>Reset to system theme</button>
        @endif
        <button type="button" class="settings-btn-save" data-role="apply">
            {{ $isGlobal ? 'Apply to everyone' : 'Apply' }}
        </button>
    </div>
</div>
