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
        Schema::create('profitability_thresholds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g. "Standard Project"
            $table->decimal('min_gpm', 5, 2); // Gross Profit Margin
            $table->decimal('min_npm', 5, 2); // Net Profit Margin
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profitability_thresholds');
    }
};
