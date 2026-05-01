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
        Schema::create(config('database.default') === 'sqlite' ? 'accrue_revenues' : 'finance.accrue_revenues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'projects' : 'project.projects')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->nullOnDelete();
            $table->foreignUuid('project_area_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'project_areas' : 'master_data.project_areas')->nullOnDelete();
            $table->string('number')->unique()->nullable();
            $table->unsignedInteger('sequence_number')->nullable();
            $table->string('company_code')->nullable();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->date('work_period')->nullable();
            $table->date('accrual_period')->nullable();
            $table->decimal('total_amount_estimated', 20, 2)->default(0);
            $table->decimal('total_amount_actual', 20, 2)->default(0);
            $table->decimal('total_amount_expense_estimated', 20, 2)->default(0);
            $table->decimal('total_amount_expense_actual', 20, 2)->default(0);
            $table->string('status')->default('open'); // open, closed, reversed
            $table->string('sap_reference')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'accrue_revenues' : 'finance.accrue_revenues');
    }
};
