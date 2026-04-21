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
        Schema::create(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'projects' : 'project.projects')->onDelete('cascade');
            $blueprint->foreignUuid('sales_order_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders')->onDelete('set null');
            $blueprint->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->onDelete('cascade');

            $blueprint->string('report_number')->unique();
            $blueprint->date('document_date');

            $blueprint->date('service_period_start');
            $blueprint->date('service_period_end');

            $blueprint->decimal('work_progress_percentage', 5, 2)->default(100.00);
            $blueprint->text('description')->nullable();
            $blueprint->jsonb('items')->nullable();

            $blueprint->string('status')->default('draft');

            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports');
    }
};
