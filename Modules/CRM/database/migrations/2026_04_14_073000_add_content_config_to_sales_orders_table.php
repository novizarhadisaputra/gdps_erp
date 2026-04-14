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
        Schema::table(config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders', function (Blueprint $table) {
            $table->jsonb('content_config')->nullable()->after('reporting_schedule');
        });

        Schema::table(config('database.default') === 'sqlite' ? 'sales_order_amendments' : 'crm.sales_order_amendments', function (Blueprint $table) {
            $table->jsonb('content_config')->nullable()->after('after_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders', function (Blueprint $table) {
            $table->dropColumn('content_config');
        });

        Schema::table(config('database.default') === 'sqlite' ? 'sales_order_amendments' : 'crm.sales_order_amendments', function (Blueprint $table) {
            $table->dropColumn('content_config');
        });
    }
};
