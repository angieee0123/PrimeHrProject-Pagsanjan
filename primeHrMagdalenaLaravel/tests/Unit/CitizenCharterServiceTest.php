<?php

namespace Tests\Unit;

use App\Models\CitizenCharter;
use App\Services\CitizenCharterService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The Citizen's Charter knowledge base.
 *
 * Three properties have to hold, and these cover all three:
 *
 *  1. the router claims only municipal-service questions — an HR question
 *     mentioning "leave", "payslip", or "salary grade" is never a charter
 *     question, however municipal the remaining words sound;
 *  2. retrieval is grounded in the stored text — a question whose words match
 *     nothing in the charter returns null so the caller falls through, rather
 *     than producing an answer from general knowledge;
 *  3. with no provider configured the answer is the charter's own words,
 *     quoted verbatim, so the chatbot stays useful instead of apologising.
 */
class CitizenCharterServiceTest extends TestCase
{
    private CitizenCharterService $charters;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('citizen_charters', function (Blueprint $table) {
            $table->id();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('file_type', 12)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('content_hash', 64)->nullable();
            $table->longText('content')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->string('status', 20)->default('extracted');
            $table->string('extractor', 40)->nullable();
            $table->text('error')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->timestamps();
        });

        // system_ai_settings must exist or AiChatService::chat() blows up
        // deciding whether a provider is configured; empty means "none", so
        // narration falls back to the verbatim excerpts under test.
        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });

        $this->charters = new CitizenCharterService();
    }

    private function seedCharter(string $content): CitizenCharter
    {
        return CitizenCharter::create([
            'original_name' => 'Citizens Charter 2026.pdf',
            'stored_path' => 'citizen-charters/seed.pdf',
            'file_type' => 'pdf',
            'status' => 'extracted',
            'content' => $content,
            'is_active' => true,
        ]);
    }

    #[Test]
    public function municipal_service_questions_are_claimed(): void
    {
        foreach ([
            'What are the requirements for a business permit?',
            'How long does a barangay clearance take to process?',
            'paano kumuha ng cedula?',
            'What are the fees in the Citizen\'s Charter?',
            'Where is the treasurer\'s office and what are its office hours?',
            'What frontline services does the munisipyo offer?',
        ] as $question) {
            $this->assertTrue(
                $this->charters->looksLikeCharterQuestion($question),
                "Expected charter question: {$question}"
            );
        }
    }

    #[Test]
    public function hr_questions_are_never_charter_questions(): void
    {
        foreach ([
            'how do i file a leave application?',
            'what is my leave balance',
            'show my latest payslip',
            'salary grade of Pedro Santos',
            'how much does a bookkeeper earn',
            'who is on leave today',
            'show me my gsis id scan',
            'list all employees with 2 or more absences',
            'what is the policy on late deductions',
        ] as $question) {
            $this->assertFalse(
                $this->charters->looksLikeCharterQuestion($question),
                "HR question must not route to the charter: {$question}"
            );
        }
    }

    #[Test]
    public function records_questions_are_never_charter_questions(): void
    {
        foreach ([
            'generate an attendance summary report',
            'how many employees are on leave today',
            'show me a bar chart of headcount by department',
            'what can you do?',
        ] as $question) {
            $this->assertFalse(
                $this->charters->looksLikeCharterQuestion($question),
                "Records question must not route to the charter: {$question}"
            );
        }
    }

    #[Test]
    public function an_unmatched_question_returns_null_so_the_caller_falls_through(): void
    {
        $this->seedCharter('Business permit requirements: application form, barangay clearance. Fee: PHP 500. Processing time: 3 working days.');

        $this->assertNull($this->charters->answer(null, 'What is the meaning of life?'));
    }

    #[Test]
    public function without_a_charter_everything_returns_null(): void
    {
        $this->assertNull($this->charters->answer(null, 'What are the requirements for a business permit?'));
    }

    #[Test]
    public function a_matched_question_is_answered_from_the_stored_text(): void
    {
        $this->seedCharter(
            "BUSINESS PERMIT (New Application)\nRequirements: duly accomplished application form, barangay clearance, DTI registration.\n"
            . "Fees: PHP 500 for micro enterprises.\nProcessing time: 3 working days at the Business Permits and Licensing Office.\n\n"
            . "BARANGAY CLEARANCE\nRequirements: valid ID, proof of residency.\nFee: PHP 50.\nProcessing time: 30 minutes."
        );

        $result = $this->charters->answer(null, 'What are the requirements for a business permit?');

        $this->assertNotNull($result);
        $this->assertStringContainsString('PHP 500', $result['answer']);
        $this->assertStringContainsString('barangay clearance', strtolower($result['answer']));
        $this->assertNotEmpty($result['follow_ups']);
    }

    #[Test]
    public function retrieval_scores_the_right_service_not_the_whole_file(): void
    {
        // A long unrelated section forces real chunking: the burial chunk must
        // outrank it rather than the whole file coming back as one chunk.
        $filler = str_repeat('The Business Permits and Licensing Office observes standard frontline procedures. ', 30);
        $charter = $this->seedCharter(
            "BUSINESS PERMIT\nFee: PHP 500. Processing time: 3 working days. {$filler}\n\n"
            . "BURIAL ASSISTANCE\nRequirements: death certificate, indigency certificate. Amount: PHP 5,000."
        );

        $found = $this->charters->relevantExcerpts($charter, 'How much burial assistance can I get?');

        $this->assertGreaterThan(0, $found['matched']);
        $this->assertStringContainsString('PHP 5,000', $found['text']);
        $this->assertStringNotContainsString('PHP 500.', $found['text']);
    }
}
