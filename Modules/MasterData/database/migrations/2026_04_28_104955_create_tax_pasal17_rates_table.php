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
        Schema::create(config('database.default') === 'sqlite' ? 'tax_pasal17_rates' : 'master_data.tax_pasal17_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('min_amount', 20, 2)->default(0);
            $table->decimal('max_amount', 20, 2)->nullable(); // Null means no upper limit
            $table->decimal('rate', 5, 2); // e.g., 5.00 for 5%
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_pasal17_rates');
    }
};
