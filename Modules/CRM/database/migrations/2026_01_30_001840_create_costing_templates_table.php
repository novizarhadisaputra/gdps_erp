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
        Schema::create(config('database.default') === 'sqlite' ? 'costing_templates' : 'crm.costing_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->nullable()->unique();
            $table->string('name');
            $table->foreignUuid('lead_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads')->cascadeOnDelete();
            $table->foreignUuid('pic_id')->nullable()->constrained('users');
            $table->decimal('total_amount', 15, 2)->default(0); // Total One-Time Cost/Investment
            $table->decimal('total_monthly_cost', 15, 2)->default(0); // Total Monthly Depr/Expense
            $table->text('description')->nullable();
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'costing_templates' : 'crm.costing_templates');
    }
};
