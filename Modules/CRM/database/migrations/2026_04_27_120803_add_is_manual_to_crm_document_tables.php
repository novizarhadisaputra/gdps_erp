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
        $tables = [
            'purchase_orders',
            'work_orders',
            'cooperation_agreements',
            'minutes_of_agreements',
        ];

        foreach ($tables as $tableName) {
            $prefixedTable = config('database.default') === 'sqlite' ? $tableName : "crm.{$tableName}";

            Schema::table($prefixedTable, function (Blueprint $table) {
                $table->boolean('is_manual')->default(false)->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'purchase_orders',
            'work_orders',
            'cooperation_agreements',
            'minutes_of_agreements',
        ];

        foreach ($tables as $tableName) {
            $prefixedTable = config('database.default') === 'sqlite' ? $tableName : "crm.{$tableName}";

            Schema::table($prefixedTable, function (Blueprint $table) {
                $table->dropColumn('is_manual');
            });
        }
    }
};
