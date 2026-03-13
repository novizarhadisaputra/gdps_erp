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
        Schema::create('sales_order_amendments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->onDelete('cascade');
            $table->string('amendment_number')->unique();
            $table->date('amendment_date');
            $table->text('reason')->nullable();
            
            // Snapshots for comparison
            $table->json('before_snapshot')->nullable();
            $table->json('after_snapshot')->nullable();
            
            $table->string('status'); // draft, approved, cancelled
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_order_amendments');
    }
};
