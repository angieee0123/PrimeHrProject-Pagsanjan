<?php

namespace App\Services;

class SystemTheme
{
    /**
     * All available palettes.
     * Each palette defines CSS custom properties injected into :root.
     */
    public const PALETTES = [
        'default' => [
            'label'   => 'Navy Blue',
            'preview' => '#0b044d',
            'vars'    => [
                '--theme-primary'        => '#0b044d',
                '--theme-primary-2'      => '#1b1464',
                '--theme-primary-light'  => '#f0effe',
                '--theme-primary-border' => '#ece9f8',
                '--theme-primary-muted'  => '#9999bb',
                '--theme-primary-soft'   => '#f4f3fb',
                '--theme-accent'         => '#0b044d',
                '--theme-sidebar-bg'     => '#0b044d',
                '--theme-sidebar-text'   => '#c8c5e8',
                '--theme-sidebar-active' => '#ffffff',
                '--theme-btn-primary-bg' => '#0b044d',
                '--theme-btn-primary-fg' => '#ffffff',
                '--theme-stat-icon-bg'   => '#f0effe',
                '--theme-stat-icon-fg'   => '#0b044d',
                '--theme-link'           => '#0b044d',
            ],
        ],
        'emerald' => [
            'label'   => 'Emerald Green',
            'preview' => '#064e3b',
            'vars'    => [
                '--theme-primary'        => '#064e3b',
                '--theme-primary-2'      => '#065f46',
                '--theme-primary-light'  => '#ecfdf5',
                '--theme-primary-border' => '#a7f3d0',
                '--theme-primary-muted'  => '#6ee7b7',
                '--theme-primary-soft'   => '#f0fdf4',
                '--theme-accent'         => '#059669',
                '--theme-sidebar-bg'     => '#064e3b',
                '--theme-sidebar-text'   => '#a7f3d0',
                '--theme-sidebar-active' => '#ffffff',
                '--theme-btn-primary-bg' => '#059669',
                '--theme-btn-primary-fg' => '#ffffff',
                '--theme-stat-icon-bg'   => '#ecfdf5',
                '--theme-stat-icon-fg'   => '#064e3b',
                '--theme-link'           => '#059669',
            ],
        ],
        'crimson' => [
            'label'   => 'Crimson Red',
            'preview' => '#7f1d1d',
            'vars'    => [
                '--theme-primary'        => '#7f1d1d',
                '--theme-primary-2'      => '#991b1b',
                '--theme-primary-light'  => '#fef2f2',
                '--theme-primary-border' => '#fecaca',
                '--theme-primary-muted'  => '#f87171',
                '--theme-primary-soft'   => '#fff5f5',
                '--theme-accent'         => '#dc2626',
                '--theme-sidebar-bg'     => '#7f1d1d',
                '--theme-sidebar-text'   => '#fca5a5',
                '--theme-sidebar-active' => '#ffffff',
                '--theme-btn-primary-bg' => '#dc2626',
                '--theme-btn-primary-fg' => '#ffffff',
                '--theme-stat-icon-bg'   => '#fef2f2',
                '--theme-stat-icon-fg'   => '#7f1d1d',
                '--theme-link'           => '#dc2626',
            ],
        ],
        'violet' => [
            'label'   => 'Violet Purple',
            'preview' => '#4c1d95',
            'vars'    => [
                '--theme-primary'        => '#4c1d95',
                '--theme-primary-2'      => '#5b21b6',
                '--theme-primary-light'  => '#f5f3ff',
                '--theme-primary-border' => '#ddd6fe',
                '--theme-primary-muted'  => '#a78bfa',
                '--theme-primary-soft'   => '#faf5ff',
                '--theme-accent'         => '#7c3aed',
                '--theme-sidebar-bg'     => '#4c1d95',
                '--theme-sidebar-text'   => '#c4b5fd',
                '--theme-sidebar-active' => '#ffffff',
                '--theme-btn-primary-bg' => '#7c3aed',
                '--theme-btn-primary-fg' => '#ffffff',
                '--theme-stat-icon-bg'   => '#f5f3ff',
                '--theme-stat-icon-fg'   => '#4c1d95',
                '--theme-link'           => '#7c3aed',
            ],
        ],
        'slate' => [
            'label'   => 'Slate Gray',
            'preview' => '#1e293b',
            'vars'    => [
                '--theme-primary'        => '#1e293b',
                '--theme-primary-2'      => '#334155',
                '--theme-primary-light'  => '#f1f5f9',
                '--theme-primary-border' => '#e2e8f0',
                '--theme-primary-muted'  => '#94a3b8',
                '--theme-primary-soft'   => '#f8fafc',
                '--theme-accent'         => '#475569',
                '--theme-sidebar-bg'     => '#1e293b',
                '--theme-sidebar-text'   => '#94a3b8',
                '--theme-sidebar-active' => '#ffffff',
                '--theme-btn-primary-bg' => '#334155',
                '--theme-btn-primary-fg' => '#ffffff',
                '--theme-stat-icon-bg'   => '#f1f5f9',
                '--theme-stat-icon-fg'   => '#1e293b',
                '--theme-link'           => '#475569',
            ],
        ],
        'amber' => [
            'label'   => 'Amber Gold',
            'preview' => '#78350f',
            'vars'    => [
                '--theme-primary'        => '#78350f',
                '--theme-primary-2'      => '#92400e',
                '--theme-primary-light'  => '#fffbeb',
                '--theme-primary-border' => '#fde68a',
                '--theme-primary-muted'  => '#fbbf24',
                '--theme-primary-soft'   => '#fefce8',
                '--theme-accent'         => '#d97706',
                '--theme-sidebar-bg'     => '#78350f',
                '--theme-sidebar-text'   => '#fcd34d',
                '--theme-sidebar-active' => '#ffffff',
                '--theme-btn-primary-bg' => '#d97706',
                '--theme-btn-primary-fg' => '#ffffff',
                '--theme-stat-icon-bg'   => '#fffbeb',
                '--theme-stat-icon-fg'   => '#78350f',
                '--theme-link'           => '#d97706',
            ],
        ],
        'custom' => [
            'label'   => 'Custom Color',
            'preview' => '#6366f1',
            'vars'    => [], // generated dynamically via fromHex()
        ],
    ];

