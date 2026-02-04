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
        Schema::create('profitability_analysis_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profitability_analysis_id')->constrained('profitability_analyses')->cascadeOnDelete();
            
            // Polymorphic Columns: costable_id & costable_type
            // This allows linking to Item, JobPosition, or any other model.
            $table->uuidMorphs('costable'); 
            
            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_cost_price', 15, 2)->default(0); // "Modal" price
            $table->decimal('markup_percentage', 5, 2)->default(0);
            $table->integer('depreciation_months')->nullable(); // Flexible override
            $table->decimal('total_monthly_cost', 15, 2)->default(0); // (unit_cost_price / depreciation_months) * quantity
            $table->decimal('total_monthly_sale', 15, 2)->default(0); // total_monthly_cost * (1 + markup/100)
            $table->integer('duration_months')->default(1);
            $table->json('cost_breakdown')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profitability_analysis_items');
    }
};
