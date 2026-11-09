<?php

/**
 * Recruitment (hiring) module configuration.
 *
 * These are the handful of knobs the native port inherited from the FastAPI
 * service's `app/core/config.py`. They live here rather than in `.env` because
 * none of them is deployment-specific: they describe the CSC screening rules,
 * and two installations that scored the same resume differently would not be
 * running the same system.
 *
 * See docs/hiring-backend-native-port.md.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Business calendar
    |--------------------------------------------------------------------------
    |
    | Every *calendar-day* question — has a closing date passed, what day was
    | this decision made — is answered in the application's own timezone, not
    | UTC and not the host clock. Laravel already stores its timestamps in
    | `app.timezone`, so the module simply uses that one calendar everywhere.
    |
    */

    'business_timezone' => env('APP_TIMEZONE', 'Asia/Manila'),

    /*
    |--------------------------------------------------------------------------
    | Position lifecycle
    |--------------------------------------------------------------------------
    |
    | How long a lapsed position's detail page keeps serving the shared
    | "no longer accepting applications" state. Spec floor is 7 days and the
    | service clamps to it, so lowering this cannot shorten a window a
    | candidate was promised.
    |
    */

    'grace_period_days' => max(7, (int) env('HIRING_GRACE_PERIOD_DAYS', 7)),

    /*
    |--------------------------------------------------------------------------
    | Screening
    |--------------------------------------------------------------------------
    |
    | `skill_similarity_floor` is the reference's SKILL_SIMILARITY_FLOOR. Under
    | the staff-only port skill matching is Jaccard token overlap (there is no
    | Word2Vec model in PHP), and the floor is kept at the reference value so a
    | ported score means the same thing it did before.
    |
    | `model_version` is written onto every screening result. It must name what
    | actually ran — see docs/hiring-backend-native-port.md §15.7 — so it says
    | "php-fallback", never a model that was not loaded.
    |
    */

    'skill_similarity_floor' => (float) env('HIRING_SKILL_SIMILARITY_FLOOR', 0.60),

    'model_version' => env('HIRING_MODEL_VERSION', 'php-fallback-regex-1.0'),

    /*
    |--------------------------------------------------------------------------
    | Resume storage
    |--------------------------------------------------------------------------
    |
    | Resumes are personal documents, so they live on the private local disk
    | and are served only through the authenticated download route. The
    | reference's limit was 10 MB per file.
    |
    */

    'disk' => env('HIRING_RESUME_DISK', 'local'),

    'resume_directory' => 'recruitment/resumes',

    'max_resume_bytes' => 10 * 1024 * 1024,

    'accepted_extensions' => ['pdf', 'docx', 'txt'],
];
