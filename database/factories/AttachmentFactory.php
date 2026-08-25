<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attachment>
 */
class AttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id ?? User::factory(),
            'path' => 'attachments/'.fake()->uuid().'.'.fake()->fileExtension(),
        ];
    }

    public function forProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => Project::class,
        ]);
    }

    public function forTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => Task::class,
        ]);
    }

    public function forComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => Comment::class,
        ]);
    }

    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => User::class,
        ]);
    }
}
