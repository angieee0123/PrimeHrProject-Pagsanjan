<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use App\Models\SiteContent;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

/**
 * The public welcome page's editable copy.
 *
 * Everything the page says used to be a literal in welcome.blade.php — the
 * announcements, the service catalogue, the vision and mission, the phone
 * numbers — so changing a advisory meant editing Blade and redeploying.
 * This service is where that copy lives now, and it owns three rules:
 *
 *  1. **Defaults are the current page, verbatim.** A section that has never
 *     been saved renders exactly what the hard-coded version rendered, so
 *     installing this changes nothing until an administrator edits something.
 *
 *  2. **A saved section is merged over its defaults, never swapped for them.**
 *     Adding a field here must not blank it out on installations that saved
 *     the section before the field existed.
 *
 *  3. **Counts are read, not typed.** The hero's "Offices & Departments" and
 *     "Government Personnel" figures come from the tables that own them.
 *     They were literals — 17 and 348 — on a public page, next to an HR system
 *     that knows the real numbers; the same reasoning as HrPolicyFactsService,
 *     which stopped restating leave rules and started reading them.
 *
 * Icons and tag colours are chosen from fixed vocabularies (ICONS,
 * ANNOUNCEMENT_TAGS). The editor never accepts markup — an administrator
 * picks a name and the Blade looks up the SVG.
 */
class SiteContentService
{
    /** Sections the editor can write. Anything else is rejected. */
    /**
     * The rail, in two groups and in the order they appear.
     *
     * The split is by *why* something changes, not by how big it is:
     *
     *  · **Everyday updates** — it changed because the world changed. A new
     *    advisory, a new phone number, a reworded headline. Nothing to learn.
     *  · **Page setup** — it changed because you are redesigning the site. What
     *    services exist, what the logo is, where footer links point. These carry
     *    rules: icons from a fixed list, links matching a pattern, files with
     *    size limits.
     *
     * Announcements leads the whole rail. It is a repeatable list, which by the
     * rule above would read as setup — but posting an advisory is the reason
     * this editor exists, and it is the only content that goes stale on its own.
     *
     * This is the source of truth; SECTIONS is derived from it so a section
     * cannot be added to one and forgotten in the other.
     */
    public const SECTION_GROUPS = [
        'Everyday updates' => [
            'announcements' => 'Announcements & advisories',
            'contact'       => 'Contact details',
            'hero'          => 'Hero banner',
            'about'         => 'About the municipality',
            'govbar'        => 'Top government bar',
        ],
        'Page setup' => [
            'brand'    => 'Logo & branding',
            'services' => 'Municipal services',
            'cta'      => 'PRIME HRIS call-to-action',
            'footer'   => 'Footer',
            'chatbot'  => 'Public chatbot',
        ],
    ];

    /** Flat key => label, the allow-list every write is checked against. */
    public static function sections(): array
    {
        return array_merge(...array_values(self::SECTION_GROUPS));
    }

    /** The only icons a service row may name. Keys match $svgs in the Blade. */
    public const ICONS = ['building', 'clipboard', 'heart', 'users', 'leaf', 'tool'];

    /** Announcement tags, each with its own dot colour on the page. */
    public const ANNOUNCEMENT_TAGS = ['Advisory', 'Event', 'Program', 'Notice'];

    /** Small icons for the chatbot's quick-question chips. */
    public const CHIP_ICONS = ['clipboard', 'clock', 'document', 'phone', 'info'];

    /**
     * The uploaded logo, kept under its own key rather than inside `brand`.
     *
     * put() replaces a section wholesale, which is what lets an administrator
     * delete the last item from a list. If the logo lived in `brand`, saving
     * the brand *text* form — which has no file input and so submits no path —
     * would silently drop the upload. A separate key means the two writes
     * cannot interfere. It is deliberately not in SECTIONS: it holds a file
     * reference, not copy, so it has no text form and no rail entry.
     */
    public const LOGO_KEY = 'logo';

    /** Where an uploaded logo lives, relative to the public disk. */
    public const LOGO_DIR = 'site';

    /** The logo shipped with the application, used until one is uploaded. */
    public const DEFAULT_LOGO = 'municipal-of-pagsanjan-logo.jpg';

