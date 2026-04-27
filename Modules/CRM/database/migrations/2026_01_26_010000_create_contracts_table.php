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
        Schema::create(config('database.default') === 'sqlite' ? 'contracts' : 'crm.contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->onDelete('set null');
            $table->foreignUuid('proposal_id')->nullable()->constrained()->onDelete('set null');
            $table->string('number')->nullable()->unique();
            $table->string('type')->default('agreement'); // agreement, work_order
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, expired, terminated
            $table->string('reminder_status')->nullable(); // 6_month, 3_month, 1_month
            $table->text('termination_reason')->nullable();
            $table->timestamps();
        });

        // Add foreign keys to sales_plans (after contracts table exists)
        Schema::table(config('database.default') === 'sqlite' ? 'sales_plans' : 'crm.sales_plans', function (Blueprint $table) {
            $table->foreign('agreement_id')->references('id')->on(config('database.default') === 'sqlite' ? 'contracts' : 'crm.contracts')->nullOnDelete();
            $table->foreign('work_order_id')->references('id')->on(config('database.default') === 'sqlite' ? 'contracts' : 'crm.contracts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('database.default') === 'sqlite' ? 'sales_plans' : 'crm.sales_plans', function (Blueprint $table) {
            $table->dropForeign(['agreement_id']);
            $table->dropForeign(['work_order_id']);
        });

        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'contracts' : 'crm.contracts');
    }
};
