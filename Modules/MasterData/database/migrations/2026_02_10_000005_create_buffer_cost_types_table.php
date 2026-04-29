<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('database.default') === 'sqlite' ? 'buffer_cost_types' : 'master_data.buffer_cost_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->after('id')->nullable();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'buffer_cost_types' : 'master_data.buffer_cost_types');
    }
};
