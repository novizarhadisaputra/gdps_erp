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
        Schema::create(config('database.default') === 'sqlite' ? 'work_schemes' : 'master_data.work_schemes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('unit_id')->nullable()->index();
            $table->string('code')->unique();
            $table->string('name');
            $table->integer('working_days')->default(21);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'work_schemes' : 'master_data.work_schemes');
    }
};
