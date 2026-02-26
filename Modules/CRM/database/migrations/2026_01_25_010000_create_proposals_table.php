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
        Schema::create('crm.proposals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->nullable()->constrained('crm.leads')->onDelete('cascade');
            $table->foreignUuid('customer_id')->nullable()->constrained('master_data.customers')->onDelete('set null');
            $table->foreignUuid('profitability_analysis_id')->nullable()->constrained('finance.profitability_analyses')->onDelete('set null');
            $table->foreignUuid('work_scheme_id')->nullable()->constrained()->onDelete('set null');
            $table->string('proposal_number')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected, converted
            $table->boolean('is_imported')->default(false);
            $table->uuid('import_source_id')->nullable();
            $table->date('submission_date')->nullable();
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
        Schema::dropIfExists('crm.proposals');
    }
};
