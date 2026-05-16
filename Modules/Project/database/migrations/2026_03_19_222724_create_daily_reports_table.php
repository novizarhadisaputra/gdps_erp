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
        Schema::create(config('database.default') === 'sqlite' ? 'project_daily_reports' : 'project.daily_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'project_projects' : 'project.projects')->cascadeOnDelete();
            $table->foreignUuid('task_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'project_project_tasks' : 'project.project_tasks')->nullOnDelete();
            $table->foreignUuid('reported_by_id')->constrained(config('database.default') === 'sqlite' ? 'project_project_members' : 'project.project_members')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('draft');
            $table->text('content');
            $table->string('weather')->nullable();
            $table->text('site_condition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};
