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
        Schema::create(config('database.default') === 'sqlite' ? 'job_position_remunerations' : 'master_data.job_position_remunerations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_position_id')->constrained(config('database.default') === 'sqlite' ? 'job_positions' : 'master_data.job_positions')->cascadeOnDelete();
            $table->foreignUuid('remuneration_component_id')->constrained(config('database.default') === 'sqlite' ? 'remuneration_components' : 'master_data.remuneration_components')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'job_position_remunerations' : 'master_data.job_position_remunerations');
    }
};
