<?php

namespace App\Support;

/**
 * Advance widths for Times, so the printed Pass Slip can size a value to the
 * ruled line it has to sit on.
 *
 * The Pass Slip form is laid out at absolute point coordinates lifted from the
 * HRMO's own document, which means every ruled line has a known width and a
 * value that runs past it has nowhere to go — `overflow: hidden` is not
 * reliable in dompdf, so a long entry has to be *measured* and stepped down to
 * a size that fits rather than clipped after the fact.
 *
 * The numbers are the Times-Roman and Times-Bold AFM advance widths in
 * 1/1000 em. dompdf's built-in Times and the browsers' Times New Roman are
 * both metric-compatible with them, so one table serves both renderers.
 */
final class TimesText
{
    private const ROMAN = [
        ' ' => 250, '!' => 333, '"' => 408, '#' => 500, '$' => 500, '%' => 833, '&' => 778,
        "'" => 333, '(' => 333, ')' => 333, '*' => 500, '+' => 564, ',' => 250, '-' => 333,
        '.' => 250, '/' => 278, '0' => 500, '1' => 500, '2' => 500, '3' => 500, '4' => 500,
        '5' => 500, '6' => 500, '7' => 500, '8' => 500, '9' => 500, ':' => 278, ';' => 278,
        '<' => 564, '=' => 564, '>' => 564, '?' => 444, '@' => 921,
        'A' => 722, 'B' => 667, 'C' => 667, 'D' => 722, 'E' => 611, 'F' => 556, 'G' => 722,
        'H' => 722, 'I' => 333, 'J' => 389, 'K' => 722, 'L' => 611, 'M' => 889, 'N' => 722,
        'O' => 722, 'P' => 556, 'Q' => 722, 'R' => 667, 'S' => 556, 'T' => 611, 'U' => 722,
        'V' => 722, 'W' => 944, 'X' => 722, 'Y' => 722, 'Z' => 611,
        '[' => 333, '\\' => 278, ']' => 333, '_' => 500,
        'a' => 444, 'b' => 500, 'c' => 444, 'd' => 500, 'e' => 444, 'f' => 333, 'g' => 500,
        'h' => 500, 'i' => 278, 'j' => 278, 'k' => 500, 'l' => 278, 'm' => 778, 'n' => 500,
        'o' => 500, 'p' => 500, 'q' => 500, 'r' => 333, 's' => 389, 't' => 278, 'u' => 500,
        'v' => 500, 'w' => 722, 'x' => 500, 'y' => 500, 'z' => 444,
    ];

    private const BOLD = [
        ' ' => 250, '!' => 333, '"' => 555, '&' => 833, "'" => 333, '(' => 333, ')' => 333,
        ',' => 250, '-' => 333, '.' => 250, '/' => 278, '0' => 500, '1' => 500, '2' => 500,
        '3' => 500, '4' => 500, '5' => 500, '6' => 500, '7' => 500, '8' => 500, '9' => 500,
        ':' => 333, ';' => 333, '?' => 500,
        'A' => 722, 'B' => 667, 'C' => 722, 'D' => 722, 'E' => 667, 'F' => 611, 'G' => 778,
        'H' => 778, 'I' => 389, 'J' => 500, 'K' => 778, 'L' => 667, 'M' => 944, 'N' => 722,
        'O' => 778, 'P' => 611, 'Q' => 778, 'R' => 722, 'S' => 556, 'T' => 667, 'U' => 722,
        'V' => 722, 'W' => 1000, 'X' => 722, 'Y' => 722, 'Z' => 667, '_' => 500,
        'a' => 500, 'b' => 556, 'c' => 444, 'd' => 556, 'e' => 444, 'f' => 333, 'g' => 500,
        'h' => 556, 'i' => 278, 'j' => 333, 'k' => 556, 'l' => 278, 'm' => 833, 'n' => 556,
        'o' => 500, 'p' => 556, 'q' => 556, 'r' => 444, 's' => 389, 't' => 333, 'u' => 556,
        'v' => 500, 'w' => 722, 'x' => 500, 'y' => 500, 'z' => 444,
    ];

    /** Width of $text at $size points. */
    public static function width(string $text, float $size, bool $bold = false): float
    {
        $table = $bold ? self::BOLD : self::ROMAN;
        $units = 0;

        foreach (self::chars($text) as $ch) {
            $units += $table[$ch] ?? 500;
        }

        return $units / 1000 * $size;
    }

    /**
     * The largest size no bigger than $size at which $text fits $maxWidth.
     *
     * Steps down in half points and stops at $min: past that the entry is
     * unreadable, and a value that still will not fit is better slightly
     * cramped than shrunk to nothing.
     */
    public static function fit(string $text, float $maxWidth, float $size = 12.0, float $min = 7.5): float
    {
        for ($try = $size; $try >= $min; $try -= 0.5) {
            if (self::width($text, $try) <= $maxWidth) {
                return $try;
            }
        }

        return $min;
    }

    /**
     * The largest size at which $text breaks into no more than $maxLines lines
     * of $width, with the lines themselves.
     *
     * `fit()` alone is not enough for the Certificate of Appearance: its rules
     * are a fixed 322pt and a pass slip's stated purpose can run to 300
     * characters, which no readable size will fit on one line. Rather than let
     * the text run off the edge of the form, it is stepped down and then
     * wrapped, and only genuinely unfittable text is left slightly cramped on
     * the last line.
     *
     * @return array{0: float, 1: string[]}
     */
    public static function fitLines(string $text, float $width, int $maxLines = 2, float $size = 12.0, float $min = 7.0): array
    {
        for ($try = $size; $try >= $min; $try -= 0.5) {
            $lines = self::greedy($text, $width, $try);

            if (count($lines) <= $maxLines) {
                return [$try, array_pad($lines, $maxLines, '')];
            }
        }

        return [$min, self::wrap($text, array_fill(0, $maxLines, $width), $min)];
    }

    /** Greedy wrap into as many lines of $width as the text needs. */
    private static function greedy(string $text, float $width, float $size): array
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = $line === '' ? $word : "$line $word";

            if ($line !== '' && self::width($candidate, $size) > $width) {
                $lines[] = $line;
                $line = $word;
                continue;
            }

            $line = $candidate;
        }

        $lines[] = $line;

        return $lines;
    }

    /**
     * Break $text across ruled lines of the given widths, one string per line.
     *
     * Always returns count($widths) entries so every rule prints whether or not
     * it is used. Overflow past the last line stays on it rather than being
     * cut — losing the end of a stated purpose is worse than a cramped line.
     */
    public static function wrap(string $text, array $widths, float $size): array
    {
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $i = count($lines);

            if ($i >= count($widths) - 1) {
                $line = $line === '' ? $word : "$line $word";
                continue;
            }

            $candidate = $line === '' ? $word : "$line $word";

            if (self::width($candidate, $size) <= $widths[$i]) {
                $line = $candidate;
                continue;
            }

            $lines[] = $line;
            $line = $word;
        }

        $lines[] = $line;

        return array_pad(array_slice($lines, 0, count($widths)), count($widths), '');
    }

    /** @return string[] */
    private static function chars(string $text): array
    {
        // The form's values are Latin; anything wider is measured as a average
        // glyph rather than dropped, which keeps the fit conservative.
        return preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    }
}
