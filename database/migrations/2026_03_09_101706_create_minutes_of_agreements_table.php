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
            $table->string('moa_number')->unique();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, approved, cancelled
            $table->date('negotiation_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('scope_of_work')->nullable();
            $table->text('timeline')->nullable();
            $table->text('terms')->nullable();
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