    /** Uploadable types. SVG is excluded — it can carry script. */
    public const LOGO_MIMES = ['jpg', 'jpeg', 'png', 'webp'];

    /** @var array<string,array<string,mixed>>|null */
    private static ?array $memo = null;

    // ─────────────────────────────────────────────────────────────────
    //  Reading
    // ─────────────────────────────────────────────────────────────────

    /**
     * Every section, saved values merged over defaults.
     *
     * @return array<string,array<string,mixed>>
     */
    public static function all(): array
    {
        if (self::$memo !== null) {
            return self::$memo;
        }

        $saved = self::saved();
        $out = [];

        foreach (self::defaults() as $key => $default) {
            $out[$key] = isset($saved[$key])
                ? self::merge($default, $saved[$key])
                : $default;
        }

        return self::$memo = $out;
    }

    /** One section, saved values merged over defaults. */
    public static function section(string $key): array
    {
        return self::all()[$key] ?? [];
    }

    /**
     * Saved rows, keyed by section.
     *
     * A missing table must not take the public homepage down with it — the
     * page has defaults for everything, so an unmigrated install still
     * renders. The exception is reported rather than swallowed silently.
     *
     * @return array<string,array<string,mixed>>
     */
    private static function saved(): array
    {
        try {
            return SiteContent::query()->pluck('value', 'key')->all();
        } catch (QueryException $e) {
            report($e);
            return [];
        }
    }

    /**
     * Saved over default, one level deep, with lists replaced wholesale.
     *
     * A list is the admin's: if they delete the fourth announcement, the
     * default's fourth must not reappear underneath. Associative blocks
     * recurse so a newly added field still arrives with its default.
     */
    private static function merge(array $default, mixed $saved): array
    {
        if (!is_array($saved)) {
            return $default;
        }

        foreach ($saved as $key => $value) {
            if (!array_key_exists($key, $default)) {
                continue;                       // unknown key — not ours to render
            }
            if (is_array($default[$key]) && is_array($value) && !array_is_list($default[$key])) {
                $default[$key] = self::merge($default[$key], $value);
            } else {
                $default[$key] = $value;
            }
        }

        return $default;
    }

    // ─────────────────────────────────────────────────────────────────
    //  Live figures
    // ─────────────────────────────────────────────────────────────────

    /**
     * The two hero counts that the HR database already knows.
     *
     * Returns null for a figure whose table cannot be read, and the Blade
     * omits that stat rather than printing a zero — "0 Government Personnel"
     * on the municipality's public homepage is worse than one fewer stat.
     *
     * @return array{departments:?int, personnel:?int}
     */
    public static function liveStats(): array
    {
        return [
            'departments' => self::count(fn () => Department::query()->count()),
            'personnel'   => self::count(fn () => Employee::query()->count()),
        ];
    }

    private static function count(callable $query): ?int
    {
        try {
            return (int) $query();
        } catch (QueryException $e) {
            report($e);
            return null;
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  Logo
    // ─────────────────────────────────────────────────────────────────

    /**
     * The stored path on the public disk, or null while the shipped logo is
     * still in use.
     *
     * Re-checked against the disk on every read: if the file has been removed
     * out from under the row, the caller must fall back to the shipped logo
     * rather than emit a broken image on the municipality's homepage.
     */
    public static function logoPath(): ?string
    {
        $path = self::logoRow()['path'] ?? null;

        if (!$path) {
            return null;
        }

        return Storage::disk('public')->exists($path) ? $path : null;
    }

    /** The URL every view should use for the seal. */
    public static function logoUrl(): string
    {
        $path = self::logoPath();

        // The version suffix is what makes a replaced logo appear immediately:
        // the filename is content-hashed, but a browser that already cached the
        // *default* would otherwise keep showing it after the first upload.
        return $path
            ? Storage::disk('public')->url($path)
            : asset(self::DEFAULT_LOGO);
    }

    /** Absolute filesystem path, for callers that must read the bytes. */
    public static function logoFile(): ?string
    {
        $path = self::logoPath();

        if ($path) {
            return Storage::disk('public')->path($path);
        }

        $default = public_path(self::DEFAULT_LOGO);

        return is_file($default) ? $default : null;
    }

    /**
     * The logo as a data: URI, for PDFs.
     *
     * dompdf cannot fetch a URL, so the leave and pass-slip forms embed the
     * bytes. The MIME type is derived rather than assumed — both callers used
     * to hard-code `image/jpeg`, which would have produced a broken image the
     * moment somebody uploaded a PNG.
     */
    public static function logoDataUri(): string
    {
        $file = self::logoFile();

        if (!$file || !is_file($file)) {
            return '';
        }

        $mime = @mime_content_type($file) ?: 'image/jpeg';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file));
    }

