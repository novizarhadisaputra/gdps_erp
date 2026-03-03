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
        Schema::create(config('database.default') === 'sqlite' ? 'units_of_measure' : 'master_data.units_of_measure', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unit_id')->nullable()->index();
            $table->string('name')->unique();
            $table->string('code')->unique(); // e.g., pcs, ltr
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'units_of_measure' : 'master_data.units_of_measure');
    }
};
