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
        $isSqlite = config('database.default') === 'sqlite';
        $schema = 'master_data';

        Schema::create($isSqlite ? "{$schema}_provinces" : "$schema.provinces", function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($isSqlite ? "{$schema}_regencies" : "$schema.regencies", function (Blueprint $table) use ($schema, $isSqlite) {
            $table->uuid('id')->primary();
            $table->foreignUuid('province_id')->constrained($isSqlite ? "{$schema}_provinces" : "$schema.provinces")->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($isSqlite ? "{$schema}_districts" : "$schema.districts", function (Blueprint $table) use ($schema, $isSqlite) {
            $table->uuid('id')->primary();
            $table->foreignUuid('regency_id')->constrained($isSqlite ? "{$schema}_regencies" : "$schema.regencies")->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($isSqlite ? "{$schema}_villages" : "$schema.villages", function (Blueprint $table) use ($schema, $isSqlite) {
            $table->uuid('id')->primary();
            $table->foreignUuid('district_id')->constrained($isSqlite ? "{$schema}_districts" : "$schema.districts")->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $isSqlite = config('database.default') === 'sqlite';
        $schema = 'master_data';

        Schema::dropIfExists($isSqlite ? "{$schema}_villages" : "$schema.villages");
        Schema::dropIfExists($isSqlite ? "{$schema}_districts" : "$schema.districts");
        Schema::dropIfExists($isSqlite ? "{$schema}_regencies" : "$schema.regencies");
        Schema::dropIfExists($isSqlite ? "{$schema}_provinces" : "$schema.provinces");
    }
};
