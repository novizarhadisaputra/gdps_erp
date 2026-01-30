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
        Schema::create('general_information_pics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('general_information_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('contact_role_id')->constrained('contact_roles');
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_information_pics');
    }
};
