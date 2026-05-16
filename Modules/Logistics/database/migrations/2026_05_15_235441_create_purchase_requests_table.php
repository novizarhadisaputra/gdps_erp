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
        Schema::create(config('database.default') === 'sqlite' ? 'logistics_purchase_requests' : 'logistics.purchase_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('pr_number')->unique();
            $table->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'project_projects' : 'project.projects');
            $table->foreignUuid('warehouse_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'logistics_warehouses' : 'logistics.warehouses');
            $table->foreignUuid('requester_id')->constrained(config('database.default') === 'sqlite' ? 'master_data_employees' : 'master_data.employees');
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, pending_approval, approved, rejected, closed
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'logistics_purchase_requests' : 'logistics.purchase_requests');
    }
};
