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
        Schema::table(config('database.default') === 'sqlite' ? 'sales_plans' : 'crm.sales_plans', function (Blueprint $table) {
            $table->dropColumn('priority_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('database.default') === 'sqlite' ? 'sales_plans' : 'crm.sales_plans', function (Blueprint $table) {
            $table->integer('priority_level')->nullable();
        });
    }
};
