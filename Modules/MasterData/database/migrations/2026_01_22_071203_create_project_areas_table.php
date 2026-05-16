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
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_project_areas' : 'master_data.project_areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->nullableUuidMorphs('parentable');
            $table->string('code')->unique()->nullable();
            $table->string('name');
            $table->string('api_code')->nullable();
            $table->foreignUuid('province_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_provinces' : 'master_data.provinces')->nullOnDelete();
            $table->foreignUuid('regency_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_regencies' : 'master_data.regencies')->nullOnDelete();
            $table->boolean('has_branches')->default(false);
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_project_areas' : 'master_data.project_areas');
    }
};
