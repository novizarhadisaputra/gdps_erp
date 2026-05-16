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
        $table = config('database.default') === 'sqlite' ? 'master_data_app_settings' : 'master_data.app_settings';

        Schema::create($table, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('group')->index()->default('general');
            $table->string('key')->unique();
            $table->jsonb('payload');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = config('database.default') === 'sqlite' ? 'master_data_app_settings' : 'master_data.app_settings';
        Schema::dropIfExists($table);
    }
};
