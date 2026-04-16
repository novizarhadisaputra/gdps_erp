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
        Schema::create('profitability_analysis_updates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Link ke PA utama
            $table->foreignUuid('profitability_analysis_id')->constrained('profitability_analyses')->cascadeOnDelete();
            
            // Link ke Monthly Actual (Sesuai Struktur Nested Opsi 1)
            $table->foreignUuid('profitability_analysis_actual_id')->nullable()->constrained('profitability_analysis_actuals')->cascadeOnDelete();

            $table->decimal('projected_revenue', 20, 2);
            $table->text('notes')->nullable();
            
            $table->integer('week_number');
            $table->integer('month');
            $table->integer('year');
            
            $table->foreignUuid('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profitability_analysis_updates');
    }
};
