<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Organization;
use App\Models\Project;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
            'description' => fake()->paragraph(),
            'status' => ProjectStatus::ACTIVE->value,
            'organization_id' => Organization::inRandomOrder()->first()->id ?? Organization::factory(),
            'created_by' => Role::query()->where('name', 'super_admin')->first()?->users()->first()?->getKey(),
            'start_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'end_date' => fake()->dateTimeBetween('now', '+6 months'),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::DRAFT->value,
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::ACTIVE->value,
        ]);
    }

    public function onHold(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::ON_HOLD->value,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::COMPLETED->value,
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::ARCHIVED->value,
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::DELETED->value,
            'deleted_at' => now(),
            'deleted_by' => Role::query()->where('name', 'super_admin')->first()?->users()->first()?->getKey(),
        ]);
    }
}
