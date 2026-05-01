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
        $tableName = config('database.default') === 'sqlite' ? 'account_mappings' : 'finance.account_mappings';
        $coaTable = config('database.default') === 'sqlite' ? 'chart_of_accounts' : 'finance.chart_of_accounts';

        Schema::create($tableName, function (Blueprint $table) use ($coaTable) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('mappable');
            $table->string('type'); // accrual, revenue, receivable, expense, etc.
            $table->foreignUuid('chart_of_account_id')->constrained($coaTable)->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['mappable_id', 'mappable_type', 'type'], 'mappable_account_type_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'account_mappings' : 'finance.account_mappings');
    }
};
