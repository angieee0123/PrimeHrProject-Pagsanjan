<?php

/*
 * The printed Daily Time Record — the office's own "Time Master · Employee
 * Attendance Logs" sheet, produced by the Detailed DTR modal's Print Form and
 * Download PDF buttons.
 *
 * What lives here is what an *office* changes: the branding artwork, the
 * heading, the field labels and the two signatories. What does not live here
 * is the sheet's geometry — the column positions, the rule pitch, the chip
 * sizes — because that is a tracing of the template measured pixel by pixel,
 * and it lives with the drawing in
 * `resources/views/admin/attendance/partials/employee-attendance-logs-form.blade.php`.
 *
 * Replacing the municipality's official form therefore means replacing that
 * one partial plus the values below; nothing in AttendanceController or
 * DtrFormDataService knows what the sheet looks like.
 */

return [

    /*
     * The wordmark at the top right. `wordmark` is a path under `public/`; if
     * the file is absent the form draws `name` + `tagline` in a bold sans at
     * the same place instead, so it still prints on an install where the
     * artwork was never copied across. See public/forms/dtr/README.md.
     */
    'brand' => [
        'wordmark' => 'forms/dtr/time-master-wordmark.png',
        'name'     => 'TIME MASTER',
        'tagline'  => 'Timekeeping System',
    ],

    'title' => 'EMPLOYEE ATTENDANCE LOGS',

    /*
     * The three identity chips under the title. Their *values* come from the
     * employee whose Detailed DTR is open — never from here.
     */
    'labels' => [
        'id'         => 'ID No. :',
        'department' => 'Dept. :',
        'name'       => 'Name :',
    ],

    /*
     * The seven column captions, keyed by the attendance slot each one prints.
     * Keyed rather than listed so a caption cannot be silently re-pointed at
     * the wrong slot: the form reads `am_in` and prints whatever caption sits
     * against it.
     *
     * The count is fixed at seven by the tracing — each chip has its own
     * measured width and position on the sheet. Adding an eighth column is a
     * change to the form partial, not a change here.
     */
    'columns' => [
        'date'   => 'DATE',
        'am_in'  => 'am IN',
        'am_out' => 'am OUT',
        'pm_in'  => 'pm IN',
        'pm_out' => 'pm OUT',
        'ot_in'  => 'OT IN',
        'ot_out' => 'OT OUT',
    ],

    /*
     * How many ruled rows the sheet holds. 24 is what the template draws, and
     * it is what paginates the export: records past the 24th open a
     * continuation page carrying the same head.
     *
     * It is configuration because it belongs to the *sheet* — re-cut the form
     * with more rows and this is the one number that has to follow, alongside
     * the rule pitch in the partial.
     */
    'rows_per_page' => 24,

    /*
     * The two officers who sign the completed DTR, printed left to right in
     * this order on the final page: HRMO first, then the Municipal
     * Administrator.
     *
     * `title` is always configuration: it is the *capacity* the person signs
     * in — an office, not a record this system holds — and it is not their
     * plantilla designation. The office's own template signs "HRMO - OIC" over
     * an Administrative Aide IV.
     *
     * `name_from => 'generator'` makes the printed name whoever produced the
     * DTR, because that is who certifies it: the HRMO staffer who pulled the
     * period and pressed the button is the one signing the sheet, and a form
     * naming somebody who did not generate it is a signature block nobody can
     * act on. Its `name` is then only a **fallback**, used when the account
     * that generated it carries no usable name at all.
     *
     * Without that key the name is printed exactly as typed here — which is
     * what the Municipal Administrator wants: nothing in this system records
     * that officer's decision, so the name is printed over a blank rule for a
     * wet signature.
     *
     * Leave a `name` empty and, with no generator to fall back from, that rule
     * prints unlabelled rather than naming the wrong officer.
     */
    'signatories' => [
        [
            'name'      => 'JEREMY R. POGI',
            'title'     => 'HRMO - OIC',
            'name_from' => 'generator',
        ],
        [
            'name'  => 'ENGR. ALEX C. PAGUIO',
            'title' => 'Municipal Administrator',
        ],
    ],

];
