<?php

namespace Database\Factories;

use App\Enums\AiCapability;
use App\Models\AiModel;
use App\Models\AiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiModel>
 */
class AiModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ai_provider_id' => AiProvider::factory(),
            'identifier' => fake()->randomElement(['gpt-4o', 'gpt-4o-mini', 'o3-mini', 'claude-3-7-sonnet']),
            'label' => null,
            'capability' => AiCapability::Text,
            'enabled' => true,
            'is_default' => false,
            'settings' => null,
        ];
    }

    /**
     * Mark the model as the default for its capability.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes): array => ['is_default' => true]);
    }

    /**
     * Set the model's capability.
     */
    public function capability(AiCapability $capability): static
    {
        return $this->state(fn (array $attributes): array => ['capability' => $capability]);
    }
}
