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
        $table_name = config('database.default') === 'sqlite' ? 'project_tasks' : 'project.project_tasks';

        Schema::create($table_name, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'projects' : 'project.projects')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable(); // Foreign key added below
            $table->foreignUuid('assigned_member_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'project_members' : 'project.project_members')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status');
            $table->string('priority');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('progress_percentage')->default(0);
            $table->timestamps();
        });

        Schema::table($table_name, function (Blueprint $table) use ($table_name) {
            $table->foreign('parent_id')->references('id')->on($table_name)->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'project_tasks' : 'project.project_tasks');
    }
};
