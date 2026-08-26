<?php

namespace Database\Factories;

use App\Models\AiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiProvider>
 */
class AiProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
            'slug' => fake()->unique()->slug(2),
            'driver' => 'openai',
            'base_url' => null,
            'api_key' => 'sk-test-'.fake()->sha256(),
            'enabled' => true,
            'settings' => null,
        ];
    }
}
