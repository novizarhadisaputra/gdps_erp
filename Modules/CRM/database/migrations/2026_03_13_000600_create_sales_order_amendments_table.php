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
        Schema::create(config('database.default') === 'sqlite' ? 'sales_order_amendments' : 'crm.sales_order_amendments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_order_id')->constrained(config('database.default') === 'sqlite' ? 'sales_orders' : 'crm.sales_orders')->onDelete('cascade');
            $table->string('number')->unique();
            $table->date('amendment_date');
            $table->text('reason')->nullable();

            // Snapshots for comparison
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            $table->jsonb('content_config')->nullable();

            $table->string('status'); // draft, approved, cancelled
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['year', 'sequence_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'sales_order_amendments' : 'crm.sales_order_amendments');
    }
};
