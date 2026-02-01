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
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->foreignUuid('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignUuid('work_scheme_id')->nullable()->constrained('work_schemes')->nullOnDelete();
            $table->string('status')->default('lead'); // lead, approach, proposal, negotiation, won, closed_lost
            $table->decimal('estimated_amount', 15, 2)->nullable();
            $table->integer('probability')->nullable(); // 0-100
            $table->date('expected_closing_date')->nullable();
            $table->integer('position')->nullable(); // For Kanban sorting
            $table->text('description')->nullable();
            
            // Person In Charge (Internal Sales)
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
