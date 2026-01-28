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
        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->json('signatures')->nullable()->after('status');
        });

        Schema::table('project_informations', function (Blueprint $table) {
            $table->json('signatures')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });

        Schema::table('project_informations', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });
    }
};
