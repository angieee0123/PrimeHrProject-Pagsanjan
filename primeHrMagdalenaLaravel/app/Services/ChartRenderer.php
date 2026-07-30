<?php

namespace App\Services;

/**
 * Renders a chart spec from ChartDataService as inline SVG.
 *
 * Inline SVG rather than a JavaScript charting library because the same markup
 * has to work in three places: the assistant panel, a printed page, and the
 * dompdf export — and dompdf runs no JavaScript. It also keeps the frontend
 * dependency-free.
 *
 * Mark treatment follows the house data-viz rules: thin marks with rounded
 * data-ends anchored to the baseline, 2px lines, 8px markers, a 2px surface
 * gap between adjacent fills, recessive grid and axes, text in ink colours
 * rather than the series colour, and a legend whenever there is more than one
 * series.
 */
class ChartRenderer
{
    private const WIDTH = 720;
    private const HEIGHT = 340;
    private const PAD_TOP = 16;
    private const PAD_BOTTOM = 44;
    private const PAD_LEFT = 56;
    private const PAD_RIGHT = 16;
    private const BAR_RADIUS = 4;
    private const GAP = 2;

    /**
     * @param array<string, mixed> $chart
     */
    public function render(array $chart): string
    {
        $type = $chart['type'] ?? 'bar';
        $horizontal = ($chart['orientation'] ?? 'vertical') === 'horizontal';

        $body = match ($type) {
            'line', 'area' => $this->renderLine($chart),
            'stacked_bar' => $this->renderStackedBar($chart, $horizontal),
            'grouped_bar' => $this->renderGroupedBar($chart, $horizontal),
            default => $horizontal ? $this->renderHorizontalBar($chart) : $this->renderVerticalBar($chart),
        };

        $legend = ($chart['legend'] ?? false) ? $this->renderLegend($chart) : '';
        $height = self::HEIGHT + (($chart['legend'] ?? false) ? 28 : 0);

        return '<svg class="viz" role="img" aria-label="' . $this->esc($chart['title'] ?? 'Chart') . '" '
            . 'viewBox="0 0 ' . self::WIDTH . ' ' . $height . '" width="100%" '
            . 'xmlns="http://www.w3.org/2000/svg" style="max-width:100%;height:auto;overflow:visible">'
            . $this->styleBlock($chart)
            . '<title>' . $this->esc($chart['title'] ?? 'Chart') . '</title>'
            . $body
            . $legend
            . '</svg>';
    }

    /**
     * Series colours become CSS classes so the dark-mode steps can be swapped
     * by media query. dompdf ignores the media block and keeps the light set,
     * which is what a printed page wants.
     *
     * @param array<string, mixed> $chart
     */
    private function styleBlock(array $chart): string
    {
        $light = $chart['colors']['light'] ?? ['#2a78d6'];
        $dark = $chart['colors']['dark'] ?? ['#3987e5'];

        $rules = '';
        foreach ($light as $i => $hex) {
            $rules .= ".s{$i}{fill:{$hex};stroke:{$hex}}";
        }

        $darkRules = '';
        foreach ($dark as $i => $hex) {
            $darkRules .= ".s{$i}{fill:{$hex};stroke:{$hex}}";
        }

        return '<style>'
            . '.viz{font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,sans-serif}'
            . '.ink{fill:#0b0b0b}.ink-2{fill:#52514e}'
            . '.grid{stroke:#e5e5e2;stroke-width:1}.axis{stroke:#c9c8c4;stroke-width:1}'
            . '.lbl{font-size:11px}.val{font-size:11px;font-variant-numeric:tabular-nums}'
            . '.ttl{font-size:13px;font-weight:600}'
            . $rules
            . '.ring{stroke:#fcfcfb}'
            . '@media (prefers-color-scheme:dark){'
            . '.ink{fill:#ffffff}.ink-2{fill:#c3c2b7}'
            . '.grid{stroke:#333330}.axis{stroke:#4a4a46}'
            . $darkRules
            . '.ring{stroke:#1a1a19}'
            . '}'
            . '</style>';
    }

