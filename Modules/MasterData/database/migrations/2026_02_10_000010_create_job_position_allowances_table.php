<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sqlite = config('database.default') === 'sqlite';
        $tableFixed = $sqlite ? 'master_data_job_position_fixed_allowances' : 'master_data.job_position_fixed_allowances';
        $tableNonFixed = $sqlite ? 'master_data_job_position_non_fixed_allowances' : 'master_data.job_position_non_fixed_allowances';
        $tablePos = $sqlite ? 'master_data_job_positions' : 'master_data.job_positions';
        $tableFixedRef = $sqlite ? 'master_data_fixed_allowances' : 'master_data.fixed_allowances';
        $tableNonFixedRef = $sqlite ? 'master_data_non_fixed_allowances' : 'master_data.non_fixed_allowances';

        Schema::create($tableFixed, function (Blueprint $table) use ($tablePos, $tableFixedRef) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_position_id')->constrained($tablePos)->cascadeOnDelete();
            $table->foreignUuid('fixed_allowance_id')->constrained($tableFixedRef)->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create($tableNonFixed, function (Blueprint $table) use ($tablePos, $tableNonFixedRef) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_position_id')->constrained($tablePos)->cascadeOnDelete();
            $table->foreignUuid('non_fixed_allowance_id')->constrained($tableNonFixedRef)->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $sqlite = config('database.default') === 'sqlite';
        Schema::dropIfExists($sqlite ? 'master_data_job_position_non_fixed_allowances' : 'master_data.job_position_non_fixed_allowances');
        Schema::dropIfExists($sqlite ? 'master_data_job_position_fixed_allowances' : 'master_data.job_position_fixed_allowances');
    }
};
