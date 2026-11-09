<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The weighted fit score and its explanation for one application.
 *
 * Ported from `class ScreeningResult` (native port, staff-only scope).
 *
 * `rank_position` is the column name because `rank` is a MySQL reserved word.
 * `subscores` holds the per-component breakdown the explanation is built from.
 * There is deliberately no denormalised overall column beyond `fit_score`:
 * `explanation` and `subscores` are written once by the pipeline, and a score is
 * never recomputed in place (a re-rank writes a new row).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screening_results', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('applicant_id')->constrained('applicant_profiles');
            $table->foreignUuid('vacancy_id')->constrained('vacancies');

            $table->decimal('fit_score', 5, 2);
            $table->json('subscores');
            $table->integer('rank_position')->nullable();
            $table->string('explanation', 2000);
            $table->string('model_version', 50);

            $table->dateTime('created_at')->useCurrent();

            $table->index(['vacancy_id', 'rank_position'], 'idx_vacancy_rank');
            // The cross-vacancy ranking sort has no vacancy_id filter, so
            // idx_vacancy_rank's leftmost prefix cannot help it.
            $table->index('fit_score', 'idx_results_fit_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_results');
    }
};
