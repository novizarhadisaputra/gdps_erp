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
        Schema::create('accrue_invoice_mappings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('accrue_revenue_item_id')->constrained('accrue_revenue_items')->onDelete('cascade');
            $table->foreignUuid('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->decimal('allocated_amount', 20, 2)->default(0);
            $table->decimal('reverse_amount', 20, 2)->default(0);
            $table->foreignUuid('reverse_journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            $table->string('status')->default('active');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accrue_invoice_mappings');
    }
};
