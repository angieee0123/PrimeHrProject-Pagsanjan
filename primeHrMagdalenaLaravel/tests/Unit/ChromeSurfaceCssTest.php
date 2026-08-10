<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The sidebar and topbar may not hard-code white.
 *
 * SystemThemeTest proves the *variables* are legible on every surface. It
 * cannot prove the stylesheets use them — and that is where this went wrong
 * twice: the surface went pale while `.nav-item` and `.banner-left h2` stayed
 * pinned to `#ffffff`, giving white-on-white at 1.0:1. Nobody notices a
 * missed rule until they switch to Light and the page loses its heading.
 *
 * So this reads the CSS. Any declaration inside a sidebar- or topbar-scoped
 * rule that names white literally is a rule that cannot follow the surface.
 * Use `var(--theme-{sidebar,topbar}-fg)` or the matching `-fg-rgb` triplet.
 */
class ChromeSurfaceCssTest extends TestCase
{
    /** Selectors whose contents sit on a themeable chrome surface. */
    private const SCOPES = [
        'sidebar' => '(^|[\s,>])\.(sidebar|nav-item|nav-label|nav-section|nav-divider|nav-active-bar|nav-logout|logo-text|logo-sub|sidebar-header|sidebar-nav|sidebar-footer|toggle-btn)\b',
        'topbar'  => '(welcome-banner|banner-left|banner-right|banner-title|banner-sub|banner-icon|banner-badge|banner-meta|banner-text|profile-banner)',
    ];

    /** Files that legitimately hold literal colours. */
    private const EXEMPT = [
        'glassSystem.css',   // the default-palette fallback block
        'themePicker.css',   // renders many palettes at once, on cards not chrome
        'confirmDialog.css',
    ];

