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
        Schema::create(config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->date('order_date');
            $table->nullableUuidMorphs('sourceable');

            $table->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'projects' : 'project.projects');
            $table->foreignUuid('proposal_id')->constrained(config('database.default') === 'sqlite' ? 'proposals' : 'crm.proposals');
            $table->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers');

            $table->string('type'); // internal, external
            $table->string('status'); // draft, sent, approved, cancelled
            $table->decimal('amount', 20, 2);

            // Financials from PA
            $table->decimal('management_fee_percentage', 5, 2)->default(10.00);
            $table->decimal('tax_percentage', 5, 2)->default(11.00);

            // Staffing & Execution
            $table->foreignUuid('sales_pic_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'employees' : 'master_data.employees');
            $table->foreignUuid('project_manager_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'employees' : 'master_data.employees');
            $table->string('service_type')->nullable();
            $table->string('job_location')->nullable();
            $table->integer('manpower_initial_qty')->default(0);
            $table->text('manpower_composition')->nullable();

            // Terms (as identified from spreadsheet)
            $table->text('payment_terms')->nullable();
            $table->string('probation_period')->nullable();
            $table->string('replacement_sla')->nullable();
            $table->string('reporting_schedule')->nullable();
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['year', 'sequence_number']);
            $table->unique(['project_id', 'proposal_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders');
    }
};
