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
            $table->uuid('id')->primary();
            $table->string('document_number')->nullable()->unique();
            $table->foreignUuid('lead_id')->nullable()->constrained('leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignUuid('general_information_id')->nullable()->constrained('general_informations')->onDelete('set null');
            $table->uuid('proposal_id')->nullable();
            $table->foreignUuid('work_scheme_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('product_cluster_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('tax_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignUuid('project_area_id')->nullable()->constrained()->onDelete('set null');

            $table->string('asset_ownership')->default('gdps-owned');
            $table->decimal('management_expense_rate', 5, 2)->default(3.00);
            $table->decimal('interest_rate', 5, 2)->default(1.50);
            $table->decimal('tax_rate', 5, 2)->default(22.00);

            $table->decimal('revenue_per_month', 15, 2)->default(0);
            $table->decimal('direct_cost', 15, 2)->default(0);
            $table->decimal('management_fee', 15, 2)->default(0);
            $table->decimal('margin_percentage', 5, 2)->default(0);
            $table->decimal('ebitda', 15, 2)->default(0);
            $table->decimal('ebit', 15, 2)->default(0);
            $table->decimal('ebt', 15, 2)->default(0);
            $table->decimal('net_profit', 15, 2)->default(0);
            $table->decimal('net_profit_margin', 5, 2)->default(0);

            $table->json('analysis_details')->nullable();

            $table->integer('project_number')->nullable();
            $table->string('status')->default('draft'); // draft, approved, rejected, converted
            $table->json('signatures')->nullable();
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

        Schema::dropIfExists('profitability_analyses');
    }
};
