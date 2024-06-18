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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // User who made the deposit
            $table->unsignedBigInteger('wallet_id')->nullable(); // Wallet where the deposit was made
            $table->unsignedBigInteger('staff_id')->nullable(); // Staff who added the deposit
            $table->decimal('amount')->unsigned();
            $table->integer('status')->default(0); // 0 - new, 1 - approved, 2 - declined
            $table->string('note')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('staff_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
