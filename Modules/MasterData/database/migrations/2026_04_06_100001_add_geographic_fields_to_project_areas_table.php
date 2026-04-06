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
    }
};
