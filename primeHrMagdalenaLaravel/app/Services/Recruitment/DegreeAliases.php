<?php

namespace App\Services\Recruitment;

/**
 * HR-approved degree alias map — owned by HRMO Qualification Standards.
 *
 * A literal port of the reference's `services/degree_aliases.py`. Each key is
 * lower-cased and punctuation/space-normalized before lookup (see
 * `DegreeNormalizer`). Only listed aliases normalize; hyper-specialized degrees
 * are deliberately deferred rather than guessed at.
 *
 * The graduate entries exist so the scorer's existing "degree" substring check
 * registers an MA/MS/MBA/PhD/JD without any change to the scoring formula.
 */
final class DegreeAliases
{
    /** @var array<string, string> */
    public const ALIASES = [
        // ── BSES variants — all map to the Envi Sci canonical (HR-signed) ──
        'bses' => 'bachelor of science in environmental science',
        'b.s.e.s.' => 'bachelor of science in environmental science',
        'bs environmental science' => 'bachelor of science in environmental science',
        'b.s. environmental science' => 'bachelor of science in environmental science',
        'bs in environmental science' => 'bachelor of science in environmental science',
        'b.s. in environmental science' => 'bachelor of science in environmental science',
        'bs env sci' => 'bachelor of science in environmental science',
        'bs env. sci.' => 'bachelor of science in environmental science',
        'bs in env sci' => 'bachelor of science in environmental science',
        'b.s. in env sci' => 'bachelor of science in environmental science',
        'bachelor of science in environmental science' => 'bachelor of science in environmental science',
        "bachelor's degree in environmental science" => 'bachelor of science in environmental science',
        'bachelors degree in environmental science' => 'bachelor of science in environmental science',
        'bachelor degree in environmental science' => 'bachelor of science in environmental science',

        // ── MA → master's degree ──
        'ma' => "master's degree",
        'm.a.' => "master's degree",
        'm a' => "master's degree",
        'master of arts' => "master's degree",
        "master's degree" => "master's degree",

        // ── MS / MSc → master's degree ──
        'ms' => "master's degree",
        'm.s.' => "master's degree",
        'm s' => "master's degree",
        'msc' => "master's degree",
        'm.sc.' => "master's degree",
        'm sc' => "master's degree",
        'master of science' => "master's degree",

        // ── MBA → master's degree in business administration ──
        'mba' => "master's degree in business administration",
        'm.b.a.' => "master's degree in business administration",
        'm b a' => "master's degree in business administration",
        'master of business administration' => "master's degree in business administration",

        // ── PhD / DPhil / Doctorate → doctoral degree ──
        'phd' => 'doctoral degree',
        'ph.d.' => 'doctoral degree',
        'ph d' => 'doctoral degree',
        'dphil' => 'doctoral degree',
        'd.phil.' => 'doctoral degree',
        'd phil' => 'doctoral degree',
        'doctor of philosophy' => 'doctoral degree',
        'doctorate' => 'doctoral degree',
        'doctoral degree' => 'doctoral degree',

        // ── JD → juris doctor (kept distinct; QS often lists it verbatim) ──
        'jd' => 'juris doctor',
        'j.d.' => 'juris doctor',
        'j d' => 'juris doctor',
        'juris doctor' => 'juris doctor',

        // ── Field-preserving variants (env sci) ──
        'master of science in environmental science' => "master's degree in environmental science",
        'master of arts in environmental science' => "master's degree in environmental science",
        'm.s. in environmental science' => "master's degree in environmental science",
        'ms in environmental science' => "master's degree in environmental science",
        'm.s in environmental science' => "master's degree in environmental science",
        "master's degree in environmental science" => "master's degree in environmental science",
        'masters degree in environmental science' => "master's degree in environmental science",
    ];

    /** @var array<int, string> */
    public const BACHELOR_CANONICALS = [
        'bachelor of science in environmental science',
    ];

    /** @var array<int, string> */
    public const GRADUATE_CANONICALS = [
        "master's degree",
        "master's degree in business administration",
        "master's degree in environmental science",
        'doctoral degree',
        'juris doctor',
    ];

    /** @var array<int, string> */
    public const MASTER_CANONICALS = [
        "master's degree",
        "master's degree in business administration",
        "master's degree in environmental science",
    ];

    /** @var array<int, string> */
    public const DOCTORAL_CANONICALS = [
        'doctoral degree',
        'juris doctor',
    ];
}
