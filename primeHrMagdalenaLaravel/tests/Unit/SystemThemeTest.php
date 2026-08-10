<?php

namespace Tests\Unit;

use App\Services\SystemTheme;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The theme generator's guarantees, pinned.
 *
 * These are the properties that make an arbitrary seed safe to apply
 * system-wide. Before this, palettes were hand-written hex lists: a light
 * seed produced white-on-pale buttons, and only the sidebar recoloured while
 * the page tints stayed the brand's navy. Each test below fails if that
 * behaviour comes back.
 */
class SystemThemeTest extends TestCase
{
    /** Seeds chosen to break naive derivation: near-white, black, and the
     *  high-lightness hues no interface can carry white text on. */
    public static function hostileSeeds(): array
    {
        return [
            'pure yellow'  => ['#ffe600'],
            'lime'         => ['#8ef542'],
            'cyan'         => ['#22d3ee'],
            'pale pink'    => ['#f9a8d4'],
            'white'        => ['#ffffff'],
            'black'        => ['#000000'],
            'mid grey'     => ['#808080'],
        ];
    }

    public static function paletteKeys(): array
    {
        return array_map(fn ($k) => [$k], array_keys(SystemTheme::PALETTES));
    }

    #[Test]
    #[DataProvider('paletteKeys')]
    public function every_built_in_palette_is_legible(string $key): void
    {
        $this->assertPaletteLegible(SystemTheme::resolve($key), $key);
    }

    #[Test]
    #[DataProvider('hostileSeeds')]
    public function any_custom_colour_produces_a_legible_palette(string $seed): void
    {
        $this->assertPaletteLegible(SystemTheme::resolve('custom', $seed), $seed);
    }

    #[Test]
    public function a_light_seed_is_darkened_rather_than_used_raw(): void
    {
        $vars = SystemTheme::resolve('custom', '#ffe600');

        // The sidebar carries navigation labels, so it may not simply be the
        // seed. Applying #ffe600 literally is what made a custom theme
        // unreadable rather than merely bright.
        $this->assertNotSame('#ffe600', $vars['--theme-sidebar-bg']);
        $this->assertGreaterThanOrEqual(
            4.5,
            SystemTheme::contrast($vars['--theme-sidebar-bg'], '#ffffff'),
            'The sidebar must carry white text.'
        );
    }

    #[Test]
    public function an_achromatic_seed_stays_grey(): void
    {
        foreach (['#ffffff', '#000000', '#808080'] as $seed) {
            foreach (['--theme-primary', '--theme-bg', '--theme-ink', '--theme-accent'] as $var) {
                [$r, $g, $b] = sscanf(SystemTheme::resolve('custom', $seed)[$var], '#%02x%02x%02x');
                $this->assertSame(
                    [$r, $r, $r],
                    [$r, $g, $b],
                    "$var drifted off grey for seed $seed — the chroma floor must not inject a hue."
                );
            }
        }
    }

    #[Test]
    public function the_neutral_ramp_follows_the_seed_hue(): void
    {
        // The defect this replaces: an emerald sidebar on lavender page tints.
        $navy    = SystemTheme::resolve('default');
        $emerald = SystemTheme::resolve('emerald');

        foreach (['--gp-bg', '--gp-bg-tint', '--gp-bg-tint-2', '--gp-border', '--gp-ink'] as $var) {
            $this->assertNotSame(
                $navy[$var],
                $emerald[$var],
                "$var is identical across palettes — the neutral ramp is not following the theme."
            );
        }
    }

    #[Test]
    public function the_default_palette_still_renders_the_municipal_navy(): void
    {
        $this->assertSame('#0b044d', SystemTheme::resolve('default')['--gp-pri']);
    }

