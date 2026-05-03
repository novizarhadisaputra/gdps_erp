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
        Schema::create(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports', function (Blueprint $blueprint) {
            $blueprint->uuid('id')->primary();
            $blueprint->foreignUuid('project_id')->constrained(config('database.default') === 'sqlite' ? 'projects' : 'project.projects')->onDelete('cascade');
            $blueprint->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->onDelete('cascade');
            $blueprint->foreignUuid('tax_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes')->onDelete('set null');
            $blueprint->uuidMorphs('sourceable');

            $blueprint->string('number')->unique();
            $blueprint->integer('sequence_number')->nullable();
            $blueprint->integer('revision_number')->default(0);
            $blueprint->string('previous_code')->nullable();
            $blueprint->integer('year')->nullable();
            $blueprint->date('document_date');

            $blueprint->date('service_period_start');
            $blueprint->date('service_period_end');

            $blueprint->decimal('work_progress_percentage', 5, 2)->default(100.00);
            $blueprint->jsonb('description')->nullable();
            $blueprint->jsonb('items')->nullable();
            $blueprint->decimal('tax_percentage', 5, 2)->default(12.00);
            $blueprint->string('tax_basis')->default('total');
            $blueprint->decimal('tax_base_amount', 15, 2)->nullable();
            $blueprint->decimal('tax_amount', 15, 2)->default(0);
            $blueprint->jsonb('tax_wording')->nullable();
            $blueprint->decimal('total_amount', 15, 2)->default(0);

            $blueprint->string('status')->default('draft');
            $blueprint->jsonb('content_config')->nullable();

            $blueprint->timestamps();
            $blueprint->softDeletes();
        });

        Schema::create(config('database.default') === 'sqlite' ? 'work_completion_report_revisions' : 'project.work_completion_report_revisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('work_completion_report_id')->constrained(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports')->onDelete('cascade');
            $table->string('number')->nullable();
            $table->integer('sequence_number')->default(0);
            $table->json('snapshot')->comment('Full data snapshot of the report at the time of revision for auditing and restoration.');
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
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'work_completion_report_revisions' : 'project.work_completion_report_revisions');
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'work_completion_reports' : 'project.work_completion_reports');
    }
};
