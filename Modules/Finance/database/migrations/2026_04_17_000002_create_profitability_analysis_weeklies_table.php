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
        $schema = config('database.default') === 'sqlite' ? 'profitability_analysis_weeklies' : 'finance.profitability_analysis_weeklies';

        Schema::create($schema, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profitability_analysis_monthly_id')
                ->constrained(config('database.default') === 'sqlite' ? 'profitability_analysis_monthlies' : 'finance.profitability_analysis_monthlies')
                ->onDelete('cascade');
            
            $table->integer('week_number');
            $table->decimal('achieved_revenue', 20, 2)->default(0);
            $table->decimal('projected_revenue', 20, 2)->default(0);
            
            $table->string('month')->nullable();
            $table->integer('year')->nullable();
            
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'profitability_analysis_weeklies' : 'finance.profitability_analysis_weeklies');
    }
};
