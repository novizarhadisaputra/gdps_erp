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
            'work_schemes',
            'product_clusters',
            'project_areas',
            'taxes',
            'items',
            'employees',
            'item_categories',
            'units_of_measure',
            'payment_terms',
            'billing_options',
            'project_types',
            'revenue_segments',
        ];

        foreach ($tables as $tableNameKey) {
            $tableName = config('database.default') === 'sqlite' ? $tableNameKey : "master_data.{$tableNameKey}";

            if (Schema::hasColumn($tableName, 'unit_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableNameKey) {
                    // Try to drop the index if it exists.
                    // Laravel index naming convention: [table]_[column]_index
                    try {
                        $table->dropIndex("{$tableNameKey}_unit_id_index");
                    } catch (\Exception $e) {
                        // Ignore if index doesn't exist
                    }
                    $table->dropColumn('unit_id');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'work_schemes',
            'product_clusters',
            'project_areas',
            'taxes',
            'items',
            'employees',
            'item_categories',
            'units_of_measure',
            'payment_terms',
            'billing_options',
            'project_types',
            'revenue_segments',
        ];

        foreach ($tables as $table) {
            $tableName = config('database.default') === 'sqlite' ? $table : "master_data.{$table}";

            if (! Schema::hasColumn($tableName, 'unit_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('unit_id')->nullable()->index();
                });
            }
        }
    }
};
