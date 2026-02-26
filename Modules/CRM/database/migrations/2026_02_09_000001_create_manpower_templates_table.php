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
        Schema::create('crm.manpower_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_area_id')->nullable()->constrained('master_data.project_areas')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('risk_level')->default('very_low');
            $table->boolean('is_labor_intensive')->default(false);
            $table->string('employee_type')->default('ppu');
            $table->boolean('bill_thr_monthly')->default(true);
            $table->boolean('bill_compensation_monthly')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('crm.manpower_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manpower_template_id')->constrained('crm.manpower_templates')->cascadeOnDelete();
            $table->foreignUuid('job_position_id')->constrained('master_data.job_positions')->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm.manpower_template_items');
        Schema::dropIfExists('crm.manpower_templates');
    }
};
