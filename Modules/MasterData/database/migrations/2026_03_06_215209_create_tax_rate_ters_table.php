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
        Schema::create(config('database.default') === 'sqlite' ? 'tax_rate_ters' : 'master_data.tax_rate_ters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('category'); // A, B, C
            $table->decimal('min_gross', 15, 2);
            $table->decimal('max_gross', 15, 2)->nullable();
            $table->decimal('rate', 5, 2); // percentage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'tax_rate_ters' : 'master_data.tax_rate_ters');
    }
};
