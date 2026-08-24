<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * `.glass-shell .btn-export` (0,2,0) paints every header pill a translucent
 * white. A colour variant of it therefore has to name `.btn-export` too — a
 * bare `.btn-export-success` (0,1,0) loses the fill but keeps its own label
 * colour, which is a white word on a white pill: the button reads as missing
 * rather than as mis-styled, so nothing about the page looks broken.
 *
 * The `:hover` pair is the same trap one step along: `.glass-shell
 * .btn-export:hover` is (0,3,0), so a variant that styles only the resting
 * state disappears mid-hover instead.
 *
 * That has now happened twice, and it cannot be caught by rendering the page —
 * the markup is correct either way. These pin the shape of the selectors.
 */
class ExportButtonVariantTest extends TestCase
{
    /** Every colour variant of the .btn-export pill. */
    private const VARIANTS = [
        'btn-export-solid'      => 'css/glassSystem.css',
        'btn-export-green'      => 'css/glassSystem.css',
        'adm-btn-primary-solid' => 'css/admin/admin.css',
    ];

    private function css(string $path): string
    {
        return file_get_contents(resource_path($path));
    }

    #[Test]
    public function every_variant_outranks_the_pill_it_recolours(): void
    {
        foreach (self::VARIANTS as $variant => $path) {
            $this->assertStringContainsString(
                '.glass-shell .btn-export.' . $variant . ' {',
                $this->css($path),
                "{$variant} must name .glass-shell and .btn-export to beat "
                . '`.glass-shell .btn-export` (0,2,0) on specificity; at (0,1,0) '
                . 'it keeps its label colour but loses its background.',
            );
        }
    }

    #[Test]
    public function every_variant_states_its_hover(): void
    {
        foreach (self::VARIANTS as $variant => $path) {
            $this->assertStringContainsString(
                '.glass-shell .btn-export.' . $variant . ':hover {',
                $this->css($path),
                "{$variant} must restate its colours on :hover, or "
                . '`.glass-shell .btn-export:hover` (0,3,0) reverts the pill to '
                . 'translucent white while the label stays light.',
            );
        }
    }

    #[Test]
    public function the_bulk_import_variant_reads_its_colours_from_the_theme(): void
    {
        $css = $this->css('css/glassSystem.css');

        // A literal here is a button that cannot follow the palette the user
        // picked under Settings → Appearance — which is how this one shipped
        // originally, as an inline `color:#fff` the theme could not reach.
        foreach (['--theme-success', '--theme-success-emphasis'] as $token) {
            $this->assertStringContainsString(
                'var(' . $token . ')',
                $css,
                "The Bulk Import pill must take its colours from {$token}, not from a hex literal.",
            );
        }
    }

    #[Test]
    public function the_bulk_import_button_carries_the_variant_rather_than_an_inline_style(): void
    {
        $partial = file_get_contents(
            resource_path('views/admin/personnel/partials/employee-records-tab.blade.php'),
        );

        $this->assertStringContainsString('class="btn-export btn-export-green"', $partial);
        $this->assertStringNotContainsString('style="background:var(--theme-success)', $partial);
    }

    /**
     * Personnel and Departments both head their table with a Bulk Import pill,
     * and the two are meant to be indistinguishable. They can only stay that
     * way by naming one rule: the colour previously lived in
     * adminDepartment.css, which Personnel never loads, so matching them meant
     * retyping a hex into a second stylesheet and hoping both got edited next
     * time the palette moved.
     */
    #[Test]
    public function personnel_and_departments_share_one_bulk_import_pill(): void
    {
        $partials = [
            'views/admin/personnel/partials/employee-records-tab.blade.php',
            'views/admin/departments/partials/departments-tab.blade.php',
            'views/admin/departments/partials/designations-tab.blade.php',
        ];

        foreach ($partials as $partial) {
            $this->assertStringContainsString(
                'class="btn-export btn-export-green"',
                file_get_contents(resource_path($partial)),
                "{$partial} must use the shared Bulk Import variant.",
            );
        }

        $this->assertStringNotContainsString(
            '.btn-export-green {',
            $this->css('css/admin/adminDepartment.css'),
            'A second, page-local copy of the pill would drift from the shared one '
            . 'and — at (0,1,0) — would not even beat the pill it recolours.',
        );
    }
}
