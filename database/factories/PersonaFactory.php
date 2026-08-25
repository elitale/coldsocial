<?php

namespace Database\Factories;

use App\Models\Persona;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Persona>
 */
class PersonaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'primary_goal' => 'entrepreneur',
            'headline' => fake()->jobTitle(),
            'industry' => 'saas',
            'experience_level' => 'founder',
            'company' => fake()->company(),
            'location' => fake()->city(),
            'personality_archetype' => 'expert',
            'emoji_usage' => 'minimal',
            'formality' => 'balanced',
            'political_stance' => 'apolitical',
            'political_leaning' => 'prefer_not_to_say',
            'controversy_comfort' => 'cautious',
            'primary_platform' => 'linkedin',
            'posting_frequency' => 'few_times_week',
            'audience_note' => fake()->sentence(),
            'dislikes' => fake()->sentence(),
            'bio' => fake()->paragraph(),
            'languages' => ['english'],
            'audiences' => ['founders', 'developers'],
            'tones' => ['professional', 'friendly'],
            'interests' => ['ai', 'startups', 'saas'],
            'content_pillars' => ['ai', 'startups'],
            'likes' => ['how_to', 'storytelling'],
            'causes' => ['open_source'],
            'content_formats' => ['short_text', 'long_form'],
            'focus_platforms' => ['linkedin', 'x'],
            'social_links' => ['linkedin' => 'https://www.linkedin.com/in/'.fake()->userName()],
            'completed_at' => now(),
        ];
    }
}
