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
        $schema = config('database.default') === 'sqlite' ? 'profitability_analysis_monthly_logs' : 'finance.profitability_analysis_monthly_logs';

        Schema::create($schema, function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('profitability_analysis_monthly_id')
                ->constrained(config('database.default') === 'sqlite' ? 'profitability_analysis_monthlies' : 'finance.profitability_analysis_monthlies')
                ->onDelete('cascade')
                ->index('pa_monthly_log_monthly_id_foreign');

            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->onDelete('set null');

            $table->string('field_name')->comment('forecast_revenue or actual_revenue');
            $table->decimal('old_value', 20, 2)->default(0);
            $table->decimal('new_value', 20, 2)->default(0);
            $table->decimal('delta', 20, 2)->default(0);
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'profitability_analysis_monthly_logs' : 'finance.profitability_analysis_monthly_logs');
    }
};
