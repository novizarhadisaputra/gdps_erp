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
        $tableName = config('database.default') === 'sqlite' ? 'journal_entries' : 'finance.journal_entries';

        Schema::create($tableName, function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('number')->unique(); // Journal Voucher Number
            $table->integer('sequence_number')->default(0);
            $table->integer('revision_number')->default(0);
            $table->integer('year')->nullable();
            $table->date('date');
            $table->text('description')->nullable();
            $table->nullableUuidMorphs('reference'); // Reference to the triggering document (e.g. AccrueRevenue)
            $table->decimal('total_amount', 20, 2)->default(0);
            $table->string('status')->default('draft'); // draft, posted, canceled
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('database.default') === 'sqlite' ? 'journal_entries' : 'finance.journal_entries');
    }
};
