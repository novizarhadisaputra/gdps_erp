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
        Schema::create(config('database.default') === 'sqlite' ? 'logistics_purchase_request_items' : 'logistics.purchase_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_request_id')->constrained(config('database.default') === 'sqlite' ? 'logistics_purchase_requests' : 'logistics.purchase_requests')->onDelete('cascade');
            $table->foreignUuid('item_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_items' : 'master_data.items');
            $table->decimal('quantity', 15, 2);
            $table->foreignUuid('unit_of_measure_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_units_of_measure' : 'master_data.units_of_measure');
            $table->decimal('estimated_price', 15, 2)->default(0);
            $table->decimal('total_estimated_price', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
