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
        Schema::create('work_completion_report_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('work_completion_report_id')->constrained('work_completion_reports')->onDelete('cascade');
            $table->string('revision_number')->nullable();
            $table->json('snapshot');
            $table->text('reason')->nullable();
            $table->foreignUuid('user_id')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_completion_report_revisions');
    }
};