    /**
     * @param array<string, mixed> $chart
     */
    private function renderVerticalBar(array $chart): string
    {
        $values = $chart['series'][0]['values'] ?? [];
        $labels = $chart['labels'] ?? [];
        $max = $this->niceMax($values);

        $plotW = self::WIDTH - self::PAD_LEFT - self::PAD_RIGHT;
        $plotH = self::HEIGHT - self::PAD_TOP - self::PAD_BOTTOM;
        $baseline = self::PAD_TOP + $plotH;
        $slot = count($values) > 0 ? $plotW / count($values) : $plotW;
        $barW = max(4, min(48, $slot * 0.6));

        $svg = $this->yGrid($max, $plotH, $chart['format'] ?? 'number');

        foreach ($values as $i => $value) {
            $h = $max > 0 ? ($value / $max) * $plotH : 0;
            $x = self::PAD_LEFT + $slot * $i + ($slot - $barW) / 2;
            $y = $baseline - $h;

            $svg .= $this->roundedTopBar($x, $y, $barW, $h, 's0');

            if ($chart['direct_label'] ?? true) {
                $svg .= '<text class="val ink-2" x="' . round($x + $barW / 2, 1) . '" y="' . round($y - 5, 1)
                    . '" text-anchor="middle">' . $this->fmt($value, $chart['format'] ?? 'number') . '</text>';
            }

            $svg .= '<text class="lbl ink-2" x="' . round(self::PAD_LEFT + $slot * $i + $slot / 2, 1) . '" y="'
                . ($baseline + 16) . '" text-anchor="middle">' . $this->esc((string) ($labels[$i] ?? '')) . '</text>';
        }

        return $svg . $this->axisLine($baseline);
    }

    /**
     * @param array<string, mixed> $chart
     */
    private function renderHorizontalBar(array $chart): string
    {
        $values = $chart['series'][0]['values'] ?? [];
        $labels = $chart['labels'] ?? [];
        $max = $this->niceMax($values);

        $labelW = 196;
        $plotW = self::WIDTH - $labelW - self::PAD_RIGHT - 40;
        $rowH = count($values) > 0 ? min(30, (self::HEIGHT - self::PAD_TOP - 20) / count($values)) : 24;
        $barH = max(6, $rowH - 10);

        $svg = '';

        foreach ($values as $i => $value) {
            $w = $max > 0 ? ($value / $max) * $plotW : 0;
            $y = self::PAD_TOP + $rowH * $i;

            $svg .= '<text class="lbl ink-2" x="' . ($labelW - 8) . '" y="' . round($y + $barH / 2 + 4, 1)
                . '" text-anchor="end">' . $this->esc($this->truncate((string) ($labels[$i] ?? ''), 26)) . '</text>';

            $svg .= $this->roundedEndBar($labelW, $y, $w, $barH, 's0');

            $svg .= '<text class="val ink-2" x="' . round($labelW + $w + 6, 1) . '" y="' . round($y + $barH / 2 + 4, 1)
                . '">' . $this->fmt($value, $chart['format'] ?? 'number') . '</text>';
        }

        return $svg . '<line class="axis" x1="' . $labelW . '" y1="' . self::PAD_TOP . '" x2="' . $labelW
            . '" y2="' . round(self::PAD_TOP + $rowH * count($values), 1) . '"/>';
    }

