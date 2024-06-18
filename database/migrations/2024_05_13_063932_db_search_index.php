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
        // This migration sets index to all searchable columns in the database

        Schema::table('deposits', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('wallet_id');
            $table->index('staff_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->index('user_id');
        });


        Schema::table('users', function (Blueprint $table) {
            $table->index('email');
            $table->index('first_name');
            $table->index('last_name');
            $table->index('is_active');
            $table->index('is_2fa_enabled');
            $table->index('email_verified_at');
            $table->index('balance');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->index('name');
            $table->index('address');
            $table->index('type');
            $table->index('created_at');
            $table->index('updated_at');
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status');
            $table->index('created_at');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['staff_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['read_by_admin_at']);
            $table->dropIndex(['read_by_user_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['first_name']);
            $table->dropIndex(['last_name']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['is_2fa_enabled']);
            $table->dropIndex(['email_verified_at']);
            $table->dropIndex(['balance']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('wallets', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['address']);
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
        });
    }
};
