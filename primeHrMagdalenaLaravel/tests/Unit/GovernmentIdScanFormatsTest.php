<?php

namespace Tests\Unit;

use App\Models\EmployeeSupportingDocument;
use App\Models\GovernmentId;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Step 5's ID scans, and why their format list is *not* Step 6's.
 *
 * The two steps look alike and share the same card UI, which makes it tempting
 * to give them one list of accepted formats. They must not have one. A 201-file
 * document is a *form* — the CSC ships the PDS as an Excel workbook — while a
 * government ID is a *scan* that OCR reads a number out of. A spreadsheet
 * uploaded as a GSIS card produces a file the auto-fill can never make sense
 * of, so it is refused here and accepted there, deliberately.
 *
 * The property that matters on both is the same one: what the picker offers is
 * what the server accepts. Each model owns its own list so the two can differ
 * without either surface drifting from its own rules.
 */
class GovernmentIdScanFormatsTest extends TestCase
{
    private function passes(string $input, UploadedFile $file): bool
    {
        return Validator::make([$input => $file], GovernmentId::rules())->passes();
    }

    #[Test]
    public function it_accepts_every_format_the_picker_offers(): void
    {
        foreach (GovernmentId::EXTENSIONS as $extension) {
            $file = $extension === 'pdf'
                ? UploadedFile::fake()->create("scan.{$extension}", 64)
                : UploadedFile::fake()->image("scan.{$extension}");

            $this->assertTrue(
                $this->passes('gsis_file', $file),
                "The picker offers .{$extension} but validation refuses it."
            );
        }
    }

    #[Test]
    public function the_accept_attribute_lists_exactly_the_accepted_extensions(): void
    {
        $offered = array_map(fn ($ext) => ltrim($ext, '.'), explode(',', GovernmentId::accept()));

        $this->assertSame(GovernmentId::EXTENSIONS, $offered);
    }

    #[Test]
    public function an_id_scan_is_not_a_spreadsheet(): void
    {
        // The distinction the shared card UI must not erase: these formats are
        // valid on Step 6 and meaningless on Step 5, because there is no ID
        // number for the OCR to read out of a workbook.
        foreach (['pds.xlsx', 'pds.xls', 'record.csv', 'form.docx'] as $name) {
            $this->assertTrue(
                Validator::make(
                    ['pds_file' => UploadedFile::fake()->create($name, 32)],
                    EmployeeSupportingDocument::rules()
                )->passes(),
                "{$name} should be accepted as a 201-file document."
            );

            $this->assertFalse(
                $this->passes('gsis_file', UploadedFile::fake()->create($name, 32)),
                "{$name} was accepted as a government ID scan."
            );
        }
    }

    #[Test]
    public function an_executable_is_still_refused(): void
    {
        foreach (['payload.php', 'payload.exe', 'payload.svg'] as $name) {
            $this->assertFalse(
                $this->passes('license_file', UploadedFile::fake()->create($name, 16)),
                "{$name} was accepted as a government ID scan."
            );
        }
    }

    #[Test]
    public function a_file_over_the_stated_ceiling_is_refused(): void
    {
        $ceiling = GovernmentId::maxKb();

        $this->assertTrue($this->passes('tin_file', UploadedFile::fake()->create('tin.pdf', $ceiling)));
        $this->assertFalse($this->passes('tin_file', UploadedFile::fake()->create('tin.pdf', $ceiling + 1)));

        // The screen states the ceiling; it must be the one being enforced,
        // and it can never exceed what PHP will hand to Laravel.
        $this->assertSame(\App\Services\UploadLimits::label($ceiling), GovernmentId::maxSizeLabel());
        $this->assertLessThanOrEqual(GovernmentId::MAX_KB, $ceiling);

        $ini = \App\Services\UploadLimits::iniKilobytes('upload_max_filesize');
        if ($ini > 0) {
            $this->assertLessThanOrEqual($ini, $ceiling);
        }
    }

    #[Test]
    public function every_id_has_a_rule_a_column_and_a_name_for_error_messages(): void
    {
        $inputs = array_keys(GovernmentId::columnMap());

        $this->assertCount(5, $inputs);
        $this->assertSame($inputs, array_keys(GovernmentId::rules()));
        $this->assertSame($inputs, array_keys(GovernmentId::attributeNames()));

        // Both the scan path and the number it was read into have to be
        // writable, or mass assignment discards them without a word.
        $fillable = (new GovernmentId)->getFillable();
        foreach (GovernmentId::IDS as $id) {
            $this->assertContains($id['key'] . '_file_path', $fillable);
            $this->assertContains($id['key'] . '_no', $fillable);
        }
    }
}
