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
        Schema::table('users', function (Blueprint $table) {
            // Add columns
            $table->string('last_name');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->softDeletes(); // Adds the deleted_at column for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_name');
            $table->dropColumn('is_active');
            $table->dropColumn('notes');
            $table->dropSoftDeletes();
        });
    }
};
