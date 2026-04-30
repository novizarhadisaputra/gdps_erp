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
        $tableName = config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders';
        $taxTableName = config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes';

        Schema::table($tableName, function (Blueprint $table) use ($taxTableName) {
            $table->foreignUuid('tax_id')->nullable()->after('tax_percentage')->constrained($taxTableName);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders';

        Schema::table($tableName, function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn(['tax_id']);
        });
    }
};
