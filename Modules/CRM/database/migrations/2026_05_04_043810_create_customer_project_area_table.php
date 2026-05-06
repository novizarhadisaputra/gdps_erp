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
        $tableName = config('database.default') === 'sqlite' ? 'customer_project_area' : 'crm.customer_project_area';
        Schema::create($tableName, function (Blueprint $table) {
            $table->foreignUuid('customer_id')->constrained(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers')->cascadeOnDelete();
            $table->foreignUuid('project_area_id')->constrained(config('database.default') === 'sqlite' ? 'project_areas' : 'master_data.project_areas')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['customer_id', 'project_area_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = config('database.default') === 'sqlite' ? 'customer_project_area' : 'crm.customer_project_area';
        Schema::dropIfExists($tableName);
    }
};
