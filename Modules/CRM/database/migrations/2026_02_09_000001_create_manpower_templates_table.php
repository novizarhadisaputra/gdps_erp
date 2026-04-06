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
        Schema::create(config('database.default') === 'sqlite' ? 'manpower_templates' : 'crm.manpower_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads')->nullOnDelete();
            $table->foreignUuid('project_area_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'project_areas' : 'master_data.project_areas')->nullOnDelete();
            $table->foreignUuid('work_scheme_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'work_schemes' : 'master_data.work_schemes')->nullOnDelete();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_imported')->default(false);
            $table->uuid('import_source_id')->nullable();
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index(['year', 'sequence_number']);
        });

        Schema::create(config('database.default') === 'sqlite' ? 'manpower_template_items' : 'crm.manpower_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manpower_template_id')->constrained(config('database.default') === 'sqlite' ? 'manpower_templates' : 'crm.manpower_templates')->cascadeOnDelete();
            $table->foreignUuid('job_position_id')->constrained(config('database.default') === 'sqlite' ? 'job_positions' : 'master_data.job_positions')->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->string('risk_level')->default('very_low');
            $table->string('employee_type')->default('ppu');
            $table->boolean('is_labor_intensive')->default(false);
            $table->boolean('bill_thr_monthly')->default(true);
            $table->boolean('bill_compensation_monthly')->default(true);
            $table->boolean('include_non_fixed_in_accruals')->default(false);
            $table->jsonb('allowances')->nullable();
            $table->jsonb('extra_costs')->nullable();
            $table->decimal('future_adjustment_rate', 5, 2)->default(0);
            $table->string('ptkp_status')->default('TK/0');
            $table->boolean('is_bpjs_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'manpower_template_items' : 'crm.manpower_template_items');
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'manpower_templates' : 'crm.manpower_templates');
    }
};
