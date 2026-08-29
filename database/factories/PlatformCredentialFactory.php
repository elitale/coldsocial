<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\PlatformCredential;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlatformCredential>
 */
class PlatformCredentialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'platform' => SocialPlatform::Linkedin,
            'client_id' => fake()->uuid(),
            'client_secret' => 'test-client-secret',
            'redirect_url' => 'https://coldsocial.test/connections/linkedin/callback',
            'enabled' => true,
            'last_tested_at' => null,
            'test_passed' => null,
            'test_message' => null,
        ];
    }

    /**
     * A disabled credential.
     */
    public function disabled(): static
    {
        return $this->state(['enabled' => false]);
    }
}
