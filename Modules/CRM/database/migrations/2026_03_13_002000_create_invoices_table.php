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
        Schema::create('invoices', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('sales_order_id')->index();
            $blueprint->uuid('work_completion_report_id')->nullable()->index();
            $blueprint->uuid('customer_id')->index();

            $blueprint->string('invoice_number')->unique();
            $blueprint->date('invoice_date');
            $blueprint->date('due_date')->nullable();

            $blueprint->decimal('amount', 15, 2);
            $blueprint->decimal('tax_amount', 15, 2)->default(0);
            $blueprint->decimal('total_amount', 15, 2);

            $blueprint->string('status')->default('draft');

            $blueprint->timestamps();
            $blueprint->softDeletes();

            $blueprint->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $blueprint->foreign('work_completion_report_id')->references('id')->on('work_completion_reports')->onDelete('set null');
            $blueprint->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
