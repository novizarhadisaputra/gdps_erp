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
        $tableName = config('database.default') === 'sqlite' ? 'chart_of_accounts' : 'finance.chart_of_accounts';

        Schema::create($tableName, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('account_type'); // Asset, Liability, Equity, Revenue, Expense, etc.
            $table->uuid('parent_id')->nullable();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // Add self-referencing foreign key in a separate step to avoid PostgreSQL unique constraint issue during creation
        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->foreign('parent_id')
                ->references('id')
                ->on($tableName)
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('database.default') === 'sqlite' ? 'chart_of_accounts' : 'finance.chart_of_accounts';
        Schema::dropIfExists($tableName);
    }
};
