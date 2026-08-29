<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\PlatformConnection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformConnection>
 */
class PlatformConnectionFactory extends Factory
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
            'platform' => SocialPlatform::Linkedin,
            'external_id' => fake()->uuid(),
            'display_name' => fake()->name(),
            'avatar_url' => fake()->imageUrl(),
            'access_token' => 'test-access-token',
            'refresh_token' => 'test-refresh-token',
            'expires_at' => now()->addHour(),
            'scopes' => 'openid profile email w_member_social',
        ];
    }
}
