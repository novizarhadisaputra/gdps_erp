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
        Schema::create('contracts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignUuid('proposal_id')->nullable()->constrained()->onDelete('set null');
            $table->string('contract_number')->unique();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, expired, terminated
            $table->string('reminder_status')->nullable(); // 6_month, 3_month, 1_month
            $table->json('signatures')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
