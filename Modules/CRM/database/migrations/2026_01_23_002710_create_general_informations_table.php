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
        Schema::create('general_informations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->string('pic_customer_name')->nullable();
            $table->string('pic_customer_phone')->nullable();
            $table->string('pic_finance_name')->nullable();
            $table->string('pic_finance_phone')->nullable();
            $table->string('pic_finance_email')->nullable();
            $table->json('risk_management')->nullable();
            $table->json('feasibility_study')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->string('rr_submission_id')->nullable(); // ID from Risk Register system
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_informations');
    }
};
