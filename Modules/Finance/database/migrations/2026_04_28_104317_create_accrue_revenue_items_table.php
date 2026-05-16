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
        Schema::create(config('database.default') === 'sqlite' ? 'finance_accrue_revenue_items' : 'finance.accrue_revenue_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accrue_revenue_id')->constrained(config('database.default') === 'sqlite' ? 'finance_accrue_revenues' : 'finance.accrue_revenues')->cascadeOnDelete();
            $table->string('type')->default('revenue');
            $table->foreignUuid('revenue_type_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_revenue_types' : 'master_data.revenue_types')->nullOnDelete();
            $table->decimal('amount_estimated', 15, 2)->default(0);
            $table->decimal('amount_actual', 15, 2)->default(0);
            $table->decimal('amount_expense_estimated', 15, 2)->default(0);
            $table->decimal('amount_expense_actual', 15, 2)->default(0);
            $table->foreignUuid('invoice_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'finance_invoices' : 'finance.invoices')->nullOnDelete();
            $table->foreignUuid('work_completion_report_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'project_work_completion_reports' : 'project.work_completion_reports')->nullOnDelete();
            $table->boolean('is_reversed')->default(false);
            $table->text('description')->nullable();
            $table->foreignUuid('revenue_chart_of_account_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'finance_chart_of_accounts' : 'finance.chart_of_accounts')->nullOnDelete();
            $table->foreignUuid('expense_chart_of_account_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'finance_chart_of_accounts' : 'finance.chart_of_accounts')->nullOnDelete();
            $table->foreignUuid('accrual_chart_of_account_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'finance_chart_of_accounts' : 'finance.chart_of_accounts')->nullOnDelete();
            $table->foreignUuid('accrued_expense_chart_of_account_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'finance_chart_of_accounts' : 'finance.chart_of_accounts')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'finance_accrue_revenue_items' : 'finance.accrue_revenue_items');
    }
};
