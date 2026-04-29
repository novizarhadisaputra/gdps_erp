<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'non_fixed_allowances' : 'master_data.non_fixed_allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->after('id')->nullable();
            $table->string('name');
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_bpjs_base')->default(false);
            $table->string('calculation_basis')->default('flat'); // flat, per_day, per_hour
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'non_fixed_allowances' : 'master_data.non_fixed_allowances');
    }
};
