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
            
            $table->decimal('target_revenue', 20, 2)->nullable();
            $table->decimal('forecast_revenue', 20, 2)->nullable();
            $table->decimal('actual_revenue', 20, 2)->default(0);
            $table->decimal('actual_cost', 20, 2)->default(0);
            
            $table->string('month');
            $table->integer('year');
            $table->string('status')->default('draft'); // draft, finalized
            $table->json('actual_details')->nullable();
            
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
