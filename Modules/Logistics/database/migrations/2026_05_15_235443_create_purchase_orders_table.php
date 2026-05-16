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
        Schema::create(config('database.default') === 'sqlite' ? 'logistics_purchase_orders' : 'logistics.purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('po_number')->unique();
            $table->foreignUuid('purchase_request_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'logistics_purchase_requests' : 'logistics.purchase_requests');
            $table->foreignUuid('vendor_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_vendors' : 'master_data.vendors');
            $table->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'project_projects' : 'project.projects');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->foreignUuid('warehouse_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'logistics_warehouses' : 'logistics.warehouses');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->string('status')->default('draft'); // draft, sent, partially_received, completed, cancelled
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'logistics_purchase_orders' : 'logistics.purchase_orders');
    }
};
