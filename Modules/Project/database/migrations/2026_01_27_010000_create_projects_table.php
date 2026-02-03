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
        Schema::create('projects', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('planning');
            $table->foreignUuid('customer_id')->constrained('customers')->onDelete('cascade');
            $table->foreignUuid('contract_id')->nullable()->constrained('contracts')->onDelete('set null');
            $table->foreignUuid('proposal_id')->nullable()->constrained('proposals')->onDelete('set null');
            $table->foreignUuid('profitability_analysis_id')->nullable()->constrained('profitability_analyses')->onDelete('set null');
            $table->string('project_number')->nullable();
            $table->foreignUuid('work_scheme_id')->nullable()->constrained('work_schemes')->onDelete('set null');
            $table->foreignUuid('product_cluster_id')->nullable()->constrained('product_clusters')->onDelete('set null');
            $table->foreignUuid('tax_id')->nullable()->constrained('taxes')->onDelete('set null');
            $table->foreignUuid('payment_term_id')->nullable()->constrained('payment_terms')->onDelete('set null');
            $table->foreignUuid('project_type_id')->nullable()->constrained('project_types')->onDelete('set null');
            $table->foreignUuid('billing_option_id')->nullable()->constrained('billing_options')->onDelete('set null');
            $table->foreignUuid('oprep_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignUuid('ams_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignUuid('project_area_id')->nullable()->constrained('project_areas')->onDelete('set null');
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
