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
        Schema::create(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('legal_entity_type')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->foreignUuid('province_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'provinces' : 'master_data.provinces')->nullOnDelete();
            $table->foreignUuid('regency_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'regencies' : 'master_data.regencies')->nullOnDelete();
            $table->foreignUuid('district_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'districts' : 'master_data.districts')->nullOnDelete();
            $table->foreignUuid('village_id')->nullable()->constrained(config('database.default') === 'sqlite' ? 'villages' : 'master_data.villages')->nullOnDelete();
            $table->json('contacts')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'customers' : 'crm.customers');
    }
};
