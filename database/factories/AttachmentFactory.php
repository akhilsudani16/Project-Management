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
            'attachable_type' => fake()->randomElement([Project::class, Task::class, Comment::class, User::class]),
            'attachable_id' => function (array $attributes) {
                return $attributes['attachable_type']::factory()->create()->id;
            },
            'user_id' => User::factory(),
            'path' => 'attachments/'.fake()->uuid().'.'.fake()->fileExtension(),
        ];
    }

    public function forProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => Project::class,
            'attachable_id' => Project::factory()->create()->id,
        ]);
    }

    public function forTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => Task::class,
            'attachable_id' => Task::factory()->create()->id,
        ]);
    }

    public function forComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => Comment::class,
            'attachable_id' => Comment::factory()->create()->id,
        ]);
    }

    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachable_type' => User::class,
            'attachable_id' => User::factory()->create()->id,
        ]);
    }
}
