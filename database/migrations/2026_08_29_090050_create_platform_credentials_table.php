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
        Schema::create('platform_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->unique();
            $table->string('client_id');
            $table->text('client_secret');
            $table->string('redirect_url')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->boolean('test_passed')->nullable();
            $table->string('test_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_credentials');
    }
};
