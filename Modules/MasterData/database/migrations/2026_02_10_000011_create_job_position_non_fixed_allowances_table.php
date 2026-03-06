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
        Schema::create(config('database.default') === 'sqlite' ? 'job_position_non_fixed_allowances' : 'master_data.job_position_non_fixed_allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_position_id')->constrained(config('database.default') === 'sqlite' ? 'job_positions' : 'master_data.job_positions')->cascadeOnDelete();
            $table->foreignUuid('non_fixed_allowance_id')->constrained(config('database.default') === 'sqlite' ? 'non_fixed_allowances' : 'master_data.non_fixed_allowances')->cascadeOnDelete();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'job_position_non_fixed_allowances' : 'master_data.job_position_non_fixed_allowances');
    }
};
