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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();

            // Single-choice / free-text dimensions.
            $table->string('primary_goal')->nullable();
            $table->string('headline')->nullable();
            $table->string('industry')->nullable();
            $table->string('experience_level')->nullable();
            $table->string('company')->nullable();
            $table->string('location')->nullable();
            $table->string('personality_archetype')->nullable();
            $table->string('emoji_usage')->nullable();
            $table->string('formality')->nullable();
            $table->string('political_stance')->nullable();
            $table->string('political_leaning')->nullable();
            $table->string('controversy_comfort')->nullable();
            $table->string('primary_platform')->nullable();
            $table->string('posting_frequency')->nullable();
            $table->text('audience_note')->nullable();
            $table->text('dislikes')->nullable();
            $table->text('bio')->nullable();

            // Multi-value dimensions (arrays / maps).
            $table->json('languages')->nullable();
            $table->json('audiences')->nullable();
            $table->json('tones')->nullable();
            $table->json('interests')->nullable();
            $table->json('content_pillars')->nullable();
            $table->json('likes')->nullable();
            $table->json('causes')->nullable();
            $table->json('content_formats')->nullable();
            $table->json('focus_platforms')->nullable();
            $table->json('social_links')->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
