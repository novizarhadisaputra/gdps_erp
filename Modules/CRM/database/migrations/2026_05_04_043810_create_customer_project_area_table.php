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
        Schema::create('customer_project_area', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('project_area_id')->constrained(config('database.default') === 'sqlite' ? 'project_areas' : 'master_data.project_areas')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['customer_id', 'project_area_id'], 'cust_proj_area_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_project_area');
    }
};
