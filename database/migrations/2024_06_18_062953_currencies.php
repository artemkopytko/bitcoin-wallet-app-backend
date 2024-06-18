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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('widget_code')->unique();
            $table->integer('type')->default(0);
            $table->string('name')->index();
            $table->decimal('price', 16, 8)->default(0)->index();
            $table->decimal('ask', 16, 8)->default(0)->index();
            $table->decimal('bid', 16, 8)->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
