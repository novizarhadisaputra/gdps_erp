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
        Schema::create(config('database.default') === 'sqlite' ? 'project_reviews' : 'crm.project_reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->nullable();
            $table->integer('sequence_number')->nullable();
            $table->integer('year')->nullable();
            $table->foreignUuid('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('general_information_id')->nullable()->constrained('general_informations')->nullOnDelete();
            $table->foreignUuid('profitability_analysis_id')->nullable()->constrained('profitability_analyses')->nullOnDelete();
            $table->foreignUuid('proposal_id')->nullable()->constrained('proposals')->nullOnDelete();

            $table->string('status')->default('draft');
            $table->integer('revision_number')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_reviews');
    }
};
