<?php

namespace App\Services;

use App\Models\TravelOrder;
use Carbon\Carbon;

/**
 * The values printed on the office's Authority to Travel.
 *
 * Same division of labour as PassSlipFormDataService: everything that has to be
 * *decided* — which personnel travel, which date heads the letter, who signs
 * which rule — is settled here, and the view is left to place already-resolved
 * strings at the template's coordinates.
 */
class TravelOrderFormDataService
{
    /**
     * Values the signatory columns carry when nobody has been named. They must
     * not reach a signature line: printing "n/a" over "Municipal Administrator"
     * is worse than leaving the rule blank for a wet signature, which is what
     * the paper form expects anyway.
     */
    private const PLACEHOLDER_NAMES = ['n/a', 'na', 'n.a.', '-', '--', 'none', 'tbd', 'not applicable'];

    /** The width the "Puso ng Pagsanjan" mark occupies in the template's footer. */
    private const FOOTER_LOGO_WIDTH = 146.0;

    /** Its height there, used only when the file itself cannot be measured. */
    private const FOOTER_LOGO_FALLBACK_HEIGHT = 42.0;

    public function build(int $travelOrderId): array
    {
        $order = TravelOrder::with([
            'employee.employmentDetail.departmentRelation',
            'employee.employmentDetail.designationRelation',
            'approver.employee.employmentDetail.designationRelation',
            'companions.employee.employmentDetail.designationRelation',
        ])->findOrFail($travelOrderId);

        // dompdf cannot fetch a URL, so the seal is embedded. Via the service so
        // an uploaded logo reaches the printed form too, and so the MIME type is
        // derived rather than assumed.
        $seal = SiteContentService::logoDataUri();

        // The letter is dated when the authority was granted, not when it is
        // reprinted: a "generated on" stamp would move every time somebody
        // opened the form, and the copy in the file would stop matching the
        // copy on screen.
        $issued = $order->approved_at ?? $order->created_at;

        // Computed once and handed to detailRows() rather than derived again
        // there: the sheet's DURATION OF TRAVEL row and any caller reading
        // `durationText` have to be the same sentence.
        $destination = (string) $order->destination;
        $durationText = $this->durationText($order);
        $purpose = trim((string) $order->purpose);

        return [
            'order' => $order,
            'orderNumber' => $order->order_number,
            'issuedDate' => $issued ? Carbon::parse($issued)->format('F d, Y') : '',

            'personnel' => $this->personnel($order),

            // The template's three labelled rows, plus the two the record can
            // carry and the template has no printed row for. See detailRows().
            'detailRows' => $this->detailRows($order, $destination, $durationText, $purpose),

            'destination' => $destination,
            'durationText' => $durationText,
            'purpose' => $purpose,

            'recommendingName' => $this->recommendingName($order),
            'recommendingTitle' => (string) config('cs_form.travel_order.recommending.title'),
            'approvingName' => $this->realName(config('cs_form.travel_order.approving.name')),
            'approvingTitle' => (string) config('cs_form.travel_order.approving.title'),

            'isApproved' => $order->status === 'approved',

            'letterhead' => $this->letterhead(),
            'footer' => $this->footer(),
            'sealBase64' => $seal,
            'agencyName' => config('cs_form.agency_name'),
            'agencyAddress' => config('cs_form.agency_address'),
        ];
    }

    /**
     * Everyone the order grants authority to travel: the filer, then the
     * companions who accepted.
     *
     * Only accepted companions are named. A companion who declined is not
     * travelling, and a form that lists them states that the municipality
     * authorised — and will reimburse the per diem of — somebody who said no.
     * A companion still pending cannot be on an approved order either: the
     * order does not reach HR until every invitation has been answered.
     *
     * @return array<int, array{name: string, designation: string}>
     */
    private function personnel(TravelOrder $order): array
    {
        $rows = [$this->personnelRow($order->employee)];

        foreach ($order->companions as $companion) {
            if ($companion->status !== 'accepted' || !$companion->employee) {
                continue;
            }

            $rows[] = $this->personnelRow($companion->employee);
        }

        return array_values(array_filter($rows, fn ($row) => $row['name'] !== ''));
    }

    /** @return array{name: string, designation: string} */
    private function personnelRow($employee): array
    {
        return [
            // The template sets both columns in caps.
            'name' => mb_strtoupper($this->naturalName($employee)),
            'designation' => mb_strtoupper(
                (string) ($employee?->employmentDetail?->designationRelation?->title ?? '')
            ),
        ];
    }

    /**
     * The label/value rows of the middle block.
     *
     * The first three are the template's own. Transportation and the estimated
     * budget are appended *only when the record carries them*, so an order
     * filed like the office's sample prints the sample's three rows exactly:
     * both columns are nullable, and the form should not print an empty label
     * for something nobody entered. They are set in the same style, in the
     * block's own trailing space, rather than in a box of their own — the
     * paragraph directly beneath them is about per diem and transportation
     * allowance, which is the figure an auditor reads them against.
     *
     * @return array<int, array{0: string, 1: string}>
     */
    private function detailRows(TravelOrder $order, string $destination, string $durationText, string $purpose): array
    {
        $rows = [
            ['DESTINATION', $destination],
            ['DURATION OF TRAVEL', $durationText],
            ['PURPOSE OF TRAVEL', $purpose],
        ];

        if (trim((string) $order->transportation_mode) !== '') {
            $rows[] = ['MEANS OF TRANSPORTATION', trim((string) $order->transportation_mode)];
        }

        if ($order->estimated_budget !== null && (float) $order->estimated_budget > 0) {
            $rows[] = ['ESTIMATED BUDGET', 'PHP ' . number_format((float) $order->estimated_budget, 2)];
        }

        return $rows;
    }

