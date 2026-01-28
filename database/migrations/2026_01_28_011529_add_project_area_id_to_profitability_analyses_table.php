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
        if (! Schema::hasColumn('profitability_analyses', 'project_area_id')) {
            Schema::table('profitability_analyses', function (Blueprint $table) {
                $table->foreignId('project_area_id')->nullable()->constrained('project_areas')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profitability_analyses', function (Blueprint $table) {
            //
        });
    }
};
