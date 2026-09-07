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
            'targetable_type' => null,
            'targetable_id' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }

    public function forProject(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => Project::class,
            'action' => fake()->randomElement([
                ActivityLogEnum::CREATED_PROJECT->value,
                ActivityLogEnum::UPDATED_PROJECT->value,
                ActivityLogEnum::PROJECT_ARCHIVED->value,
                ActivityLogEnum::PROJECT_RESTORED->value,
            ]),
        ]);
    }

    public function forTask(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => Task::class,
            'action' => fake()->randomElement([
                ActivityLogEnum::CREATED_TASK->value,
                ActivityLogEnum::UPDATED_TASK->value,
                ActivityLogEnum::COMPLETED_TASK->value,
                ActivityLogEnum::TASK_ASSIGNED->value,
            ]),
        ]);
    }

    public function forUser(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => User::class,
            'action' => fake()->randomElement([
                ActivityLogEnum::LOGIN->value,
                ActivityLogEnum::LOGOUT->value,
                ActivityLogEnum::PASSWORD_CHANGED->value,
                ActivityLogEnum::SESSION_REVOKED->value,
            ]),
        ]);
    }

    public function forOrganization(): static
    {
        return $this->state(fn (array $attributes) => [
            'targetable_type' => Organization::class,
            'action' => fake()->randomElement([
                ActivityLogEnum::ORGANIZATION_CREATED->value,
                ActivityLogEnum::ORGANIZATION_ARCHIVED->value,
                ActivityLogEnum::MEMBERSHIP_CHANGED->value,
            ]),
        ]);
    }
}