    #[Test]
    public function an_accent_override_recomputes_the_label_on_top_of_it(): void
    {
        // White on #ffe600 is unreadable, so the override must flip the
        // button's foreground rather than keep the hard-coded white.
        $vars = SystemTheme::resolve('emerald', null, null, '#ffe600', null);

        $this->assertSame('#ffe600', $vars['--theme-btn-primary-bg']);
        $this->assertNotSame('#ffffff', $vars['--theme-btn-primary-fg']);
        $this->assertGreaterThanOrEqual(
            4.5,
            SystemTheme::contrast($vars['--theme-btn-primary-bg'], $vars['--theme-btn-primary-fg'])
        );
    }

    #[Test]
    public function the_muted_override_targets_the_variable_the_picker_shows(): void
    {
        // The picker is seeded from `defaults.muted`; if the override wrote to
        // a different variable, changing it would appear to do nothing.
        $defaults = collect(SystemTheme::all())->firstWhere('key', 'emerald')['defaults'];

        $this->assertSame($defaults['muted'], SystemTheme::resolve('emerald')['--theme-muted']);
        $this->assertSame('#777777', SystemTheme::resolve('emerald', null, null, null, '#777777')['--gp-muted']);
    }

    #[Test]
    public function every_chrome_surface_carries_readable_text(): void
    {
        // The failure this prevents: choosing a light sidebar while the nav
        // labels stay pinned to white, i.e. white on white.
        foreach (array_keys(SystemTheme::SURFACE_STYLES) as $sidebar) {
            foreach (array_keys(SystemTheme::SURFACE_STYLES) as $topbar) {
                foreach (['default', 'emerald', 'amber', 'custom'] as $palette) {
                    $vars = SystemTheme::resolve($palette, '#ffe600', null, null, null, $sidebar, $topbar);

                    foreach (['sidebar' => $sidebar, 'topbar' => $topbar] as $name => $style) {
                        $this->assertGreaterThanOrEqual(
                            4.5,
                            SystemTheme::contrast($vars["--theme-$name-bg"], $vars["--theme-$name-fg"]),
                            "[$palette] a '$style' $name cannot carry its own label colour.",
                        );
                    }
                }
            }
        }
    }

    #[Test]
    public function an_unknown_surface_style_falls_back_to_brand(): void
    {
        // Validation rejects these at the endpoint; this is the second line,
        // so a stale stored value cannot render a colourless surface.
        $brand = SystemTheme::resolve('emerald')['--theme-sidebar-bg'];

        foreach (['', 'purple', '../etc', 'DARK'] as $bogus) {
            $this->assertSame(
                $brand,
                SystemTheme::resolve('emerald', null, null, null, null, $bogus)['--theme-sidebar-bg'],
            );
        }
    }

    #[Test]
    public function every_palette_defines_every_variable(): void
    {
        $expected = array_keys(SystemTheme::resolve('default'));

        foreach (array_keys(SystemTheme::PALETTES) as $key) {
            $this->assertSame(
                [],
                array_diff($expected, array_keys(SystemTheme::resolve($key))),
                "Palette $key is missing variables the default defines."
            );
        }
    }

    private function assertPaletteLegible(array $vars, string $label): void
    {
        $checks = [
            'button label on button'   => [$vars['--theme-btn-primary-bg'], $vars['--theme-btn-primary-fg'], 4.5],
            'body text on page'        => [$vars['--theme-bg'], $vars['--theme-ink'], 4.5],
            'link on white'            => ['#ffffff', $vars['--theme-link'], 4.5],
            'sidebar label on sidebar' => [$vars['--theme-sidebar-bg'], $vars['--theme-sidebar-text'], 3.0],
            'brand mark on sidebar'    => [$vars['--theme-sidebar-bg'], $vars['--theme-sidebar-active'], 4.5],
        ];

        foreach ($checks as $what => [$bg, $fg, $minimum]) {
            $this->assertGreaterThanOrEqual(
                $minimum,
                SystemTheme::contrast($bg, $fg),
                "[$label] $what is below WCAG AA ($bg vs $fg)."
            );
        }
    }
}
