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
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_bpjs_jp_configs' : 'master_data.bpjs_jp_configs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('employee_type')->default('ppu'); // ppu, pbpu, jakon
            $table->decimal('employer_rate', 8, 4)->default(0);
            $table->decimal('employee_rate', 8, 4)->default(0);
            $table->decimal('cap_nominal', 15, 2)->nullable(); // limit income 10,547,400
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_bpjs_jp_configs' : 'master_data.bpjs_jp_configs');
    }
};
