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
        $tableName = config('database.default') === 'sqlite' ? 'finance_journal_items' : 'finance.journal_items';
        $entryTable = config('database.default') === 'sqlite' ? 'finance_journal_entries' : 'finance.journal_entries';
        $coaTable = config('database.default') === 'sqlite' ? 'finance_chart_of_accounts' : 'finance.chart_of_accounts';

        Schema::create($tableName, function (Blueprint $table) use ($entryTable, $coaTable) {
            $table->uuid('id')->primary();
            $table->foreignUuid('journal_entry_id')->constrained($entryTable)->cascadeOnDelete();
            $table->foreignUuid('chart_of_account_id')->constrained($coaTable)->cascadeOnDelete();
            $table->decimal('debit', 20, 2)->default(0);
            $table->decimal('credit', 20, 2)->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'finance_journal_items' : 'finance.journal_items');
    }
};
