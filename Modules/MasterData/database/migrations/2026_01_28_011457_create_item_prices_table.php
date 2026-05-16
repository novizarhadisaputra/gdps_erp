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
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_item_prices' : 'master_data.item_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('item_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('project_area_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_project_areas' : 'master_data.project_areas')->cascadeOnDelete();
            $table->decimal('price', 15, 2);
            $table->timestamps();

            $table->unique(['item_id', 'project_area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_item_prices' : 'master_data.item_prices');
    }
};
