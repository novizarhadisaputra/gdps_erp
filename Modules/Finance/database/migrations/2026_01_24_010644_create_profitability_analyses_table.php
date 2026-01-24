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
        Schema::create('profitability_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('client_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('work_scheme_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_cluster_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('tax_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('project_area_id')->nullable()->constrained()->onDelete('set null');

            $table->decimal('revenue_per_month', 15, 2)->default(0);
            $table->decimal('direct_cost', 15, 2)->default(0);
            $table->decimal('management_fee', 15, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->default(0);

            $table->json('manpower_details')->nullable();
            $table->json('material_details')->nullable();

            $table->integer('project_number')->nullable();
            $table->string('status')->default('draft'); // draft, approved, rejected, converted

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profitability_analyses');
    }
};
