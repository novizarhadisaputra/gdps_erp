<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'profitability_analysis_revisions' : 'finance.profitability_analysis_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profitability_analysis_id')->constrained(config('database.default') === 'sqlite' ? 'profitability_analyses' : 'finance.profitability_analyses')->onDelete('cascade');
            $table->integer('revision_number');
            $table->json('snapshot')->comment('Full data snapshot of the profitability analysis at the time of revision for auditing and restoration.');
            $table->text('reason')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index(['year', 'sequence_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'profitability_analysis_revisions' : 'finance.profitability_analysis_revisions');
    }
};
