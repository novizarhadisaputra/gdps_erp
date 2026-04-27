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
        $table = config('database.default') === 'sqlite' ? 'bank_accounts' : 'master_data.bank_accounts';

        Schema::table($table, function (Blueprint $table) {
            $table->string('account_code')->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = config('database.default') === 'sqlite' ? 'bank_accounts' : 'master_data.bank_accounts';

        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn('account_code');
        });
    }
};
