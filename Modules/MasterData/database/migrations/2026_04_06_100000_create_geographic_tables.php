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

        // Add geographic fields to existing project areas table
        Schema::table($schema ? "$schema.project_areas" : 'project_areas', function (Blueprint $table) use ($schema) {
            $table->string('api_code')->nullable()->after('code');
            $table->foreignUuid('province_id')->nullable()->after('api_code')->constrained($schema ? "$schema.provinces" : 'provinces')->nullOnDelete();
            $table->foreignUuid('regency_id')->nullable()->after('province_id')->constrained($schema ? "$schema.regencies" : 'regencies')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = config('database.default') === 'sqlite' ? null : 'master_data';

        Schema::table($schema ? "$schema.project_areas" : 'project_areas', function (Blueprint $table) {
            $table->dropForeign(['regency_id']);
            $table->dropForeign(['province_id']);
            $table->dropColumn(['api_code', 'province_id', 'regency_id']);
        });

        Schema::dropIfExists($schema ? "$schema.villages" : 'villages');
        Schema::dropIfExists($schema ? "$schema.districts" : 'districts');
        Schema::dropIfExists($schema ? "$schema.regencies" : 'regencies');
        Schema::dropIfExists($schema ? "$schema.provinces" : 'provinces');
    }
};
