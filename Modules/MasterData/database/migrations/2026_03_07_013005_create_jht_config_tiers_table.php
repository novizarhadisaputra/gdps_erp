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
        Schema::create(config('database.default') === 'sqlite' ? 'jht_config_tiers' : 'master_data.jht_config_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('jht_config_id')->constrained(config('database.default') === 'sqlite' ? 'jht_configs' : 'master_data.jht_configs')->cascadeOnDelete();
            $table->decimal('min_income', 15, 2)->default(0);
            $table->decimal('max_income', 15, 2)->nullable();
            $table->decimal('employer_nominal', 15, 2)->default(0);
            $table->decimal('employee_nominal', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'jht_config_tiers' : 'master_data.jht_config_tiers');
    }
};
