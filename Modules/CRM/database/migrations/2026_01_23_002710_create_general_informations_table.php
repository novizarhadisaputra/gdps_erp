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
        Schema::create(config('database.default') === 'sqlite' ? 'crm_general_informations' : 'crm.general_informations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_leads' : 'crm.leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'crm_customers' : 'crm.customers')->onDelete('cascade');
            $table->foreignUuid('sales_plan_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_sales_plans' : 'crm.sales_plans')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('number')->nullable()->unique();
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->text('scope_of_work')->nullable();
            $table->foreignUuid('project_area_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_project_areas' : 'master_data.project_areas');
            $table->foreignUuid('work_scheme_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_work_schemes' : 'master_data.work_schemes')->nullOnDelete();
            $table->foreignUuid('tax_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_taxes' : 'master_data.taxes')->nullOnDelete();
            $table->string('location')->nullable(); // Keep for legacy or specific detail if needed, or deprecate
            $table->date('estimated_start_date')->nullable();
            $table->date('estimated_end_date')->nullable();
            $table->text('manpower_qualifications')->nullable();
            $table->text('work_activities')->nullable();
            $table->text('service_level')->nullable();
            $table->text('billing_requirements')->nullable();
            $table->json('risk_management')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->string('rr_document_number')->nullable(); // Risk Register Document Number
            $table->string('rr_document_path')->nullable();
            $table->string('rr_submission_id')->nullable();
            $table->string('rr_status')->nullable();
            $table->json('rr_payload')->nullable();
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'crm_general_informations' : 'crm.general_informations');
    }
};
