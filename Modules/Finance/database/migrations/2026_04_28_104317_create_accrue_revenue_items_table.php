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
        Schema::create(config('database.default') === 'sqlite' ? 'accrue_revenue_items' : 'finance.accrue_revenue_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accrue_revenue_id')->constrained(config('database.default') === 'sqlite' ? 'accrue_revenues' : 'finance.accrue_revenues')->cascadeOnDelete();
            $table->string('revenue_type');
            $table->decimal('amount_estimated', 15, 2)->default(0);
            $table->decimal('amount_actual', 15, 2)->default(0);
            $table->decimal('amount_expense_estimated', 15, 2)->default(0);
            $table->decimal('amount_expense_actual', 15, 2)->default(0);
            $table->boolean('has_management_fee')->default(false);
            $table->foreignUuid('invoice_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'invoices' : 'finance.invoices')->nullOnDelete();
            $table->foreignUuid('work_completion_report_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports')->nullOnDelete();
            $table->boolean('is_reversed')->default(false);
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'accrue_revenue_items' : 'finance.accrue_revenue_items');
    }
};
