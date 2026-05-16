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
        Schema::create(config('database.default') === 'sqlite' ? 'logistics_stock_balances' : 'logistics.stock_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warehouse_id')->constrained(config('database.default') === 'sqlite' ? 'logistics_warehouses' : 'logistics.warehouses');
            $table->foreignUuid('item_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_items' : 'master_data.items');
            $table->decimal('quantity', 15, 2)->default(0);
            $table->date('last_in_date')->nullable();
            $table->date('last_out_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'logistics_stock_balances' : 'logistics.stock_balances');
    }
};
