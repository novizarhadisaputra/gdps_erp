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
        $tableName = config('database.default') === 'sqlite' ? 'finance_account_mappings' : 'finance.account_mappings';
        $coaTable = config('database.default') === 'sqlite' ? 'finance_chart_of_accounts' : 'finance.chart_of_accounts';

        Schema::create($tableName, function (Blueprint $table) use ($coaTable) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('mappable');
            $table->string('type'); // accrual, revenue, receivable, expense, etc.
            $table->foreignUuid('revenue_type_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_revenue_types' : 'master_data.revenue_types')->nullOnDelete();
            $table->foreignUuid('revenue_segment_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_revenue_segments' : 'master_data.revenue_segments')->nullOnDelete();
            $table->foreignUuid('tax_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'master_data_taxes' : 'master_data.taxes')->nullOnDelete();
            $table->foreignUuid('chart_of_account_id')->constrained($coaTable)->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['mappable_id', 'mappable_type', 'type', 'revenue_type_id', 'revenue_segment_id', 'tax_id'], 'mappable_account_details_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'finance_account_mappings' : 'finance.account_mappings');
    }
};
