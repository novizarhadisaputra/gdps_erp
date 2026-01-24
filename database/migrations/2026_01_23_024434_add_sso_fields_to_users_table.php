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
        Schema::table('users', function (Blueprint $table): void {
            $table->string('employee_code')->nullable()->unique()->after('email');
            $table->string('company')->nullable()->after('employee_code');
            $table->string('unit_id')->nullable()->after('company');
            $table->string('unit')->nullable()->after('unit_id');
            $table->string('position_id')->nullable()->after('unit');
            $table->string('position')->nullable()->after('position_id');
            $table->string('mobile_phone')->nullable()->after('position');
            $table->text('access_token')->nullable()->after('password');
            $table->text('refresh_token')->nullable()->after('access_token');
            $table->timestamp('token_expires_at')->nullable()->after('refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'employee_code',
                'company',
                'unit_id',
                'unit',
                'position_id',
                'position',
                'mobile_phone',
                'access_token',
                'refresh_token',
                'token_expires_at',
            ]);
        });
    }
};
