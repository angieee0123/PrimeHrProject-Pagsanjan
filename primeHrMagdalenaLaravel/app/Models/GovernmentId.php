<?php

namespace App\Models;

use App\Services\UploadLimits;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The employee's government ID numbers and the scans they were read from.
 *
 * Like EmployeeSupportingDocument, this model owns the vocabulary as well as
 * the rows — the five IDs, their labels, the formats a scan may be uploaded in
 * and the rules that enforce them. Step 5's markup,
 * EmployeeRegistrationController::store() and the personnel update route all
 * read it, so the picker cannot offer a format validation then refuses.
 *
 * The formats stay narrower than the 201 file's on purpose: these are ID
 * *scans* that feed OCR, not forms. A spreadsheet has no ID number to read out
 * of it, so accepting one would only produce a scan the auto-fill can never
 * make sense of.
 */
class GovernmentId extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    public $timestamps = false;

    /** A scan or a clear photograph. Nothing else has a number to read. */
    public const EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];

    public const MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
    ];

    /** What a scan may weigh, before UploadLimits clamps it to what PHP takes. */
    public const MAX_KB = 5120;

    /**
     * The five IDs. `note` is what the card says under the name — the OCR
     * read-back is the reason the scan comes first and the number second.
     */
    public const IDS = [
        [
            'key'         => 'gsis',
            'label'       => 'GSIS',
            'note'        => 'Government Service Insurance System membership ID.',
            'placeholder' => 'GSIS ID',
            'attribute'   => 'GSIS file',
        ],
        [
            'key'         => 'philhealth',
            'label'       => 'PhilHealth',
            'note'        => 'PhilHealth Identification Number (PIN).',
            'placeholder' => 'PhilHealth ID',
            'attribute'   => 'PhilHealth file',
        ],
        [
            'key'         => 'pagibig',
            'label'       => 'Pag-IBIG',
            'note'        => 'Pag-IBIG MID / RTN number.',
            'placeholder' => 'Pag-IBIG ID',
            'attribute'   => 'Pag-IBIG file',
        ],
        [
            'key'         => 'tin',
            'label'       => 'TIN',
            'note'        => 'BIR Taxpayer Identification Number.',
            'placeholder' => 'Tax ID',
            'attribute'   => 'TIN file',
        ],
        [
            'key'         => 'license',
            'label'       => 'Professional License',
            'note'        => 'PRC licence, where the position requires one.',
            'placeholder' => 'License No.',
            'attribute'   => 'license file',
        ],
    ];

    protected $fillable = [
        'employee_id', 'gsis_no', 'gsis_file_path',
        'philhealth_no', 'philhealth_file_path',
        'pagibig_no', 'pagibig_file_path',
        'tin_no', 'tin_file_path',
        'license_no', 'license_file_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Request input name => the column its stored path lands in. */
    public static function columnMap(): array
    {
        $map = [];
        foreach (self::IDS as $id) {
            $map[$id['key'] . '_file'] = $id['key'] . '_file_path';
        }

        return $map;
    }

    /**
     * Validation rules for the five scan inputs.
     *
     * `extensions` rather than `mimes` for the same reason the 201 file uses
     * it — one gate on the filename, a MIME check behind it — and because the
     * two surfaces stating different rules for the same upload is exactly what
     * putting the vocabulary on the model prevents.
     */
    public static function rules(): array
    {
        $rule = [
            'nullable',
            'file',
            'max:' . self::maxKb(),
            'extensions:' . implode(',', self::EXTENSIONS),
            'mimetypes:' . implode(',', self::MIME_TYPES),
        ];

        return array_fill_keys(array_keys(self::columnMap()), $rule);
    }

    /** Input name => the name an error message should use for it. */
    public static function attributeNames(): array
    {
        $names = [];
        foreach (self::IDS as $id) {
            $names[$id['key'] . '_file'] = $id['attribute'];
        }

        return $names;
    }

    /** Extensions, not MIME types — see EmployeeSupportingDocument::accept(). */
    public static function accept(): string
    {
        return implode(',', array_map(fn ($ext) => '.' . $ext, self::EXTENSIONS));
    }

    /** "PDF, JPG, PNG" — for on-screen hints. */
    public static function formatLabel(): string
    {
        $shown = array_values(array_diff(self::EXTENSIONS, ['jpeg']));

        return implode(', ', array_map('strtoupper', $shown));
    }

    public static function maxKb(): int
    {
        return UploadLimits::perFileKb(self::MAX_KB);
    }

    public static function maxSizeLabel(): string
    {
        return UploadLimits::label(self::maxKb());
    }
}
