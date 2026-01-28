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
        Schema::create('project_informations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('project_id')->nullable();
            $table->string('status')->default('planning');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->json('profitability_analysis')->nullable();
            $table->foreignUuid('payment_term_id')->nullable()->constrained('payment_terms')->onDelete('set null');
            $table->foreignUuid('project_type_id')->nullable()->constrained('project_types')->onDelete('set null');
            $table->foreignUuid('billing_option_id')->nullable()->constrained('billing_options')->onDelete('set null');
            $table->foreignUuid('oprep_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignUuid('ams_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->string('ipk_status')->nullable();
            $table->string('thr_status')->nullable();
            $table->string('previous_code')->nullable();
            $table->decimal('direct_cost', 15, 2)->nullable();
            $table->date('process_date')->nullable();

            // Section B & C: Operational & Financial
            $table->string('operational_visit_schedule')->nullable();
            $table->date('bapp_cut_off_date')->nullable();
            $table->decimal('revenue_per_month', 15, 2)->nullable();
            $table->date('max_invoice_send_date')->nullable();

            // Section E: Material, Equipment & Others
            $table->json('analysis_details')->nullable();
            $table->decimal('management_fee_per_month', 15, 2)->nullable();
            $table->decimal('ppn_percentage', 5, 2)->default(11.00);

            // Section G: Remuneration & Payroll
            $table->json('remuneration_details')->nullable();
            $table->date('payroll_date')->nullable();
            $table->date('overtime_cut_off_date')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_informations');
    }
};
