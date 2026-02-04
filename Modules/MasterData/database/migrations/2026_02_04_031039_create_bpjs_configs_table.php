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
        Schema::create('bpjs_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('type'); // employment, health
            $table->string('category'); // JKK, JKM, JHT, JP, Health
            $table->decimal('employer_rate', 8, 6); // as percentage e.g. 0.037000
            $table->decimal('employee_rate', 8, 6);
            $table->string('floor_type')->default('none'); // none, umk, nominal
            $table->decimal('floor_nominal', 15, 2)->default(0);
            $table->string('cap_type')->default('none'); // none, nominal
            $table->decimal('cap_nominal', 15, 2)->default(0);
            $table->string('risk_level')->nullable(); // very_low, low, medium, high, very_high
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bpjs_configs');
    }
};
