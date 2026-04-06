<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = config('database.default') === 'sqlite' ? null : 'master_data';

        Schema::create($schema ? "{$schema}.job_position_fixed_allowances" : 'job_position_fixed_allowances', function (Blueprint $table) use ($schema) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_position_id')->constrained($schema ? "{$schema}.job_positions" : 'job_positions')->cascadeOnDelete();
            $table->foreignUuid('fixed_allowance_id')->constrained($schema ? "{$schema}.fixed_allowances" : 'fixed_allowances')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });

        Schema::create($schema ? "{$schema}.job_position_non_fixed_allowances" : 'job_position_non_fixed_allowances', function (Blueprint $table) use ($schema) {
            $table->uuid('id')->primary();
            $table->foreignUuid('job_position_id')->constrained($schema ? "{$schema}.job_positions" : 'job_positions')->cascadeOnDelete();
            $table->foreignUuid('non_fixed_allowance_id')->constrained($schema ? "{$schema}.non_fixed_allowances" : 'non_fixed_allowances')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $schema = config('database.default') === 'sqlite' ? null : 'master_data';
        Schema::dropIfExists($schema ? "{$schema}.job_position_non_fixed_allowances" : 'job_position_non_fixed_allowances');
        Schema::dropIfExists($schema ? "{$schema}.job_position_fixed_allowances" : 'job_position_fixed_allowances');
    }
};
