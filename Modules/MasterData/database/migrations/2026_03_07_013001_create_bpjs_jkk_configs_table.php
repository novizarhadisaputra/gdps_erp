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
        Schema::create(config('database.default') === 'sqlite' ? 'bpjs_jkk_configs' : 'master_data.bpjs_jkk_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('employee_type')->default('ppu'); // ppu, pbpu, jakon
            $table->string('calculation_method')->default('salary_based'); // salary_based, contract_value_based (for Jakon)
            $table->string('risk_level')->nullable(); // very_low, low, medium, high, very_high
            $table->boolean('has_tier')->default(false); // Does it use tiers?
            $table->decimal('employer_rate', 8, 4)->default(0); // For PPU
            $table->decimal('employee_rate', 8, 4)->default(0);
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'bpjs_jkk_configs' : 'master_data.bpjs_jkk_configs');
    }
};
