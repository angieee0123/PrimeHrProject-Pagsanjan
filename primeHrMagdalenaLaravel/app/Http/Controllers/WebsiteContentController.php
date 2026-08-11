<?php

namespace App\Http\Controllers;

use App\Services\SiteContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Website Content — the admin editor for the public welcome page.
 *
 * This writes the only page in the system an unauthenticated visitor can
 * read, so two rules hold throughout:
 *
 *  · **Administrators only, checked here.** The nav entry being hidden is not
 *    a permission. Every endpoint re-checks `hasRole('admin')` server-side,
 *    the same way AppearanceController guards its global scope.
 *
 *  · **No markup, ever.** Each field is validated as a plain string with a
 *    length cap, icons and tags must be members of a fixed vocabulary, and
 *    links accept a `#anchor` or an http(s) URL and nothing else. Blade
 *    escapes all of it on the way out. There is no rich-text field, because
 *    a public page that renders admin-supplied HTML is a stored-XSS surface
 *    aimed at every visitor to the municipality's website.
 */
class WebsiteContentController extends Controller
{
    /** Longest a single copy field may be. Enough for a paragraph, not an essay. */
    private const MAX = 2000;

    /** Caps on the repeatable lists, so one save cannot make the page endless. */
    private const MAX_ITEMS = 20;

    public function index()
    {
        if ($deny = $this->denyNonAdmin()) {
            return $deny;
        }

        return view('admin.website.adminWebsite', [
            'content'    => SiteContentService::all(),
            'defaults'   => SiteContentService::defaults(),
            'groups'     => SiteContentService::SECTION_GROUPS,
            'sections'   => SiteContentService::sections(),
            'blurbs'     => SiteContentService::SECTION_BLURBS,
            'icons'      => SiteContentService::ICONS,
            'chipIcons'  => SiteContentService::CHIP_ICONS,
            'tags'       => SiteContentService::ANNOUNCEMENT_TAGS,
            'liveStats'  => SiteContentService::liveStats(),
            'editLog'    => SiteContentService::editLog(),
            'logoUrl'       => SiteContentService::logoUrl(),
            'logoIsDefault' => SiteContentService::logoPath() === null,
        ]);
    }

    public function update(Request $request, string $section): JsonResponse
    {
        if ($deny = $this->denyNonAdmin(true)) {
            return $deny;
        }

        $sections = SiteContentService::sections();

        if (!array_key_exists($section, $sections)) {
            return response()->json(['message' => 'Unknown section.'], 404);
        }

        $data = $request->validate($this->rules($section));

        SiteContentService::put($section, $data, Auth::id());

        return response()->json([
            'message' => $sections[$section] . ' saved.',
            'content' => SiteContentService::section($section),
        ]);
    }

    public function reset(string $section): JsonResponse
    {
        if ($deny = $this->denyNonAdmin(true)) {
            return $deny;
        }

        $sections = SiteContentService::sections();

        if (!array_key_exists($section, $sections)) {
            return response()->json(['message' => 'Unknown section.'], 404);
        }

        SiteContentService::reset($section);

        return response()->json([
            'message' => $sections[$section] . ' reset to the default text.',
            'content' => SiteContentService::section($section),
        ]);
    }

    /**
     * Replace the municipal seal.
     *
     * The filename is generated here, never taken from the upload: a client
     * controls its own filename, and this one ends up inside a URL served from
     * the public disk. `store()` on the public disk keeps it under
     * storage/app/public/site, so there is no path for `../` to escape into.
     *
     * `image` + an extension allow-list + a dimension check together mean a
     * file has to actually decode as one of four raster formats. SVG is not on
     * the list because it can carry script, and this image is rendered on a
     * page anonymous visitors read.
     */
    public function updateLogo(Request $request): JsonResponse
    {
        if ($deny = $this->denyNonAdmin(true)) {
            return $deny;
        }

        $request->validate([
            'logo' => [
                'required',
                'file',
                'image',
                'mimes:' . implode(',', SiteContentService::LOGO_MIMES),
                'max:2048',                                   // kilobytes
                'dimensions:min_width=64,min_height=64,max_width=2000,max_height=2000',
            ],
        ], [
            'logo.dimensions' => 'The logo must be between 64 and 2000 pixels square.',
            'logo.max'        => 'The logo may not be larger than 2 MB.',
            'logo.mimes'      => 'The logo must be a JPG, PNG or WEBP image.',
        ]);

        $file = $request->file('logo');
        $name = 'logo-' . bin2hex(random_bytes(8)) . '.' . strtolower($file->getClientOriginalExtension());

        $path = $file->storeAs(SiteContentService::LOGO_DIR, $name, 'public');

        SiteContentService::putLogo($path, Auth::id());

        return response()->json([
            'message' => 'Logo updated. It now appears everywhere the seal is shown.',
            'url'     => SiteContentService::logoUrl(),
        ]);
    }

