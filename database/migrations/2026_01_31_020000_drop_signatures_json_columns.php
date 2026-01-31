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
        Schema::table('general_informations', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });

        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });

        Schema::table('project_informations', function (Blueprint $table) {
            $table->dropColumn('signatures');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_informations', function (Blueprint $table) {
            $table->json('signatures')->nullable();
        });

        Schema::table('proposals', function (Blueprint $table) {
            $table->json('signatures')->nullable();
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->json('signatures')->nullable();
        });

        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->json('signatures')->nullable();
        });

        Schema::table('project_informations', function (Blueprint $table) {
            $table->json('signatures')->nullable();
        });
    }
};
