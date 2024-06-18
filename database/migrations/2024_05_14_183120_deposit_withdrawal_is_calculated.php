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
        // add is_calculated column to deposits and withdrawals
        Schema::table('deposits', function (Blueprint $table) {
            $table->boolean('is_calculated')->default(false);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->boolean('is_calculated')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn('is_calculated');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('is_calculated');
        });
    }
};
