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
        Schema::create('crm.contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->nullable()->constrained('crm.leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->nullable()->constrained('master_data.customers')->onDelete('set null');
            $table->foreignUuid('proposal_id')->nullable()->constrained()->onDelete('set null');
            $table->string('contract_number')->unique();
            $table->string('type')->default('agreement'); // agreement, work_order
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, expired, terminated
            $table->string('reminder_status')->nullable(); // 6_month, 3_month, 1_month
            $table->text('termination_reason')->nullable();
            $table->timestamps();
        });

        // Add foreign keys to sales_plans (after contracts table exists)
        Schema::table('crm.sales_plans', function (Blueprint $table) {
            $table->foreign('agreement_id')->references('id')->on('crm.contracts')->nullOnDelete();
            $table->foreign('work_order_id')->references('id')->on('crm.contracts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('crm.sales_plans', function (Blueprint $table) {
            $table->dropForeign(['agreement_id']);
            $table->dropForeign(['work_order_id']);
        });

        Schema::dropIfExists('crm.contracts');
    }
};
