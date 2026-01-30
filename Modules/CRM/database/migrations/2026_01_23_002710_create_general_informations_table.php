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
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->text('scope_of_work')->nullable();
            $table->string('location')->nullable();
            $table->date('estimated_start_date')->nullable();
            $table->date('estimated_end_date')->nullable();
            $table->text('manpower_qualifications')->nullable();
            $table->text('work_activities')->nullable();
            $table->text('service_level')->nullable();
            $table->text('billing_requirements')->nullable();
            $table->json('risk_management')->nullable();
            $table->json('feasibility_study')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->json('signatures')->nullable();
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
