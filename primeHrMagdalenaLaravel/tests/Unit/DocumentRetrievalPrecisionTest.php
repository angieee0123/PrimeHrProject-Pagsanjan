<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Models\DocumentExtraction;
use App\Models\Employee;
use App\Models\User;
use App\Services\AiAccessPolicy;
use App\Services\AiFileResolver;
use App\Services\DocumentSearchService;
use App\Services\SemanticSearchService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Retrieval quality scoreboard for document search.
 *
 * The security tests prove a search cannot return a file the caller may not
 * see. This one asks the other question — of the files they *may* see, does the
 * right one come back, and does it come back first?
 *
 * That distinction matters because ranking here is currently `uploaded_at`
 * descending and nothing else: SemanticSearchService::rank() exists but no
 * caller uses it, so the newest matching file wins regardless of how well it
 * matches. The precision assertions below record today's behaviour honestly —
 * including one that documents the gap rather than pretending it away — so that
 * wiring real relevance in has a before-and-after to point at.
 */
class DocumentRetrievalPrecisionTest extends TestCase
{
    private DocumentSearchService $search;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->createSchema();
        $this->seedCorpus();

        // The schema above deliberately leaves the AI settings empty so the
        // provider resolution falls back to the curated map. That fallback only
        // works if the .env GROQ key does not leak into the test — force it
        // off everywhere (config, $_ENV, $_SERVER, getenv), otherwise every
        // unrecognised-term query makes a live API call (which is also exactly
        // what a spent daily budget would break).
        config(['services.groq.api_key' => '']);
        $_ENV['GROQ_API_KEY'] = '';
        $_SERVER['GROQ_API_KEY'] = '';
        putenv('GROQ_API_KEY=');