    /**
     * @param array<string, mixed> $chart
     */
    private function renderStackedBar(array $chart, bool $horizontal): string
    {
        $labels = $chart['labels'] ?? [];
        $series = $chart['series'] ?? [];

        $totals = [];
        foreach ($labels as $i => $_) {
            $totals[$i] = array_sum(array_map(fn (array $s) => $s['values'][$i] ?? 0, $series));
        }
        $max = $this->niceMax($totals);

        $labelW = 196;
        $plotW = self::WIDTH - $labelW - self::PAD_RIGHT - 40;
        $rowH = count($labels) > 0 ? min(38, (self::HEIGHT - self::PAD_TOP - 20) / count($labels)) : 30;
        $barH = max(8, $rowH - 12);

        $svg = '';

        foreach ($labels as $i => $label) {
            $y = self::PAD_TOP + $rowH * $i;
            $x = $labelW;

            $svg .= '<text class="lbl ink-2" x="' . ($labelW - 8) . '" y="' . round($y + $barH / 2 + 4, 1)
                . '" text-anchor="end">' . $this->esc($this->truncate((string) $label, 26)) . '</text>';

            foreach ($series as $si => $s) {
                $value = $s['values'][$i] ?? 0;
                if ($value <= 0) {
                    continue;
                }

                $w = $max > 0 ? ($value / $max) * $plotW : 0;
                // 2px surface gap so adjacent segments read as separate marks.
                $drawW = max(0, $w - self::GAP);

                $svg .= '<rect class="s' . ($si % 8) . '" x="' . round($x, 1) . '" y="' . round($y, 1)
                    . '" width="' . round($drawW, 1) . '" height="' . round($barH, 1) . '"/>';

                if ($drawW > 30) {
                    $svg .= '<text class="val" x="' . round($x + $drawW / 2, 1) . '" y="' . round($y + $barH / 2 + 4, 1)
                        . '" text-anchor="middle" fill="#ffffff">' . $this->fmt($value, 'number') . '</text>';
                }

                $x += $w;
            }

            $svg .= '<text class="val ink-2" x="' . round($x + 6, 1) . '" y="' . round($y + $barH / 2 + 4, 1)
                . '">' . $this->fmt($totals[$i], $chart['format'] ?? 'number') . '</text>';
        }

        return $svg;
    }

    /**
     * @param array<string, mixed> $chart
     */
    private function renderGroupedBar(array $chart, bool $horizontal): string
    {
        $labels = $chart['labels'] ?? [];
        $series = $chart['series'] ?? [];

        $all = [];
        foreach ($series as $s) {
            $all = array_merge($all, $s['values']);
        }
        $max = $this->niceMax($all);

        $labelW = 196;
        $plotW = self::WIDTH - $labelW - self::PAD_RIGHT - 40;
        $groupH = count($labels) > 0 ? min(44, (self::HEIGHT - self::PAD_TOP - 20) / count($labels)) : 36;
        $barH = max(5, ($groupH - 12) / max(count($series), 1) - self::GAP);

        $svg = '';

        foreach ($labels as $i => $label) {
            $gy = self::PAD_TOP + $groupH * $i;

            $svg .= '<text class="lbl ink-2" x="' . ($labelW - 8) . '" y="' . round($gy + $groupH / 2, 1)
                . '" text-anchor="end">' . $this->esc($this->truncate((string) $label, 26)) . '</text>';

            foreach ($series as $si => $s) {
                $value = $s['values'][$i] ?? 0;
                $w = $max > 0 ? ($value / $max) * $plotW : 0;
                $y = $gy + $si * ($barH + self::GAP);

                if ($w > 0) {
                    $svg .= $this->roundedEndBar($labelW, $y, $w, $barH, 's' . ($si % 8));
                }

                $svg .= '<text class="val ink-2" x="' . round($labelW + $w + 6, 1) . '" y="' . round($y + $barH / 2 + 4, 1)
                    . '">' . $this->fmt($value, $chart['format'] ?? 'number') . '</text>';
            }
        }

        return $svg . '<line class="axis" x1="' . $labelW . '" y1="' . self::PAD_TOP . '" x2="' . $labelW
            . '" y2="' . round(self::PAD_TOP + $groupH * count($labels), 1) . '"/>';
    }

