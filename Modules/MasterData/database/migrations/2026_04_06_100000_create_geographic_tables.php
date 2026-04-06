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
        $schema = config('database.default') === 'sqlite' ? null : 'master_data';

        Schema::create($schema ? "$schema.provinces" : 'provinces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($schema ? "$schema.regencies" : 'regencies', function (Blueprint $table) use ($schema) {
            $table->uuid('id')->primary();
            $table->foreignUuid('province_id')->constrained($schema ? "$schema.provinces" : 'provinces')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($schema ? "$schema.districts" : 'districts', function (Blueprint $table) use ($schema) {
            $table->uuid('id')->primary();
            $table->foreignUuid('regency_id')->constrained($schema ? "$schema.regencies" : 'regencies')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create($schema ? "$schema.villages" : 'villages', function (Blueprint $table) use ($schema) {
            $table->uuid('id')->primary();
            $table->foreignUuid('district_id')->constrained($schema ? "$schema.districts" : 'districts')->onDelete('cascade');
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
        $schema = config('database.default') === 'sqlite' ? null : 'master_data';

        Schema::dropIfExists($schema ? "$schema.villages" : 'villages');
        Schema::dropIfExists($schema ? "$schema.districts" : 'districts');
        Schema::dropIfExists($schema ? "$schema.regencies" : 'regencies');
        Schema::dropIfExists($schema ? "$schema.provinces" : 'provinces');
    }
};
