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
        $schema = config('database.default') === 'sqlite' ? 'profitability_analysis_monthlies' : 'finance.profitability_analysis_monthlies';

        Schema::create($schema, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profitability_analysis_id')
                ->constrained(config('database.default') === 'sqlite' ? 'profitability_analyses' : 'finance.profitability_analyses')
                ->onDelete('cascade');
            
            // Period Identification
            $table->string('month');
            $table->integer('year');
            $table->string('status')->default('draft'); // draft, finalized
            
            // Financial Baseline & Forecasts
            $table->decimal('target_revenue', 20, 2)->nullable()->comment('Original target revenue from Sales Plan (Baseline)');
            $table->decimal('forecast_revenue', 20, 2)->nullable()->comment('Latest Rolling Forecast (RoFo)');
            
            // Actual Performance Metrics
            $table->decimal('actual_revenue', 20, 2)->default(0)->comment('Realized revenue');
            $table->decimal('actual_cost', 20, 2)->default(0)->comment('Realized costs');
            $table->decimal('gross_profit', 20, 2)->default(0)->comment('Realized Gross Profit (Revenue - Direct Cost)');
            $table->decimal('ebit', 20, 2)->default(0)->comment('Earnings Before Interest and Taxes');
            $table->decimal('actual_net_profit', 20, 2)->default(0)->comment('Final realized net profit');
            $table->decimal('actual_margin_percentage', 10, 2)->default(0)->comment('Realized profit margin percentage');
            
            // Variance Analysis
            $table->decimal('variance_revenue', 20, 2)->default(0)->comment('Actual Revenue vs Target Revenue');
            $table->decimal('variance_profit', 20, 2)->default(0)->comment('Actual Net Profit vs Initial Plan');
            
            $table->json('actual_details')->nullable()->comment('Detailed breakdown of realized costs');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'profitability_analysis_monthlies' : 'finance.profitability_analysis_monthlies');
    }
};
