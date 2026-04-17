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
            
            $table->decimal('target_revenue', 20, 2)->nullable()->comment('Original target revenue from Sales Plan');
            $table->decimal('forecast_revenue', 20, 2)->nullable()->comment('Rolling forecast revenue from weekly updates');
            $table->decimal('actual_revenue', 20, 2)->default(0)->comment('Actual revenue recognized');
            $table->decimal('actual_cost', 20, 2)->default(0)->comment('Actual costs incurred from implementation');
            $table->decimal('actual_net_profit', 20, 2)->default(0)->comment('Actual net profit calculated as (Revenue - Cost)');
            $table->decimal('actual_margin_percentage', 10, 2)->default(0)->comment('Actual profit margin percentage');
            $table->decimal('variance_revenue', 20, 2)->default(0)->comment('Difference between actual revenue and target revenue');
            $table->decimal('variance_profit', 20, 2)->default(0)->comment('Difference between actual net profit and expected profit');
            
            $table->string('month');
            $table->integer('year');
            $table->string('status')->default('draft'); // draft, finalized
            $table->json('actual_details')->nullable()->comment('Detailed breakdown of actual costs incurred');
            
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
