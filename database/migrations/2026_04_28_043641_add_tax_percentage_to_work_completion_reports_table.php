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
            $table->decimal('tax_percentage', 5, 2)->default(12.00)->after('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports', function (Blueprint $table) {
            $table->dropColumn('tax_percentage');
        });
    }
};
