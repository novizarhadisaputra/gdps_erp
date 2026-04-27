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
        Schema::create('accrue_revenues', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->foreignUuid('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount_revenue', 15, 2);
            $table->decimal('amount_cost', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrue_revenues');
    }
};
