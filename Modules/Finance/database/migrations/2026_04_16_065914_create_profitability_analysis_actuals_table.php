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
        Schema::create('profitability_analysis_actuals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profitability_analysis_id')->constrained('profitability_analyses')->cascadeOnDelete();
            
            $table->integer('month');
            $table->integer('year');
            
            $table->decimal('actual_revenue', 20, 2)->default(0);
            $table->decimal('actual_cost', 20, 2)->default(0);
            
            $table->json('actual_details')->nullable();
            
            $table->foreignUuid('user_id')->constrained('users');
            $table->timestamps();
            
            $table->unique(['profitability_analysis_id', 'month', 'year'], 'pa_actual_period_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profitability_analysis_actuals');
    }
};
