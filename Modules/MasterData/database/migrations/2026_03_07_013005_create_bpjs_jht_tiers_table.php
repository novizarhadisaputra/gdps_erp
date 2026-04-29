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
        Schema::create(config('database.default') === 'sqlite' ? 'bpjs_jht_tiers' : 'master_data.bpjs_jht_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('bpjs_jht_config_id')->constrained(config('database.default') === 'sqlite' ? 'bpjs_jht_configs' : 'master_data.bpjs_jht_configs')->cascadeOnDelete();
            $table->decimal('min_value', 20, 2)->default(0);
            $table->decimal('max_value', 20, 2)->nullable();
            $table->decimal('employer_nominal', 15, 2)->default(0);
            $table->decimal('employee_nominal', 15, 2)->default(0);
            $table->decimal('employer_rate', 8, 4)->default(0);
            $table->decimal('employee_rate', 8, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'bpjs_jht_tiers' : 'master_data.bpjs_jht_tiers');
    }
};
