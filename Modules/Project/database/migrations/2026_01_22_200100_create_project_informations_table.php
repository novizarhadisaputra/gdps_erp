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
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('status')->default('planning');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->json('feasibility_study')->nullable();
            $table->json('profitability_analysis')->nullable();
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->onDelete('set null');
            $table->foreignId('project_type_id')->nullable()->constrained('project_types')->onDelete('set null');
            $table->foreignId('billing_option_id')->nullable()->constrained('billing_options')->onDelete('set null');
            $table->foreignId('oprep_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('ams_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->text('remarks')->nullable();
            $table->string('ipk_status')->nullable();
            $table->string('thr_status')->nullable();
            $table->string('previous_code')->nullable();
            $table->string('pic_client_name')->nullable();
            $table->string('pic_client_phone')->nullable();
            $table->json('risk_management')->nullable();
            $table->decimal('direct_cost', 15, 2)->nullable();
            $table->date('process_date')->nullable();

            // Section B & C: Operational & Financial
            $table->string('operational_visit_schedule')->nullable();
            $table->date('bapp_cut_off_date')->nullable();
            $table->decimal('revenue_per_month', 15, 2)->nullable();
            $table->string('pic_finance_name')->nullable();
            $table->string('pic_finance_phone')->nullable();
            $table->string('pic_finance_email')->nullable();
            $table->date('max_invoice_send_date')->nullable();

            // Section E: Material, Equipment & Others
            $table->json('material_equipment_details')->nullable(); // For individual items checkboxes/counts
            $table->decimal('management_fee_per_month', 15, 2)->nullable();
            $table->decimal('ppn_percentage', 5, 2)->default(11.00);

            // Section F: Manpower
            $table->integer('manpower_cleaner')->default(0);
            $table->integer('manpower_leader_cleaner')->default(0);
            $table->integer('manpower_engineer')->default(0);
            $table->integer('manpower_security')->default(0);

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
