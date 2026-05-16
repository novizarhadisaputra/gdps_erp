<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_tax_ptkp_configs' : 'master_data.tax_ptkp_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); // TK/0, K/1 etc
            $table->string('name');
            $table->string('tax_category'); // A, B, C
            $table->decimal('annual_amount', 15, 2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_tax_ptkp_configs' : 'master_data.tax_ptkp_configs');
    }
};
