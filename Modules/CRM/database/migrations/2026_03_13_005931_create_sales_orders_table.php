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
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('so_number')->unique();
            $table->date('order_date');
            
            $table->foreignId('project_id')->constrained('projects');
            $table->foreignId('proposal_id')->constrained('proposals');
            $table->foreignId('customer_id')->constrained('customers');
            
            $table->string('type'); // internal, external
            $table->string('status'); // draft, sent, approved, cancelled
            $table->decimal('amount', 20, 2);
            
            // Financials from PA
            $table->decimal('management_fee_percentage', 5, 2)->default(10.00);
            $table->decimal('tax_percentage', 5, 2)->default(11.00);
            
            // Staffing & Execution
            $table->foreignId('sales_pic_id')->nullable()->constrained('employees');
            $table->foreignId('project_manager_id')->nullable()->constrained('employees');
            $table->string('service_type')->nullable();
            $table->string('job_location')->nullable();
            $table->integer('manpower_initial_qty')->default(0);
            $table->text('manpower_composition')->nullable();
            
            // Terms (as identified from spreadsheet)
            $table->text('payment_terms')->nullable();
            $table->string('probation_period')->nullable();
            $table->string('replacement_sla')->nullable();
            $table->string('reporting_schedule')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
