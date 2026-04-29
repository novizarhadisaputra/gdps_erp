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
        Schema::create(config('database.default') === 'sqlite' ? 'work_patterns' : 'master_data.work_patterns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique()->after('id')->nullable();
            $table->string('name');
            $table->integer('days_per_week')->default(5);
            $table->decimal('hours_per_day', 4, 2)->default(8);
            $table->boolean('is_shift')->default(false);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_patterns');
    }
};
