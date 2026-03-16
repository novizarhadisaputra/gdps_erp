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
        Schema::create(config('database.default') === 'sqlite' ? 'item_categories' : 'master_data.item_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignUuid('asset_group_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'asset_groups' : 'master_data.asset_groups')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'item_categories' : 'master_data.item_categories');
    }
};