    /** Replace the logo, deleting whatever it replaces. */
    public static function putLogo(string $path, ?int $userId = null): void
    {
        $previous = self::logoRow()['path'] ?? null;

        SiteContent::updateOrCreate(
            ['key' => self::LOGO_KEY],
            ['value' => ['path' => $path], 'updated_by' => $userId],
        );

        if ($previous && $previous !== $path) {
            Storage::disk('public')->delete($previous);
        }

        self::flushCache();
    }

    /** Go back to the shipped logo, removing the uploaded file. */
    public static function resetLogo(): void
    {
        $previous = self::logoRow()['path'] ?? null;

        if ($previous) {
            Storage::disk('public')->delete($previous);
        }

        SiteContent::where('key', self::LOGO_KEY)->delete();
        self::flushCache();
    }

    /** @return array<string,mixed> */
    private static function logoRow(): array
    {
        try {
            $row = SiteContent::where('key', self::LOGO_KEY)->first();
            return is_array($row?->value) ? $row->value : [];
        } catch (QueryException $e) {
            report($e);
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  Writing
    // ─────────────────────────────────────────────────────────────────

    public static function put(string $key, array $value, ?int $userId = null): void
    {
        SiteContent::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => $userId],
        );

        self::flushCache();
    }

    /** Drop a section back to its default by deleting the row. */
    public static function reset(string $key): void
    {
        SiteContent::where('key', $key)->delete();
        self::flushCache();
    }

    /** Clear the per-request memo. Call after any write. */
    public static function flushCache(): void
    {
        self::$memo = null;
    }

    /** When each section was last edited, and by whom. */
    public static function editLog(): array
    {
        try {
            return SiteContent::with('updatedBy')->get()
                ->mapWithKeys(fn (SiteContent $r) => [$r->key => [
                    'at' => $r->updated_at,
                    'by' => $r->updatedBy?->username ?? $r->updatedBy?->email,
                ]])->all();
        } catch (QueryException $e) {
            report($e);
            return [];
        }
    }

    // ─────────────────────────────────────────────────────────────────
    //  Defaults — the page exactly as it read before it became editable
    // ─────────────────────────────────────────────────────────────────

