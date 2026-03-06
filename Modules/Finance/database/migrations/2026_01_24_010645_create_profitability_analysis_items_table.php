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
        Schema::create(config('database.default') === 'sqlite' ? 'profitability_analysis_items' : 'finance.profitability_analysis_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profitability_analysis_id')->constrained(config('database.default') === 'sqlite' ? 'profitability_analyses' : 'finance.profitability_analyses')->cascadeOnDelete();
            $table->uuid('import_source_id')->nullable();
            $table->uuid('direct_cost_category_id')->nullable();
            $table->uuid('ptkp_config_id')->nullable();

            // Polymorphic Columns: costable_id & costable_type
            // This allows linking to Item, JobPosition, or any other model.
            $table->nullableUuidMorphs('costable');

            $table->decimal('quantity', 15, 2)->default(1);
            $table->decimal('unit_cost_price', 15, 2)->default(0); // "Modal" price
            $table->string('calculation_type')->default('nominal'); // nominal, percentage
            $table->string('percentage_basis')->default('none'); // none, revenue, direct_cost
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'profitability_analysis_items' : 'finance.profitability_analysis_items');
    }
};
