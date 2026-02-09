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
        Schema::create('manpower_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_area_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('manpower_template_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manpower_template_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('job_position_id')->constrained('job_positions')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manpower_template_items');
        Schema::dropIfExists('manpower_templates');
    }
};
