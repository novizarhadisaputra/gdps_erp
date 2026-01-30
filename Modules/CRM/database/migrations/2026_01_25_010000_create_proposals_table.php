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
        Schema::create('proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignUuid('profitability_analysis_id')->nullable()->constrained('profitability_analyses')->onDelete('set null');
            $table->foreignUuid('work_scheme_id')->nullable()->constrained()->onDelete('set null');
            $table->string('proposal_number')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected, converted
            $table->date('submission_date')->nullable();
            $table->json('signatures')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
