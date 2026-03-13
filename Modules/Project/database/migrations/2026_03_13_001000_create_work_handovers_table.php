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
        Schema::create('work_handovers', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->uuid('project_id')->index();
            $blueprint->uuid('sales_order_id')->nullable()->index();
            $blueprint->uuid('customer_id')->index();

            $blueprint->string('handover_number')->unique();
            $blueprint->date('document_date');
            
            $blueprint->date('service_period_start');
            $blueprint->date('service_period_end');
            
            $blueprint->decimal('work_progress_percentage', 5, 2)->default(100.00);
            $blueprint->text('description')->nullable();

            $blueprint->string('status')->default('draft');
            
            $blueprint->timestamps();
            $blueprint->softDeletes();
            
            $blueprint->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $blueprint->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('set null');
            $blueprint->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_handovers');
    }
};