    public function resetLogo(): JsonResponse
    {
        if ($deny = $this->denyNonAdmin(true)) {
            return $deny;
        }

        SiteContentService::resetLogo();

        return response()->json([
            'message' => 'Logo reset to the one shipped with the system.',
            'url'     => SiteContentService::logoUrl(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────

    private function denyNonAdmin(bool $json = false)
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'hasRole') && $user->hasRole('admin')) {
            return null;
        }

        return $json
            ? response()->json(['message' => 'Only administrators can edit the website.'], 403)
            : abort(403, 'Only administrators can edit the website.');
    }

    /**
     * Per-section rules.
     *
     * Every leaf is `string` with a length cap — never `html`, never `any`.
     * `nullable` on the copy fields lets an administrator clear a line;
     * SiteContentService merges over defaults, so a blank saves as blank
     * rather than silently restoring the default.
     */
    private function rules(string $section): array
    {
        $text = ['nullable', 'string', 'max:' . self::MAX];
        $req  = ['required', 'string', 'max:' . self::MAX];
        $list = ['nullable', 'array', 'max:' . self::MAX_ITEMS];
        // A same-page anchor or an absolute http(s) link. Blocks javascript:
        // and data: URIs, which is the whole reason this is not a free string.
        $link = ['required', 'string', 'max:300', 'regex:/^(#[\w-]+|https?:\/\/[^\s<>"]+)$/'];

        return match ($section) {
            'govbar' => [
                'left'  => $text,
                'right' => $text,
            ],

            'brand' => [
                'name'         => $text,
                'sub'          => $text,
                'portal_label' => $text,
                'nav_links'            => $list,
                'nav_links.*.label'    => $req,
                'nav_links.*.anchor'   => $link,
            ],

            'hero' => [
                'badge'            => $text,
                'title'            => $text,
                'title_highlight'  => $text,
                'subtitle'         => $text,
                'primary_label'    => $text,
                'secondary_label'  => $text,
                'card_title'       => $text,
                'stat_departments_label' => $text,
                'stat_personnel_label'   => $text,
                'stat_extra_value' => ['nullable', 'string', 'max:20'],
                'stat_extra_label' => $text,
                'tags'   => ['nullable', 'array', 'max:8'],
                'tags.*' => ['required', 'string', 'max:60'],
            ],

            'services' => [
                'eyebrow' => $text,
                'heading' => $text,
                'sub'     => $text,
                'categories'                 => ['nullable', 'array', 'max:6'],
                'categories.*.label'         => $req,
                'categories.*.icon'          => ['required', Rule::in(SiteContentService::ICONS)],
                'categories.*.items'         => ['nullable', 'array', 'max:' . self::MAX_ITEMS],
                'categories.*.items.*.icon'  => ['required', Rule::in(SiteContentService::ICONS)],
                'categories.*.items.*.title' => $req,
                'categories.*.items.*.desc'  => $text,
                'categories.*.items.*.office' => $text,
            ],

            'announcements' => [
                'eyebrow'        => $text,
                'heading'        => $text,
                'sub'            => $text,
                'side_heading'   => $text,
                'items'           => $list,
                'items.*.date'    => ['required', 'date'],
                'items.*.tag'     => ['required', Rule::in(SiteContentService::ANNOUNCEMENT_TAGS)],
                'items.*.title'   => $req,
                'items.*.excerpt' => $text,
            ],

            'about' => [
                'eyebrow'        => $text,
                'heading'        => $text,
                'sub'            => $text,
                'frame_tag'      => $text,
                'frame_meta'     => $text,
                'profile_name'   => $text,
                'profile_badge'  => $text,
                'facts'          => ['nullable', 'array', 'max:12'],
                'facts.*.label'  => $req,
                'facts.*.value'  => $text,
                'body_heading'   => $text,
                'body_highlight' => $text,
                'paragraphs'     => ['nullable', 'array', 'max:6'],
                'paragraphs.*'   => ['required', 'string', 'max:' . self::MAX],
                'vision_label'   => $text,
                'vision'         => $text,
                'mission_label'  => $text,
                'mission'        => $text,
            ],

            'contact' => [
                'eyebrow'      => $text,
                'heading'      => $text,
                'sub'          => $text,
                'office_title' => $text,
                'office_sub'   => $text,
                'address'      => $text,
                'phone'        => $text,
                'email'        => ['nullable', 'string', 'max:200'],
                'hours'        => $text,
                'closed_note'  => $text,
                'form_title'   => $text,
                'form_sub'     => $text,
                'form_badge'   => $text,
                'form_privacy' => $text,
            ],

            'cta' => [
                'eyebrow'      => $text,
                'heading'      => $text,
                'text'         => $text,
                'button_label' => $text,
                'note'         => $text,
                'card_label'   => $text,
                'card_sub'     => $text,
                'features'     => ['nullable', 'array', 'max:12'],
                'features.*'   => ['required', 'string', 'max:120'],
            ],

            'footer' => [
                'name'            => $text,
                'sub'             => $text,
                'links'           => ['nullable', 'array', 'max:8'],
                'links.*.label'   => $req,
                'links.*.anchor'  => $link,
                'copyright'       => $text,
            ],

            'chatbot' => [
                'name'        => $text,
                'greeting'    => $text,
                'placeholder' => $text,
                'quick_actions'            => ['nullable', 'array', 'max:8'],
                'quick_actions.*.label'    => $req,
                'quick_actions.*.icon'     => ['required', Rule::in(SiteContentService::CHIP_ICONS)],
                'quick_actions.*.question' => $req,
            ],

            default => [],
        };
    }
}