    /** @return array<string,array<string,mixed>> */
    public static function defaults(): array
    {
        return [
            'govbar' => [
                'left'  => 'Republic of the Philippines  ·  Province of Laguna',
                'right' => 'Official Website of the Municipal Government of Pagsanjan',
            ],

            'brand' => [
                'name'     => 'Pagsanjan, Laguna',
                'sub'      => 'Municipal Government',
                'portal_label' => 'Employee Portal',
                'nav_links' => [
                    ['label' => 'Services',      'anchor' => '#services'],
                    ['label' => 'Announcements', 'anchor' => '#announcements'],
                    ['label' => 'About',         'anchor' => '#about'],
                    ['label' => 'Contact',       'anchor' => '#contact'],
                ],
            ],

            'hero' => [
                'badge'          => 'Official Municipal Government Portal',
                'title'          => 'Serving the People of',
                'title_highlight' => 'Pagsanjan, Laguna',
                'subtitle'       => 'Access municipal services, announcements, and government information from the Municipal Government of Pagsanjan, Province of Laguna.',
                'primary_label'  => 'Explore Services',
                'secondary_label' => 'Ask Our AI Assistant',
                'card_title'     => 'Municipal Services Portal',
                // Values for these two are counted from the database; only the
                // wording under them is editable.
                'stat_departments_label' => 'Offices & Departments',
                'stat_personnel_label'   => 'Government Personnel',
                // Not derivable from any table, so it stays typed.
                'stat_extra_value' => '24/7',
                'stat_extra_label' => 'AI Chatbot Support',
                'tags' => ['BIR Compliant', 'GSIS Ready', 'CSC Accredited', 'ARTA Compliant'],
            ],

            'services' => [
                'eyebrow'  => 'MUNICIPAL SERVICES',
                'heading'  => 'What can we help you with?',
                'sub'      => 'Access the services offered by the Municipal Government of Pagsanjan, Laguna.',
                'categories' => [
                    [
                        'label' => 'Permits & Registration',
                        'icon'  => 'building',
                        'items' => [
                            ['icon' => 'building',  'title' => 'Business Permits',    'desc' => 'Apply and renew business permits online through the Municipal Business Office.', 'office' => 'Municipal Business Office'],
                            ['icon' => 'clipboard', 'title' => 'Civil Registration',  'desc' => 'Request birth, marriage, and death certificates from the Civil Registrar.',      'office' => 'Office of the Civil Registrar'],
                        ],
                    ],
                    [
                        'label' => 'Health & Social Services',
                        'icon'  => 'heart',
                        'items' => [
                            ['icon' => 'heart', 'title' => 'Health Services', 'desc' => 'Access municipal health programs, consultations, and medical assistance.', 'office' => 'Municipal Health Office'],
                            ['icon' => 'users', 'title' => 'Social Welfare',  'desc' => 'MSWD programs for senior citizens, PWDs, and indigent families.',          'office' => 'Social Welfare & Development Office'],
                        ],
                    ],
                    [
                        'label' => 'Community Development',
                        'icon'  => 'leaf',
                        'items' => [
                            ['icon' => 'leaf', 'title' => 'Agricultural Support',     'desc' => 'Livelihood programs and agricultural assistance for local farmers.',   'office' => 'Municipal Agriculture Office'],
                            ['icon' => 'tool', 'title' => 'Infrastructure Projects',  'desc' => 'Updates on public works, road projects, and community infrastructure.', 'office' => 'Municipal Engineering Office'],
                        ],
                    ],
                ],
            ],

            'announcements' => [
                'eyebrow' => 'LATEST UPDATES',
                'heading' => 'Announcements & Advisories',
                'sub'     => 'Stay informed with the latest news from the Municipal Government.',
                'side_heading' => 'More Updates',
                'items' => [
                    ['date' => '2025-06-20', 'tag' => 'Advisory', 'title' => 'Schedule of Payment for Real Property Tax — 2nd Quarter 2025', 'excerpt' => 'Property owners are reminded to settle 2nd quarter real property tax payments on or before the deadline to avoid penalties.'],
                    ['date' => '2025-06-18', 'tag' => 'Event',    'title' => 'Pagsanjan Founding Anniversary Celebration — June 25, 2025',   'excerpt' => 'Join the community celebration marking the founding anniversary of Pagsanjan with a parade, cultural shows, and local exhibits.'],
                    ['date' => '2025-06-15', 'tag' => 'Program',  'title' => 'MSWD Livelihood Training Program — Open for Registration',     'excerpt' => 'Residents may now register for the MSWD livelihood training program offering skills development and starter kits.'],
                    ['date' => '2025-06-10', 'tag' => 'Notice',   'title' => 'Water Interruption Advisory — Barangay Pinagsanjan Area',      'excerpt' => 'A scheduled water service interruption will affect Barangay Pinagsanjan Area residents; please store water in advance.'],
                ],
            ],

            'about' => [
                'eyebrow' => 'ABOUT THE MUNICIPALITY',
                'heading' => 'Municipal Government of Pagsanjan',
                'sub'     => 'A brief overview of the municipality, its leadership, and its commitment to public service.',
                'frame_tag'   => 'Official Municipal Profile',
                'frame_meta'  => 'Local Government Unit · Province of Laguna',
                'profile_name'  => 'Pagsanjan, Laguna',
                'profile_badge' => '1st Class Municipality',
                'facts' => [
                    ['label' => 'Province',    'value' => 'Laguna'],
                    ['label' => 'Region',      'value' => 'IV-A (CALABARZON)'],
                    ['label' => 'Barangays',   'value' => '16'],
                    ['label' => 'Departments', 'value' => '17 Offices'],
                    ['label' => 'Personnel',   'value' => '348 Employees'],
                    ['label' => 'Population',  'value' => '40,000+'],
                ],
                'body_heading'   => 'Home of the Famous',
                'body_highlight' => 'Pagsanjan Falls',
                'paragraphs' => [
                    'Pagsanjan is a first-class municipality in the Province of Laguna, Philippines — known as the "Shooting the Rapids" capital. Composed of 16 barangays, it serves a population of over 40,000 residents across Region IV-A (CALABARZON).',
                    'The Municipal Government is committed to transparent, efficient, and responsive governance through its 17 offices and departments, serving every Pagsanjeño.',
                ],
                'vision_label' => 'Our Vision',
                'vision' => 'A progressive, peaceful, and self-reliant municipality with empowered citizens enjoying a high quality of life under a transparent and accountable local government.',
                'mission_label' => 'Our Mission',
                'mission' => 'To deliver efficient, effective, and equitable public services through good governance, community participation, and sustainable development programs for all Pagsanjeños.',
            ],

            'contact' => [
                'eyebrow' => 'GET IN TOUCH',
                'heading' => 'Contact Us',
                'sub'     => 'Reach out to the Municipal Government of Pagsanjan for inquiries, concerns, or assistance.',
                'office_title' => 'Municipal Hall',
                'office_sub'   => 'Pagsanjan, Laguna',
                'address'      => 'Poblacion, Pagsanjan, Laguna 4008',
                'phone'        => '(049) 501-0000 · (049) 501-0001',
                'email'        => 'info@pagsanjan.gov.ph',
                'hours'        => 'Mon – Fri, 8:00 AM – 5:00 PM',
                'closed_note'  => 'Closed on weekends & public holidays',
                'form_title'   => 'Send us a Message',
                'form_sub'     => 'Fill out the form and our office will get back to you.',
                'form_badge'   => '1–2 business days',
                'form_privacy' => 'Your information is kept confidential and used only to respond to your inquiry.',
            ],

            'cta' => [
                'eyebrow' => 'PRIME HRIS',
                'heading' => 'Are you a Municipal Government Employee?',
                'text'    => 'The PRIME HRIS portal is exclusively for authorized employees of the Municipal Government of Pagsanjan, Laguna. Access your payroll, leave, and personnel records here.',
                'button_label' => 'Sign In to PRIME HRIS',
                'note'    => 'Municipal Government employees only · Contact your administrator for access',
                'card_label' => 'PRIME HRIS',
                'card_sub'   => 'Personnel Records & Information Management for Employees',
                'features' => [
                    'Payroll Processing',
                    '201 File Management',
                    'Leave & Benefits',
                    'DTR Monitoring',
                    'BIR / GSIS / PhilHealth',
                    'Payroll Reports',
                ],
            ],

            'footer' => [
                'name' => 'Municipal Government of Pagsanjan',
                'sub'  => 'Province of Laguna · Republic of the Philippines',
                'links' => [
                    ['label' => 'Privacy Policy', 'anchor' => '#privacy'],
                    ['label' => 'Terms of Use',   'anchor' => '#terms'],
                    ['label' => 'Contact Us',     'anchor' => '#contact'],
                    ['label' => 'Sitemap',        'anchor' => '#sitemap'],
                ],
                // The year is appended at render time — a hard-coded "© 2025"
                // was already a year out of date.
                'copyright' => 'Municipal Government of Pagsanjan, Laguna. All rights reserved.',
            ],

            'chatbot' => [
                'name'     => 'Pagsanjan LGU Assistant',
                'greeting' => "Hello! I'm the Pagsanjan LGU Assistant. I can help you with information about municipal services, requirements, fees, and procedures. How can I assist you today?",
                'placeholder' => 'Ask about our services...',
                'quick_actions' => [
                    ['label' => 'Services', 'icon' => 'clipboard', 'question' => 'What services do you offer?'],
                    ['label' => 'Hours',    'icon' => 'clock',     'question' => 'What are your office hours?'],
                    ['label' => 'Permits',  'icon' => 'document',  'question' => 'How do I get a permit?'],
                    ['label' => 'Contact',  'icon' => 'phone',     'question' => 'Contact information?'],
                ],
            ],
        ];
    }
}
