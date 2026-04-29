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
        Schema::create(config('database.default') === 'sqlite' ? 'invoices' : 'finance.invoices', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->foreignUuid('work_completion_report_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports')->onDelete('set null');
            $blueprint->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->onDelete('cascade');
            $blueprint->nullableUuidMorphs('sourceable');

            $blueprint->string('number')->unique();
            $blueprint->integer('sequence_number')->nullable();
            $blueprint->integer('year')->nullable();
            $blueprint->date('invoice_date');
            $blueprint->date('due_date')->nullable();

            $blueprint->decimal('amount', 15, 2);
            $blueprint->decimal('tax_percentage', 5, 2)->default(12.00);
            $blueprint->string('tax_basis')->default('total');
            $blueprint->decimal('tax_base_amount', 15, 2)->nullable();
            $blueprint->decimal('tax_amount', 15, 2)->default(0);
            $blueprint->decimal('total_amount', 15, 2);

            $blueprint->string('status')->default('draft');
            $blueprint->json('payment_info')->nullable();
            $blueprint->jsonb('items')->nullable();
            $blueprint->jsonb('tax_wording')->nullable();
            $blueprint->jsonb('content_config')->nullable();

            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'invoices' : 'finance.invoices');
    }
};
