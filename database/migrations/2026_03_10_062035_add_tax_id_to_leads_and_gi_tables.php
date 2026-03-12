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
        $taxTable = config('database.default') === 'sqlite' ? 'taxes' : 'master_data.taxes';

        Schema::table(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads', function (Blueprint $table) use ($taxTable) {
            $table->foreignUuid('tax_id')->nullable()->constrained($taxTable)->nullOnDelete();
        });

        Schema::table(config('database.default') === 'sqlite' ? 'general_informations' : 'crm.general_informations', function (Blueprint $table) use ($taxTable) {
            $table->foreignUuid('tax_id')->nullable()->constrained($taxTable)->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('database.default') === 'sqlite' ? 'leads' : 'crm.leads', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn('tax_id');
        });

        Schema::table(config('database.default') === 'sqlite' ? 'general_informations' : 'crm.general_informations', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->dropColumn('tax_id');
        });
    }
};