    #[Test]
    public function no_chrome_surface_rule_hard_codes_white(): void
    {
        $offenders = [];

        foreach ($this->stylesheets() as $path) {
            foreach (self::SCOPES as $surface => $selector) {
                foreach ($this->literalWhitesIn($path, $selector) as [$line, $text]) {
                    $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                    $offenders[] = "$rel:$line ($surface) — " . trim($text);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These rules sit on a themeable surface but name white literally, so they\n"
            . "cannot follow a Light sidebar or topbar. Use var(--theme-<surface>-fg)\n"
            . "or rgba(var(--theme-<surface>-fg-rgb), <alpha>):\n  "
            . implode("\n  ", $offenders),
        );
    }

    /**
     * The brand navy is never a categorical colour, so an inline style naming
     * it is always a element that will not follow the theme.
     *
     * Inline styles beat every class, which is how the Personnel page's
     * "Bulk Assign" button stayed navy under an emerald theme: the stylesheet
     * sweeps only ever looked at .css and .js files, and 458 colour literals
     * were sitting in `style=""` attributes in Blade.
     */
    #[Test]
    public function no_blade_inline_style_hard_codes_the_brand_colour(): void
    {
        $brand = ['#0b044d', '#150c63', '#1b1464', '#080334'];
        $offenders = [];

        foreach ($this->bladeFiles() as $path) {
            foreach (file($path) as $i => $line) {
                if (! preg_match_all('/style\s*=\s*"([^"]*)"/i', $line, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $style) {
                    foreach ($brand as $hex) {
                        if (stripos($style, $hex) !== false) {
                            $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                            $offenders[] = "$rel:" . ($i + 1) . " — $hex";
                        }
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These inline styles pin the brand navy, so they stay navy under every\n"
            . "other palette. Use var(--gp-pri) / var(--gp-pri-2):\n  "
            . implode("\n  ", $offenders),
        );
    }

    /**
     * A `style=""` attribute may not contain a double quote.
     *
     * The attribute is delimited by `"`, so a nested one closes it early and
     * the browser parses every declaration after that point as stray HTML
     * attributes. `font-family:…,"SF Pro Display",… display:flex; gap:8px;`
     * produced real attributes literally named `display:flex;` and `gap:8px;`
     * — which is why the Bulk Import buttons stacked their icon above the
     * label despite appearing to say `display:flex`.
     *
     * It also blinds the two guards below: they read `style="([^"]*)"`, so
     * anything past the break is invisible to them. One of these was hiding
     * a hard-coded brand navy that no test could see.
     */
    #[Test]
    public function no_blade_style_attribute_contains_a_double_quote(): void
    {
        $offenders = [];

        foreach ($this->bladeFiles() as $path) {
            foreach (file($path) as $i => $line) {
                // The signature of a truncated value: it ends on a comma or
                // colon, which no complete CSS declaration ever does. Testing
                // for "contains a quote" instead would false-positive on the
                // ordinary `style="…" class="…"` pairing.
                if (preg_match('/style\s*=\s*"[^"]*[,:]\s*"/', $line)) {
                    $rel = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);
                    $offenders[] = "$rel:" . ($i + 1);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "A style=\"…\" attribute here contains a double quote, which ends the\n"
            . "attribute early — everything after it is parsed as stray HTML, not\n"
            . "CSS. Use single quotes inside the style value:\n  "
            . implode("\n  ", $offenders),
        );
    }

    /**
     * Blade `<style>` blocks are a third place colours hide.
     *
     * The sweeps went stylesheet → inline `style=""` → and missed this one,
     * which is why the Bulk Assignment modal stayed navy under every palette:
     * its whole design lives in a `<style>` block inside its own Blade file.
     *
     * Standalone documents are exempt and must keep literal hexes: a PDF
     * rendered by dompdf, or a print view opened with `window.open()`, is its
     * own document and never receives the theme's `:root` block, so a custom
     * property there resolves to no colour at all.
     */
    #[Test]
    public function no_blade_style_block_hard_codes_the_brand_colour(): void
    {
        $brand = ['#0b044d', '#150c63', '#1b1464', '#080334'];
        $offenders = [];

        foreach ($this->bladeFiles() as $path) {
            $source = file_get_contents($path);

            if (preg_match('/<!DOCTYPE|<html/i', $source) || $this->isPrintPartial($path)) {
                continue;
            }

            if (! preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $source, $blocks)) {
                continue;
            }

            foreach ($blocks[1] as $css) {
                foreach ($brand as $hex) {
                    if (stripos($css, $hex) !== false) {
                        $offenders[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path) . " — $hex";
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These <style> blocks pin the brand navy, so they stay navy under every\n"
            . "other palette. Use var(--gp-pri) / var(--gp-pri-2):\n  "
            . implode("\n  ", array_unique($offenders)),
        );
    }

    /**
     * Partials that are only ever included into a print/PDF document, so the
     * per-file `<html>` check above cannot see that they are exempt.
     */
    private function isPrintPartial(string $path): bool
    {
        return str_contains($path, 'cs-form-no-6');
    }

    /** @return list<string> */
    private function bladeFiles(): array
    {
        $found = [];
        $dir = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(resource_path('views'))
        );

        foreach ($dir as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
                $found[] = $file->getPathname();
            }
        }

        return $found;
    }

    /** @return list<string> */
    private function stylesheets(): array
    {
        $files = glob(resource_path('css') . '/{,*/}*.css', GLOB_BRACE) ?: [];

        return array_values(array_filter(
            $files,
            fn ($f) => ! in_array(basename($f), self::EXEMPT, true),
        ));
    }

    /**
     * Walk the file tracking brace depth, so a declaration is attributed to
     * the selector that opened its block rather than to whatever happens to
     * share its line.
     *
     * @return list<array{0:int,1:string}>
     */
    private function literalWhitesIn(string $path, string $selectorPattern): array
    {
        $white = '/#fff(fff)?\b|rgba\(\s*255\s*,\s*255\s*,\s*255\s*,/i';
        $found = [];
        $depth = 0;
        $inScope = false;

        foreach (file($path) as $i => $line) {
            if (str_contains($line, '{') && $depth === 0) {
                $selector = explode('{', $line)[0];
                $inScope = preg_match("/$selectorPattern/i", $selector) === 1;
            }

            if ($inScope && preg_match($white, $line)) {
                $found[] = [$i + 1, $line];
            }

            $depth += substr_count($line, '{') - substr_count($line, '}');
            if ($depth <= 0) {
                $depth = 0;
                $inScope = false;
            }
        }

        return $found;
    }
}