        $this->search = new DocumentSearchService(
            new AiAccessPolicy(),
            new SemanticSearchService(),
            new AiFileResolver(new AiAccessPolicy()),
        );
    }

    /**
     * Only the tables these assertions touch. The project's migrations cannot
     * run on the test connection — see tests/Unit/AiFileResolverTest.php — so
     * the schema is built by hand.
     */
    private function createSchema(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('photo')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('document_type')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('uploaded_at')->nullable();
        });

        Schema::create('document_extractions', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 40);
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('file_path')->nullable();
            $table->longText('content')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('title')->nullable();
            $table->string('certificate_path')->nullable();
            $table->string('status')->nullable();
            $table->date('date_to')->nullable();
            $table->timestamps();
        });

        Schema::create('government_ids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('gsis_file_path')->nullable();
            $table->string('philhealth_file_path')->nullable();
            $table->string('pagibig_file_path')->nullable();
            $table->string('tin_file_path')->nullable();
            $table->string('license_file_path')->nullable();
        });

        // Consulted when an unrecognised term needs LLM expansion; empty means
        // "no provider", so expansion falls back to the curated map alone and
        // the corpus below is the only thing driving results.
        Schema::create('system_ai_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->nullable();
            $table->text('api_key')->nullable();
            $table->string('model')->nullable();
            $table->timestamps();
        });
    }

    /**
     * A small corpus with the properties that make retrieval interesting: two
     * employees, several document types, and one file whose *contents* are the
     * only thing connecting it to the query.
     */
    private function seedCorpus(): void
    {
        Employee::insert([
            ['id' => 1, 'employee_id' => '2021-001', 'first_name' => 'Juan', 'last_name' => 'Dela Cruz', 'created_at' => '2021-01-04'],
            ['id' => 2, 'employee_id' => '2022-014', 'first_name' => 'Maria', 'last_name' => 'Santos', 'created_at' => '2022-03-11'],
        ]);

        $documents = [
            // id, employee, type, file, uploaded_at
            [1, 1, 'Medical Certificate', 'juan-medcert.pdf', '2024-01-10'],
            [2, 1, 'Employment Contract', 'juan-contract.pdf', '2024-06-01'],
            [3, 2, 'Medical Certificate', 'maria-medcert.pdf', '2025-02-20'],
            [4, 2, 'Diploma', 'maria-diploma.pdf', '2025-03-15'],
            // Type says nothing about asbestos; only the extracted text does.
            [5, 1, 'Clearance', 'juan-clearance.pdf', '2023-05-05'],
        ];

        foreach ($documents as [$id, $employeeId, $type, $name, $uploadedAt]) {
            $path = "documents/{$name}";
            Storage::disk('public')->put($path, 'x');

            Document::insert([
                'id' => $id,
                'employee_id' => $employeeId,
                'document_type' => $type,
                'file_path' => $path,
                'status' => 'approved',
                'uploaded_at' => $uploadedAt,
            ]);
        }

        DocumentExtraction::insert([
            'source_type' => 'document',
            'source_id' => 5,
            'employee_id' => 1,
            'file_path' => 'documents/juan-clearance.pdf',
            'content' => 'This is to certify that the bearer has completed the asbestos handling safety orientation.',
            'status' => 'extracted',
        ]);
    }

    private function user(array $roles, ?int $employeeId = null): User
    {
        $user = new User();
        $user->roles = $roles;
        $user->status = 'Active';
        $user->employee_id = $employeeId;

        return $user;
    }

    /** The document ids a search returned, in the order it returned them. */
    private function idsFor(string $query, array $roles = ['hr'], ?int $employeeId = null): array
    {
        $result = $this->search->search($this->user($roles, $employeeId), $query);

        return array_map(fn (array $row) => $row['id'], $result['data']);
    }

    #[Test]
    public function a_document_type_query_returns_every_matching_file_and_nothing_else(): void
    {
        $ids = $this->idsFor('show me the medical certificates');

        $this->assertContains(1, $ids, 'Juan\'s medical certificate is missing');
        $this->assertContains(3, $ids, 'Maria\'s medical certificate is missing');
        $this->assertNotContains(4, $ids, 'a diploma matched a medical-certificate query');
    }

    /**
     * Naming an employee has to narrow the result set, otherwise "Maria's
     * medical certificate" hands back Juan's too.
     */
    #[Test]
    public function naming_an_employee_narrows_the_results_to_them(): void
    {
        $ids = $this->idsFor('medical certificate of Maria Santos');

        $this->assertContains(3, $ids);
        $this->assertNotContains(1, $ids, "Juan's file came back for a query naming Maria");
    }

    /**
     * The whole point of document_extractions: a file findable by what is
     * inside it, not just by its type or filename. Nothing in row 5's
     * document_type or path mentions asbestos.
     */
    #[Test]
    public function a_file_is_findable_by_its_extracted_contents(): void
    {
        $ids = $this->idsFor('asbestos');

        $this->assertContains(5, $ids, 'content search did not reach document_extractions');
    }

    /**
     * Scoping still holds on the retrieval path: an employee searching a type
     * that exists for several people gets only their own.
     */
    #[Test]
    public function an_employee_only_ever_retrieves_their_own_files(): void
    {
        $ids = $this->idsFor('medical certificate', ['employee'], 2);

        $this->assertSame([3], $ids);
    }

    /**
     * Precision@1 — the measurement step 5 has to improve.
     *
     * Both medical certificates match equally on type, so the ranking has
     * nothing to separate them but `uploaded_at DESC`. That is recorded here as
     * the current behaviour, not endorsed: once relevance ranking is wired in,
     * this assertion is the one that should change, deliberately and visibly.
     */
    #[Test]
    public function ranking_is_currently_recency_not_relevance(): void
    {
        $ids = $this->idsFor('medical certificate');

        $this->assertSame(
            3,
            $ids[0],
            'Expected the newest match first — ranking is uploaded_at DESC today. '
            . 'If relevance ranking has been wired in, update this test to assert relevance instead.'
        );
    }

    /**
     * "latest" is meant to collapse to one file per employee per type. With one
     * certificate each, that is one row per employee rather than a full list.
     */
    #[Test]
    public function asking_for_the_latest_collapses_duplicates_per_employee(): void
    {
        $ids = $this->idsFor('latest medical certificate');

        $this->assertCount(2, array_intersect($ids, [1, 3]));
    }

    /**
     * A query matching nothing must come back empty with an explanation,
     * never with an unfiltered fallback set.
     */
    #[Test]
    public function a_query_matching_nothing_returns_no_rows(): void
    {
        $result = $this->search->search($this->user(['hr']), 'show me the passport of Juan Dela Cruz');

        $this->assertSame([], $result['data']);
        $this->assertStringContainsString('could not find', $result['answer']);
    }
}
