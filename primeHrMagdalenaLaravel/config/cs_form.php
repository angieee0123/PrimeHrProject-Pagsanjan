<?php

return [
    'agency_name' => 'Municipal Government of Pagsanjan',
    'agency_address' => 'Pagsanjan, Laguna',
    'logo_path' => 'municipal-of-pagsanjan-logo.jpg',

    /*
     * The letterhead printed on the HRMO's own forms (Pass Slip / Certificate
     * of Appearance). It is the *office's* masthead, not the municipality's
     * website chrome — the telephone number here is the HRMO's direct line and
     * is deliberately not the Municipal Hall number in Website Content.
     *
     * `masthead` + `mark` are the office's own artwork, lifted from the HRMO's
     * PASS SLIP (NEW).pdf, so the printed form carries the exact letterhead
     * rather than a rebuilt lookalike. The masthead is one image — seal,
     * blackletter wording, flourish, office name and telephone line together —
     * which is how the source document holds it.
     *
     * If either file is missing the form *draws* the masthead instead, from
     * the wording below plus `emblems`. That fallback is why the text is still
     * configuration: it keeps the form printing on an install where the
     * artwork was never copied across.
     */
    'letterhead' => [
        'masthead'     => 'forms/letterhead/masthead.png',
        'mark'         => 'forms/letterhead/i-love-pagsanjan.png',

        'republic'     => 'Republic of the Philippines',
        'municipality' => 'Municipality of Pagsanjan',
        'tagline'      => 'The Tourist Capital of Laguna',
        'office'       => 'Human Resource Management Office',
        'telephone'    => 'Telephone number: (049) 501 - 9994',
        'emblems'      => [
            'forms/letterhead/hrmo-logo.png',
            'forms/letterhead/i-love-pagsanjan.png',
        ],
    ],

    /*
     * The Travel Order's letterhead, footer and signature blocks.
     *
     * The Travel Order is issued by the *municipality*, not by the HRMO, so it
     * does not wear `letterhead` above: its masthead carries no office line and
     * no HRMO seal, and it is closed by the tourism footer rather than by the
     * HRMO's telephone line. It is drawn from the wording here plus the seal
     * from SiteContentService and the tourism mark, which is what the office's
     * own template shows — there is no single-image masthead for this form.
     *
     * The two signatories are configuration because they are *offices*, not
     * records this system holds:
     *
     *  - `recommending.name` is only a fallback. The name printed is whoever
     *    approved the order in the system (`travel_orders.approved_by`), since
     *    that approval *is* the recommendation. `recommending.title` is the
     *    capacity that person signs in, which is not their plantilla
     *    designation — the office's own template signs "HRMO - OIC" over an
     *    Administrative Aide IV.
     *  - `approving` is the final signature. Nothing in this system records the
     *    Municipal Administrator's decision, so the name is printed from here
     *    over a blank rule for a wet signature. Leave `name` empty and the rule
     *    prints unlabelled rather than naming the wrong officer.
     */
    'travel_order' => [
        'letterhead' => [
            'mark' => 'forms/letterhead/i-love-pagsanjan.png',
        ],

        /*
         * `logo` is the "Puso ng Pagsanjan" mark at the foot of the template.
         * It is not shipped with this project; drop the office's own artwork
         * in at that path and the footer picks it up on the next render — no
         * code change, no restart. Until then the footer prints the contact
         * rule across the full column rather than a broken-image box.
         *
         * It is drawn 146pt wide, the width the template gives it, and its
         * height follows the file's own aspect ratio, so a tighter crop or a
         * 2x export prints taller instead of squashed. The telephone and the
         * address are badged with the ☎ and ✉ glyphs; the template's own icons
         * are artwork this project does not ship either.
         */
        'footer' => [
            'logo'      => 'forms/letterhead/pusong-pagsanjan.png',
            'telephone' => '(049) 808 4057',
            'email'     => 'januarioferry.garcia@gmail.com',
        ],

        'recommending' => [
            'name'  => '',
            'title' => 'HRMO - OIC',
        ],

        'approving' => [
            'name'  => 'ENGR. ALEX C. PAGUIO',
            'title' => 'Municipal Administrator',
        ],
    ],
];
