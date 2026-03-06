<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'fixed_allowances' : 'master_data.fixed_allowances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->boolean('is_bpjs_base')->default(true);
            $table->boolean('is_taxable')->default(true);
            $table->decimal('default_amount', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'fixed_allowances' : 'master_data.fixed_allowances');
    }
};
