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
        Schema::create(config('database.default') === 'sqlite' ? 'health_configs' : 'master_data.health_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('employee_type')->default('ppu'); // ppu, pbpu, pbi
            $table->decimal('employer_rate', 8, 4)->default(0);
            $table->decimal('employee_rate', 8, 4)->default(0);
            $table->string('floor_type')->default('none'); // umk, nominal
            $table->decimal('cap_nominal', 15, 2)->nullable(); // limit income 12,000,000
            $table->decimal('employer_nominal', 15, 2)->default(0); // for PBI or Class 1 fixed
            $table->decimal('employee_nominal', 15, 2)->default(0); // for PBPU fixed
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'health_configs' : 'master_data.health_configs');
    }
};
