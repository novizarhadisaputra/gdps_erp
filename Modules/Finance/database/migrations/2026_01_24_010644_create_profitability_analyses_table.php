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
        Schema::create(config('database.default') === 'sqlite' ? 'finance_profitability_analyses' : 'finance.profitability_analyses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->nullable()->unique();
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_leads' : 'crm.leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_customers' : 'crm.customers')->onDelete('set null');
            $table->foreignUuid('general_information_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_general_informations' : 'crm.general_informations')->onDelete('set null');
            $table->uuid('proposal_id')->nullable();
            $table->foreignUuid('project_type_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_project_types' : 'master_data.project_types')->onDelete('set null');
            $table->foreignUuid('product_cluster_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_product_clusters' : 'master_data.product_clusters')->onDelete('set null');
            $table->foreignUuid('tax_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_taxes' : 'master_data.taxes')->onDelete('set null');
            $table->foreignUuid('project_area_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_project_areas' : 'master_data.project_areas')->onDelete('set null');
            $table->foreignUuid('payment_term_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_payment_terms' : 'master_data.payment_terms')->onDelete('set null');
            $table->foreignUuid('work_scheme_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_work_schemes' : 'master_data.work_schemes')->nullOnDelete();
            $table->foreignUuid('revenue_segment_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_revenue_segments' : 'master_data.revenue_segments')->onDelete('set null');

            $table->string('asset_ownership')->default('gdps-owned');
            $table->decimal('interest_rate', 5, 2)->default(1.50);
            $table->decimal('tax_rate', 5, 2)->default(22.00);

            $table->decimal('revenue_per_month', 15, 2)->default(0);
            $table->decimal('direct_cost', 15, 2)->default(0);
            $table->decimal('depreciation', 15, 2)->default(0);
            $table->decimal('manual_depreciation', 15, 2)->default(0);
            $table->decimal('management_fee', 15, 2)->default(0);
            $table->decimal('management_fee_rate', 5, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->default(0);
            $table->decimal('ebitda', 15, 2)->default(0);
            $table->decimal('ebit', 15, 2)->default(0);
            $table->decimal('ebt', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('net_profit_margin', 5, 2)->default(0);

            $table->json('analysis_details')->nullable();

            $table->integer('project_number')->nullable();
            $table->string('status')->default('draft'); // draft, approved, rejected, converted
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_margin_approved')->default(false);
            $table->integer('revision_number')->default(0);
            $table->string('previous_code')->nullable();
            $table->boolean('is_imported')->default(false);
            $table->boolean('is_manual_cost')->default(false);
            $table->uuid('import_source_id')->nullable();
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['year', 'sequence_number']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'finance_profitability_analyses' : 'finance.profitability_analyses');
    }
};
