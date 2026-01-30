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
            $table->string('document_number')->nullable()->after('id');
        });

        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->string('document_number')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_informations', function (Blueprint $table) {
            $table->dropColumn('document_number');
        });

        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->dropColumn('document_number');
        });
    }
};
