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
        Schema::create(config('database.default') === 'sqlite' ? 'cooperation_agreements' : 'crm.cooperation_agreements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique();
            $table->date('agreement_date');

            $table->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers');
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads');
            $table->foreignUuid('proposal_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'proposals' : 'crm.proposals');

            $table->decimal('amount', 20, 2)->default(0);
            $table->decimal('tax_percentage', 5, 2)->default(11.00);
            $table->string('status')->default('draft');
            $table->boolean('is_manual')->default(false);

            $table->jsonb('items')->nullable();
            $table->integer('sequence_number')->default(0);
            $table->integer('year')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['year', 'sequence_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'cooperation_agreements' : 'crm.cooperation_agreements');
    }
};
