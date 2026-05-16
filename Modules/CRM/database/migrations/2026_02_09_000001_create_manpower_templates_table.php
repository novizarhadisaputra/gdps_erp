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
        Schema::create(config('database.default') === 'sqlite' ? 'crm_manpower_templates' : 'crm.manpower_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_leads' : 'crm.leads')->nullOnDelete()->comment('Reference to the associated lead/opportunity');
            $table->foreignUuid('project_area_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_project_areas' : 'master_data.project_areas')->nullOnDelete()->comment('Determines the applicable minimum wage (UMK) for calculations');
            $table->foreignUuid('work_scheme_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_work_schemes' : 'master_data.work_schemes')->nullOnDelete()->comment('Defines the operational schedule (e.g., 24/7, 5/2)');
            $table->string('code')->nullable()->unique()->comment('Auto-generated template code');
            $table->string('name')->comment('Descriptive name for the costing sheet');
            $table->text('description')->nullable()->comment('Additional context or project details');
            $table->boolean('is_active')->default(true)->comment('Whether the template is currently available for use');
            $table->boolean('is_imported')->default(false)->comment('Flag for templates imported from legacy data or spreadsheets');
            $table->uuid('import_source_id')->nullable()->comment('Reference to the original source if imported');
            $table->integer('sequence_number')->default(0)->comment('Ordering sequence within the year');
            $table->integer('year')->nullable()->comment('Fiscal year for the template');
            $table->timestamps();

            $table->index(['year', 'sequence_number']);
        });

        Schema::create(config('database.default') === 'sqlite' ? 'crm_manpower_template_clusters' : 'crm.manpower_template_clusters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manpower_template_id')->constrained(config('database.default') === 'sqlite' ? 'crm_manpower_templates' : 'crm.manpower_templates')->cascadeOnDelete()->comment('Parent template reference');
            $table->foreignUuid('product_cluster_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_product_clusters' : 'master_data.product_clusters')->nullOnDelete()->comment('Master data product cluster reference (Aviation, FM, etc.)');
            $table->text('description')->nullable()->comment('Detailed information about the cluster');

            // Default policies for the cluster (can be overridden by items)
            $table->string('jkn_category')->default('PPU')->comment('Default BPJS JKN category for personnel in this cluster');
            $table->string('thr_billing_method')->default('monthly_accrual')->comment('How THR is billed (Monthly Accrual or One-time)');
            $table->string('compensation_billing_method')->default('monthly_accrual')->comment('How compensation is billed (Monthly Accrual or One-time)');

            $table->timestamps();
        });

        Schema::create(config('database.default') === 'sqlite' ? 'crm_manpower_template_items' : 'crm.manpower_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manpower_template_id')->constrained(config('database.default') === 'sqlite' ? 'crm_manpower_templates' : 'crm.manpower_templates')->cascadeOnDelete()->comment('Parent template reference');
            $table->foreignUuid('manpower_template_cluster_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'crm_manpower_template_clusters' : 'crm.manpower_template_clusters')->cascadeOnDelete()->comment('Cluster grouping reference');

            $table->foreignUuid('product_cluster_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_product_clusters' : 'master_data.product_clusters')->nullOnDelete()->comment('Override product cluster for specific role');
            $table->foreignUuid('job_position_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_job_positions' : 'master_data.job_positions')->cascadeOnDelete()->comment('Associated job position');
            $table->foreignUuid('work_pattern_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_work_patterns' : 'master_data.work_patterns')->nullOnDelete()->comment('Shift/Work pattern reference');

            $table->decimal('basic_salary', 15, 2)->default(0)->comment('Base monthly salary for the position');
            $table->integer('quantity')->default(1)->comment('Number of personnel for this role');
            $table->text('notes')->nullable()->comment('Specific requirements or role notes');
            $table->string('risk_level')->default('very_low')->comment('BPJS JKK risk category');
            $table->string('employee_type')->default('ppu')->comment('PPU or PBPU status');
            $table->string('jkn_category')->default('PPU')->comment('BPJS Health participation category');
            $table->boolean('is_labor_intensive')->default(false)->comment('Whether the role is manually intensive');

            // Remuneration Policy
            $table->string('thr_billing_method')->default('monthly_accrual')->comment('THR billing override');
            $table->foreignUuid('thr_basis_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_thr_basis_types' : 'master_data.thr_basis_types')->nullOnDelete()->comment('Components included in THR calculation');
            $table->string('compensation_billing_method')->default('monthly_accrual')->comment('Compensation billing override');
            $table->foreignUuid('compensation_basis_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_thr_basis_types' : 'master_data.thr_basis_types')->nullOnDelete()->comment('Components included in compensation calculation');
            $table->foreignUuid('bpjs_basis_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_bpjs_basis_types' : 'master_data.bpjs_basis_types')->nullOnDelete()->comment('Salary basis for BPJS calculations');

            $table->boolean('bill_thr_monthly')->default(true)->comment('Flag to enable monthly THR billing');
            $table->boolean('bill_compensation_monthly')->default(true)->comment('Flag to enable monthly compensation billing');
            $table->boolean('include_non_fixed_in_accruals')->default(false)->comment('Whether non-fixed allowances are added to accrual basis');
            $table->jsonb('allowances')->nullable()->comment('JSON array of defined allowances');
            $table->jsonb('extra_costs')->nullable()->comment('JSON array of equipment/training costs');
            $table->decimal('future_adjustment_rate', 5, 2)->default(0)->comment('Percentage rate for salary scaling/forecast');
            $table->string('ptkp_status')->default('TK/0')->comment('Tax status for PPh 21');
            $table->boolean('is_bpjs_active')->default(true)->comment('Whether BPJS is enabled for this position');
            $table->boolean('is_tax_borne_by_company')->default(false)->comment('Whether company pays the employee tax portion');
            $table->boolean('use_ter_method')->default(true)->comment('Whether to use TER (Effective Rate) for tax calculation');

            // Employee Portions Borne by Company
            $table->boolean('is_employee_jkn_borne_by_company')->default(false)->comment('Company covers employee BPJS Health portion');
            $table->boolean('is_employee_jkk_borne_by_company')->default(false)->comment('Company covers employee BPJS JKK portion');
            $table->boolean('is_employee_jkm_borne_by_company')->default(false)->comment('Company covers employee BPJS JKM portion');
            $table->boolean('is_employee_jht_borne_by_company')->default(false)->comment('Company covers employee BPJS JHT portion');
            $table->boolean('is_employee_jp_borne_by_company')->default(false)->comment('Company covers employee BPJS JP portion');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'crm_manpower_template_items' : 'crm.manpower_template_items');
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'crm_manpower_template_clusters' : 'crm.manpower_template_clusters');
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'crm_manpower_templates' : 'crm.manpower_templates');
    }
};
