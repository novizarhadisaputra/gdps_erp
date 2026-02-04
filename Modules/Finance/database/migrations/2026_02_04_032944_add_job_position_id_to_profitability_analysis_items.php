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
        Schema::table('profitability_analysis_items', function (Blueprint $table) {
            $table->foreignUuid('item_id')->nullable()->change();
            $table->foreignUuid('job_position_id')->nullable()->after('item_id')->constrained('job_positions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profitability_analysis_items', function (Blueprint $table) {
            //
        });
    }
};
