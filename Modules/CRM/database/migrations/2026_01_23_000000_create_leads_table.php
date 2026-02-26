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
        Schema::create('crm.leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->foreignUuid('customer_id')->constrained('master_data.customers')->cascadeOnDelete();
            $table->foreignUuid('work_scheme_id')->nullable()->constrained('master_data.work_schemes')->nullOnDelete();
            $table->string('status')->default('lead'); // lead, approach, proposal, negotiation, won, closed_lost

            // Categorization (Flows to Sales Plan)
            $table->foreignUuid('revenue_segment_id')->nullable()->constrained('master_data.revenue_segments')->nullOnDelete();
            $table->foreignUuid('product_cluster_id')->nullable()->constrained('master_data.product_clusters')->nullOnDelete();
            $table->foreignUuid('project_type_id')->nullable()->constrained('master_data.project_types')->nullOnDelete();

            $table->foreignUuid('industrial_sector_id')->nullable()->constrained('master_data.industrial_sectors')->nullOnDelete();
            $table->foreignUuid('project_area_id')->nullable()->constrained('master_data.project_areas')->nullOnDelete();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('estimated_amount', 15, 2)->nullable();
            $table->integer('probability')->nullable(); // 0-100
            $table->date('expected_closing_date')->nullable();
            $table->string('confidence_level')->nullable(); // optimistic, moderate, pessimistic
            $table->flowforgePositionColumn('position');
            $table->json('job_positions')->nullable();
            $table->text('description')->nullable();

            // Person In Charge (Internal Sales)
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at'], 'idx_leads_status_created');
            $table->index(['user_id', 'status'], 'idx_leads_user_status');
            $table->index('expected_closing_date', 'idx_leads_expected_closing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm.leads');
    }
};
