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
        Schema::create(config('database.default') === 'sqlite' ? 'crm_costing_template_items' : 'crm.costing_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('costing_template_id')->constrained(config('database.default') === 'sqlite' ? 'crm_costing_templates' : 'crm.costing_templates')->onDelete('cascade');
            $table->foreignUuid('item_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_items' : 'master_data.items')->nullOnDelete();

            // Enum for Costing Category (Tools, Material, IT, etc.)
            $table->string('category')->nullable();

            $table->string('name'); // Item Name (from Item or Manual)
            $table->integer('quantity')->default(1);
            $table->string('unit')->nullable();

            $table->decimal('unit_price', 15, 2)->default(0); // Base Price
            $table->decimal('markup_percent', 5, 2)->default(0); // Markup %
            $table->decimal('unit_price_markup', 15, 2)->default(0); // Price after markup

            $table->decimal('total_price', 15, 2)->default(0); // Qty * Price Markup

            // Asset Logic
            $table->foreignUuid('asset_group_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_asset_groups' : 'master_data.asset_groups')->nullOnDelete();
            $table->integer('useful_life_years')->nullable();
            $table->integer('depreciation_months')->nullable();
            $table->decimal('monthly_cost', 15, 2)->default(0); // Depr or Expense
            $table->string('depreciation_method')->default('straight_line');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'crm_costing_template_items' : 'crm.costing_template_items');
    }
};
