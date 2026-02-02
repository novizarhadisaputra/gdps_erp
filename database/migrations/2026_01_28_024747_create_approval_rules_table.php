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
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resource_type');         // e.g., 'Modules\Finance\Models\ProfitabilityAnalysis'
            $table->string('criteria_field')->nullable();        // e.g., 'revenue_per_month'
            $table->string('operator')->nullable();              // e.g., '>', '=', '<'
            $table->decimal('value', 15, 2)->nullable(); // e.g., 1000000000
            $table->string('approver_type')->default('Role'); // Role, User, Position
            $table->json('approver_role')->nullable();      // Array of Roles
            $table->json('approver_user_id')->nullable();   // Array of User IDs
            $table->json('approver_unit_id')->nullable();   // Array of Unit IDs
            $table->json('approver_position')->nullable();  // Array of Job Positions
            $table->string('signature_type')->default('Approver'); // e.g., 'Reviewer', 'Approver'
            $table->integer('order')->default(0);    // For sequence
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
    }
};
