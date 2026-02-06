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
        Schema::create('sales_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->constrained()->cascadeOnDelete();

            // Categorization
            $table->foreignUuid('project_type_id')->nullable()->constrained('project_types')->nullOnDelete();
            $table->foreignUuid('revenue_segment_id')->nullable()->constrained('revenue_segments')->nullOnDelete();
            $table->foreignUuid('service_line_id')->nullable()->constrained('service_lines')->nullOnDelete();
            $table->foreignUuid('industrial_sector_id')->nullable()->constrained('industrial_sectors')->nullOnDelete();
            $table->foreignUuid('skill_category_id')->nullable()->constrained('skill_categories')->nullOnDelete();

            $table->string('industry')->nullable(); // Legacy/Alternative

            // Financials
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->decimal('management_fee_percentage', 5, 2)->nullable();
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->integer('top_days')->nullable();

            // Timeline
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Level & Confidence
            $table->integer('priority_level')->nullable(); // 1, 2, 3
            $table->string('confidence_level')->nullable(); // optimistic, moderate, pessimistic

            // References
            $table->string('project_code')->nullable();
            $table->string('proposal_number')->nullable();
            $table->string('document_reference')->nullable();

            $table->json('revenue_distribution_planning')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_plans');
    }
};
