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
        Schema::create('sales_plan_monthlies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_plan_id')->constrained('sales_plans')->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month'); // 1-12
            $table->decimal('budget_amount', 15, 2)->default(0);
            $table->decimal('forecast_amount', 15, 2)->default(0);
            $table->decimal('actual_amount', 15, 2)->default(0);
            $table->string('proposal_number')->nullable();
            $table->string('project_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_plan_monthlies');
    }
};
