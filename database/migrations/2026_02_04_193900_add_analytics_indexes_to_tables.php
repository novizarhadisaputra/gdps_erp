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
        // Add indexes to leads table for analytics queries
        Schema::table('leads', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'idx_leads_status_created');
            $table->index(['user_id', 'status'], 'idx_leads_user_status');
            $table->index('expected_closing_date', 'idx_leads_expected_closing');
        });

        // Add indexes to projects table for analytics queries
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['status', 'start_date'], 'idx_projects_status_start');
            $table->index(['status', 'end_date'], 'idx_projects_status_end');
            $table->index('created_at', 'idx_projects_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex('idx_leads_status_created');
            $table->dropIndex('idx_leads_user_status');
            $table->dropIndex('idx_leads_expected_closing');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_status_start');
            $table->dropIndex('idx_projects_status_end');
            $table->dropIndex('idx_projects_created');
        });
    }
};
