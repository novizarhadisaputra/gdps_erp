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
        $schema = config('database.default') === 'sqlite' ? 'profitability_analysis_monthlies' : 'finance.profitability_analysis_monthlies';
 
        Schema::table($schema, function (Blueprint $table) {
            $table->decimal('actual_net_profit', 20, 2)->default(0)->after('actual_cost');
            $table->decimal('actual_margin_percentage', 10, 2)->default(0)->after('actual_net_profit');
            $table->decimal('variance_revenue', 20, 2)->default(0)->after('actual_margin_percentage');
            $table->decimal('variance_profit', 20, 2)->default(0)->after('variance_revenue');
        });
    }
 
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = config('database.default') === 'sqlite' ? 'profitability_analysis_monthlies' : 'finance.profitability_analysis_monthlies';
 
        Schema::table($schema, function (Blueprint $table) {
            $table->dropColumn([
                'actual_net_profit',
                'actual_margin_percentage',
                'variance_revenue',
                'variance_profit',
            ]);
        });
    }
};
