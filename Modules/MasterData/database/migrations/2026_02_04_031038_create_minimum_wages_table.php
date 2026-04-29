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
        Schema::create(config('database.default') === 'sqlite' ? 'minimum_wages' : 'master_data.minimum_wages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('province')->nullable();
            $table->string('type')->nullable();
            $table->foreignUuid('project_area_id')->constrained(config('database.default') === 'sqlite' ? 'project_areas' : 'master_data.project_areas')->cascadeOnDelete();
            $table->integer('year');
            $table->decimal('amount', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'minimum_wages' : 'master_data.minimum_wages');
    }
};
