<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement(TaskStatus::values()),
            'priority' => fake()->randomElement(TaskPriority::values()),
            'project_id' => Project::inRandomOrder()->first()?->id ?? Project::factory(),
            'user_id' => User::query()->where('role_id', Role::query()->where('name', 'member')->first()?->id)->inRandomOrder()->first()?->id,
            'created_by' => User::query()->where('role_id', Role::query()->where('name', 'project_manager')->first()?->id)->inRandomOrder()->first()?->id,
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
        ];
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
            'deleted_by' => User::query()->where('role_id', Role::query()->where('name', 'super_admin')->first()?->id)->inRandomOrder()->first()?->id,
        ]);
    }
    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function todo(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::TODO->value,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::DONE->value,
        ]);
    }

    public function lowPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::LOW->value,
        ]);
    }

    public function mediumPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::MEDIUM->value,
        ]);
    }

    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::HIGH->value,
        ]);
    }

    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => TaskPriority::URGENT->value,
        ]);
    }

    public function review(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TaskStatus::REVIEW->value,
        ]);
    }
}
