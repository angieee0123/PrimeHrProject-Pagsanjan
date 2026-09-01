<?php

namespace Tests\Unit;

use App\Http\Controllers\EmployeeRegistrationController;
use App\Models\EmployeeSupportingDocument as Doc;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The 201 file accepts the formats government forms actually arrive in.
 *
 * Step 6 shipped as `image|mimes:jpg,jpeg,png`. A 201 file is a folder of
 * *forms*: the CSC publishes CS Form 212 (the PDS) and CS Form 33 as Excel
 * workbooks, offices keep position descriptions and clearances as Word or PDF,
 * and a service record exported from another system comes out as CSV — so the
 * rule rejected the authoritative copy of nearly every document it asked for
 * and left a phone photograph of a printout as the only accepted form of the
 * PDS.
 *
 * Two properties are pinned here.
 *
 * One: what the file picker offers and what the server accepts come from the
 * same list on the model. A picker advertising XLSX against a validator that
 * refuses it is the failure being fixed, and it is invisible until an admin
 * has filled in six other steps.
 *
 * Two: CSV survives. `mimes:csv` resolves to `text/csv` alone, and most CSVs
 * are detected as `text/plain` — which is why the rules use `extensions:`
 * rather than `mimes:`. A CSV rejected by a form that lists CSV as accepted is
 * exactly the same broken promise in a subtler place.
 */
class SupportingDocumentFormatsTest extends TestCase
{
    private function passes(string $input, UploadedFile $file): bool
    {
        return Validator::make([$input => $file], Doc::rules())->passes();
    }

    #[Test]
    public function it_accepts_every_format_the_picker_offers(): void
    {
        foreach (Doc::EXTENSIONS as $extension) {
            $file = $extension === 'png' || $extension === 'jpg' || $extension === 'jpeg'
                ? UploadedFile::fake()->image("scan.{$extension}")
                : UploadedFile::fake()->create("form.{$extension}", 64);

            $this->assertTrue(
                $this->passes('pds_file', $file),
                "The picker offers .{$extension} but validation refuses it."
            );
        }
    }

    #[Test]
    public function the_accept_attribute_lists_exactly_the_accepted_extensions(): void
    {
        $offered = array_map(
            fn ($ext) => ltrim($ext, '.'),
            explode(',', Doc::accept())
        );

        $this->assertSame(Doc::EXTENSIONS, $offered);
    }

    #[Test]
    public function a_csv_is_accepted_however_it_is_typed(): void
    {
        // text/plain is what PHP most often reports for a hand-made CSV, and
        // application/vnd.ms-excel is what one saved out of Excel arrives as.
        foreach (['text/csv', 'text/plain', 'application/vnd.ms-excel'] as $mime) {
            $this->assertTrue(
                $this->passes('performance_eval_file', UploadedFile::fake()->create('ipcr.csv', 32, $mime)),
                "A CSV reported as {$mime} was refused."
            );
        }
    }

    #[Test]
    public function an_executable_is_still_refused(): void
    {
        foreach (['payload.php', 'payload.exe', 'payload.svg'] as $name) {
            $this->assertFalse(
                $this->passes('other_records_file', UploadedFile::fake()->create($name, 16)),
                "{$name} was accepted into the 201 file."
            );
        }
    }

    #[Test]
    public function a_file_over_the_stated_ceiling_is_refused(): void
    {
        $ceiling = Doc::maxKb();

        $this->assertTrue($this->passes('pds_file', UploadedFile::fake()->create('pds.pdf', $ceiling)));
        $this->assertFalse($this->passes('pds_file', UploadedFile::fake()->create('pds.pdf', $ceiling + 1)));

        // The screen states the ceiling; it must be the one being enforced.
        $this->assertSame(Doc::sizeLabel($ceiling), Doc::maxSizeLabel());
    }

    #[Test]
    public function the_stated_ceiling_never_exceeds_what_php_will_accept(): void
    {
        // `upload_max_filesize` is enforced before Laravel is reached, so a
        // rule above it can never fire — the admin is told the upload failed,
        // with no way to tell an over-size file from a broken one. The number
        // on screen has to be one this server can actually honour: the app is
        // served by a SAPI whose php.ini caps uploads at 2M, well under the
        // 10 MB a scanned PDS wants.
        $this->assertLessThanOrEqual(Doc::MAX_KB, Doc::maxKb());

        $ini = \App\Services\UploadLimits::iniKilobytes('upload_max_filesize');

        if ($ini > 0) {
            $this->assertLessThanOrEqual($ini, Doc::maxKb());
        }
    }

    #[Test]
    public function sizes_are_stated_in_units_a_reader_recognises(): void
    {
        $this->assertSame('10 MB', Doc::sizeLabel(10240));
        $this->assertSame('2 MB', Doc::sizeLabel(2048));
        $this->assertSame('1.5 MB', Doc::sizeLabel(1536));
        $this->assertSame('512 KB', Doc::sizeLabel(512));
    }

    #[Test]
    public function every_document_has_a_rule_a_column_and_a_name_for_error_messages(): void
    {
        $inputs = array_keys(Doc::columnMap());

        $this->assertCount(12, $inputs);
        $this->assertSame($inputs, array_keys(Doc::rules()));
        $this->assertSame($inputs, array_keys(Doc::attributeNames()));

        // Every column the map points at must be writable, or the upload is
        // silently discarded by mass assignment.
        foreach (Doc::columnMap() as $input => $column) {
            $this->assertContains($column, (new Doc)->getFillable(), "{$input} writes to an unfillable column.");
        }
    }

    #[Test]
    public function two_documents_scanned_to_the_same_filename_do_not_overwrite_each_other(): void
    {
        Storage::fake('public');

        // The normal case, not a contrived one: an office MFP scans every page
        // to the same default name, and all twelve documents are submitted in
        // the same second, so `time()` alone cannot tell them apart.
        $first  = EmployeeRegistrationController::handleFileUpload(
            UploadedFile::fake()->create('scan.pdf', 8), 'employees/supporting_documents'
        );
        $second = EmployeeRegistrationController::handleFileUpload(
            UploadedFile::fake()->create('scan.pdf', 8), 'employees/supporting_documents'
        );

        $this->assertNotSame($first, $second, 'The second scan overwrote the first.');
        $this->assertCount(2, Storage::disk('public')->files('employees/supporting_documents'));
    }

    #[Test]
    public function a_stored_name_is_safe_to_put_in_a_url(): void
    {
        Storage::fake('public');

        // Government forms come with names like this. A `#` truncates the
        // `/storage/...` link and the document becomes unreachable from the
        // wizard; spaces and parentheses are merely ugly until something
        // downstream does not encode them.
        $url = EmployeeRegistrationController::handleFileUpload(
            UploadedFile::fake()->create('CS Form 212 (Revised 2017) - Dela Cruz #2.xlsx', 8),
            'employees/supporting_documents'
        );

        $this->assertSame($url, rawurldecode($url), 'The stored name needs URL-encoding to be linkable.');
        $this->assertMatchesRegularExpression('#^/storage/employees/supporting_documents/[A-Za-z0-9._-]+$#', $url);
        $this->assertStringEndsWith('.xlsx', $url);
        $this->assertStringContainsString('cs-form-212', $url, 'The original name should still be recognisable.');
    }
}