    /** Derive a full palette vars array from a single hex color. */
    public static function fromHex(string $hex): array
    {
        $hex = ltrim($hex, '#');
        [$r, $g, $b] = [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];

        // Darken by mixing with black
        $darken = fn($c, $pct) => max(0, (int) round($c * (1 - $pct)));
        // Lighten by mixing with white
        $lighten = fn($c, $pct) => min(255, (int) round($c + (255 - $c) * $pct));
        $toHex   = fn($r, $g, $b) => sprintf('#%02x%02x%02x', $r, $g, $b);

        $primary  = '#' . $hex;
        $primary2 = $toHex($darken($r, 0.1), $darken($g, 0.1), $darken($b, 0.1));
        $accent   = $toHex($darken($r, 0.05), $darken($g, 0.05), $darken($b, 0.05));
        $light    = $toHex($lighten($r, 0.92), $lighten($g, 0.92), $lighten($b, 0.92));
        $border   = $toHex($lighten($r, 0.80), $lighten($g, 0.80), $lighten($b, 0.80));
        $muted    = $toHex($lighten($r, 0.55), $lighten($g, 0.55), $lighten($b, 0.55));
        $soft     = $toHex($lighten($r, 0.95), $lighten($g, 0.95), $lighten($b, 0.95));
        $sidebarText = $toHex($lighten($r, 0.60), $lighten($g, 0.60), $lighten($b, 0.60));

        return [
            '--theme-primary'        => $primary,
            '--theme-primary-2'      => $primary2,
            '--theme-primary-light'  => $light,
            '--theme-primary-border' => $border,
            '--theme-primary-muted'  => $muted,
            '--theme-primary-soft'   => $soft,
            '--theme-accent'         => $accent,
            '--theme-sidebar-bg'     => $primary,
            '--theme-sidebar-text'   => $sidebarText,
            '--theme-sidebar-active' => '#ffffff',
            '--theme-btn-primary-bg' => $primary,
            '--theme-btn-primary-fg' => '#ffffff',
            '--theme-stat-icon-bg'   => $light,
            '--theme-stat-icon-fg'   => $primary,
            '--theme-link'           => $primary,
        ];
    }

    public static function get(string $key): array
    {
        return self::PALETTES[$key] ?? self::PALETTES['default'];
    }

    public static function toCss(
        string  $key,
        ?string $customHex  = null,
        ?string $secondary  = null,
        ?string $accent     = null,
        ?string $muted      = null
    ): string {
        if ($key === 'custom' && $customHex && preg_match('/^#[0-9a-fA-F]{6}$/', $customHex)) {
            $vars = self::fromHex($customHex);
        } elseif ($key === 'custom') {
            $vars = self::PALETTES['default']['vars'];
        } else {
            $vars = self::get($key)['vars'];
        }

        $hex = '/^#[0-9a-fA-F]{6}$/';

        // --gp-pri / --gp-pri-2 are what the CSS actually consumes for
        // sidebar bg, button gradients, active tabs, pagination, modal buttons.
        $vars['--gp-pri']   = $vars['--theme-primary'];
        $vars['--gp-pri-2'] = ($secondary && preg_match($hex, $secondary))
            ? $secondary
            : $vars['--theme-primary-2'];

        // --c-blue / --c-blue-2 drive links, focus rings, chart tabs, form labels in admin.css
        $vars['--c-blue']   = ($accent && preg_match($hex, $accent))
            ? $accent
            : $vars['--theme-accent'];
        $vars['--c-blue-2'] = $vars['--gp-pri-2'];

        // --gp-muted drives stat labels, table subtitles, sidebar nav text
        if ($muted && preg_match($hex, $muted)) {
            $vars['--gp-muted'] = $muted;
        }

        // Keep theme-* vars in sync with the resolved values
        if ($secondary && preg_match($hex, $secondary)) $vars['--theme-primary-2']    = $secondary;
        if ($accent    && preg_match($hex, $accent))    $vars['--theme-accent']        = $accent;
        if ($muted     && preg_match($hex, $muted))     $vars['--theme-primary-muted'] = $muted;

        $lines = array_map(fn($k, $v) => "    $k: $v;", array_keys($vars), $vars);
        return ":root {\n" . implode("\n", $lines) . "\n}";
    }

    public static function all(): array
    {
        return array_map(fn($k, $p) => [
            'key'     => $k,
            'label'   => $p['label'],
            'preview' => $p['preview'],
        ], array_keys(self::PALETTES), self::PALETTES);
    }
}
