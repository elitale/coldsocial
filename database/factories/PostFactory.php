<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
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
            'update_id' => null,
            'platform' => 'linkedin',
            'status' => PostStatus::Draft,
            'body' => fake()->paragraphs(2, true),
        ];
    }

    /**
     * Mark the draft as approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => ['status' => PostStatus::Approved]);
    }
}
