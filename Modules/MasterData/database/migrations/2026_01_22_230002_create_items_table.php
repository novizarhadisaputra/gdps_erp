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
        Schema::create(config('database.default') === 'sqlite' ? 'items' : 'master_data.items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('item_category_id')->constrained(config('database.default') === 'sqlite' ? 'item_categories' : 'master_data.item_categories')->onDelete('cascade');
            $table->foreignUuid('asset_group_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'asset_groups' : 'master_data.asset_groups')->nullOnDelete();
            $table->foreignUuid('unit_of_measure_id')->constrained(config('database.default') === 'sqlite' ? 'units_of_measure' : 'master_data.units_of_measure')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('depreciation_months')->nullable();
            $table->date('price_valid_at')->nullable()->default(now());
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'items' : 'master_data.items');
    }
};
