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
        Schema::table(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports', function (Blueprint $table) {
            $table->foreignUuid('tax_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes')->onDelete('set null')->after('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn('tax_id');
        });
    }
};
