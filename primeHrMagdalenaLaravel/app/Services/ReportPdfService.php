<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Turns a generated report (or chart) into a downloadable PDF.
 *
 * The assistant is stateless, so a report is stashed under a short-lived token
 * when it is produced and exchanged for a PDF on download. The token is bound
 * to the user who generated it — a leaked token is useless to anyone else, and
 * the payload expires on its own rather than accumulating on disk.
 */
class ReportPdfService
{
    private const TTL_SECONDS = 3600;

    public function __construct(private ChartRenderer $renderer)
    {
    }

    /**
     * Stash a report for later export. Returns the token the UI puts on its
     * download button.
     *
     * @param array<string, mixed> $report
     * @param array<int, array<string, mixed>> $rows
     * @param array<int, array<string, mixed>>|null $charts
     */
    public function stash(User $user, array $report, array $rows, ?array $charts = null): string
    {
        $token = Str::random(40);

        Cache::put($this->key($token), [
            'user_id' => $user->id,
            'report' => $report,
            'rows' => $rows,
            'charts' => $charts,
            'generated_at' => now()->toIso8601String(),
        ], self::TTL_SECONDS);

        return $token;
    }

    /**
     * Build the PDF, or null if the token is unknown, expired, or belongs to
     * a different user.
     */
    public function download(User $user, string $token): ?array
    {
        $payload = Cache::get($this->key($token));

        if (!$payload || (int) $payload['user_id'] !== (int) $user->id) {
            return null;
        }

        $report = $payload['report'];
        $charts = $payload['charts'] ?? null;

        $svg = null;
        if (!empty($charts)) {
            $svg = collect($charts)->map(fn (array $chart) => $this->renderer->render($chart))->implode('');
        }

        $pdf = Pdf::loadView('ai.report-pdf', [
            'title' => $report['title'] ?? 'Report',
            'period' => $report['period'] ?? null,
            'columns' => $report['columns'] ?? [],
            'rows' => $payload['rows'] ?? [],
            'totals' => $report['totals'] ?? [],
            'chartSvg' => $svg,
            'generatedAt' => $payload['generated_at'],
            'generatedBy' => $user->username ?? $user->email,
        ])->setPaper('a4', $this->orientation($report));

        $filename = Str::slug($report['title'] ?? 'report') . '-' . now()->format('Ymd-His') . '.pdf';

        return ['pdf' => $pdf, 'filename' => $filename];
    }

    /**
     * Wide tables get landscape so columns are not crushed.
     *
     * @param array<string, mixed> $report
     */
    private function orientation(array $report): string
    {
        return count($report['columns'] ?? []) > 6 ? 'landscape' : 'portrait';
    }

    private function key(string $token): string
    {
        return 'ai:report:' . $token;
    }
}
