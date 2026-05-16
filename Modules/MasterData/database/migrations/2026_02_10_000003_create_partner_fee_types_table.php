<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_partner_fee_types' : 'master_data.partner_fee_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->after('id')->nullable();
            $table->string('name');
            $table->string('calculation_basis')->default('flat'); // flat, per_output, per_hour, per_day, percentage
            $table->boolean('is_taxable')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_partner_fee_types' : 'master_data.partner_fee_types');
    }
};
