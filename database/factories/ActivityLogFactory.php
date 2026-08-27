<?php

namespace Database\Factories;

use App\Enums\ActivityLog as ActivityLogEnum;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
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
            'action' => fake()->randomElement(ActivityLogEnum::values()),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function forProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => Project::class,
        ]);
    }

    public function forTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => Task::class,
        ]);
    }

    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => User::class,
        ]);
    }

    public function forOrganization(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => Organization::class,
        ]);
    }
}
