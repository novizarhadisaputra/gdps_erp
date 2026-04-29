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
        Schema::create(config('database.default') === 'sqlite' ? 'minutes_of_agreements' : 'crm.minutes_of_agreements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('lead_id')->constrained(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads')->onDelete('cascade');
            $table->foreignUuid('proposal_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'proposals' : 'crm.proposals')->onDelete('set null');
            $table->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->onDelete('cascade');
            $table->string('number')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, approved, cancelled
            $table->boolean('is_manual')->default(false);
            $table->date('negotiation_date')->nullable();
            $table->jsonb('notes')->nullable();
            $table->jsonb('scope_of_work')->nullable();
            $table->jsonb('timeline')->nullable();
            $table->jsonb('terms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'minutes_of_agreements' : 'crm.minutes_of_agreements');
    }
};
