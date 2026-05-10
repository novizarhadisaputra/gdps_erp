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
            $blueprint->foreignUuid('tax_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes')->onDelete('set null');
            $blueprint->foreignUuid('project_area_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'project_areas' : 'master_data.project_areas')->onDelete('set null');
            $blueprint->foreignUuid('bank_account_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'bank_accounts' : 'master_data.bank_accounts')->onDelete('set null');
            $blueprint->nullableUuidMorphs('sourceable');

            $blueprint->string('number')->unique();
            $blueprint->integer('sequence_number')->nullable();
            $blueprint->integer('revision_number')->default(0);
            $blueprint->string('previous_code')->nullable();
            $blueprint->integer('year')->nullable();
            $blueprint->date('invoice_date');
            $blueprint->date('due_date')->nullable();

            $blueprint->decimal('amount', 15, 2);
            $blueprint->decimal('tax_percentage', 5, 2)->default(12.00);
            $blueprint->string('tax_basis')->default('total');
            $blueprint->decimal('tax_base_amount', 15, 2)->nullable();
            $blueprint->decimal('tax_amount', 15, 2)->default(0);
            $blueprint->json('tax_details')->nullable();
            $blueprint->decimal('withholding_tax_amount', 20, 2)->default(0);
            $blueprint->decimal('total_amount', 15, 2);

            $blueprint->string('status')->default('draft');
            $blueprint->string('invoice_type')->nullable();
            $blueprint->json('payment_info')->nullable();
            $blueprint->jsonb('items')->nullable();
            $blueprint->jsonb('tax_wording')->nullable();
            $blueprint->jsonb('content_config')->nullable();
            $blueprint->json('snapshot')->nullable();

            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        Schema::create(config('database.default') === 'sqlite' ? 'invoice_revisions' : 'finance.invoice_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('invoice_id')->constrained(config('database.default') === 'sqlite' ? 'invoices' : 'finance.invoices')->onDelete('cascade');
            $table->string('number')->nullable();
            $table->integer('sequence_number')->default(0);
            $table->json('snapshot')->comment('Full data snapshot of the invoice at the time of revision for auditing and restoration.');
            $table->text('reason')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index(['year', 'sequence_number'], 'finance_inv_rev_year_seq_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'invoice_revisions' : 'finance.invoice_revisions');
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'invoices' : 'finance.invoices');
    }
};
