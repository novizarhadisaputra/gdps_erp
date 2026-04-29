<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'tax_schemes' : 'master_data.tax_schemes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->after('id')->nullable();
            $table->string('name');
            $table->string('scheme_code'); // skema_1, skema_2a, skema_2b, skema_2c, skema_2d, skema_3, skema_4, skema_5
            $table->decimal('rate_percentage', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_tax_borne_by_company')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'tax_schemes' : 'master_data.tax_schemes');
    }
};
