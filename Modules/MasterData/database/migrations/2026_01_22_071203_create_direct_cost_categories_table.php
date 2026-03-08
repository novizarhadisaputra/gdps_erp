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
        $schema = config('database.default') === 'sqlite' ? '' : 'master_data.';

        Schema::create($schema.'direct_cost_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('type')->default('direct'); // direct, indirect
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table($schema.'direct_cost_categories', function (Blueprint $table) use ($schema) {
            $table->foreign('parent_id')
                ->references('id')
                ->on($schema.'direct_cost_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = config('database.default') === 'sqlite' ? '' : 'master_data.';

        Schema::dropIfExists($schema.'direct_cost_categories');
    }
};
