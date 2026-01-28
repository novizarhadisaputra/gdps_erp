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
        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->string('asset_ownership')->default('gdps-owned')->after('status');
            $table->decimal('management_expense_rate', 5, 2)->default(3.00)->after('asset_ownership');
            $table->decimal('interest_rate', 5, 2)->default(1.50)->after('management_expense_rate');
            $table->decimal('tax_rate', 5, 2)->default(22.00)->after('interest_rate');
            $table->decimal('ebitda', 15, 2)->default(0)->after('margin_percentage');
            $table->decimal('ebit', 15, 2)->default(0)->after('ebitda');
            $table->decimal('ebt', 15, 2)->default(0)->after('ebit');
            $table->decimal('net_profit', 15, 2)->default(0)->after('ebt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profitability_analyses', function (Blueprint $table) {
            $table->dropColumn([
                'asset_ownership',
                'management_expense_rate',
                'interest_rate',
                'tax_rate',
                'ebitda',
                'ebit',
                'ebt',
                'net_profit',
            ]);
        });
    }
};