    /**
     * @param array<string, mixed> $chart
     */
    private function renderLine(array $chart): string
    {
        $labels = $chart['labels'] ?? [];
        $series = $chart['series'] ?? [];

        $all = [];
        foreach ($series as $s) {
            $all = array_merge($all, $s['values']);
        }
        $max = $this->niceMax($all);

        $plotW = self::WIDTH - self::PAD_LEFT - self::PAD_RIGHT;
        $plotH = self::HEIGHT - self::PAD_TOP - self::PAD_BOTTOM;
        $baseline = self::PAD_TOP + $plotH;
        $count = max(count($labels), 1);
        $step = $count > 1 ? $plotW / ($count - 1) : 0;

        $svg = $this->yGrid($max, $plotH, $chart['format'] ?? 'number');

        foreach ($series as $si => $s) {
            $points = [];
            foreach ($s['values'] as $i => $value) {
                $x = self::PAD_LEFT + $step * $i;
                $y = $baseline - ($max > 0 ? ($value / $max) * $plotH : 0);
                $points[] = round($x, 1) . ',' . round($y, 1);
            }

            if (empty($points)) {
                continue;
            }

            // fill/stroke overrides go in an inline style, not presentation
            // attributes: the .sN class also sets fill, and a class beats a
            // presentation attribute in SVG — which would paint the line as a
            // filled blob.
            $svg .= '<polyline class="s' . ($si % 8) . '" style="fill:none" stroke-width="2" stroke-linejoin="round" '
                . 'stroke-linecap="round" points="' . implode(' ', $points) . '"/>';

            // 8px markers with a 2px surface ring so overlapping series stay
            // readable where lines cross.
            foreach ($s['values'] as $i => $value) {
                $x = self::PAD_LEFT + $step * $i;
                $y = $baseline - ($max > 0 ? ($value / $max) * $plotH : 0);
                $svg .= '<circle class="s' . ($si % 8) . ' ring" cx="' . round($x, 1) . '" cy="' . round($y, 1)
                    . '" r="4" stroke-width="2"/>';
            }
        }

        // Direct-label the last point of each series rather than every point.
        if (($chart['direct_label'] ?? true) && $count > 0) {
            foreach ($series as $si => $s) {
                $last = count($s['values']) - 1;
                if ($last < 0) {
                    continue;
                }
                $value = $s['values'][$last];
                $x = self::PAD_LEFT + $step * $last;
                $y = $baseline - ($max > 0 ? ($value / $max) * $plotH : 0);
                $svg .= '<text class="val ink-2" x="' . round($x - 4, 1) . '" y="' . round($y - 10, 1)
                    . '" text-anchor="end">' . $this->fmt($value, $chart['format'] ?? 'number') . '</text>';
            }
        }

        foreach ($labels as $i => $label) {
            $svg .= '<text class="lbl ink-2" x="' . round(self::PAD_LEFT + $step * $i, 1) . '" y="' . ($baseline + 16)
                . '" text-anchor="middle">' . $this->esc((string) $label) . '</text>';
        }

        return $svg . $this->axisLine($baseline);
    }

    private function yGrid(float $max, float $plotH, string $format): string
    {
        $svg = '';
        $steps = 4;

        for ($i = 0; $i <= $steps; $i++) {
            $value = $max * $i / $steps;
            $y = self::PAD_TOP + $plotH - ($plotH * $i / $steps);

            $svg .= '<line class="grid" x1="' . self::PAD_LEFT . '" y1="' . round($y, 1) . '" x2="'
                . (self::WIDTH - self::PAD_RIGHT) . '" y2="' . round($y, 1) . '"/>';
            $svg .= '<text class="lbl ink-2" x="' . (self::PAD_LEFT - 8) . '" y="' . round($y + 4, 1)
                . '" text-anchor="end">' . $this->fmt($value, $format) . '</text>';
        }

        return $svg;
    }

    private function axisLine(float $baseline): string
    {
        return '<line class="axis" x1="' . self::PAD_LEFT . '" y1="' . round($baseline, 1) . '" x2="'
            . (self::WIDTH - self::PAD_RIGHT) . '" y2="' . round($baseline, 1) . '"/>';
    }

