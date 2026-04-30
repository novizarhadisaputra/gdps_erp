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
        Schema::create(config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->default('sales'); // sales, purchase, internal
            $table->string('calculation_type')->default('exclusive'); // exclusive, inclusive, formula
            $table->decimal('rate', 5, 2)->default(0);
            $table->integer('base_rate_numerator')->default(1);
            $table->integer('base_rate_denominator')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes');
    }
};
