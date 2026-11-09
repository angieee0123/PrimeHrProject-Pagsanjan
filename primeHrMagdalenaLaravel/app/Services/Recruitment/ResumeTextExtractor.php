<?php

namespace App\Services\Recruitment;

use App\Exceptions\RecruitmentRuleViolation;
use Smalot\PdfParser\Parser;
use ZipArchive;

/**
 * Text extraction from PDF / DOCX / TXT resumes.
 *
 * The reference used pdfplumber and python-docx, both already present in this
 * project as PHP equivalents (`smalot/pdfparser` and `phpoffice/phpword`). DOCX
 * is read straight out of `word/document.xml` rather than through PhpWord: the
 * only thing the pipeline needs is the paragraph text, and going through the
 * full document model would pull in styles and relationships it immediately
 * throws away.
 */
class ResumeTextExtractor
{
    /** Extensions this can read, lower-case. */
    public function supportedExtensions(): array
    {
        return (array) config('hiring.accepted_extensions', ['pdf', 'docx', 'txt']);
    }

    public function supports(string $filename): bool
    {
        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), $this->supportedExtensions(), true);
    }

    /**
     * Read one resume's text.
     *
     * An unsupported extension is a user-facing refusal; a file that exists but
     * cannot be decoded is a failure the pipeline records as FAILED, because it
     * is the document's problem rather than the uploader's.
     */
    public function extract(string $absolutePath, ?string $extension = null): string
    {
        if (! is_file($absolutePath)) {
            throw RecruitmentRuleViolation::notFound('The stored resume file could not be found.');
        }

        $extension = strtolower($extension ?? pathinfo($absolutePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'txt' => $this->fromText($absolutePath),
            'pdf' => $this->fromPdf($absolutePath),
            'docx' => $this->fromDocx($absolutePath),
            default => throw RecruitmentRuleViolation::badRequest(
                'Unsupported resume format. Accepted: PDF, DOCX, TXT.'
            ),
        };
    }

    private function fromText(string $path): string
    {
        $text = file_get_contents($path);

        if ($text === false) {
            throw new \RuntimeException('The text resume could not be read.');
        }

        // Strip a UTF-8 BOM: it survives into the first line and defeats the
        // name heuristic, which requires the line to start with a letter.
        return preg_replace('/^\xEF\xBB\xBF/', '', $text) ?? $text;
    }

    private function fromPdf(string $path): string
    {
        try {
            $pdf = (new Parser())->parseFile($path);
        } catch (\Throwable $e) {
            throw new \RuntimeException('The PDF resume could not be read: '.$e->getMessage(), 0, $e);
        }

        $pages = [];

        foreach ($pdf->getPages() as $page) {
            $pages[] = $page->getText();
        }

        return implode("\n", $pages);
    }

    /**
     * DOCX is a zip; the body text is `word/document.xml`.
     *
     * `</w:p>` becomes a newline (one paragraph per line, which is what the
     * name heuristic and the training/experience regexes expect) and `<w:tab/>`
     * a tab. The remaining tags are markup.
     */
    private function fromDocx(string $path): string
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('The DOCX resume could not be opened.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false || $xml === '') {
            throw new \RuntimeException('The DOCX resume carries no readable document body.');
        }

        $text = str_replace(
            ['</w:p>', '<w:br/>', '<w:br />', '<w:tab/>', '<w:tab />'],
            ["\n", "\n", "\n", "\t", "\t"],
            $xml
        );

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
    }
}