    /** Bar growing upward: rounded at the data end, square on the baseline. */
    private function roundedTopBar(float $x, float $y, float $w, float $h, string $class): string
    {
        if ($h <= 0) {
            return '';
        }

        $r = min(self::BAR_RADIUS, $h, $w / 2);
        $x = round($x, 1);
        $y = round($y, 1);
        $w = round($w, 1);
        $h = round($h, 1);

        return '<path class="' . $class . '" d="M' . $x . ',' . ($y + $h)
            . 'L' . $x . ',' . ($y + $r)
            . 'Q' . $x . ',' . $y . ' ' . ($x + $r) . ',' . $y
            . 'L' . ($x + $w - $r) . ',' . $y
            . 'Q' . ($x + $w) . ',' . $y . ' ' . ($x + $w) . ',' . ($y + $r)
            . 'L' . ($x + $w) . ',' . ($y + $h) . 'Z"/>';
    }

    /** Bar growing rightward: rounded at the data end, square at the axis. */
    private function roundedEndBar(float $x, float $y, float $w, float $h, string $class): string
    {
        if ($w <= 0) {
            return '';
        }

        $r = min(self::BAR_RADIUS, $w, $h / 2);
        $x = round($x, 1);
        $y = round($y, 1);
        $w = round($w, 1);
        $h = round($h, 1);

        return '<path class="' . $class . '" d="M' . $x . ',' . $y
            . 'L' . ($x + $w - $r) . ',' . $y
            . 'Q' . ($x + $w) . ',' . $y . ' ' . ($x + $w) . ',' . ($y + $r)
            . 'L' . ($x + $w) . ',' . ($y + $h - $r)
            . 'Q' . ($x + $w) . ',' . ($y + $h) . ' ' . ($x + $w - $r) . ',' . ($y + $h)
            . 'L' . $x . ',' . ($y + $h) . 'Z"/>';
    }

    /**
     * @param array<string, mixed> $chart
     */
    private function renderLegend(array $chart): string
    {
        $svg = '';
        $x = self::PAD_LEFT;
        $y = self::HEIGHT + 12;

        foreach ($chart['series'] as $i => $series) {
            $svg .= '<rect class="s' . ($i % 8) . '" x="' . round($x, 1) . '" y="' . ($y - 8) . '" width="10" height="10" rx="2"/>';
            $svg .= '<text class="lbl ink-2" x="' . round($x + 15, 1) . '" y="' . $y . '">'
                . $this->esc((string) $series['name']) . '</text>';

            $x += 22 + mb_strlen((string) $series['name']) * 6.2;
        }

        return $svg;
    }

    /**
     * @param array<int, int|float> $values
     */
    private function niceMax(array $values): float
    {
        $max = empty($values) ? 0 : max(array_map('floatval', $values));

        if ($max <= 0) {
            return 1;
        }

        $magnitude = 10 ** floor(log10($max));
        $normalised = $max / $magnitude;

        // Fine-grained ladder so the axis sits just above the data rather than
        // leaving half the plot empty (266 → 300, not 500). Every step divides
        // into 4 gridlines cleanly enough for readable tick labels.
        $step = match (true) {
            $normalised <= 1 => 1,
            $normalised <= 1.2 => 1.2,
            $normalised <= 1.6 => 1.6,
            $normalised <= 2 => 2,
            $normalised <= 2.4 => 2.4,
            $normalised <= 3 => 3,
            $normalised <= 4 => 4,
            $normalised <= 5 => 5,
            $normalised <= 6 => 6,
            $normalised <= 8 => 8,
            default => 10,
        };

        return $step * $magnitude;
    }

    private function fmt(float|int $value, string $format): string
    {
        if ($format === 'money') {
            return abs($value) >= 1000000
                ? round($value / 1000000, 1) . 'M'
                : (abs($value) >= 1000 ? round($value / 1000, 1) . 'k' : number_format((float) $value, 0));
        }

        return $value == (int) $value ? (string) (int) $value : (string) round((float) $value, 1);
    }

    private function truncate(string $text, int $length): string
    {
        return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 1) . '…' : $text;
    }

    private function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
