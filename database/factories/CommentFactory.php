<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory()->create()->getKey(),
            'body' => fake()->paragraph(),
        ];
    }

    public function forProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => Project::class,
        ]);
    }

    public function forTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'commentable_type' => Task::class,
        ]);
    }
}
