<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The success modal's "Verification email sent" panel is filled in by
 * `adminPersonnel.js` looking up element ids in
 * `modals/successModal.blade.php`. Nothing connects the two but the strings,
 * and `getElementById` returns null rather than complaining — so renaming one
 * id leaves the panel silently blank on a page that only ever renders it after
 * a successful registration, which is not a state anyone re-tests by accident.
 *
 * These read the two files and pin the join.
 */
class PersonnelEmailNoticeTest extends TestCase
{
    private const IDS = [
        'successEmailNotice',
        'successEmailNoticeTitle',
        'successEmailNoticeAddress',
        'successEmailNoticeText',
    ];

    private function modal(): string
    {
        return file_get_contents(resource_path('views/admin/personnel/modals/successModal.blade.php'));
    }

    private function script(): string
    {
        return file_get_contents(resource_path('js/admin/personnel/adminPersonnel.js'));
    }

    #[Test]
    public function the_modal_renders_every_element_the_script_fills_in(): void
    {
        $modal = $this->modal();

        foreach (self::IDS as $id) {
            $this->assertStringContainsString(
                'id="' . $id . '"',
                $modal,
                "successModal.blade.php is missing #{$id}, which the script writes to.",
            );
        }
    }

    #[Test]
    public function the_script_looks_up_every_element_the_modal_renders(): void
    {
        $script = $this->script();

        foreach (self::IDS as $id) {
            $this->assertStringContainsString(
                "'" . $id . "'",
                $script,
                "adminPersonnel.js never references #{$id}.",
            );
        }
    }

    /**
     * The panel ships hidden. Without the attribute it would render its empty
     * paragraphs on every success — an edit, a schedule assignment — as a
     * blank bordered box claiming nothing.
     */
    #[Test]
    public function the_panel_is_hidden_until_something_fills_it(): void
    {
        $this->assertMatchesRegularExpression(
            '/id="successEmailNotice"[^>]*\bhidden\b/',
            $this->modal(),
        );
    }

    /**
     * The wizard flashes `email_notice`; the blade has to hand it to the
     * script under the key the script reads.
     */
    #[Test]
    public function the_flash_reaches_the_script(): void
    {
        $page = file_get_contents(resource_path('views/admin/personnel/adminPersonnel.blade.php'));

        $this->assertStringContainsString("emailNotice: @json(session('email_notice'))", $page);
        $this->assertStringContainsString('flash.emailNotice', $this->script());
    }

    /**
     * The panel has a failure state, and it has to be reachable: an email that
     * did not send is exactly the case the admin must not be told about in the
     * past tense.
     */
    #[Test]
    public function the_failure_state_is_wired_on_both_sides(): void
    {
        $this->assertStringContainsString('is-failed', $this->script());
        $this->assertStringContainsString(
            '.personnel-modal-mail.is-failed',
            file_get_contents(resource_path('css/admin/adminPersonnel.css')),
        );
    }
}