    /**
     * The travel dates as the template spells them.
     *
     * A one-day trip prints the bare long date, which is what the office's
     * sample shows. A range names both ends and the filed day count — the
     * duration column is what the employee typed, and it does not always equal
     * the span between the two dates (a Saturday in the middle is still one
     * calendar day and not always a travelled one), so it is printed rather
     * than recomputed.
     */
    private function durationText(TravelOrder $order): string
    {
        $from = $order->travel_date ? Carbon::parse($order->travel_date) : null;
        $to = $order->return_date ? Carbon::parse($order->return_date) : null;

        if (!$from) {
            return '';
        }

        if (!$to || $from->isSameDay($to)) {
            return $from->format('F d, Y');
        }

        $days = (int) $order->duration;

        return $from->format('F d, Y') . ' to ' . $to->format('F d, Y')
            . ($days > 0 ? ' (' . $days . ' ' . ($days === 1 ? 'day' : 'days') . ')' : '');
    }

    /**
     * Who signs "Recommending Approval".
     *
     * The approval recorded in the system *is* the recommendation, so the name
     * is whoever approved the order. `users` has no `name` column — only
     * `username` — so the name is resolved through the approver's employee
     * record first, the way TravelOrderController::show() does it for the modal.
     * With nothing resolvable it falls back to the configured officer, and with
     * that empty the rule prints blank rather than naming the wrong person.
     */
    private function recommendingName(TravelOrder $order): string
    {
        $approver = $order->approver;

        $name = $approver ? $this->naturalName($approver->employee) : '';

        if ($name === '') {
            $name = $this->realName($approver?->username);
        }

        if ($name === '') {
            $name = $this->realName(config('cs_form.travel_order.recommending.name'));
        }

        return mb_strtoupper($name);
    }

    /** The letterhead wording, with the tourism mark embedded. */
    private function letterhead(): array
    {
        $head = config('cs_form.letterhead', []);

        return [
            // The Travel Order's masthead carries no office line and no HRMO
            // seal, so it cannot reuse `letterhead.masthead` — it is drawn from
            // the wording plus the seal and the tourism mark.
            'republic' => $head['republic'] ?? '',
            'municipality' => $head['municipality'] ?? config('cs_form.agency_name'),
            'tagline' => $head['tagline'] ?? '',
            'mark' => $this->embed(config('cs_form.travel_order.letterhead.mark')),
        ];
    }

    /** The tourism footer: the mark if it is on disk, and the contact line. */
    private function footer(): array
    {
        $footer = config('cs_form.travel_order.footer', []);
        $logo = $footer['logo'] ?? null;

        return [
            'logo' => $this->embed($logo),
            // The mark is drawn at the template's own 146pt width and its
            // height follows the file's proportions, so re-cutting the artwork
            // — a tighter crop, a 2x export — moves the height rather than
            // squashing the heart. Measured here because a Blade view cannot
            // read the file, and it is the same file embed() just read.
            'logoHeight' => $this->scaledHeight($logo, self::FOOTER_LOGO_WIDTH),
            'telephone' => $footer['telephone'] ?? '',
            'email' => $footer['email'] ?? '',
        ];
    }

    /**
     * The height $relative takes at $width, keeping its own aspect ratio.
     *
     * A file that cannot be measured falls back to the template's slot rather
     * than to nothing: `height: auto` would be the obvious alternative, but
     * dompdf resolves it against the image's pixel size, which for a 2x export
     * prints the mark at twice the height the letter has room for.
     */
    private function scaledHeight(?string $relative, float $width): float
    {
        $file = $relative ? public_path($relative) : null;
        $size = ($file && is_file($file)) ? @getimagesize($file) : false;

        if (!$size || empty($size[0])) {
            return self::FOOTER_LOGO_FALLBACK_HEIGHT;
        }

        return round($width * $size[1] / $size[0], 2);
    }

    /**
     * A file under public/ as a data: URI, or '' when it is not there.
     *
     * dompdf cannot fetch a URL, and a missing image must leave a gap rather
     * than a broken-image box on an official form.
     */
    private function embed(?string $relative): string
    {
        if (!$relative) {
            return '';
        }

        $file = public_path($relative);

        if (!is_file($file)) {
            return '';
        }

        $mime = @mime_content_type($file) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    /** "First M. Last Jr." — the spelling the template's Name column uses. */
    private function naturalName($employee): string
    {
        if (!$employee) {
            return '';
        }

        $initial = $employee->middle_name ? substr($employee->middle_name, 0, 1) . '.' : '';

        return trim(preg_replace('/\s+/', ' ', implode(' ', array_filter([
            $employee->first_name,
            $initial,
            $employee->last_name,
            $employee->suffix ?? null,
        ]))));
    }

    /** Null out the placeholders sitting in the signatory columns. */
    private function realName(?string $name): string
    {
        $name = trim((string) $name);

        if ($name === '' || in_array(mb_strtolower($name), self::PLACEHOLDER_NAMES, true)) {
            return '';
        }

        return $name;
    }
}
