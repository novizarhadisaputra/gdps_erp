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
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('set null');
            $table->string('contract_number')->unique();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, expired, terminated
            $table->string('reminder_status')->nullable(); // 6_month, 3_month, 1_month
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
