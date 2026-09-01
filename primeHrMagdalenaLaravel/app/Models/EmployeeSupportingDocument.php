<?php

namespace App\Models;

use App\Services\UploadLimits;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * The employee's 201 file — the supporting documents collected at registration.
 *
 * This model owns the *vocabulary* as well as the rows: which documents exist,
 * what each is called, what a browser may offer for it and what the server will
 * accept. The wizard's Step 6 markup, `EmployeeRegistrationController::store()`
 * and the personnel update route all read it, so the file picker cannot offer a
 * format that validation then refuses — the failure this list was written to
 * remove.
 */
class EmployeeSupportingDocument extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /**
     * Extensions a 201-file document may arrive as.
     *
     * A 201 file is a folder of *forms*, not a folder of photographs. The CSC
     * publishes CS Form 212 (PDS) and CS Form 33 as spreadsheets, offices keep
     * their position descriptions and clearances as Word or PDF, and a service
     * record exported from another system comes out as CSV — so an image-only
     * rule rejected the authoritative copy of nearly every document here and
     * left a phone photo of a printout as the only accepted form of the PDS.
     */
    public const EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'jpg', 'jpeg', 'png'];

    /**
     * The MIME types those extensions are actually detected as, as a second
     * check behind the extension allow-list.
     *
     * Deliberately generous, because the alternative is rejecting real files:
     * a CSV is sniffed as `text/plain` as often as `text/csv` and Excel-saved
     * ones come through as `application/vnd.ms-excel`, while legacy .doc/.xls
     * are OLE2 containers that libmagic reports as `application/vnd.ms-office`,
     * `application/CDFV2` or `application/x-cfb` depending on its version. The
     * extension list above is the real gate; this only stops a file whose
     * contents are nothing like what it claims.
     */
    public const MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-office',
        'application/CDFV2',
        'application/x-cfb',
        'application/zip',            // .docx/.xlsx are zip containers; some hosts report the container
        'text/csv',
        'text/plain',
        'application/csv',
        'image/jpeg',
        'image/png',
    ];

    /**
     * What the 201 file *wants* per document, in kilobytes. A scanned
     * multi-page PDS clears 5 MB routinely.
     *
     * This is a ceiling, not the enforced limit — see maxKb(). PHP rejects an
     * over-size upload before Laravel is reached, so a rule stated above
     * `upload_max_filesize` is a promise the server discards for us.
     */
    public const MAX_KB = 10240;

    /**
     * The documents themselves, in the three groups the migration already
     * files them under. The wizard renders these groups as headed sections —
     * twelve undifferentiated file inputs is a list nobody reads to the end of.
     */
    public const GROUPS = [
        [
            'title' => 'Appointment & Personnel Forms',
            'hint'  => 'The CSC forms that open the 201 file.',
            'icon'  => 'form',
            'items' => [
                [
                    'key'       => 'pds',
                    'input'     => 'pds_file',
                    'column'    => 'pds_file_path',
                    'label'     => 'CS Form 212 — Personal Data Sheet',
                    'note'      => 'The CSC issues this as an Excel workbook — upload the filled .xlsx, or a scan of the signed copy.',
                    'attribute' => 'CS Form 212 (PDS) file',
                ],
                [
                    'key'       => 'appointment_form',
                    'input'     => 'appointment_form_file',
                    'column'    => 'appointment_form_file_path',
                    'label'     => 'CS Form 33 — Appointment Form',
                    'note'      => 'The signed appointment issued by the appointing authority.',
                    'attribute' => 'CS Form 33 (Appointment Form) file',
                ],
                [
                    'key'       => 'position_description',
                    'input'     => 'position_description_file',
                    'column'    => 'position_description_file_path',
                    'label'     => 'Position Description Form',
                    'note'      => 'Required for every appointment type.',
                    'attribute' => 'Position Description Form file',
                ],
            ],
        ],
        [
            'title' => 'Clearances & Examinations',
            'hint'  => 'Pre-employment requirements. Some apply only to transfers or re-employment.',
            'icon'  => 'shield',
            'items' => [
                [
                    'key'       => 'medical_certificate',
                    'input'     => 'medical_certificate_file',
                    'column'    => 'medical_certificate_file_path',
                    'label'     => 'Medical Certificate',
                    'note'      => 'CS Form 211 or the issuing physician’s certificate.',
                    'attribute' => 'Medical Certificate file',
                ],
                [
                    'key'       => 'nbi_clearance',
                    'input'     => 'nbi_clearance_file',
                    'column'    => 'nbi_clearance_file_path',
                    'label'     => 'NBI Clearance',
                    'note'      => 'The clearance as issued — the printed copy carries the QR.',
                    'attribute' => 'NBI Clearance file',
                ],
                [
                    'key'       => 'financial_clearance',
                    'input'     => 'financial_clearance_file',
                    'column'    => 'financial_clearance_file_path',
                    'label'     => 'Financial & Property Clearance',
                    'note'      => 'Clearance from money and property accountability — transfer or re-employment only.',
                    'attribute' => 'financial clearance file',
                ],
                [
                    'key'       => 'neuro_exam',
                    'input'     => 'neuro_exam_file',
                    'column'    => 'neuro_exam_file_path',
                    'label'     => 'Neuro-psychiatric Examination',
                    'note'      => 'Where the position requires it.',
                    'attribute' => 'Neuro-psychiatric Examination file',
                ],
            ],
        ],
        [
            'title' => 'Service Record & Credentials',
            'hint'  => 'Everything the 201 file accumulates after appointment.',
            'icon'  => 'award',
            'items' => [
                [
                    'key'       => 'supporting_licenses',
                    'input'     => 'supporting_licenses_file',
                    'column'    => 'licenses_file_path',
                    'label'     => 'Professional Licenses',
                    'note'      => 'PRC or other licences the position requires.',
                    'attribute' => 'Licenses file',
                ],
                [
                    'key'       => 'performance_eval',
                    'input'     => 'performance_eval_file',
                    'column'    => 'performance_eval_file_path',
                    'label'     => 'Performance Evaluation Documents',
                    'note'      => 'IPCR / OPCR ratings, often kept as a spreadsheet.',
                    'attribute' => 'Performance Evaluation file',
                ],
                [
                    'key'       => 'commendation',
                    'input'     => 'commendation_file',
                    'column'    => 'commendation_file_path',
                    'label'     => 'Commendations & Awards',
                    'note'      => 'Certificates of achievement, commendations, citations.',
                    'attribute' => 'Commendation / Award file',
                ],
                [
                    'key'       => 'disciplinary',
                    'input'     => 'disciplinary_file',
                    'column'    => 'disciplinary_file_path',
                    'label'     => 'Disciplinary & Action Documents',
                    'note'      => 'Notices, decisions and resolutions on record.',
                    'attribute' => 'Disciplinary / Action file',
                ],
                [
                    'key'       => 'other_records',
                    'input'     => 'other_records_file',
                    'column'    => 'other_records_file_path',
                    'label'     => 'Other Employee Records',
                    'note'      => 'Anything else that belongs in the 201 file.',
                    'attribute' => 'other records file',
                ],
            ],
        ],
    ];

    protected $fillable = [
        'employee_id',
        'pds_file_path',
        'appointment_form_file_path',
        'position_description_file_path',
        'medical_certificate_file_path',
        'nbi_clearance_file_path',
        'financial_clearance_file_path',
        'neuro_exam_file_path',
        'licenses_file_path',
        'performance_eval_file_path',
        'commendation_file_path',
        'disciplinary_file_path',
        'other_records_file_path',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /** Every document, flattened out of its group. */
    public static function documents(): array
    {
        return array_merge(...array_column(self::GROUPS, 'items'));
    }

    /** Request input name => the column its stored path lands in. */
    public static function columnMap(): array
    {
        return array_column(self::documents(), 'column', 'input');
    }

    /**
     * Validation rules for all twelve inputs.
     *
     * `extensions` rather than `mimes`, because `mimes:csv` resolves to
     * `text/csv` alone and a CSV that PHP detects as `text/plain` — which is
     * most of them — fails a rule the admin was told they had satisfied.
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

    /**
     * Input name => the name to use in an error message, so the admin is told
     * "The CS Form 212 (PDS) file must be…" rather than "The pds file must be…".
     */
    public static function attributeNames(): array
    {
        return array_column(self::documents(), 'attribute', 'input');
    }

    /**
     * The `accept` attribute for the file pickers.
     *
     * Extensions, not MIME types: a native picker filtering on `text/csv`
     * greys out CSVs the OS has typed as `application/vnd.ms-excel`, which is
     * the same mismatch `rules()` avoids on the server side.
     */
    public static function accept(): string
    {
        return implode(',', array_map(fn ($ext) => '.' . $ext, self::EXTENSIONS));
    }

    /** "PDF, DOC, DOCX, XLS, XLSX, CSV, JPG, PNG" — for on-screen hints. */
    public static function formatLabel(): string
    {
        $shown = array_values(array_diff(self::EXTENSIONS, ['jpeg']));

        return implode(', ', array_map('strtoupper', $shown));
    }

    /**
     * The per-document ceiling this server can actually honour — 10 MB, or
     * `upload_max_filesize` where that is lower. See UploadLimits for why the
     * number is read rather than stated.
     */
    public static function maxKb(): int
    {
        return UploadLimits::perFileKb(self::MAX_KB);
    }

    /** The ceiling on the whole submission, which every file input counts toward. */
    public static function postMaxKb(): int
    {
        return UploadLimits::postMaxKb();
    }

    /** The per-document ceiling as the screen states it. */
    public static function maxSizeLabel(): string
    {
        return UploadLimits::label(self::maxKb());
    }

    public static function sizeLabel(int $kb): string
    {
        return UploadLimits::label($kb);
    }
}
