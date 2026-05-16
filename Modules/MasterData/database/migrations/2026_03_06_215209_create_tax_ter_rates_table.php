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
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_tax_ter_rates' : 'master_data.tax_ter_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type')->default('monthly'); // monthly, daily
            $table->string('category')->nullable(); // A, B, C (for monthly)
            $table->decimal('min_gross', 15, 2);
            $table->decimal('max_gross', 15, 2)->nullable();
            $table->decimal('rate', 5, 2); // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_tax_ter_rates' : 'master_data.tax_ter_rates');
    }
};
