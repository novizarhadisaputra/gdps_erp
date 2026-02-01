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
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->constrained()->onDelete('cascade');
            $table->string('document_number')->nullable()->unique();
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected
            $table->text('scope_of_work')->nullable();
            $table->foreignUuid('project_area_id')->nullable()->constrained('project_areas');
            $table->string('location')->nullable(); // Keep for legacy or specific detail if needed, or deprecate
            $table->date('estimated_start_date')->nullable();
            $table->date('estimated_end_date')->nullable();
            $table->text('manpower_qualifications')->nullable();
            $table->text('work_activities')->nullable();
            $table->text('service_level')->nullable();
            $table->text('billing_requirements')->nullable();
            $table->json('risk_management')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->json('signatures')->nullable();
            $table->string('risk_register_number')->nullable(); // Risk Register Document Number
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();
            $table->timestamps();

            $table->index(['year', 'sequence_number']);
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
