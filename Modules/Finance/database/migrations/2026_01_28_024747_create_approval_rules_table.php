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
        Schema::create(config('database.default') === 'sqlite' ? 'master_data_approval_rules' : 'master_data.approval_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resource_type');         // e.g., 'Modules\Finance\Models\ProfitabilityAnalysis'
            $table->string('criteria_field')->nullable();        // e.g., 'revenue_per_month'
            $table->string('operator')->nullable();              // e.g., '>', '=', '<'
            $table->decimal('value', 20, 2)->nullable();
            $table->json('conditions')->nullable();
            $table->decimal('max_value', 20, 2)->nullable();
            $table->string('approver_type')->default('Role');
            $table->json('approver_role')->nullable();      // Array of Roles
            $table->json('approver_user_id')->nullable();   // Array of User IDs
            $table->json('approver_unit_id')->nullable();   // Array of Unit IDs
            $table->json('approver_position')->nullable();  // Array of Job Positions
            $table->string('signature_type')->default('Approver'); // e.g., 'Reviewer', 'Approver'
            $table->integer('order')->default(0);    // For sequence
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'master_data_approval_rules' : 'master_data.approval_rules');
    }
};
