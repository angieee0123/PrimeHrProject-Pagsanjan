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
];
