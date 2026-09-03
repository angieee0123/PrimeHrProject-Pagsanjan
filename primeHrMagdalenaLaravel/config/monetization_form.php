<?php

/*
 * The printed Monetization sheet — the office's own one-page computation of
 * Total Leave Benefits, produced by the Print Sheet / Download PDF buttons on
 * the Monetization request.
 *
 * Same split as config/dtr_form.php: what lives here is what an *office*
 * changes — the heading, the field captions, the wording of the formula key
 * and the "Prepared by" block. What does not live here is the sheet's
 * geometry, because that is a tracing of the office's template measured off
 * the artwork, and it lives with the drawing in
 * `resources/views/admin/leaveAndBenefits/partials/monetization-form.blade.php`.
 *
 * Replacing the municipality's official form is therefore that one partial
 * plus the values below. Neither MonetizationRequestController nor
 * MonetizationFormDataService knows what the sheet looks like, and none of the
 * *figures* come from here — every peso and every leave credit is read off the
 * monetization_requests row the request was decided on.
 */

return [

    /*
     * The two heading lines above the title, printed centred. The template
     * carries the province in regular weight and the municipality in bold.
     */
    'heading' => [
        'province'     => 'Province of Laguna',
        'municipality' => 'Municipality of Pagsanjan',
    ],

    'title' => 'Monetization',

    /*
     * The identity captions down the left of the sheet. Their *values* come
     * from the employee the request belongs to — never from here.
     */
    'labels' => [
        'name'     => 'Name:',
        'position' => 'Position:',
        'salary'   => 'Salary:',

        // "as of <date>" is completed with the date the balances on the
        // request were read, not with today.
        'credits_as_of' => 'No. of Leave Credits as of',
        'vacation'      => 'Vacation Leave',
        'sick'          => 'Sick Leave',
        'total_credits' => 'Total Earned Leave Credits',

        'computation' => 'Computation:',
        'formula'     => 'Total Leave Benefits = S x D x CF',
    ],

    /*
     * The formula key — the four lines that read "S = Monthly Salary" and so
     * on. Keyed by symbol so a caption cannot be silently re-pointed at the
     * wrong term: the form prints whatever wording sits against each symbol.
     *
     * The symbols themselves are fixed by the arithmetic in
     * MonetizationRequest::computeAmount(); only the wording is configuration.
     */
    'legend' => [
        'S'   => 'Monthly Salary',
        'D'   => 'Total No. of Leave Credits',
        'CF'  => 'Constant Factor',
        'TLB' => 'S x D x CF',
    ],

    /*
     * The peso mark printed against the salary. The template writes a plain
     * "P" and the sheet is set in Times, whose dompdf core font has no ₱
     * glyph — a peso sign there prints as a hollow box on the signed copy.
     */
    'currency_symbol' => 'P',

    /* The suffix on the computed total, as the template writes it. */
    'amount_suffix' => 'Php',

    /*
     * The one signature block on the sheet.
     *
     * `name_from => 'generator'` makes the printed name whoever produced this
     * copy — the staffer who opened the request and pressed Print Sheet is the
     * one preparing it, so a fixed name there would put somebody else's name
     * on a document they never saw. Same rule as the DTR's HRMO signatory and
     * the Travel Order's `recommending.name`.
     *
     * `title` falls back to this wording only when the generating account
     * carries no designation of its own; an account linked to an employee row
     * signs under that employee's plantilla title, because on this sheet the
     * line under the name is a designation and not, as on the DTR, the
     * capacity an officer signs in.
     *
     * The person named here is the *preparer*, never the employee being paid:
     * MonetizationFormDataService resolves the two independently.
     */
    'prepared_by' => [
        'caption'   => 'Prepared by:',
        'name'      => '',
        'title'     => '',
        'name_from' => 'generator',
    ],

];
