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
        Schema::table('platform_credentials', function (Blueprint $table) {
            $table->boolean('enabled')->default(true)->after('redirect_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_credentials', function (Blueprint $table) {
            $table->dropColumn('enabled');
        });
    }
};
